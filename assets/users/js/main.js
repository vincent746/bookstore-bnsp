
function selectCat(el, name, type) {
    document.getElementById('selectedCat').textContent = name;

    const kategoriInput = document.getElementById('filterKategori');
    const kotaInput = document.getElementById('filterKota');

    if (!kategoriInput) {
        return false;
    }

    if (type === 'kategori') {
        kategoriInput.value = name === 'Semua Kategori' ? '' : name;
        if (kotaInput) {
            kotaInput.value = '';
        }
    } else if (type === 'kota' && kotaInput) {
        kotaInput.value = name;
        kategoriInput.value = '';
    } else {
        kategoriInput.value = '';
        if (kotaInput) {
            kotaInput.value = '';
        }
    }

    return false;
}

function toggleFilter(btn) {
    btn.classList.toggle('active');
}

function selectTicket(btn) {
    document.querySelectorAll('.ticket-type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

const BOOKING_MAX_PER_TX = 5;
const CART_STORAGE_KEY = 'bookstore_cart_v1';

function formatRupiah(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
}

function loadCartItems() {
    try {
        var raw = localStorage.getItem(CART_STORAGE_KEY);
        var data = raw ? JSON.parse(raw) : [];
        return Array.isArray(data) ? data : [];
    } catch (e) {
        return [];
    }
}

function saveCartItems(items) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
    updateCartBadge();
    renderCartModal();
}

function cartItemPayload(line) {
    return {
        id_buku: line.id,
        qty: line.qty,
    };
}

function lineFromWishlistButton(btn) {
    var d = btn.dataset;
    return {
        id: parseInt(d.eventId, 10) || 0,
        title: d.eventTitle || '',
        harga: parseFloat(d.eventHarga) || 0,
        kuota: parseInt(d.eventKuota, 10) || 0,
        qty: 1,
        date: d.eventDate || '',
        location: d.eventLocation || '',
        icon: d.eventIcon || '📚',
    };
}

function maxQtyForLine(line) {
    var kuota = Math.max(0, parseInt(line.kuota, 10) || 0);
    return Math.min(BOOKING_MAX_PER_TX, kuota);
}

function addWishlistToCart(btn) {
    var line = lineFromWishlistButton(btn);
    if (line.id <= 0) {
        return;
    }

    if (line.kuota < 1) {
        alert('Maaf, stok buku ini sudah habis.');
        return;
    }

    var items = loadCartItems();
    var found = items.findIndex(function (x) {
        return x.id === line.id;
    });

    if (found === -1) {
        items.push(line);
    } else {
        var nextQty = items[found].qty + 1;
        var cap = maxQtyForLine(items[found]);
        if (nextQty > cap) {
            if (items[found].kuota < BOOKING_MAX_PER_TX) {
                alert('Stok tersisa ' + items[found].kuota + ' untuk buku ini.');
            } else {
                alert('Maksimal ' + BOOKING_MAX_PER_TX + ' buku per judul di keranjang.');
            }
            return;
        }
        items[found].qty = nextQty;
    }

    saveCartItems(items);
    showCartToast('Ditambahkan ke keranjang');
}

function updateCartBadge() {
    var badge = document.getElementById('navCartBadge');
    if (!badge) {
        return;
    }
    var items = loadCartItems();
    var totalQty = items.reduce(function (s, x) {
        return s + (parseInt(x.qty, 10) || 0);
    }, 0);
    if (totalQty > 0) {
        badge.textContent = totalQty > 99 ? '99+' : String(totalQty);
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function renderCartModal() {
    var wrap = document.getElementById('cartLines');
    var emptyEl = document.getElementById('cartEmptyState');
    var totalEl = document.getElementById('cartGrandTotal');
    if (!wrap || !emptyEl || !totalEl) {
        return;
    }

    var items = loadCartItems();
    wrap.innerHTML = '';

    if (items.length === 0) {
        emptyEl.classList.remove('d-none');
        totalEl.textContent = formatRupiah(0);
        return;
    }

    emptyEl.classList.add('d-none');

    var grand = 0;

    items.forEach(function (line, idx) {
        var sub = (parseFloat(line.harga) || 0) * (parseInt(line.qty, 10) || 0);
        grand += sub;

        var row = document.createElement('div');
        row.className = 'cart-line-card';
        row.innerHTML =
            '<div style="flex:1; min-width:200px;">' +
            '<div class="cart-line-title">' + escapeHtml(line.title || '-') + '</div>' +
            '<div class="cart-line-meta"><i class="bi bi-calendar3"></i> ' + escapeHtml(line.date || '-') + '</div>' +
            '<div class="cart-line-meta"><i class="bi bi-person-lines-fill"></i> ' + escapeHtml(line.location || '-') + '</div>' +
            '<div class="cart-line-price mt-1">' + formatRupiah(line.harga) + ' × ' + line.qty + ' = ' + formatRupiah(sub) + '</div>' +
            '</div>' +
            '<div class="cart-line-actions">' +
            '<div class="qty-control">' +
            '<button type="button" class="qty-btn cart-qty-minus" data-idx="' + idx + '">−</button>' +
            '<div class="qty-value">' + line.qty + '</div>' +
            '<button type="button" class="qty-btn cart-qty-plus" data-idx="' + idx + '">+</button>' +
            '</div>' +
            '<button type="button" class="btn-cancel cart-remove" data-idx="' + idx + '" style="padding:6px 12px; font-size:0.78rem;">Hapus</button>' +
            '</div>';

        wrap.appendChild(row);
    });

    totalEl.textContent = formatRupiah(grand);

    wrap.querySelectorAll('.cart-qty-minus').forEach(function (b) {
        b.addEventListener('click', function () {
            var i = parseInt(b.getAttribute('data-idx'), 10);
            cartChangeQty(i, -1);
        });
    });
    wrap.querySelectorAll('.cart-qty-plus').forEach(function (b) {
        b.addEventListener('click', function () {
            var i = parseInt(b.getAttribute('data-idx'), 10);
            cartChangeQty(i, 1);
        });
    });
    wrap.querySelectorAll('.cart-remove').forEach(function (b) {
        b.addEventListener('click', function () {
            var i = parseInt(b.getAttribute('data-idx'), 10);
            cartRemoveLine(i);
        });
    });
}

function escapeHtml(s) {
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}

function cartChangeQty(index, delta) {
    var items = loadCartItems();
    if (!items[index]) {
        return;
    }
    var line = items[index];
    var cap = maxQtyForLine(line);
    var q = (parseInt(line.qty, 10) || 1) + delta;
    if (q < 1) {
        items.splice(index, 1);
    } else if (q > cap) {
        alert(cap < 1 ? 'Stok habis.' : 'Maksimal ' + cap + ' untuk buku ini.');
        return;
    } else {
        items[index].qty = q;
    }
    saveCartItems(items);
}

function cartRemoveLine(index) {
    var items = loadCartItems();
    if (items[index]) {
        items.splice(index, 1);
        saveCartItems(items);
    }
}

function showCartToast(message) {
    var container = document.getElementById('cartToastContainer');
    if (!container || typeof bootstrap === 'undefined') {
        return;
    }
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-bg-dark border border-secondary';
    el.setAttribute('role', 'alert');
    el.innerHTML =
        '<div class="d-flex">' +
        '<div class="toast-body py-2"><i class="bi bi-heart-fill text-danger me-2"></i>' + escapeHtml(message) + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div>';
    container.appendChild(el);
    var toast = new bootstrap.Toast(el, { delay: 2600 });
    toast.show();
    el.addEventListener('hidden.bs.toast', function () {
        el.remove();
    });
}

function submitCartCheckout() {
    var items = loadCartItems();
    if (items.length === 0) {
        return;
    }

    if (!window.USER_LOGGED_IN) {
        showCartToast('Silakan login untuk checkout');
        var loginModal = document.getElementById('loginModal');
        if (loginModal) {
            bootstrap.Modal.getOrCreateInstance(loginModal).show();
        }
        return;
    }

    var payload = items.map(cartItemPayload);
    var yakin = window.confirm('Buat pesanan untuk semua item di keranjang?');
    if (!yakin) {
        return;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = window.USER_PROSES_URL || '/users/proses.php';

    form.appendChild(hiddenInput('action', 'book_cart'));
    form.appendChild(hiddenInput('cart_json', JSON.stringify(payload)));
    form.appendChild(hiddenInput('redirect', window.location.href.split('#')[0]));

    document.body.appendChild(form);
    form.submit();
}

function hiddenInput(name, value) {
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
}

document.querySelectorAll('.btn-wishlist-trigger').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        addWishlistToCart(this);
    });
});

var cartModalEl = document.getElementById('cartModal');
if (cartModalEl) {
    cartModalEl.addEventListener('show.bs.modal', function (ev) {
        var errBox = document.getElementById('cartCheckoutError');
        if (errBox && ev.relatedTarget) {
            errBox.textContent = '';
            errBox.classList.add('d-none');
        }
        renderCartModal();
    });
}

var cartCheckoutBtn = document.getElementById('cartCheckoutBtn');
if (cartCheckoutBtn) {
    cartCheckoutBtn.addEventListener('click', submitCartCheckout);
}

document.addEventListener('DOMContentLoaded', function () {
    updateCartBadge();
    renderCartModal();

    try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('open_cart') === '1') {
            var cartModal = document.getElementById('cartModal');
            if (cartModal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(cartModal).show();
            }
            var u = new URL(window.location.href);
            u.searchParams.delete('open_cart');
            var qs = u.searchParams.toString();
            window.history.replaceState({}, '', u.pathname + (qs ? '?' + qs : '') + u.hash);
        }
    } catch (e) {}
});

function showFileName(input) {
    if (input.files.length > 0) {
        const fn = document.getElementById('fileName');
        fn.style.display = 'block';
        fn.querySelector('span').textContent = input.files[0].name;
    }
}

// Navbar active state
const navLinks = document.querySelectorAll('.nav-pill .nav-link');

function setActiveNavLink(activeLink) {
    navLinks.forEach(link => link.classList.remove('active'));
    activeLink.classList.add('active');
}

function syncActiveNavLink() {
    const currentHash = window.location.hash || '#';
    const activeLink = Array.from(navLinks).find(link => link.getAttribute('href') === currentHash);

    if (activeLink) {
        setActiveNavLink(activeLink);
    }
}

navLinks.forEach(link => {
    link.addEventListener('click', function () {
        setActiveNavLink(this);
    });
});

syncActiveNavLink();
window.addEventListener('hashchange', syncActiveNavLink);

// Event detail modal
const eventDetailModalEl = document.getElementById('eventDetailModal');
const eventDetailModal = eventDetailModalEl ? new bootstrap.Modal(eventDetailModalEl) : null;
const detailEventTitle = document.getElementById('detailEventTitle');
const detailEventCategory = document.getElementById('detailEventCategory');
const detailEventDateTime = document.getElementById('detailEventDateTime');
const detailEventLocation = document.getElementById('detailEventLocation');
const detailEventDescription = document.getElementById('detailEventDescription');
const detailEventImage = document.getElementById('detailEventImage');
const detailEventImageIcon = document.getElementById('detailEventImageIcon');

function openEventDetail(card) {
    if (!eventDetailModal) {
        return;
    }

    detailEventTitle.textContent = card.dataset.eventTitle;
    detailEventCategory.textContent = card.dataset.eventCategory;
    detailEventDateTime.textContent = card.dataset.eventDatetime;
    detailEventLocation.textContent = card.dataset.eventLocation;
    detailEventDescription.textContent = card.dataset.eventDescription;

    const cardVisual = card.querySelector('.card-img-wrapper');
    const cardIcon = card.querySelector('.card-visual-icon');
    const visualClass = cardVisual
        ? Array.from(cardVisual.classList).find(className => className.startsWith('cat-'))
        : '';
    const eventImage = card.dataset.eventImage || '';

    detailEventImage.className = `event-detail-image ${visualClass || ''}`.trim();
    detailEventImage.style.backgroundImage = eventImage ? `url('${eventImage}')` : '';
    detailEventImage.style.backgroundSize = eventImage ? 'cover' : '';
    detailEventImage.style.backgroundPosition = eventImage ? 'center' : '';

    if (detailEventImageIcon) {
        detailEventImageIcon.textContent = cardIcon ? cardIcon.textContent : '📚';
        detailEventImageIcon.style.display = eventImage ? 'none' : '';
    }

    eventDetailModal.show();
}

document.querySelectorAll('.event-card[data-event-title]').forEach(card => {
    card.addEventListener('click', function (event) {
        if (event.target.closest('button, a')) {
            return;
        }

        openEventDetail(this);
    });

    card.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openEventDetail(this);
        }
    });
});

// Animate cards on scroll
const cards = document.querySelectorAll('.event-card, .ticket-card, .featured-card');
const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.style.opacity = '1';
            e.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });
cards.forEach(c => {
    c.style.opacity = '0';
    c.style.transform = 'translateY(20px)';
    c.style.transition = 'opacity 0.5s ease, transform 0.5s ease, box-shadow 0.25s, border-color 0.25s';
    obs.observe(c);
});

// Pesanan Saya — filter tab
const tiketTabs = document.getElementById('tiketTabs');

if (tiketTabs) {
    tiketTabs.querySelectorAll('.section-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const filter = this.dataset.ticketFilter || 'semua';

            tiketTabs.querySelectorAll('.section-tab').forEach(function (t) {
                t.classList.remove('active');
            });
            this.classList.add('active');

            document.querySelectorAll('.ticket-card-item').forEach(function (card) {
                const group = card.dataset.ticketGroup || '';
                const show = filter === 'semua' || group === filter;
                card.style.display = show ? '' : 'none';
            });
        });
    });
}

// Upload bukti modal
const uploadEventName = document.getElementById('uploadEventName');
const uploadOrderInfo = document.getElementById('uploadOrderInfo');
const uploadIdHeader = document.getElementById('uploadIdHeader');

function openUploadModal(trigger) {
    const data = trigger.dataset;
    const uploadForm = document.getElementById('uploadBuktiForm');
    const fileInput = document.getElementById('fileInput');
    const fileNameEl = document.getElementById('fileName');

    if (uploadEventName) {
        uploadEventName.textContent = data.eventName || '-';
    }
    if (uploadOrderInfo) {
        uploadOrderInfo.textContent = 'Order #' + (data.kode || '-') + ' · ' + formatRupiah(data.total || 0);
    }
    if (uploadIdHeader) {
        uploadIdHeader.value = data.idHeader || '';
    }
    if (uploadForm) {
        uploadForm.reset();
        if (uploadIdHeader) {
            uploadIdHeader.value = data.idHeader || '';
        }
    }
    if (fileNameEl) {
        fileNameEl.style.display = 'none';
        const span = fileNameEl.querySelector('span');
        if (span) {
            span.textContent = '';
        }
    }
    if (fileInput) {
        fileInput.value = '';
    }
}

document.querySelectorAll('.btn-upload-bukti').forEach(function (btn) {
    btn.addEventListener('click', function () {
        openUploadModal(this);
    });
});

// Filter halaman semua buku (semua-buku)
(function initAllEventsFilter() {
    const grid = document.getElementById('allEventsGrid');
    const searchInput = document.getElementById('eventSearch');
    const categoryButtons = document.querySelectorAll('.event-category-filter');
    const resultCount = document.getElementById('eventResultCount');
    const noResult = document.getElementById('eventNoResult');

    if (!grid) {
        return;
    }

    const items = grid.querySelectorAll('.event-list-item');
    let activeCategory = (window.ALL_EVENTS_FILTER && window.ALL_EVENTS_FILTER.initialCategory) || '';

    function applyAllEventsFilter() {
        const query = (searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
        let visible = 0;

        items.forEach(function (item) {
            const category = item.dataset.category || '';
            const haystack = item.dataset.searchText || '';
            const matchCategory = !activeCategory || category === activeCategory;
            const matchSearch = !query || haystack.indexOf(query) !== -1;
            const show = matchCategory && matchSearch;

            item.style.display = show ? '' : 'none';
            if (show) {
                visible += 1;
            }
        });

        if (resultCount) {
            resultCount.textContent = String(visible);
        }
        if (noResult) {
            noResult.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    categoryButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeCategory = this.dataset.category || '';
            categoryButtons.forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            applyAllEventsFilter();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyAllEventsFilter);
    }

    if (window.ALL_EVENTS_FILTER && window.ALL_EVENTS_FILTER.initialSearch && searchInput) {
        searchInput.value = window.ALL_EVENTS_FILTER.initialSearch;
    }

    applyAllEventsFilter();
})();

document.querySelectorAll('.btn-cancel-transaksi').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const idHeader = this.dataset.idHeader || '';

        if (!idHeader) {
            return;
        }

        const yakin = confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?');
        if (!yakin) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.USER_PROSES_URL || '/users/proses.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'cancel_transaksi';
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_header';
        idInput.value = idHeader;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    });
});

document.querySelectorAll('.btn-konfirmasi-terima').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var idHeader = this.dataset.idHeader || '';

        if (!idHeader) {
            return;
        }

        var yakin = window.confirm('Konfirmasi bahwa paket sudah sampai di tujuan Anda?');
        if (!yakin) {
            return;
        }

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = window.USER_PROSES_URL || '/users/proses.php';

        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'konfirmasi_terima_paket';
        form.appendChild(actionInput);

        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_header';
        idInput.value = idHeader;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    });
});
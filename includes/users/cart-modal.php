<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$booking_error = $booking_error ?? '';
?>
<div class="toast-container position-fixed top-0 end-0 p-3 pt-5" id="cartToastContainer" style="z-index: 1090;"></div>

<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-custom">
            <div class="modal-header-custom d-flex align-items-center justify-content-between">
                <div class="modal-title-custom" id="cartModalLabel"><i class="bi bi-cart3 me-2"></i>Keranjang</div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div id="cartCheckoutError"
                class="alert alert-danger py-2 px-3 mx-3 mt-3 mb-0 d-none"
                style="font-size:0.85rem;"
                role="alert"></div>
            <div class="modal-body-custom">
                <div id="cartEmptyState" class="text-center py-4" style="color:var(--clr-muted); font-size:0.9rem;">
                    Keranjang masih kosong. Tambahkan buku dari tombol <strong>Wishlist</strong>.
                </div>
                <div id="cartLines" class="d-flex flex-column gap-3"></div>
            </div>
            <div class="modal-body-custom pt-0 border-top border-secondary border-opacity-25">
                <div class="cart-cod-notice mb-3" role="region" aria-label="Informasi pembayaran COD">
                    <div class="cart-cod-notice-icon" aria-hidden="true"><i class="bi bi-truck"></i></div>
                    <div>
                        <div class="cart-cod-notice-title">Payment At Delivery (COD)</div>
                        <p class="cart-cod-notice-text">
                            Bayar tunai saat paket tiba di tujuan. Setelah membuat pesanan, cek status di
                            <strong>Pesanan Saya</strong> dan siapkan nominal sesuai total estimasi di bawah.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span style="font-weight:600; color:var(--clr-text);">Total estimasi</span>
                    <span id="cartGrandTotal" style="font-family:'Syne',sans-serif; font-weight:800; font-size:1.15rem; color:var(--clr-accent);">Rp 0</span>
                </div>
                <button type="button" class="btn-confirm w-100" id="cartCheckoutBtn">
                    <i class="bi bi-bag-check-fill me-1"></i> Checkout &amp; buat pesanan
                </button>
                <p class="mb-0 mt-2" style="font-size:0.75rem; color:var(--clr-muted); text-align:center;">
                    Satu kode transaksi untuk semua item di keranjang. Maks. 5 buku per judul.
                </p>
            </div>
        </div>
    </div>
</div>

<?php if ($booking_error !== ''): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var msg = <?= json_encode($booking_error, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var cartModal = document.getElementById('cartModal');
    var box = document.getElementById('cartCheckoutError');
    if (box && msg) {
        box.textContent = msg;
        box.classList.remove('d-none');
    }
    if (cartModal && msg && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(cartModal).show();
    }
});
</script>
<?php endif; ?>

<?php

require_once __DIR__ . '/includes/users/init.php';
require_once __DIR__ . '/config/database.php';

$search_q = trim($_GET['q'] ?? '');
$filter_kategori = trim($_GET['kategori'] ?? '');
$kategori_list = get_kategori_options($conn, true);
$event_list = get_user_events($conn, $search_q, $filter_kategori, '', 7);
$featured_event = $event_list[0] ?? null;
$grid_events = array_slice($event_list, 1);
$selected_filter_label = user_search_label($filter_kategori, '');
$user_tickets = [];

if (is_user_logged_in()) {
    $user_tickets = get_user_tickets($conn, (int) ($_SESSION['user_id'] ?? 0));
}

$booking_error = $_SESSION['user_booking_error'] ?? '';
unset($_SESSION['user_booking_error']);

$contact_success = $_SESSION['user_contact_success'] ?? '';
unset($_SESSION['user_contact_success']);

$contact_error = $_SESSION['user_contact_error'] ?? '';
unset($_SESSION['user_contact_error']);

$contact_old = $_SESSION['user_contact_old'] ?? null;
unset($_SESSION['user_contact_old']);

if ($contact_old === null) {
    $contact_old = [
        'nama' => is_user_logged_in() ? ($_SESSION['user_nama'] ?? '') : '',
        'email' => is_user_logged_in() ? ($_SESSION['user_email'] ?? '') : '',
        'deskripsi' => '',
    ];
}

$page_title = 'BookStore — Temukan & Pesan Buku';
$active_nav = 'beranda';

require_once USER_INCLUDES . 'layout.php';

?>

  <!-- ══════════════════ HERO ══════════════════ -->
  <section class="hero-section">
    <div class="hero-glow glow-1"></div>
    <div class="hero-glow glow-2"></div>
    <div class="hero-glow glow-3"></div>

    <div class="container position-relative">

      <p class="hero-sub anim-1">
        Cari judul buku, penulis, atau kategori favorit Anda
      </p>

      <!-- SEARCH BAR -->
      <form class="search-wrapper anim-2" method="get" action="<?= HOME_URL ?>#buku" id="searchEventForm">
        <i class="bi bi-search" style="color:var(--clr-muted); font-size:1rem; flex-shrink:0;"></i>
        <input class="search-input" type="text" name="q" id="searchInput"
          placeholder="Cari buku…" value="<?= htmlspecialchars($search_q) ?>" />

        <div class="search-divider"></div>

        <input type="hidden" name="kategori" id="filterKategori" value="<?= htmlspecialchars($filter_kategori) ?>" />

        <!-- Category Dropdown -->
        <div class="dropdown">
          <button class="search-dropdown-btn dropdown-toggle" type="button" id="catDrop" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <span id="selectedCat"><?= htmlspecialchars($selected_filter_label) ?></span>
            <i class="bi bi-chevron-down"></i>
          </button>
          <ul class="dropdown-menu cat-dropdown-menu" aria-labelledby="catDrop">
            <li>
              <a class="cat-dropdown-item" href="#" onclick="return selectCat(this, 'Semua Kategori', 'all')">
                <span class="cat-icon">🌐</span> Semua Kategori
              </a>
            </li>
            <?php foreach ($kategori_list as $kategori_item): ?>
              <?php $nama_kat = $kategori_item['nama_kategori'] ?? ''; ?>
              <li>
                <a class="cat-dropdown-item" href="#"
                  onclick="return selectCat(this, <?= htmlspecialchars(json_encode($nama_kat), ENT_QUOTES) ?>, 'kategori')">
                  <span class="cat-icon"><?= user_event_cat_icon($nama_kat) ?></span>
                  <?= htmlspecialchars($nama_kat) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="search-divider d-none d-sm-block"></div>

        <button type="submit" class="search-btn-main">
          <i class="bi bi-search"></i>
          Cari Buku
        </button>
      </form>

    </div>
  </section>

  <div class="divider"></div>

  <!-- ══════════════════ ABOUT ══════════════════ -->
  <section class="section-gap pb-0 landing-about-wrap">
    <div class="container">
      <div class="landing-about-card">
        <div class="landing-about-inner">
          <div class="landing-about-row">
            <div>
              <div class="landing-about-label">Tentang kami</div>
              <h2 class="landing-about-title">Ruang baca digital untuk penjelajah buku</h2>
              <p class="landing-about-lead">
                BookStore hadir supaya Anda menemukan judul yang tepat tanpa ribet—dari fiksi hingga referensi,
                lengkap dengan harga transparan, stok real time, dan alur pesan sampai bayar yang jelas.
                Kami percaya baca buku harusnya ringan di kantong dan nyaman di pengalaman.
              </p>
            </div>
            <div class="landing-about-grid">
              <div class="landing-about-pill">
                <div class="landing-about-pill-icon" aria-hidden="true">📚</div>
                <div>
                  <h3>Katalog pilihan</h3>
                  <p>Buku aktif dikurasi per kategori; deskripsi, penulis, dan tahun terbit tampil rapi di setiap kartu.</p>
                </div>
              </div>
              <div class="landing-about-pill">
                <div class="landing-about-pill-icon" aria-hidden="true">🔒</div>
                <div>
                  <h3>Pembayaran aman</h3>
                  <p>Checkout sederhana: konfirmasi jumlah, unggah bukti, lalu tim kami verifikasi dengan status transaksi.</p>
                </div>
              </div>
              <div class="landing-about-pill">
                <div class="landing-about-pill-icon" aria-hidden="true">🚚</div>
                <div>
                  <h3>Perjalanan pesanan</h3>
                  <p>Setelah pembayaran dikonfirmasi, tim admin memperbarui status pengiriman sampai pesanan sampai ke Anda.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════════ KATALOG BUKU ══════════════════ -->
  <section class="section-gap" id="buku">
    <div class="container">
      <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
        <div>
          <div class="section-label">⭐ Pilihan Editor</div>
          <h2 class="section-title">Buku Terbaru</h2>
        </div>
      </div>

      <?php require USER_INCLUDES . 'event-list.php'; ?>

      <div class="text-center mt-4">
        <?php
        $all_events_query = [];
        if ($search_q !== '') {
            $all_events_query['q'] = $search_q;
        }
        if ($filter_kategori !== '' && $filter_kategori !== 'Semua Kategori') {
            $all_events_query['kategori'] = $filter_kategori;
        }
        $all_events_url = user_module_url('semua-buku');
        if (!empty($all_events_query)) {
            $all_events_url .= '?' . http_build_query($all_events_query);
        }
        ?>
        <a href="<?= htmlspecialchars($all_events_url) ?>" class="btn-outline-more">
          Lihat Semua Buku <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <?php if (is_user_logged_in()): ?>
  <?php require USER_INCLUDES . 'ticket-list.php'; ?>
  <?php endif; ?>

  <!-- ══════════════════ KONTAK ADMIN ══════════════════ -->
  <section class="section-gap landing-contact-wrap" id="kontak">
    <div class="container">
      <div class="landing-contact-card">
        <div class="landing-contact-inner landing-contact-split">
          <div>
            <div class="landing-contact-label">Hubungi kami</div>
            <h2 class="landing-contact-title">Ada pertanyaan? Tim admin siap membantu</h2>
            <p class="landing-contact-lead">
              Ceritakan kebutuhan Anda—stok buku, status pesanan, atau kerja sama—kami akan membaca setiap pesan
              dan merespons secepatnya melalui email yang Anda cantumkan.
            </p>
          </div>
          <div>
            <?php if ($contact_success !== ''): ?>
              <div class="alert alert-success py-2 px-3 mb-3" style="font-size:0.85rem;">
                <?= htmlspecialchars($contact_success) ?>
              </div>
            <?php endif; ?>
            <?php if ($contact_error !== ''): ?>
              <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.85rem;">
                <?= htmlspecialchars($contact_error) ?>
              </div>
            <?php endif; ?>

            <form class="landing-contact-form" method="post" action="<?= htmlspecialchars(USER_PROSES_URL) ?>" novalidate>
              <input type="hidden" name="action" value="contact_admin" />
              <input type="hidden" name="redirect" value="<?= htmlspecialchars(HOME_URL) ?>" />

              <div class="mb-3">
                <label class="form-label" for="contactNama">Nama</label>
                <input class="form-control" type="text" name="nama" id="contactNama" autocomplete="name" required
                  maxlength="191"
                  value="<?= htmlspecialchars($contact_old['nama'] ?? '') ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="contactEmail">Email</label>
                <input class="form-control" type="email" name="email" id="contactEmail" autocomplete="email" required
                  maxlength="191"
                  value="<?= htmlspecialchars($contact_old['email'] ?? '') ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="contactDeskripsi">Pesan / deskripsi</label>
                <textarea class="form-control" name="deskripsi" id="contactDeskripsi" required
                  maxlength="65535" placeholder="Tulis pertanyaan atau pesan Anda di sini…"><?= htmlspecialchars($contact_old['deskripsi'] ?? '') ?></textarea>
              </div>
              <button type="submit" class="btn btn-contact-submit">
                <i class="bi bi-send-fill me-1"></i> Kirim ke admin
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════════ MODAL: DETAIL BUKU ══════════════════ -->
  <div class="modal fade" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modal-custom">
        <div class="modal-header-custom d-flex align-items-center justify-content-between">
          <div class="modal-title-custom">Detail Buku</div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body-custom">
          <div class="event-detail-photo">
            <span>Sampul</span>
            <div class="event-detail-image" id="detailEventImage">
              <div class="card-visual-pattern"></div>
              <div class="event-detail-image-icon" id="detailEventImageIcon">📚</div>
            </div>
          </div>

          <div class="event-detail-category" id="detailEventCategory">Kategori</div>
          <h3 class="event-detail-title" id="detailEventTitle">Judul Buku</h3>

          <div class="event-detail-grid">
            <div class="event-detail-item">
              <i class="bi bi-calendar3"></i>
              <div>
                <span>Tahun terbit</span>
                <strong id="detailEventDateTime">-</strong>
              </div>
            </div>
            <div class="event-detail-item">
              <i class="bi bi-person-lines-fill"></i>
              <div>
                <span>Penulis &amp; penerbit</span>
                <strong id="detailEventLocation">-</strong>
              </div>
            </div>
          </div>

          <div class="event-detail-description">
            <span>Deskripsi</span>
            <p id="detailEventDescription">-</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════════════ MODAL: UPLOAD ══════════════════ -->
  <div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-custom">
        <div class="modal-header-custom d-flex align-items-center justify-content-between">
          <div class="modal-title-custom">📎 Upload Bukti Pembayaran</div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="post" action="<?= USER_PROSES_URL ?>" enctype="multipart/form-data" id="uploadBuktiForm">
          <input type="hidden" name="action" value="upload_bukti" />
          <input type="hidden" name="id_header" id="uploadIdHeader" value="" />
          <div class="modal-body-custom">
            <div
              style="background:var(--clr-bg); border:1px solid var(--clr-border); border-radius:var(--radius-sm); padding:12px; margin-bottom:20px; font-size:0.83rem; color:var(--clr-muted);">
              <div id="uploadEventName"
                style="font-family:'Syne',sans-serif; font-weight:700; font-size:0.9rem; color:var(--clr-text); margin-bottom:4px;">
                -</div>
              <span id="uploadOrderInfo">Order #- &nbsp;·&nbsp; Rp 0</span>
            </div>

            <label class="form-label-custom">Bukti Transfer / Screenshot</label>
          <div class="upload-zone mb-3" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
            <div class="upload-title">Seret & Lepas File di Sini</div>
            <div class="upload-sub">atau klik untuk pilih file</div>
            <div class="upload-sub mt-1">JPG, PNG, PDF — Maks. 5MB</div>
            <button type="button" class="btn-upload"
              onclick="event.stopPropagation(); document.getElementById('fileInput').click();">Pilih File</button>
            <input type="file" name="bukti" id="fileInput" accept=".jpg, .png, .jpeg"
              style="display:none;" required onchange="showFileName(this)" />
          </div>
          <div id="fileName" style="font-size:0.82rem; color:var(--clr-accent); display:none; margin-bottom:16px;"><i
              class="bi bi-check-circle-fill"></i> <span></span></div>

          <label class="form-label-custom" for="uploadCatatan">Catatan (opsional)</label>
          <textarea class="form-control-custom" id="uploadCatatan" name="catatan" rows="3"
            placeholder="Mis: Transfer dari BCA a/n Budi Santoso…" style="resize:none;"></textarea>

          <button type="submit" class="btn-confirm mt-4 w-100">
            <i class="bi bi-upload"></i> Upload & Konfirmasi
          </button>
        </div>
        </form>
      </div>
    </div>
  </div>
<?php
require_once USER_INCLUDES . 'login.php';
require_once USER_INCLUDES . 'register.php';
require_once USER_INCLUDES . 'footer.php';


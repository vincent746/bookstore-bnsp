<?php

require_once __DIR__ . '/../../../includes/users/init.php';
require_once __DIR__ . '/../../../config/database.php';

$search_q = trim($_GET['q'] ?? '');
$filter_kategori = trim($_GET['kategori'] ?? '');
$kategori_list = get_kategori_options($conn, true);
$kategori_counts = get_user_kategori_buku_counts($conn);
$event_list = get_user_buku($conn, '', '', 0);
$total_events = (int) ($kategori_counts['total'] ?? count($event_list));
$active_kategori_slug = $filter_kategori !== '' ? user_kategori_slug($filter_kategori) : '';

$page_title = 'BookStore — Semua Buku';
$active_nav = 'buku';

$booking_error = $_SESSION['user_booking_error'] ?? '';
unset($_SESSION['user_booking_error']);

require_once USER_INCLUDES . 'layout.php';

?>

<!-- ══════════════════ PAGE HEADER ══════════════════ -->
<section class="all-events-hero">
  <div class="hero-glow glow-1"></div>
  <div class="hero-glow glow-2"></div>

  <div class="container position-relative">
    <a href="<?= HOME_URL ?>#buku" class="btn-outline-more mb-4">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
    </a>

    <div class="section-label">📚 Jelajahi Katalog</div>
    <h1 class="hero-title mb-3" style="font-size:3rem;">
      Semua Buku<br>
      <span class="highlight">BookStore</span>
    </h1>
    <p class="hero-sub mb-0">
      Temukan novel, buku pelajaran, komik, dan koleksi lainnya dalam satu halaman.
    </p>
  </div>
</section>

<!-- ══════════════════ ALL BOOKS ══════════════════ -->
<section class="section-gap pt-4">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-md-4">
        <aside class="all-events-sidebar">
          <div class="mb-4">
            <label class="event-filter-label" for="eventSearch">Cari Buku</label>
            <div class="position-relative">
              <i class="bi bi-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--clr-muted);"></i>
              <input id="eventSearch" class="event-filter-input" type="text"
                placeholder="Judul, penulis, atau penerbit..."
                value="<?= htmlspecialchars($search_q) ?>"
                style="padding-left:40px;">
            </div>
          </div>

          <div>
            <div class="event-filter-label">Kategori</div>
            <div class="event-category-list">
              <button type="button"
                class="event-category-filter<?= $active_kategori_slug === '' ? ' active' : '' ?>"
                data-category="">
                <span>Semua Kategori</span>
                <span class="event-count-badge"><?= $total_events ?></span>
              </button>
              <?php foreach ($kategori_list as $kategori_item): ?>
                <?php
                $nama_kat = $kategori_item['nama_kategori'] ?? '';
                $kat_slug = user_kategori_slug($nama_kat);
                $kat_count = (int) ($kategori_counts['categories'][$nama_kat] ?? 0);
                $is_active = $active_kategori_slug === $kat_slug;
                ?>
                <button type="button"
                  class="event-category-filter<?= $is_active ? ' active' : '' ?>"
                  data-category="<?= htmlspecialchars($kat_slug, ENT_QUOTES) ?>">
                  <span><?= htmlspecialchars($nama_kat) ?></span>
                  <span class="event-count-badge"><?= $kat_count ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>

      <div class="col-md-8">
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
          <div>
            <div class="section-label">📌 Daftar Buku</div>
            <h2 class="section-title">Pilih Buku Favoritmu</h2>
          </div>
          <div style="font-size:0.85rem; color:var(--clr-muted);">
            <span id="eventResultCount"><?= count($event_list) ?></span> buku ditemukan
          </div>
        </div>

        <?php require USER_INCLUDES . 'event-grid-all.php'; ?>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════ MODAL: BOOK DETAIL ══════════════════ -->
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.ALL_EVENTS_FILTER = {
        initialCategory: <?= json_encode($active_kategori_slug) ?>,
        initialSearch: <?= json_encode($search_q) ?>,
    };
});
</script>
<?php
require_once USER_INCLUDES . 'login.php';
require_once USER_INCLUDES . 'register.php';
require_once USER_INCLUDES . 'footer.php';

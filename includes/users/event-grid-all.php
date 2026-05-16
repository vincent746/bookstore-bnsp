<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$event_list = $event_list ?? [];
?>

<div class="row g-3" id="allEventsGrid">
    <?php if (empty($event_list)): ?>
        <div class="col-12">
            <div class="text-center py-5" style="color:var(--clr-muted);">
                Belum ada buku aktif saat ini.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($event_list as $book): ?>
            <?php
            $cat_name = $book['nama_kategori'] ?? '-';
            $cat_slug = user_kategori_slug($cat_name);
            $cat_class = user_event_cat_class($cat_name);
            $badge_dot = user_event_badge_dot($cat_name);
            $gambar_url = user_event_gambar_url($book['gambar'] ?? '');
            $img_style = $gambar_url !== '' ? "background-image:url('" . htmlspecialchars($gambar_url, ENT_QUOTES) . "'); background-size:cover; background-position:center;" : '';
            $terbit_tampil = user_format_tanggal_card($book['tahun_penerbit'] ?? '');
            $penulis = trim((string) ($book['penulis'] ?? ''));
            $penerbit = trim((string) ($book['penerbit'] ?? ''));
            $meta_line = $penulis;
            if ($penerbit !== '') {
                $meta_line = ($meta_line !== '' ? $penulis . ' · ' : '') . $penerbit;
            }
            if ($meta_line === '') {
                $meta_line = '-';
            }
            $search_text = strtolower(
                ($book['judul_buku'] ?? '') . ' '
                . $cat_name . ' '
                . $penulis . ' '
                . $penerbit . ' '
                . ($book['deskripsi'] ?? '')
            );
            ?>
            <div class="col-md-6 event-list-item"
                data-category="<?= htmlspecialchars($cat_slug, ENT_QUOTES) ?>"
                data-search-text="<?= htmlspecialchars($search_text, ENT_QUOTES) ?>">
                <div class="event-card all-event-card" role="button" tabindex="0"
                    data-event-id="<?= (int) ($book['id'] ?? 0) ?>"
                    data-event-title="<?= htmlspecialchars($book['judul_buku'] ?? '', ENT_QUOTES) ?>"
                    data-event-category="<?= htmlspecialchars($cat_name, ENT_QUOTES) ?>"
                    data-event-datetime="<?= htmlspecialchars('Terbit: ' . $terbit_tampil, ENT_QUOTES) ?>"
                    data-event-location="<?= htmlspecialchars($meta_line, ENT_QUOTES) ?>"
                    data-event-description="<?= htmlspecialchars($book['deskripsi'] ?? '', ENT_QUOTES) ?>"
                    data-event-image="<?= htmlspecialchars($gambar_url, ENT_QUOTES) ?>">
                    <div class="card-img-wrapper <?= htmlspecialchars($cat_class) ?> <?= $gambar_url !== '' ? 'has-image' : '' ?>"
                        <?= $img_style !== '' ? 'style="' . $img_style . '"' : '' ?>>
                        <?php if ($gambar_url === ''): ?>
                            <div class="card-visual-pattern"></div>
                            <div class="card-visual-icon"><?= user_event_cat_icon($cat_name) ?></div>
                        <?php endif; ?>
                        <div class="card-badge">
                            <span class="badge-dot <?= htmlspecialchars($badge_dot) ?>"></span>
                            <?= htmlspecialchars($cat_name) ?>
                        </div>
                    </div>
                    <div class="card-body-custom">
                        <div class="card-date-row">
                            <i class="bi bi-calendar3"></i> <?= htmlspecialchars($terbit_tampil) ?>
                        </div>
                        <div class="card-title-custom"><?= htmlspecialchars($book['judul_buku'] ?? '') ?></div>
                        <div class="card-location">
                            <i class="bi bi-person-lines-fill"></i> <?= htmlspecialchars($meta_line) ?>
                        </div>
                        <div class="card-seats">
                            <span style="white-space:nowrap;"><?= format_angka_id($book['stok'] ?? 0) ?> stok</span>
                        </div>
                        <div class="card-footer-custom">
                            <div>
                                <div class="card-price"><?= format_rupiah($book['harga'] ?? 0) ?></div>
                                <div class="card-price-label">per buku</div>
                            </div>
                            <button type="button" <?= user_event_booking_attrs($book, $cat_name, 'btn-book') ?>><i class="bi bi-heart-fill"></i> Wishlist</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="event-empty-state mt-3" id="eventNoResult" style="display:none;">
    <i class="bi bi-search" style="font-size:1.6rem; color:var(--clr-accent);"></i>
    <div style="font-family:'Syne',sans-serif; font-weight:700; color:var(--clr-text); margin-top:10px;">Buku tidak ditemukan</div>
    <div style="font-size:0.86rem; margin-top:4px;">Coba ubah kata pencarian atau pilih kategori lain.</div>
</div>

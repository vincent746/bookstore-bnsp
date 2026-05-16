<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$featured_event = $featured_event ?? null;
$grid_events = $grid_events ?? [];
$has_search = !empty($search_q) || (!empty($filter_kategori) && $filter_kategori !== 'Semua Kategori');
?>
<?php if ($featured_event): ?>
    <?php
    $feat = $featured_event;
    $feat_cat = $feat['nama_kategori'] ?? '-';
    $feat_cat_class = user_event_cat_class($feat_cat);
    $feat_gambar = user_event_gambar_url($feat['gambar'] ?? '');
    $feat_visual_style = $feat_gambar !== '' ? "background-image:url('" . htmlspecialchars($feat_gambar, ENT_QUOTES) . "'); background-size:cover; background-position:center;" : '';
    $feat_terbit = user_format_tanggal_lengkap($feat['tahun_penerbit'] ?? '');
    $feat_penulis = trim((string) ($feat['penulis'] ?? ''));
    $feat_penerbit = trim((string) ($feat['penerbit'] ?? ''));
    ?>
    <div class="featured-card mb-4">
        <div class="featured-visual <?= $feat_gambar !== '' ? 'has-image' : '' ?> <?= htmlspecialchars($feat_cat_class) ?>"
            <?= $feat_visual_style !== '' ? 'style="' . $feat_visual_style . '"' : '' ?>>
            <?php if ($feat_gambar === ''): ?>
                <div class="featured-visual-grid"></div>
                <div class="featured-visual-icon"><?= user_event_cat_icon($feat_cat) ?></div>
                <div class="featured-glow"></div>
            <?php endif; ?>
        </div>
        <div class="featured-content">
            <div>
                <div class="featured-tag"><i class="bi bi-lightning-fill"></i> UNGGULAN</div>
                <div class="featured-title"><?= htmlspecialchars($feat['judul_buku'] ?? '') ?></div>
                <div class="featured-meta">
                    <span><i class="bi bi-calendar3"></i> Terbit: <?= htmlspecialchars($feat_terbit) ?></span>
                    <?php if ($feat_penulis !== ''): ?>
                        <span><i class="bi bi-person"></i> <?= htmlspecialchars($feat_penulis) ?></span>
                    <?php endif; ?>
                    <?php if ($feat_penerbit !== ''): ?>
                        <span><i class="bi bi-building"></i> <?= htmlspecialchars($feat_penerbit) ?></span>
                    <?php endif; ?>
                    <span><i class="bi bi-box-seam"></i> Stok: <?= format_angka_id($feat['stok'] ?? 0) ?></span>
                </div>
            </div>
            <div class="featured-footer">
                <div class="featured-price-group">
                    <span class="featured-price-label">Mulai dari</span>
                    <span class="featured-price"><?= format_rupiah($feat['harga'] ?? 0) ?></span>
                </div>
                <button type="button" <?= user_event_booking_attrs($feat, $feat_cat, 'btn-book-lg') ?>>
                    <i class="bi bi-heart-fill"></i>
                    Wishlist
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <?php if (empty($grid_events) && !$featured_event): ?>
        <div class="col-12">
            <div class="text-center py-5" style="color:var(--clr-muted);">
                <?php if ($has_search): ?>
                    Tidak ada buku yang cocok dengan pencarian Anda.
                <?php else: ?>
                    Belum ada buku aktif saat ini.
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($grid_events as $book): ?>
        <?php
        $cat_name = $book['nama_kategori'] ?? '-';
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
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="event-card" role="button" tabindex="0"
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
                        <span style="white-space:nowrap;">Stok:</span>
                        <span style="white-space:nowrap;"><?= format_angka_id($book['stok'] ?? 0) ?></span>
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
</div>

<?php

if (!defined('ADMIN_INIT')) {
    require_once __DIR__ . '/init.php';
}

$active_menu = $active_menu ?? '';

if (!isset($pending_transactions)) {
    $pending_transactions = count_pending_transactions($conn);
}

$menu_items = [
    'utama' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill', 'url' => admin_module_url('dashboard')],
    ],
    'manajemen' => [
        ['key' => 'kategori', 'label' => 'Kategori Buku', 'icon' => 'bi-tags-fill', 'url' => admin_module_url('kategori')],
        ['key' => 'buku', 'label' => 'Buku', 'icon' => 'bi-book-fill', 'url' => admin_module_url('buku')],
        ['key' => 'user', 'label' => 'User', 'icon' => 'bi-people-fill', 'url' => admin_module_url('user')],
        ['key' => 'pesan-user', 'label' => 'Pesan User', 'icon' => 'bi-chat-dots-fill', 'url' => admin_module_url('pesan-user')],
    ],
    'transaksi' => [
        ['key' => 'transaksi', 'label' => 'Transaksi Pesanan', 'icon' => 'bi-bag-check-fill', 'url' => admin_module_url('transaksi'), 'badge' => $pending_transactions],
        ['key' => 'laporan', 'label' => 'Laporan Penjualan Buku', 'icon' => 'bi-bar-chart-line-fill', 'url' => admin_module_url('laporan')],
    ],
];

$section_labels = [
    'utama' => 'Utama',
    'manajemen' => 'Manajemen',
    'transaksi' => 'Transaksi',
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-text">BookStore</div>
        <div class="brand-badge">ADMIN</div>
    </div>

    <nav class="nav-section">
        <?php $is_first = true; ?>
        <?php foreach ($menu_items as $section_key => $items): ?>
            <div class="nav-section-label"<?= $is_first ? '' : ' style="margin-top:16px;"' ?>><?= $section_labels[$section_key] ?></div>
            <?php $is_first = false; ?>

            <?php foreach ($items as $item): ?>
                <a href="<?= htmlspecialchars($item['url']) ?>"
                    class="nav-item<?= $active_menu === $item['key'] ? ' active' : '' ?>">
                    <span class="nav-icon"><i class="bi <?= htmlspecialchars($item['icon']) ?>"></i></span>
                    <?= htmlspecialchars($item['label']) ?>
                    <?php if (isset($item['badge']) && (int) $item['badge'] > 0): ?>
                        <span class="nav-badge"><?= (int) $item['badge'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-version">BookStore Admin v2.0.1 · © <?= date('Y') ?></div>
    </div>
</aside>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

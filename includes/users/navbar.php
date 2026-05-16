<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$active_nav = $active_nav ?? 'beranda';
$user_logout_url = USER_PROSES_URL . '?logout=1&redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? HOME_URL);
?>
<!-- ══════════════════ NAVBAR ══════════════════ -->
<nav class="navbar-custom">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <a href="<?= HOME_URL ?>" class="navbar-brand-text text-decoration-none">BookStore</a>

            <div class="nav-pill d-none d-md-flex">
                <a href="<?= HOME_URL ?>" class="nav-link<?= $active_nav === 'beranda' ? ' active' : '' ?>">Beranda</a>
                <a href="<?= user_module_url('semua-buku') ?>"
                    class="nav-link<?= $active_nav === 'buku' ? ' active' : '' ?>">Semua Buku</a>
                <a href="<?= HOME_URL ?>#kontak" class="nav-link">Kontak</a>
                <?php if (is_user_logged_in()): ?>
                    <a href="<?= HOME_URL ?>#pesanan-saya"
                        class="nav-link<?= $active_nav === 'tiket' ? ' active' : '' ?>">Pesanan Saya</a>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="nav-cart-btn" id="navCartBtn" data-bs-toggle="modal" data-bs-target="#cartModal"
                    aria-label="Buka keranjang">
                    <i class="bi bi-cart3"></i>
                    <span class="nav-cart-badge d-none" id="navCartBadge">0</span>
                </button>
                <?php if (is_user_logged_in()): ?>
                    <?php $user_nama = current_user_nama(); ?>
                    <div class="dropdown nav-user-dropdown">
                        <button class="nav-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span class="nav-user-avatar"><?= htmlspecialchars(strtoupper(substr($user_nama, 0, 1))) ?></span>
                            <span class="nav-user-name"><?= htmlspecialchars($user_nama) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end nav-user-menu">
                            <li>
                                <a class="dropdown-item nav-user-menu-item danger" href="<?= htmlspecialchars($user_logout_url) ?>">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <button class="btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">Masuk</button>
                    <button class="btn-register" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

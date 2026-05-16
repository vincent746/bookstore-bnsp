<?php

if (!defined('ADMIN_INIT')) {
    require_once __DIR__ . '/init.php';
}

$breadcrumb_title = $breadcrumb_title ?? ($page_title ?? 'Dashboard');
$admin_nama = $_SESSION['admin_nama'] ?? 'Admin';
$admin_inisial = strtoupper(substr($admin_nama, 0, 2));
?>
<header class="header" id="header">
    <div class="header-left">
        <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" title="Toggle menu">
            <i class="bi bi-list" id="hamburgerIcon"></i>
        </button>

        <div class="header-breadcrumb">
            <span>Buku</span>
            <i class="bi bi-chevron-right" style="font-size:0.6rem;"></i>
            <span class="current" id="breadcrumbCurrent"><?= htmlspecialchars($breadcrumb_title) ?></span>
        </div>
    </div>

    <div class="header-right">
        <div class="user-dropdown" id="userDropdown">
            <div class="user-btn" onclick="toggleDropdown()">
                <div class="user-avatar"><?= htmlspecialchars($admin_inisial) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($admin_nama) ?></div>
                    <div class="user-role">Admin</div>
                </div>
                <i class="bi bi-chevron-down user-chevron"></i>
            </div>
            <div class="dropdown-menu-custom">
                <div class="dropdown-sep"></div>
                <a href="<?= ADMIN_URL ?>proses.php?logout=1" class="dropdown-item-custom danger">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </div>
    </div>
</header>

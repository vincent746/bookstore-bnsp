<?php

if (!defined('ADMIN_INIT')) {
    require_once __DIR__ . '/init.php';
}

// Belum login? arahkan ke halaman login
if (empty($_SESSION['admin_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

require_once ADMIN_INCLUDES . 'head.php';
require_once ADMIN_INCLUDES . 'sidebar.php';
require_once ADMIN_INCLUDES . 'header.php';

echo '<main class="main-content" id="mainContent">';

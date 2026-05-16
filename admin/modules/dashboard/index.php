<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

$active_menu = 'dashboard';
$page_title = 'Dashboard';
$breadcrumb_title = 'Dashboard';

$stats = get_dashboard_stats($conn);
$admin_nama = $_SESSION['admin_nama'] ?? 'Admin';

require_once ADMIN_INCLUDES . 'layout.php';
?>

<div class="page active">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">Selamat datang kembali, <?= htmlspecialchars($admin_nama) ?> 👋 — <?= tanggal_id() ?></p>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card yellow">
            <div class="sc-top">
                <div class="sc-icon ic-yellow"><i class="bi bi-book-fill"></i></div>
            </div>
            <div class="sc-value"><?= format_angka_id($stats['total_buku']) ?></div>
            <div class="sc-label">Total Buku</div>
            <div class="sc-footer"><i class="bi bi-info-circle"></i> Buku dengan status aktif</div>
        </div>

        <div class="stat-card blue">
            <div class="sc-top">
                <div class="sc-icon ic-blue"><i class="bi bi-bag-check-fill"></i></div>
            </div>
            <div class="sc-value"><?= format_angka_id($stats['total_pemesanan']) ?></div>
            <div class="sc-label">Total Pemesanan</div>
            <div class="sc-footer"><i class="bi bi-info-circle"></i> Transaksi sudah dibayar (paid)</div>
        </div>

        <div class="stat-card green">
            <div class="sc-top">
                <div class="sc-icon ic-green"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="sc-value"><?= format_angka_id($stats['total_pengguna']) ?></div>
            <div class="sc-label">Total Pengguna</div>
            <div class="sc-footer"><i class="bi bi-info-circle"></i> Seluruh user terdaftar</div>
        </div>

        <div class="stat-card orange">
            <div class="sc-top">
                <div class="sc-icon ic-orange"><i class="bi bi-currency-dollar"></i></div>
            </div>
            <div class="sc-value"><?= format_rupiah($stats['total_pendapatan']) ?></div>
            <div class="sc-label">Total Pendapatan</div>
            <div class="sc-footer"><i class="bi bi-info-circle"></i> Dari transaksi paid</div>
        </div>

        <div class="stat-card red">
            <div class="sc-top">
                <div class="sc-icon ic-red"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="sc-value"><?= format_angka_id($stats['menunggu_konfirmasi']) ?></div>
            <div class="sc-label">Menunggu Konfirmasi</div>
            <div class="sc-footer"><i class="bi bi-exclamation-circle"></i> Perlu konfirmasi admin</div>
        </div>

        <div class="stat-card" style="border-color:rgba(199,125,255,0.12);">
            <div class="sc-top">
                <div class="sc-icon ic-purple"><i class="bi bi-tags-fill"></i></div>
            </div>
            <div class="sc-value"><?= format_angka_id($stats['total_kategori']) ?></div>
            <div class="sc-label">Kategori Buku</div>
            <div class="sc-footer"><i class="bi bi-info-circle"></i> Kategori aktif</div>
        </div>
    </div>
</div>

<?php require_once ADMIN_INCLUDES . 'footer.php'; ?>

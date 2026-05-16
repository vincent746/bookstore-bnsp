<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

$filter_buku = (int) ($_GET['id_buku'] ?? 0);
$filter_dari = trim($_GET['tanggal_dari'] ?? '');
$filter_sampai = trim($_GET['tanggal_sampai'] ?? '');

$laporan = get_laporan_penjualan($conn, $filter_buku, $filter_dari, $filter_sampai);

if ($laporan['error']) {
    die('Gagal mengekspor laporan: ' . $laporan['error']);
}

$filename = 'laporan-penjualan-' . date('Ymd-His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

$filter_info = [];

if ($filter_buku > 0) {
    $buku_query = mysqli_query($conn, 'SELECT judul_buku FROM m_buku WHERE id = ' . $filter_buku . ' LIMIT 1');
    $buku_row = mysqli_fetch_assoc($buku_query);
    $filter_info[] = 'Buku: ' . ($buku_row['judul_buku'] ?? '-');
}

if ($filter_dari !== '') {
    $filter_info[] = 'Dari: ' . $filter_dari;
}

if ($filter_sampai !== '') {
    $filter_info[] = 'Sampai: ' . $filter_sampai;
}

echo "Laporan Penjualan Buku\n";
echo 'Diekspor: ' . now_indonesia() . "\n";

if (!empty($filter_info)) {
    echo 'Filter: ' . implode(' | ', $filter_info) . "\n";
}

echo "\n";
echo "No\tJudul Buku\tStok Saat Ini\tJumlah Terjual\tTotal Penjualan\n";

$no = 1;

foreach ($laporan['rows'] as $row) {
    echo $no++ . "\t";
    echo ($row['judul_buku'] ?? '-') . "\t";
    echo (int) ($row['stok_tersedia'] ?? 0) . "\t";
    echo (int) ($row['jumlah_terjual'] ?? 0) . "\t";
    echo (float) ($row['total_harga'] ?? 0) . "\n";
}

echo "\n";
echo "TOTAL\t\t";
echo (int) $laporan['total_stok_tersedia'] . "\t";
echo (int) $laporan['total_buku_terjual'] . "\t";
echo (float) $laporan['total_harga'] . "\n";

exit;

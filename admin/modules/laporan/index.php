<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

$active_menu = 'laporan';
$page_title = 'Laporan Penjualan Buku';
$breadcrumb_title = 'Laporan Penjualan Buku';

$filter_buku = (int) ($_GET['id_buku'] ?? 0);
$filter_dari = trim($_GET['tanggal_dari'] ?? '');
$filter_sampai = trim($_GET['tanggal_sampai'] ?? '');

$buku_options = [];
$buku_query = mysqli_query($conn, 'SELECT id, judul_buku FROM m_buku ORDER BY judul_buku ASC');

if ($buku_query) {
    while ($row = mysqli_fetch_assoc($buku_query)) {
        $buku_options[] = $row;
    }
}

$laporan = get_laporan_penjualan($conn, $filter_buku, $filter_dari, $filter_sampai);
$laporan_rows = $laporan['rows'];
$db_error = $laporan['error'];

$export_query = http_build_query([
    'id_buku' => $filter_buku,
    'tanggal_dari' => $filter_dari,
    'tanggal_sampai' => $filter_sampai,
]);

$export_url = admin_module_url('laporan') . 'export.php' . ($export_query ? '?' . $export_query : '');

require_once ADMIN_INCLUDES . 'layout.php';
?>

<style>
    .laporan-filter-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 16px;
    }

    .laporan-filter-card .form-label {
        font-size: 0.75rem;
        color: var(--muted2);
        margin-bottom: 4px;
    }

    .laporan-filter-card .form-control,
    .laporan-filter-card .form-select {
        background: var(--surface);
        border-color: var(--border);
        color: var(--heading);
    }

    .laporan-filter-card .form-control:focus,
    .laporan-filter-card .form-select:focus {
        background: var(--card2);
        border-color: var(--accent);
        color: var(--heading);
        box-shadow: none;
    }

    .laporan-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }

    .laporan-summary-item {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 18px;
        min-width: 200px;
        flex: 1;
    }

    .laporan-summary-item .label {
        font-size: 0.72rem;
        color: var(--muted2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .laporan-summary-item .value {
        font-family: 'Syne', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--heading);
        margin-top: 4px;
    }

    .custom-table tfoot td {
        background: var(--surface);
        font-weight: 700;
        color: var(--heading);
        border-top: 2px solid var(--border);
    }
</style>

<?php if ($db_error): ?>
    <div class="alert alert-warning alert-dismissible fade show mx-0 mt-0" role="alert">
        <i class="bi bi-database-exclamation me-2"></i>Gagal memuat laporan: <?= htmlspecialchars($db_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="page active">
    <div class="laporan-filter-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="id_buku" class="form-label">Buku</label>
                <select name="id_buku" id="id_buku" class="form-select">
                    <option value="0">Semua Buku</option>
                    <?php foreach ($buku_options as $b): ?>
                        <option value="<?= (int) $b['id'] ?>" <?= $filter_buku === (int) $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['judul_buku']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="<?= htmlspecialchars($filter_dari) ?>">
            </div>
            <div class="col-md-3">
                <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="<?= htmlspecialchars($filter_sampai) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="laporan-summary">
        <div class="laporan-summary-item">
            <div class="label">Total Buku Terjual (qty)</div>
            <div class="value"><?= (int) $laporan['total_buku_terjual'] ?> eksemplar</div>
        </div>
        <div class="laporan-summary-item">
            <div class="label">Total Penjualan</div>
            <div class="value"><?= format_rupiah($laporan['total_harga']) ?></div>
        </div>
        <div class="laporan-summary-item">
            <div class="label">Jumlah Judul Buku</div>
            <div class="value"><?= count($laporan_rows) ?></div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Rekap Penjualan per Buku</div>
            <!-- <a href="<?= htmlspecialchars($export_url) ?>" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a> -->
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Stok Saat Ini</th>
                        <th>Jumlah Terjual</th>
                        <th>Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_rows)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color: var(--muted2);">
                                Tidak ada data laporan untuk filter yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($laporan_rows as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="t-event-name"><?= htmlspecialchars($row['judul_buku'] ?? '-') ?></div>
                                </td>
                                <td><?= (int) ($row['stok_tersedia'] ?? 0) ?></td>
                                <td><strong><?= (int) ($row['jumlah_terjual'] ?? 0) ?></strong></td>
                                <td><?= format_rupiah($row['total_harga'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($laporan_rows)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end">TOTAL</td>
                            <td><?= (int) $laporan['total_buku_terjual'] ?></td>
                            <td><?= format_rupiah($laporan['total_harga']) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php require_once ADMIN_INCLUDES . 'footer.php'; ?>

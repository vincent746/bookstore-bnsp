<?php

date_default_timezone_set('Asia/Jakarta');

/**
 * Waktu sekarang zona Indonesia (WIB).
 *
 * @param string $format Format tanggal PHP, default: Y-m-d H:i:s
 */
function now_indonesia($format = 'Y-m-d H:i:s')
{
    return (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format($format);
}

/**
 * Upload gambar buku ke assets/admin/img/buku/
 *
 * @param array       $file       $_FILES['gambar']
 * @param string|null $old_gambar Nama file lama (saat update)
 *
 * @return string|false Nama file baru, nama lama jika tidak ada upload, atau false jika gagal
 */
function upload_buku_gambar($file, $old_gambar = null)
{
    $upload_dir = ROOT_PATH . '/assets/admin/img/buku/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $old_gambar ?? '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $target = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return false;
    }

    if ($old_gambar && file_exists($upload_dir . $old_gambar)) {
        unlink($upload_dir . $old_gambar);
    }

    return $filename;
}

/**
 * Hapus file gambar buku dari folder upload.
 */
function delete_buku_gambar_file($filename)
{
    if ($filename === '') {
        return;
    }

    $path = ROOT_PATH . '/assets/admin/img/buku/' . $filename;

    if (file_exists($path)) {
        unlink($path);
    }
}

/**
 * URL gambar buku untuk ditampilkan di HTML.
 */
function buku_gambar_url($filename)
{
    if ($filename === '') {
        return '';
    }

    return asset_url('admin/img/buku/' . ltrim($filename, '/'));
}

/**
 * Upload gambar event ke assets/admin/img/event/
 *
 * @param array       $file       $_FILES['gambar']
 * @param string|null $old_gambar Nama file lama (saat update)
 *
 * @return string|false Nama file baru, nama lama jika tidak ada upload, atau false jika gagal
 */
function upload_event_gambar($file, $old_gambar = null)
{
    $upload_dir = ROOT_PATH . '/assets/admin/img/buku/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $old_gambar ?? '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $target = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return false;
    }

    if ($old_gambar && file_exists($upload_dir . $old_gambar)) {
        unlink($upload_dir . $old_gambar);
    }

    return $filename;
}

/**
 * Hapus file gambar buku dari folder upload.
 */
function delete_event_gambar_file($filename)
{
    if ($filename === '') {
        return;
    }

    $path = ROOT_PATH . '/assets/admin/img/buku/' . $filename;

    if (file_exists($path)) {
        unlink($path);
    }
}

/**
 * URL gambar buku untuk ditampilkan di HTML.
 */
function event_gambar_url($filename)
{
    if ($filename === '') {
        return '';
    }

    return asset_url('admin/img/buku/' . ltrim($filename, '/'));
}

/**
 * Format datetime DB ke value input datetime-local.
 */
function datetime_local_value($datetime)
{
    if ($datetime === null || $datetime === '') {
        return '';
    }

    return date('Y-m-d\TH:i', strtotime($datetime));
}

/**
 * Format tanggal DB ke value input type="date".
 */
function date_input_value($date)
{
    if ($date === null || $date === '') {
        return '';
    }

    return date('Y-m-d', strtotime($date));
}

/**
 * Format rupiah untuk tampilan tabel.
 */
function format_rupiah($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/** ID status transaksi (m_status) */
define('STATUS_MENUNGGU_PEMBAYARAN', 1);
define('STATUS_MENUNGGU_KONFIRMASI', 2);
define('STATUS_TERKONFIRMASI', 3);
define('STATUS_DIBATALKAN', 4);

/**
 * Statistik ringkas untuk dashboard admin.
 *
 * @return array<string, int|float>
 */
function get_dashboard_stats($conn)
{
    $stats = [
        'total_buku' => 0,
        'total_pemesanan' => 0,
        'total_pengguna' => 0,
        'total_pendapatan' => 0,
        'menunggu_konfirmasi' => 0,
        'total_kategori' => 0,
    ];

    $queries = [
        'total_buku' => 'SELECT COUNT(*) AS total FROM m_buku WHERE is_active = 1',
        'total_pemesanan' => 'SELECT COUNT(*) AS total FROM trans_h_pesanan WHERE is_paid = 1',
        'total_pengguna' => 'SELECT COUNT(*) AS total FROM users',
        'total_pendapatan' => 'SELECT COALESCE(SUM(d.total_harga), 0) AS total
            FROM trans_d_pesanan d
            INNER JOIN trans_h_pesanan h ON h.id = d.id_header AND h.is_paid = 1',
        'total_kategori' => 'SELECT COUNT(*) AS total FROM m_kategori WHERE is_active = 1',
    ];

    foreach ($queries as $key => $sql) {
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats[$key] = $key === 'total_pendapatan'
                ? (float) ($row['total'] ?? 0)
                : (int) ($row['total'] ?? 0);
        }
    }

    $stats['menunggu_konfirmasi'] = count_pending_transactions($conn);

    return $stats;
}

/**
 * Format angka dengan pemisah ribuan Indonesia.
 */
function format_angka_id($number)
{
    return number_format((int) $number, 0, ',', '.');
}

/**
 * Jumlah transaksi Menunggu Konfirmasi (badge sidebar).
 */
function count_pending_transactions($conn)
{
    $id_status = (int) STATUS_MENUNGGU_KONFIRMASI;
    $result = mysqli_query($conn, "SELECT COUNT(id) AS total FROM trans_h_pesanan WHERE id_status = $id_status");

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int) ($row['total'] ?? 0);
}

/**
 * Data laporan penjualan per buku (hanya transaksi Terkonfirmasi).
 *
 * @return array{
 *     rows: array<int, array<string, mixed>>,
 *     total_stok_tersedia: int,
 *     total_buku_terjual: int,
 *     total_harga: float,
 *     error: string|null
 * }
 */
function get_laporan_penjualan($conn, $id_buku = 0, $tanggal_dari = '', $tanggal_sampai = '')
{
    $id_buku = (int) $id_buku;
    $tanggal_dari = trim($tanggal_dari);
    $tanggal_sampai = trim($tanggal_sampai);

    $sold_where = 'h.id_status = ' . (int) STATUS_TERKONFIRMASI;

    if ($tanggal_dari !== '') {
        $sold_where .= " AND DATE(h.created_at) >= '" . mysqli_real_escape_string($conn, $tanggal_dari) . "'";
    }

    if ($tanggal_sampai !== '') {
        $sold_where .= " AND DATE(h.created_at) <= '" . mysqli_real_escape_string($conn, $tanggal_sampai) . "'";
    }

    $buku_where = '';

    if ($id_buku > 0) {
        $buku_where = ' WHERE b.id = ' . $id_buku;
    }

    $sql = "SELECT
                b.id,
                b.judul_buku,
                b.stok AS stok_tersedia,
                COALESCE(sold.jumlah_terjual, 0) AS jumlah_terjual,
                COALESCE(sold.total_harga, 0) AS total_harga
            FROM m_buku b
            LEFT JOIN (
                SELECT
                    d.id_buku,
                    SUM(d.jumlah_buku) AS jumlah_terjual,
                    SUM(d.total_harga) AS total_harga
                FROM trans_d_pesanan d
                INNER JOIN trans_h_pesanan h ON h.id = d.id_header AND {$sold_where}
                GROUP BY d.id_buku
            ) sold ON sold.id_buku = b.id
            {$buku_where}
            ORDER BY b.judul_buku ASC";

    $result = mysqli_query($conn, $sql);
    $empty = [
        'rows' => [],
        'total_stok_tersedia' => 0,
        'total_buku_terjual' => 0,
        'total_harga' => 0,
        'error' => null,
    ];

    if (!$result) {
        $empty['error'] = mysqli_error($conn);

        return $empty;
    }

    $rows = [];
    $total_stok_tersedia = 0;
    $total_buku_terjual = 0;
    $total_harga = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
        $total_stok_tersedia += (int) ($row['stok_tersedia'] ?? 0);
        $total_buku_terjual += (int) ($row['jumlah_terjual'] ?? 0);
        $total_harga += (float) ($row['total_harga'] ?? 0);
    }

    return [
        'rows' => $rows,
        'total_stok_tersedia' => $total_stok_tersedia,
        'total_buku_terjual' => $total_buku_terjual,
        'total_harga' => $total_harga,
        'error' => null,
    ];
}

/**
 * URL bukti pembayaran transaksi.
 */
function bukti_pembayaran_url($filename)
{
    if ($filename === '') {
        return '';
    }

    return asset_url('admin/img/bukti_pembayaran/' . ltrim($filename, '/'));
}

/**
 * Daftar kategori untuk dropdown form buku.
 *
 * @return array<int, array{id: int|string, nama_kategori: string}>
 */
function get_kategori_options($conn, $active_only = true)
{
    $options = [];
    $where = $active_only ? ' WHERE is_active = 1' : '';
    $query = mysqli_query($conn, 'SELECT id, nama_kategori FROM m_kategori' . $where . ' ORDER BY nama_kategori ASC');

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = $row;
        }
    }

    return $options;
}

/**
 * Daftar status pengiriman untuk form admin transaksi.
 *
 * @return array<int, array{id: int|string, status_pengiriman: string}>
 */
function get_status_pengiriman_options($conn)
{
    $options = [];
    $query = mysqli_query(
        $conn,
        'SELECT id, status_pengiriman FROM m_status_pengiriman ORDER BY id ASC'
    );

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = $row;
        }
    }

    return $options;
}

/**
 * Ambil data dengan paginasi (mysqli).
 *
 * @param mysqli $conn
 * @param string $count_sql Query hitung total, wajib alias kolom: total
 * @param string $data_sql  Query data (tanpa LIMIT)
 * @param int    $per_page  Jumlah baris per halaman
 * @param int|null $page    Halaman aktif (null = dari $_GET)
 * @param string $page_param Nama parameter URL halaman
 */
function paginate($conn, $count_sql, $data_sql, $per_page = 10, $page = null, $page_param = 'page')
{
    $per_page = max(1, $per_page);

    if ($page === null) {
        $page = max(1, (int) ($_GET[$page_param] ?? 1));
    } else {
        $page = max(1, (int) $page);
    }

    $empty = [
        'data' => [],
        'page' => $page,
        'per_page' => $per_page,
        'total' => 0,
        'total_pages' => 1,
        'offset' => 0,
        'from' => 0,
        'to' => 0,
        'has_prev' => false,
        'has_next' => false,
        'page_param' => $page_param,
        'error' => null,
    ];

    $count_result = mysqli_query($conn, $count_sql);

    if (!$count_result) {
        $empty['error'] = mysqli_error($conn);

        return $empty;
    }

    $count_row = mysqli_fetch_assoc($count_result);
    $total = (int) ($count_row['total'] ?? 0);
    $total_pages = max(1, (int) ceil($total / $per_page));

    if ($page > $total_pages) {
        $page = $total_pages;
    }

    $offset = ($page - 1) * $per_page;
    $data_sql_trimmed = rtrim(trim($data_sql), ';');
    $paginated_sql = $data_sql_trimmed . ' LIMIT ' . $offset . ', ' . $per_page;
    $data_result = mysqli_query($conn, $paginated_sql);

    if (!$data_result) {
        $empty['total'] = $total;
        $empty['total_pages'] = $total_pages;
        $empty['page'] = $page;
        $empty['offset'] = $offset;
        $empty['error'] = mysqli_error($conn);

        return $empty;
    }

    $data = [];

    while ($row = mysqli_fetch_assoc($data_result)) {
        $data[] = $row;
    }

    $from = $total > 0 ? $offset + 1 : 0;
    $to = min($offset + $per_page, $total);

    return [
        'data' => $data,
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'from' => $from,
        'to' => $to,
        'has_prev' => $page > 1,
        'has_next' => $page < $total_pages,
        'page_param' => $page_param,
        'error' => null,
    ];
}

function paginate_search_get($search_param = 'search')
{
    return trim($_GET[$search_param] ?? '');
}

function paginate_search_where($conn, $keyword, array $columns)
{
    if ($keyword === '' || empty($columns)) {
        return '';
    }

    $escaped = mysqli_real_escape_string($conn, $keyword);
    $conditions = [];

    foreach ($columns as $column) {
        $conditions[] = $column . " LIKE '%" . $escaped . "%'";
    }

    return ' WHERE (' . implode(' OR ', $conditions) . ')';
}

function paginate_clear_filters_url(array $remove_params = ['search', 'page'])
{
    $query = $_GET;

    foreach ($remove_params as $param) {
        unset($query[$param]);
    }

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

    return $path . ($query ? '?' . http_build_query($query) : '');
}

function render_table_search($placeholder = 'Cari...', $search_param = 'search', $keyword = null)
{
    $keyword = $keyword ?? paginate_search_get($search_param);

    ob_start();
    ?>
    <div class="table-toolbar">
        <form method="get" class="table-search-form" role="search">
            <?php foreach ($_GET as $key => $value): ?>
                <?php if (in_array($key, [$search_param, 'page'], true) || is_array($value)): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars((string) $value) ?>">
            <?php endforeach; ?>
            <div class="input-group table-search-input">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input
                    type="search"
                    name="<?= htmlspecialchars($search_param) ?>"
                    class="form-control"
                    placeholder="<?= htmlspecialchars($placeholder) ?>"
                    value="<?= htmlspecialchars($keyword) ?>"
                    autocomplete="off">
                <?php if ($keyword !== ''): ?>
                    <a href="<?= htmlspecialchars(paginate_clear_filters_url()) ?>" class="btn btn-outline-secondary" title="Reset pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>
    </div>
    <?php

    return ob_get_clean();
}

function paginate_url($page, $page_param = 'page', array $extra_query = [])
{
    $query = array_merge($_GET, $extra_query);
    $query[$page_param] = max(1, (int) $page);

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

    return $path . '?' . http_build_query($query);
}

function render_pagination($pagination, array $extra_query = [])
{
    $page = (int) $pagination['page'];
    $total_pages = max(1, (int) $pagination['total_pages']);
    $page_param = $pagination['page_param'] ?? 'page';
    $has_prev = (bool) ($pagination['has_prev'] ?? false);
    $has_next = (bool) ($pagination['has_next'] ?? false);

    $window = 2;
    $start = max(1, $page - $window);
    $end = min($total_pages, $page + $window);

    ob_start();
    ?>
    <div class="table-pagination">
        <div class="pagination-info">
            Menampilkan <strong><?= (int) $pagination['from'] ?></strong>–<strong><?= (int) $pagination['to'] ?></strong>
            dari <strong><?= (int) $pagination['total'] ?></strong> data
            <?php if (!empty($pagination['search'])): ?>
                <span class="pagination-search-tag">| Pencarian: <strong><?= htmlspecialchars($pagination['search']) ?></strong></span>
            <?php endif; ?>
        </div>
        <nav aria-label="Navigasi halaman">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $has_prev ? '' : 'disabled' ?>">
                    <?php if ($has_prev): ?>
                        <a class="page-link" href="<?= htmlspecialchars(paginate_url($page - 1, $page_param, $extra_query)) ?>" aria-label="Sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                    <?php endif; ?>
                </li>

                <?php if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= htmlspecialchars(paginate_url(1, $page_param, $extra_query)) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <?php if ($i === $page): ?>
                            <span class="page-link"><?= $i ?></span>
                        <?php else: ?>
                            <a class="page-link" href="<?= htmlspecialchars(paginate_url($i, $page_param, $extra_query)) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= htmlspecialchars(paginate_url($total_pages, $page_param, $extra_query)) ?>"><?= $total_pages ?></a>
                    </li>
                <?php endif; ?>

                <li class="page-item <?= $has_next ? '' : 'disabled' ?>">
                    <?php if ($has_next): ?>
                        <a class="page-link" href="<?= htmlspecialchars(paginate_url($page + 1, $page_param, $extra_query)) ?>" aria-label="Berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
    <?php

    return ob_get_clean();
}


<?php

function user_event_gambar_url($filename)
{
    if ($filename === null || $filename === '') {
        return '';
    }

    return BASE_URL . 'assets/admin/img/buku/' . ltrim($filename, '/');
}

function user_format_tanggal_lengkap($datetime)
{
    if ($datetime === null || $datetime === '') {
        return '-';
    }

    $ts = strtotime($datetime);
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    return $hari[(int) date('w', $ts)] . ', '
        . date('j', $ts) . ' '
        . $bulan[(int) date('n', $ts)] . ' '
        . date('Y', $ts);
}

function user_format_tanggal_card($datetime)
{
    if ($datetime === null || $datetime === '') {
        return '-';
    }

    $ts = strtotime($datetime);
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function user_format_jam($datetime)
{
    if ($datetime === null || $datetime === '') {
        return '-';
    }

    return date('H:i', strtotime($datetime)) . ' WIB';
}

function user_event_cat_class($nama_kategori)
{
    $key = strtolower(trim((string) $nama_kategori));

    $map = [
        'musik' => 'cat-music',
        'musik & konser' => 'cat-music',
        'teknologi' => 'cat-tech',
        'kuliner' => 'cat-food',
        'olahraga' => 'cat-sports',
        'seni & budaya' => 'cat-art',
        'seni' => 'cat-art',
        'bisnis' => 'cat-tech',
        'pendidikan' => 'cat-tech',
        'fiksi' => 'cat-art',
        'non-fiksi' => 'cat-tech',
        'anak' => 'cat-food',
    ];

    return $map[$key] ?? 'cat-default';
}

function user_event_badge_dot($nama_kategori)
{
    $key = strtolower(trim((string) $nama_kategori));

    $map = [
        'musik' => 'music',
        'musik & konser' => 'music',
        'teknologi' => 'tech',
        'kuliner' => 'food',
        'olahraga' => 'sports',
        'seni & budaya' => 'art',
        'seni' => 'art',
        'fiksi' => 'art',
    ];

    return $map[$key] ?? 'tech';
}

function user_event_cat_icon($nama_kategori)
{
    $key = strtolower(trim((string) $nama_kategori));

    $map = [
        'musik' => '🎵',
        'musik & konser' => '🎵',
        'teknologi' => '💻',
        'kuliner' => '🍜',
        'olahraga' => '⚽',
        'seni & budaya' => '🎨',
        'seni' => '🎨',
        'bisnis' => '📊',
        'pendidikan' => '📚',
        'fiksi' => '📖',
        'anak' => '🧸',
    ];

    return $map[$key] ?? '📚';
}

function user_kategori_slug($nama_kategori)
{
    return strtolower(preg_replace('/[^a-z0-9]/', '', (string) $nama_kategori));
}

/**
 * Jumlah buku aktif per kategori (sidebar filter).
 *
 * @return array{total: int, categories: array<string, int>}
 */
function get_user_kategori_buku_counts($conn)
{
    $counts = ['total' => 0, 'categories' => []];

    $total_query = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM m_buku WHERE is_active = 1');
    if ($total_query && ($row = mysqli_fetch_assoc($total_query))) {
        $counts['total'] = (int) ($row['total'] ?? 0);
    }

    $query = mysqli_query(
        $conn,
        'SELECT m_kategori.nama_kategori, COUNT(m_buku.id) AS jumlah
         FROM m_buku
         INNER JOIN m_kategori ON m_buku.id_kategori = m_kategori.id
         WHERE m_buku.is_active = 1 AND m_kategori.is_active = 1
         GROUP BY m_kategori.id, m_kategori.nama_kategori
         ORDER BY m_kategori.nama_kategori ASC'
    );

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $nama = $row['nama_kategori'] ?? '';
            if ($nama !== '') {
                $counts['categories'][$nama] = (int) ($row['jumlah'] ?? 0);
            }
        }
    }

    return $counts;
}

/** @deprecated Gunakan get_user_kategori_buku_counts */
function get_user_kategori_event_counts($conn)
{
    return get_user_kategori_buku_counts($conn);
}

/**
 * Daftar buku untuk katalog user.
 *
 * @return array<int, array<string, mixed>>
 */
function get_user_buku($conn, $search = '', $kategori = '', $limit = 7)
{
    $where = ['m_buku.is_active = 1'];

    $search = trim($search);
    $kategori = trim($kategori);

    if ($search !== '') {
        $search_esc = mysqli_real_escape_string($conn, $search);
        $where[] = "(
            m_buku.judul_buku LIKE '%$search_esc%'
            OR m_kategori.nama_kategori LIKE '%$search_esc%'
            OR m_buku.penulis LIKE '%$search_esc%'
            OR m_buku.penerbit LIKE '%$search_esc%'
            OR m_buku.deskripsi LIKE '%$search_esc%'
        )";
    }

    if ($kategori !== '' && $kategori !== 'Semua Kategori') {
        $kategori_esc = mysqli_real_escape_string($conn, $kategori);
        $where[] = "m_kategori.nama_kategori = '$kategori_esc'";
    }

    $where_sql = 'WHERE ' . implode(' AND ', $where);
    $limit_sql = '';

    if ($limit !== null && (int) $limit !== 0) {
        $limit = max(1, min(500, (int) $limit));
        $limit_sql = ' LIMIT ' . $limit;
    }

    $query = "SELECT
                m_kategori.nama_kategori,
                m_buku.id,
                m_buku.id_kategori,
                m_buku.judul_buku,
                m_buku.deskripsi,
                m_buku.penulis,
                m_buku.penerbit,
                m_buku.tahun_penerbit,
                m_buku.harga,
                m_buku.stok,
                m_buku.gambar,
                m_buku.is_active
            FROM m_buku
            LEFT JOIN m_kategori ON m_buku.id_kategori = m_kategori.id
            $where_sql
            ORDER BY m_buku.id DESC
            $limit_sql";

    $result = mysqli_query($conn, $query);
    $rows = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    return $rows;
}

/**
 * Wrapper agar pemanggilan lama get_user_events(...) tetap jalan ($kota diabaikan).
 *
 * @return array<int, array<string, mixed>>
 */
function get_user_events($conn, $search = '', $kategori = '', $kota = '', $limit = 7)
{
    return get_user_buku($conn, $search, $kategori, $limit);
}

function user_event_date_short($date)
{
    if ($date === null || $date === '') {
        return '-';
    }

    return user_format_tanggal_card($date);
}

function user_event_booking_attrs($buku, $cat_name = null, $extra_class = '')
{
    $cat = $cat_name ?? ($buku['nama_kategori'] ?? '-');
    $penulis = trim((string) ($buku['penulis'] ?? ''));
    $penerbit = trim((string) ($buku['penerbit'] ?? ''));
    $meta = $penulis;
    if ($penerbit !== '') {
        $meta = ($meta !== '' ? $penulis . ' · ' : '') . $penerbit;
    }
    if ($meta === '') {
        $meta = '-';
    }

    $attrs = [
        'data-event-id' => (int) ($buku['id'] ?? 0),
        'data-event-title' => $buku['judul_buku'] ?? '',
        'data-event-harga' => (float) ($buku['harga'] ?? 0),
        'data-event-kuota' => (int) ($buku['stok'] ?? 0),
        'data-event-date' => user_event_date_short($buku['tahun_penerbit'] ?? ''),
        'data-event-location' => $meta,
        'data-event-icon' => user_event_cat_icon($cat),
    ];

    $class = trim('btn-wishlist-trigger ' . $extra_class);
    $html = 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';

    foreach ($attrs as $key => $value) {
        $html .= ' ' . $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
    }

    return $html;
}

function user_search_label($kategori, $kota)
{
    if ($kota !== '') {
        return $kota;
    }

    if ($kategori !== '' && $kategori !== 'Semua Kategori') {
        return $kategori;
    }

    return 'Semua Kategori';
}

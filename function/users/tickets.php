<?php

define('USER_STATUS_MENUNGGU_PEMBAYARAN', 1);
define('USER_STATUS_MENUNGGU_KONFIRMASI', 2);
define('USER_STATUS_TERKONFIRMASI', 3);
define('USER_STATUS_DIBATALKAN', 4);
define('USER_BOOKING_MAX_PER_TX', 5);

/**
 * @return array<int, array<string, mixed>>
 */
function get_user_tickets($conn, $user_id)
{
    $user_id = (int) $user_id;

    if ($user_id <= 0) {
        return [];
    }

    $query = "SELECT
                h.id AS id_header,
                h.kode_transaksi,
                (SELECT GROUP_CONCAT(b.judul_buku ORDER BY d.id ASC SEPARATOR ', ')
                 FROM trans_d_pesanan d
                 INNER JOIN m_buku b ON b.id = d.id_buku
                 WHERE d.id_header = h.id) AS nama_event,
                (SELECT MIN(b.tahun_penerbit)
                 FROM trans_d_pesanan d
                 INNER JOIN m_buku b ON b.id = d.id_buku
                 WHERE d.id_header = h.id) AS tanggal_event,
                (SELECT CONCAT(
                    COALESCE(NULLIF(TRIM(b.penulis), ''), '-'),
                    ' · ',
                    COALESCE(NULLIF(TRIM(b.penerbit), ''), '-')
                 )
                 FROM trans_d_pesanan d
                 INNER JOIN m_buku b ON b.id = d.id_buku
                 WHERE d.id_header = h.id
                 ORDER BY d.id ASC
                 LIMIT 1) AS lokasi,
                (SELECT SUM(d.total_harga) FROM trans_d_pesanan d WHERE d.id_header = h.id) AS total_harga,
                (SELECT SUM(d.jumlah_buku) FROM trans_d_pesanan d WHERE d.id_header = h.id) AS jumlah_tiket,
                h.id_pengirim,
                h.deskripsi_pengiriman,
                sp.status_pengiriman AS status_pengiriman,
                h.id_status,
                m_status.status
            FROM trans_h_pesanan h
            LEFT JOIN m_status ON h.id_status = m_status.id
            LEFT JOIN m_status_pengiriman sp ON h.id_pengirim = sp.id
            WHERE h.id_user = $user_id
            ORDER BY h.id DESC";

    $result = mysqli_query($conn, $query);
    $tickets = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tickets[] = $row;
        }
    }

    return $tickets;
}

function user_ticket_stub_parts($datetime)
{
    if ($datetime === null || $datetime === '') {
        return ['day' => '-', 'month' => '-'];
    }

    $ts = strtotime($datetime);
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    return [
        'day' => date('j', $ts),
        'month' => $bulan[(int) date('n', $ts)],
    ];
}

function user_ticket_filter_group($id_status)
{
    $id_status = (int) $id_status;

    if (in_array($id_status, [USER_STATUS_MENUNGGU_PEMBAYARAN, USER_STATUS_MENUNGGU_KONFIRMASI], true)) {
        return 'aktif';
    }

    if (in_array($id_status, [USER_STATUS_TERKONFIRMASI, USER_STATUS_DIBATALKAN], true)) {
        return 'selesai';
    }

    return 'selesai';
}

function user_ticket_status_class($id_status)
{
    switch ((int) $id_status) {
        case USER_STATUS_MENUNGGU_PEMBAYARAN:
            return 'status-pending';
        case USER_STATUS_MENUNGGU_KONFIRMASI:
            return 'status-waiting';
        case USER_STATUS_TERKONFIRMASI:
            return 'status-confirmed';
        case USER_STATUS_DIBATALKAN:
            return 'status-cancelled';
        default:
            return 'status-waiting';
    }
}

function user_ticket_status_icon($id_status)
{
    switch ((int) $id_status) {
        case USER_STATUS_MENUNGGU_PEMBAYARAN:
            return 'bi-clock-history';
        case USER_STATUS_MENUNGGU_KONFIRMASI:
            return 'bi-hourglass-split';
        case USER_STATUS_TERKONFIRMASI:
            return 'bi-check-circle-fill';
        case USER_STATUS_DIBATALKAN:
            return 'bi-x-circle-fill';
        default:
            return 'bi-info-circle-fill';
    }
}

function user_ticket_stub_style($id_status)
{
    switch ((int) $id_status) {
        case USER_STATUS_MENUNGGU_PEMBAYARAN:
            return 'background: linear-gradient(180deg, #2d1f00 0%, #1a1200 100%);';
        case USER_STATUS_DIBATALKAN:
            return 'background: linear-gradient(180deg, #1f1f1f 0%, #111 100%);';
        default:
            return '';
    }
}

function user_ticket_show_actions($id_status)
{
    return (int) $id_status === USER_STATUS_MENUNGGU_PEMBAYARAN;
}

/**
 * Status pengiriman dari admin = "Dikirim" (case-insensitive).
 */
function user_ticket_pengiriman_is_dikirim($ticket)
{
    $s = trim((string) ($ticket['status_pengiriman'] ?? ''));

    return strcasecmp($s, 'Dikirim') === 0;
}

/**
 * Tampilkan tombol konfirmasi paket sampai (hanya saat pembayaran terkonfirmasi & sedang dikirim).
 */
function user_ticket_show_konfirmasi_terima($ticket)
{
    $id_status = (int) ($ticket['id_status'] ?? 0);

    if ($id_status !== USER_STATUS_TERKONFIRMASI) {
        return false;
    }

    return user_ticket_pengiriman_is_dikirim($ticket);
}

/**
 * ID status_pengiriman untuk konfirmasi barang diterima (urutan prioritas nama di master).
 *
 * @return int|null
 */
function user_lookup_status_pengiriman_diterima_id($conn)
{
    $candidates = ['Diterima', 'Sampai Tujuan', 'Selesai', 'Paket diterima'];

    foreach ($candidates as $label) {
        $esc = mysqli_real_escape_string($conn, $label);
        $q = mysqli_query(
            $conn,
            "SELECT id FROM m_status_pengiriman WHERE LOWER(TRIM(status_pengiriman)) = LOWER('$esc') LIMIT 1"
        );

        if ($q && mysqli_num_rows($q) === 1) {
            $row = mysqli_fetch_assoc($q);

            return (int) ($row['id'] ?? 0);
        }
    }

    return null;
}

/**
 * User menandai paket sudah sampai (dari status "Dikirim" ke status diterima berikutnya di master).
 *
 * @return array{success: bool, message: string}
 */
function user_confirm_terima_paket($conn, $user_id, $id_header)
{
    $user_id = (int) $user_id;
    $id_header = (int) $id_header;

    if ($user_id <= 0 || $id_header <= 0) {
        return ['success' => false, 'message' => 'Data pesanan tidak valid.'];
    }

    $query = mysqli_query(
        $conn,
        "SELECT h.id, h.id_status, h.id_pengirim, sp.status_pengiriman
         FROM trans_h_pesanan h
         LEFT JOIN m_status_pengiriman sp ON sp.id = h.id_pengirim
         WHERE h.id = $id_header AND h.id_user = $user_id
         LIMIT 1"
    );

    if (!$query || mysqli_num_rows($query) !== 1) {
        return ['success' => false, 'message' => 'Pesanan tidak ditemukan.'];
    }

    $row = mysqli_fetch_assoc($query);
    $id_status = (int) ($row['id_status'] ?? 0);

    if ($id_status !== USER_STATUS_TERKONFIRMASI) {
        return ['success' => false, 'message' => 'Konfirmasi paket hanya untuk pesanan yang pembayarannya sudah dikonfirmasi.'];
    }

    $sp_status = trim((string) ($row['status_pengiriman'] ?? ''));

    if (strcasecmp($sp_status, 'Dikirim') !== 0) {
        return ['success' => false, 'message' => 'Tombol ini hanya aktif saat status pengiriman "Dikirim".'];
    }

    $next_id = user_lookup_status_pengiriman_diterima_id($conn);

    if ($next_id <= 0) {
        return [
            'success' => false,
            'message' => 'Admin belum mengatur status penerima (mis. "Diterima") di master pengiriman. Hubungi admin.',
        ];
    }

    $current_pengirim = (int) ($row['id_pengirim'] ?? 0);

    if ($current_pengirim === $next_id) {
        return ['success' => false, 'message' => 'Status pengiriman sudah diperbarui.'];
    }

    $upd = mysqli_query(
        $conn,
        "UPDATE trans_h_pesanan SET id_pengirim = $next_id
         WHERE id = $id_header AND id_user = $user_id
         LIMIT 1"
    );

    if (!$upd) {
        return ['success' => false, 'message' => 'Gagal memperbarui status. Silakan coba lagi.'];
    }

    return [
        'success' => true,
        'message' => 'Terima kasih! Paket ditandai sudah sampai tujuan.',
    ];
}

function user_ticket_card_opacity($id_status)
{
    return (int) $id_status === USER_STATUS_DIBATALKAN ? '0.6' : '1';
}

function cancel_user_transaksi($conn, $user_id, $id_header)
{
    $user_id = (int) $user_id;
    $id_header = (int) $id_header;

    if ($user_id <= 0 || $id_header <= 0) {
        return ['success' => false, 'message' => 'Data transaksi tidak valid.'];
    }

    $query = mysqli_query(
        $conn,
        "SELECT id, id_status FROM trans_h_pesanan
         WHERE id = $id_header AND id_user = $user_id
         LIMIT 1"
    );

    if (!$query || mysqli_num_rows($query) !== 1) {
        return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
    }

    $row = mysqli_fetch_assoc($query);

    if ((int) $row['id_status'] !== USER_STATUS_MENUNGGU_PEMBAYARAN) {
        return ['success' => false, 'message' => 'Hanya pesanan Menunggu Pembayaran yang dapat dibatalkan.'];
    }

    $now = mysqli_real_escape_string($conn, date('Y-m-d H:i:s'));
    $update = mysqli_query(
        $conn,
        "UPDATE trans_h_pesanan SET
            id_status = " . USER_STATUS_DIBATALKAN . ",
            updated_at = '$now'
         WHERE id = $id_header AND id_user = $user_id
         LIMIT 1"
    );

    if (!$update) {
        return ['success' => false, 'message' => 'Gagal membatalkan pemesanan.'];
    }

    return ['success' => true, 'message' => 'Pemesanan berhasil dibatalkan.'];
}

function upload_bukti_pembayaran($file, $old_bukti = null)
{
    $upload_dir = ROOT_PATH . '/assets/admin/img/bukti_pembayaran/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $old_bukti ?? '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext, true)) {
        return false;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return false;
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $target = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return false;
    }

    if ($old_bukti && file_exists($upload_dir . $old_bukti)) {
        unlink($upload_dir . $old_bukti);
    }

    return $filename;
}

function upload_user_bukti_pembayaran($conn, $user_id, $id_header, $file, $catatan = '')
{
    $user_id = (int) $user_id;
    $id_header = (int) $id_header;

    if ($user_id <= 0) {
        return ['success' => false, 'message' => 'Anda harus login terlebih dahulu.'];
    }

    if ($id_header <= 0) {
        return ['success' => false, 'message' => 'Data transaksi tidak valid.'];
    }

    $query = mysqli_query(
        $conn,
        "SELECT id, id_status, bukti_pembayaran, deskripsi
         FROM trans_h_pesanan
         WHERE id = $id_header AND id_user = $user_id
         LIMIT 1"
    );

    if (!$query || mysqli_num_rows($query) !== 1) {
        return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
    }

    $row = mysqli_fetch_assoc($query);

    if ((int) $row['id_status'] !== USER_STATUS_MENUNGGU_PEMBAYARAN) {
        return ['success' => false, 'message' => 'Upload bukti hanya untuk pesanan Menunggu Pembayaran.'];
    }

    $old_bukti = $row['bukti_pembayaran'] ?? '';
    $uploaded = upload_bukti_pembayaran($file, $old_bukti);

    if ($uploaded === false) {
        return ['success' => false, 'message' => 'Gagal upload file. Gunakan JPG, PNG, atau PDF maks. 5MB.'];
    }

    if ($uploaded === '') {
        return ['success' => false, 'message' => 'File bukti pembayaran wajib diupload.'];
    }

    $bukti_esc = mysqli_real_escape_string($conn, $uploaded);
    $now = mysqli_real_escape_string($conn, date('Y-m-d H:i:s'));
    $catatan = trim($catatan);
    $deskripsi = $row['deskripsi'] ?? '';

    if ($catatan !== '') {
        $catatan_esc = mysqli_real_escape_string($conn, $catatan);
        $deskripsi = trim($deskripsi . "\nCatatan pembayaran: " . $catatan);
    }

    $deskripsi_esc = mysqli_real_escape_string($conn, $deskripsi);

    $update = mysqli_query(
        $conn,
        "UPDATE trans_h_pesanan SET
            bukti_pembayaran = '$bukti_esc',
            deskripsi = '$deskripsi_esc',
            id_status = " . USER_STATUS_MENUNGGU_KONFIRMASI . ",
            updated_at = '$now'
         WHERE id = $id_header AND id_user = $user_id
         LIMIT 1"
    );

    if (!$update) {
        $upload_dir = ROOT_PATH . '/assets/admin/img/bukti_pembayaran/';
        if (file_exists($upload_dir . $uploaded)) {
            unlink($upload_dir . $uploaded);
        }

        return ['success' => false, 'message' => 'Gagal menyimpan bukti pembayaran.'];
    }

    return [
        'success' => true,
        'message' => 'Bukti pembayaran berhasil diupload. Status berubah menjadi Menunggu Konfirmasi.',
    ];
}

function generate_kode_transaksi($conn)
{
    $prefix = 'BK-' . date('Ymd') . '-';

    do {
        $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $kode = $prefix . $suffix;
        $kode_esc = mysqli_real_escape_string($conn, $kode);
        $check = mysqli_query($conn, "SELECT id FROM trans_h_pesanan WHERE kode_transaksi = '$kode_esc' LIMIT 1");
    } while ($check && mysqli_num_rows($check) > 0);

    return $kode;
}

function create_user_booking($conn, $user_id, $buku_id, $qty)
{
    $user_id = (int) $user_id;
    $buku_id = (int) $buku_id;
    $qty = (int) $qty;

    if ($user_id <= 0) {
        return ['success' => false, 'message' => 'Anda harus login terlebih dahulu.'];
    }

    if ($buku_id <= 0) {
        return ['success' => false, 'message' => 'Buku tidak valid.'];
    }

    if ($qty < 1) {
        return ['success' => false, 'message' => 'Jumlah buku minimal 1.'];
    }

    if ($qty > USER_BOOKING_MAX_PER_TX) {
        return ['success' => false, 'message' => 'Maksimal ' . USER_BOOKING_MAX_PER_TX . ' buku per transaksi.'];
    }

    mysqli_begin_transaction($conn);

    $buku_query = mysqli_query(
        $conn,
        "SELECT id, judul_buku, harga, stok, is_active
         FROM m_buku
         WHERE id = $buku_id
         LIMIT 1
         FOR UPDATE"
    );

    if (!$buku_query || mysqli_num_rows($buku_query) !== 1) {
        mysqli_rollback($conn);

        return ['success' => false, 'message' => 'Buku tidak ditemukan.'];
    }

    $buku = mysqli_fetch_assoc($buku_query);

    if ((int) ($buku['is_active'] ?? 0) !== 1) {
        mysqli_rollback($conn);

        return ['success' => false, 'message' => 'Buku tidak tersedia.'];
    }

    $stok = (int) ($buku['stok'] ?? 0);

    if ($stok < $qty) {
        mysqli_rollback($conn);

        return [
            'success' => false,
            'message' => 'Stok tersisa ' . $stok . ', pesanan ' . $qty . ' buku tidak dapat diproses.',
        ];
    }

    $harga_satuan = (float) ($buku['harga'] ?? 0);
    $total_harga = $harga_satuan * $qty;
    $kode = generate_kode_transaksi($conn);
    $kode_esc = mysqli_real_escape_string($conn, $kode);
    $now = mysqli_real_escape_string($conn, date('Y-m-d H:i:s'));
    $deskripsi = mysqli_real_escape_string($conn, 'Pemesanan buku: ' . ($buku['judul_buku'] ?? ''));

    $insert_header = mysqli_query(
        $conn,
        "INSERT INTO trans_h_pesanan (
            kode_transaksi, id_user, bukti_pembayaran, deskripsi,
            is_paid, id_status, created_at
        ) VALUES (
            '$kode_esc', $user_id, '', '$deskripsi',
            0, " . USER_STATUS_MENUNGGU_PEMBAYARAN . ", '$now'
        )"
    );

    if (!$insert_header) {
        mysqli_rollback($conn);

        return ['success' => false, 'message' => 'Gagal menyimpan pesanan: ' . mysqli_error($conn)];
    }

    $id_header = (int) mysqli_insert_id($conn);

    $insert_detail = mysqli_query(
        $conn,
        "INSERT INTO trans_d_pesanan (
            id_header, id_buku, harga_satuan, jumlah_buku, total_harga
        ) VALUES (
            $id_header, $buku_id, $harga_satuan, $qty, $total_harga
        )"
    );

    if (!$insert_detail) {
        mysqli_rollback($conn);

        return ['success' => false, 'message' => 'Gagal menyimpan detail pesanan: ' . mysqli_error($conn)];
    }

    mysqli_commit($conn);

    return [
        'success' => true,
        'message' => 'Pesanan berhasil dibuat. Status: Menunggu Pembayaran. Silakan upload bukti pembayaran di Pesanan Saya.',
        'kode_transaksi' => $kode,
    ];
}

/**
 * Satu transaksi header dengan satu atau beberapa baris detail (keranjang).
 *
 * @param mysqli $conn
 * @param array<int, array{id_buku:int, qty:int}> $items
 * @return array{success: bool, message: string, kode_transaksi?: string}
 */
function create_user_booking_from_cart($conn, $user_id, $items)
{
    $user_id = (int) $user_id;

    if ($user_id <= 0) {
        return ['success' => false, 'message' => 'Anda harus login terlebih dahulu.'];
    }

    if (!is_array($items) || $items === []) {
        return ['success' => false, 'message' => 'Keranjang kosong.'];
    }

    $merged = [];

    foreach ($items as $row) {
        $buku_id = (int) ($row['id_buku'] ?? $row['id'] ?? 0);
        $qty = (int) ($row['qty'] ?? 0);

        if ($buku_id <= 0 || $qty < 1) {
            continue;
        }

        if ($qty > USER_BOOKING_MAX_PER_TX) {
            return [
                'success' => false,
                'message' => 'Maksimal ' . USER_BOOKING_MAX_PER_TX . ' buku per judul dalam satu pesanan.',
            ];
        }

        if (!isset($merged[$buku_id])) {
            $merged[$buku_id] = 0;
        }

        $merged[$buku_id] += $qty;

        if ($merged[$buku_id] > USER_BOOKING_MAX_PER_TX) {
            return [
                'success' => false,
                'message' => 'Maksimal ' . USER_BOOKING_MAX_PER_TX . ' buku per judul dalam satu pesanan.',
            ];
        }
    }

    if ($merged === []) {
        return ['success' => false, 'message' => 'Item keranjang tidak valid.'];
    }

    ksort($merged, SORT_NUMERIC);

    mysqli_begin_transaction($conn);

    $buku_rows = [];

    foreach ($merged as $buku_id => $qty) {
        $buku_query = mysqli_query(
            $conn,
            "SELECT id, judul_buku, harga, stok, is_active
             FROM m_buku
             WHERE id = $buku_id
             LIMIT 1
             FOR UPDATE"
        );

        if (!$buku_query || mysqli_num_rows($buku_query) !== 1) {
            mysqli_rollback($conn);

            return ['success' => false, 'message' => 'Salah satu buku tidak ditemukan.'];
        }

        $buku = mysqli_fetch_assoc($buku_query);

        if ((int) ($buku['is_active'] ?? 0) !== 1) {
            mysqli_rollback($conn);

            return ['success' => false, 'message' => 'Buku "' . ($buku['judul_buku'] ?? '') . '" tidak tersedia.'];
        }

        $stok = (int) ($buku['stok'] ?? 0);

        if ($stok < $qty) {
            mysqli_rollback($conn);

            return [
                'success' => false,
                'message' => 'Stok "' . ($buku['judul_buku'] ?? '') . '" tersisa ' . $stok . ', tidak cukup untuk ' . $qty . ' buku.',
            ];
        }

        $buku_rows[$buku_id] = [
            'buku' => $buku,
            'qty' => $qty,
        ];
    }

    $judul_ringkas = [];

    foreach ($buku_rows as $row) {
        $judul_ringkas[] = $row['buku']['judul_buku'] ?? '';
    }

    $judul_ringkas = array_values(array_filter($judul_ringkas));
    $deskripsi_text = 'Pemesanan keranjang: ' . implode(', ', $judul_ringkas);
    $deskripsi = mysqli_real_escape_string($conn, $deskripsi_text);
    $kode = generate_kode_transaksi($conn);
    $kode_esc = mysqli_real_escape_string($conn, $kode);
    $now = mysqli_real_escape_string($conn, date('Y-m-d H:i:s'));

    $insert_header = mysqli_query(
        $conn,
        "INSERT INTO trans_h_pesanan (
            kode_transaksi, id_user, bukti_pembayaran, deskripsi,
            is_paid, id_status, created_at
        ) VALUES (
            '$kode_esc', $user_id, '', '$deskripsi',
            0, " . USER_STATUS_MENUNGGU_PEMBAYARAN . ", '$now'
        )"
    );

    if (!$insert_header) {
        mysqli_rollback($conn);

        return ['success' => false, 'message' => 'Gagal menyimpan pesanan: ' . mysqli_error($conn)];
    }

    $id_header = (int) mysqli_insert_id($conn);

    foreach ($buku_rows as $buku_id => $row) {
        $buku = $row['buku'];
        $qty = (int) $row['qty'];
        $harga_satuan = (float) ($buku['harga'] ?? 0);
        $total_harga = $harga_satuan * $qty;

        $insert_detail = mysqli_query(
            $conn,
            "INSERT INTO trans_d_pesanan (
                id_header, id_buku, harga_satuan, jumlah_buku, total_harga
            ) VALUES (
                $id_header, $buku_id, $harga_satuan, $qty, $total_harga
            )"
        );

        if (!$insert_detail) {
            mysqli_rollback($conn);

            return ['success' => false, 'message' => 'Gagal menyimpan detail pesanan: ' . mysqli_error($conn)];
        }
    }

    mysqli_commit($conn);

    return [
        'success' => true,
        'message' => 'Pesanan berhasil dibuat. Status: Menunggu Pembayaran. Silakan upload bukti pembayaran di Pesanan Saya.',
        'kode_transaksi' => $kode,
    ];
}

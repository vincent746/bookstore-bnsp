<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

$redirect_url = admin_module_url('transaksi');
$admin_id = (int) $_SESSION['admin_id'];
$action = $_POST['action'] ?? '';
$now = now_indonesia();

function transaksi_redirect($url, $type, $message)
{
    $_SESSION['transaksi_' . $type] = $message;
    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect_url);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    transaksi_redirect($redirect_url, 'error', 'Data transaksi tidak valid.');
}

if ($action === 'update_pengiriman') {
    $id_pengirim = (int) ($_POST['id_pengirim'] ?? 0);
    $deskripsi_pengiriman = mysqli_real_escape_string($conn, trim($_POST['deskripsi_pengiriman'] ?? ''));

    if ($id_pengirim <= 0) {
        transaksi_redirect($redirect_url, 'error', 'Pilih status pengiriman.');
    }

    $cek_status = mysqli_query($conn, "SELECT id FROM m_status_pengiriman WHERE id = $id_pengirim LIMIT 1");

    if (!$cek_status || mysqli_num_rows($cek_status) === 0) {
        transaksi_redirect($redirect_url, 'error', 'Status pengiriman tidak valid.');
    }

    $header_query = mysqli_query($conn, "SELECT id, id_status FROM trans_h_pesanan WHERE id = $id LIMIT 1");
    $header = mysqli_fetch_assoc($header_query);

    if (!$header) {
        transaksi_redirect($redirect_url, 'error', 'Transaksi tidak ditemukan.');
    }

    $id_status = (int) $header['id_status'];

    if ($id_status === STATUS_MENUNGGU_PEMBAYARAN) {
        transaksi_redirect($redirect_url, 'error', 'Status pengiriman belum dapat diubah saat Menunggu Pembayaran.');
    }

    $update = mysqli_query(
        $conn,
        "UPDATE trans_h_pesanan SET
            id_pengirim = $id_pengirim,
            deskripsi_pengiriman = '$deskripsi_pengiriman',
            updated_at = '$now',
            updated_by = $admin_id
         WHERE id = $id LIMIT 1"
    );

    if (!$update) {
        transaksi_redirect($redirect_url, 'error', 'Gagal memperbarui status pengiriman: ' . mysqli_error($conn));
    }

    transaksi_redirect($redirect_url, 'success', 'Status pengiriman berhasil diperbarui.');
}

$deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));

if ($deskripsi === '') {
    transaksi_redirect($redirect_url, 'error', 'Deskripsi wajib diisi.');
}

$header_query = mysqli_query($conn, "SELECT id, id_status, is_paid FROM trans_h_pesanan WHERE id = $id LIMIT 1");
$header = mysqli_fetch_assoc($header_query);

if (!$header) {
    transaksi_redirect($redirect_url, 'error', 'Transaksi tidak ditemukan.');
}

$id_status = (int) $header['id_status'];
$is_paid = (int) $header['is_paid'];

$detail_rows = [];
$detail_query = mysqli_query($conn, "SELECT id_buku, jumlah_buku FROM trans_d_pesanan WHERE id_header = $id");

if ($detail_query) {
    while ($row = mysqli_fetch_assoc($detail_query)) {
        $detail_rows[] = $row;
    }
}

if (empty($detail_rows)) {
    transaksi_redirect($redirect_url, 'error', 'Detail pesanan tidak ditemukan.');
}

mysqli_begin_transaction($conn);

if ($action === 'paid') {
    if ($id_status !== STATUS_MENUNGGU_KONFIRMASI) {
        mysqli_rollback($conn);
        transaksi_redirect($redirect_url, 'error', 'Transaksi hanya bisa dikonfirmasi saat status Menunggu Konfirmasi.');
    }

    foreach ($detail_rows as $detail) {
        $id_buku = (int) $detail['id_buku'];
        $jumlah = (int) $detail['jumlah_buku'];

        if ($jumlah <= 0 || $id_buku <= 0) {
            mysqli_rollback($conn);
            transaksi_redirect($redirect_url, 'error', 'Jumlah buku tidak valid.');
        }

        $update_stok = mysqli_query(
            $conn,
            "UPDATE m_buku SET stok = stok - $jumlah WHERE id = $id_buku AND stok >= $jumlah"
        );

        if (!$update_stok || mysqli_affected_rows($conn) === 0) {
            mysqli_rollback($conn);
            transaksi_redirect($redirect_url, 'error', 'Stok buku tidak mencukupi atau buku tidak ditemukan.');
        }
    }

    $update_header = mysqli_query(
        $conn,
        "UPDATE trans_h_pesanan SET
            deskripsi = '$deskripsi',
            is_paid = 1,
            id_status = " . STATUS_TERKONFIRMASI . ",
            updated_at = '$now',
            updated_by = $admin_id
         WHERE id = $id LIMIT 1"
    );

    if (!$update_header) {
        mysqli_rollback($conn);
        transaksi_redirect($redirect_url, 'error', 'Gagal mengkonfirmasi pembayaran: ' . mysqli_error($conn));
    }

    mysqli_commit($conn);
    transaksi_redirect($redirect_url, 'success', 'Pembayaran berhasil dikonfirmasi. Stok buku telah dikurangi.');
}

if ($action === 'cancel') {
    $success_message = 'Transaksi berhasil dibatalkan.';
    $restore_stok = false;

    if ($id_status === STATUS_MENUNGGU_KONFIRMASI) {
        $restore_stok = false;
        $success_message = 'Transaksi berhasil dibatalkan. Status menjadi Dibatalkan.';
    } elseif ($id_status === STATUS_TERKONFIRMASI && $is_paid === 1) {
        $restore_stok = true;
        $success_message = 'Transaksi berhasil dibatalkan. Stok buku telah dikembalikan.';
    } else {
        mysqli_rollback($conn);
        transaksi_redirect($redirect_url, 'error', 'Transaksi tidak dapat dibatalkan pada status saat ini.');
    }

    if ($restore_stok) {
        foreach ($detail_rows as $detail) {
            $id_buku = (int) $detail['id_buku'];
            $jumlah = (int) $detail['jumlah_buku'];

            $restore_ok = mysqli_query(
                $conn,
                "UPDATE m_buku SET stok = stok + $jumlah WHERE id = $id_buku"
            );

            if (!$restore_ok) {
                mysqli_rollback($conn);
                transaksi_redirect($redirect_url, 'error', 'Gagal mengembalikan stok buku: ' . mysqli_error($conn));
            }
        }
    }

    $update_header = mysqli_query(
        $conn,
        "UPDATE trans_h_pesanan SET
            deskripsi = '$deskripsi',
            is_paid = 0,
            id_status = " . STATUS_DIBATALKAN . ",
            updated_at = '$now',
            updated_by = $admin_id
         WHERE id = $id LIMIT 1"
    );

    if (!$update_header) {
        mysqli_rollback($conn);
        transaksi_redirect($redirect_url, 'error', 'Gagal membatalkan transaksi: ' . mysqli_error($conn));
    }

    mysqli_commit($conn);
    transaksi_redirect($redirect_url, 'success', $success_message);
}

mysqli_rollback($conn);
transaksi_redirect($redirect_url, 'error', 'Aksi tidak dikenali.');

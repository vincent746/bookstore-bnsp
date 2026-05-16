<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

$redirect_url = admin_module_url('buku');
$admin_id = (int) $_SESSION['admin_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$now = now_indonesia();

function buku_redirect($url, $type, $message)
{
    $_SESSION['buku_' . $type] = $message;
    header('Location: ' . $url);
    exit;
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        buku_redirect($redirect_url, 'error', 'Data buku tidak valid.');
    }

    $gambar_row = mysqli_query($conn, "SELECT gambar FROM m_buku WHERE id = $id LIMIT 1");
    $gambar_data = mysqli_fetch_assoc($gambar_row);

    if (!$gambar_data) {
        buku_redirect($redirect_url, 'error', 'Buku tidak ditemukan.');
    }

    $cek_pesanan = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt FROM trans_d_pesanan WHERE id_buku = $id"
    );
    $pesanan_row = $cek_pesanan ? mysqli_fetch_assoc($cek_pesanan) : null;
    $jumlah_ref = (int) ($pesanan_row['cnt'] ?? 0);

    if ($jumlah_ref > 0) {
        buku_redirect(
            $redirect_url,
            'error',
            'Buku tidak dapat dihapus karena masih dipakai di riwayat pesanan (' . $jumlah_ref . ' baris). Nonaktifkan buku (status Tidak Aktif) jika tidak ingin ditampilkan, atau hapus data pesanan terkait di database jika memang diperbolehkan.'
        );
    }

    $query = "DELETE FROM m_buku WHERE id = $id LIMIT 1";

    if (mysqli_query($conn, $query)) {
        if (!empty($gambar_data['gambar'])) {
            delete_buku_gambar_file($gambar_data['gambar']);
        }

        buku_redirect($redirect_url, 'success', 'Buku berhasil dihapus.');
    }

    buku_redirect($redirect_url, 'error', 'Gagal menghapus buku: ' . mysqli_error($conn));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect_url);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$id_kategori = (int) ($_POST['id_kategori'] ?? 0);
$judul_buku = mysqli_real_escape_string($conn, trim($_POST['judul_buku'] ?? ''));
$deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
$penulis = mysqli_real_escape_string($conn, trim($_POST['penulis'] ?? ''));
$penerbit = mysqli_real_escape_string($conn, trim($_POST['penerbit'] ?? ''));
$tahun_input = trim($_POST['tahun_penerbit'] ?? '');
$tahun_penerbit = mysqli_real_escape_string($conn, $tahun_input);
$harga = (float) str_replace(',', '.', $_POST['harga'] ?? 0);
$stok = (int) ($_POST['stok'] ?? 0);
$is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;
$gambar_lama = trim($_POST['gambar_lama'] ?? '');

if ($id_kategori <= 0 || $judul_buku === '' || $penulis === '' || $penerbit === '' || $tahun_input === '') {
    buku_redirect($redirect_url, 'error', 'Kategori, judul, penulis, penerbit, dan tahun terbit wajib diisi.');
}

if ($harga < 0 || $stok < 0) {
    buku_redirect($redirect_url, 'error', 'Harga dan stok tidak boleh negatif.');
}

$gambar_result = upload_buku_gambar($_FILES['gambar'] ?? [], $gambar_lama);

if ($gambar_result === false) {
    buku_redirect($redirect_url, 'error', 'Gagal mengupload gambar.');
}

$gambar = mysqli_real_escape_string($conn, (string) $gambar_result);

if ($action === 'create') {
    $query = "INSERT INTO m_buku (
                id_kategori, judul_buku, deskripsi, penulis, penerbit, tahun_penerbit,
                harga, stok, gambar, is_active, created_at, created_by
              ) VALUES (
                $id_kategori, '$judul_buku', '$deskripsi', '$penulis', '$penerbit', '$tahun_penerbit',
                $harga, $stok, '$gambar', $is_active, '$now', $admin_id
              )";

    if (mysqli_query($conn, $query)) {
        buku_redirect($redirect_url, 'success', 'Buku berhasil ditambahkan.');
    }

    buku_redirect($redirect_url, 'error', 'Gagal menambahkan buku: ' . mysqli_error($conn));
}

if ($action === 'update') {
    if ($id <= 0) {
        buku_redirect($redirect_url, 'error', 'Data buku tidak valid.');
    }

    $query = "UPDATE m_buku SET
                id_kategori = $id_kategori,
                judul_buku = '$judul_buku',
                deskripsi = '$deskripsi',
                penulis = '$penulis',
                penerbit = '$penerbit',
                tahun_penerbit = '$tahun_penerbit',
                harga = $harga,
                stok = $stok,
                gambar = '$gambar',
                is_active = $is_active,
                updated_at = '$now',
                updated_by = $admin_id
              WHERE id = $id LIMIT 1";

    if (mysqli_query($conn, $query)) {
        buku_redirect($redirect_url, 'success', 'Buku berhasil diperbarui.');
    }

    buku_redirect($redirect_url, 'error', 'Gagal memperbarui buku: ' . mysqli_error($conn));
}

buku_redirect($redirect_url, 'error', 'Aksi tidak dikenali.');

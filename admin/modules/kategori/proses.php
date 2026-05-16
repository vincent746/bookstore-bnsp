<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

$redirect_url = admin_module_url('kategori');
$admin_id = (int) $_SESSION['admin_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$now = now_indonesia();

function kategori_redirect($url, $type, $message)
{
    $_SESSION['kategori_' . $type] = $message;
    header('Location: ' . $url);
    exit;
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        kategori_redirect($redirect_url, 'error', 'Data kategori tidak valid.');
    }

    $exists = mysqli_query($conn, "SELECT id FROM m_kategori WHERE id = $id LIMIT 1");
    if (!$exists || mysqli_num_rows($exists) === 0) {
        kategori_redirect($redirect_url, 'error', 'Kategori tidak ditemukan.');
    }

    $cek_buku = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt FROM m_buku WHERE id_kategori = $id"
    );
    $buku_row = $cek_buku ? mysqli_fetch_assoc($cek_buku) : null;
    $jumlah_buku = (int) ($buku_row['cnt'] ?? 0);

    if ($jumlah_buku > 0) {
        kategori_redirect(
            $redirect_url,
            'error',
            'Kategori tidak dapat dihapus karena masih dipakai oleh ' . $jumlah_buku . ' buku. Pindahkan buku ke kategori lain atau hapus buku yang sudah tidak dipakai (dan tidak terikat pesanan), lalu coba lagi. Atau nonaktifkan kategori jika hanya ingin menyembunyikannya.'
        );
    }

    $query = "DELETE FROM m_kategori WHERE id = $id LIMIT 1";

    if (mysqli_query($conn, $query)) {
        kategori_redirect($redirect_url, 'success', 'Kategori berhasil dihapus.');
    }

    kategori_redirect($redirect_url, 'error', 'Gagal menghapus kategori: ' . mysqli_error($conn));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect_url);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$initial = mysqli_real_escape_string($conn, trim($_POST['initial'] ?? ''));
$nama_kategori = mysqli_real_escape_string($conn, trim($_POST['nama_kategori'] ?? ''));
$is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;

if ($initial === '' || $nama_kategori === '') {
    kategori_redirect($redirect_url, 'error', 'Initial dan nama kategori wajib diisi.');
}

if ($action === 'create') {
    $query = "INSERT INTO m_kategori (initial, nama_kategori, is_active, created_at, created_by)
              VALUES ('$initial', '$nama_kategori', $is_active, '$now', $admin_id)";

    if (mysqli_query($conn, $query)) {
        kategori_redirect($redirect_url, 'success', 'Kategori berhasil ditambahkan.');
    }

    kategori_redirect($redirect_url, 'error', 'Gagal menambahkan kategori: ' . mysqli_error($conn));
}

if ($action === 'update') {
    if ($id <= 0) {
        kategori_redirect($redirect_url, 'error', 'Data kategori tidak valid.');
    }

    $query = "UPDATE m_kategori SET
                initial = '$initial',
                nama_kategori = '$nama_kategori',
                is_active = $is_active,
                updated_at = '$now',
                updated_by = $admin_id
              WHERE id = $id LIMIT 1";

    if (mysqli_query($conn, $query)) {
        kategori_redirect($redirect_url, 'success', 'Kategori berhasil diperbarui.');
    }

    kategori_redirect($redirect_url, 'error', 'Gagal memperbarui kategori: ' . mysqli_error($conn));
}

kategori_redirect($redirect_url, 'error', 'Aksi tidak dikenali.');

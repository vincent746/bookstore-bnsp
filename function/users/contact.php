<?php

/**
 * Simpan pesan kontak pengunjung ke tabel m_contact (nama, email, deskripsi).
 *
 * Pastikan tabel ada di database, contoh:
 * CREATE TABLE m_contact (
 *   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 *   nama VARCHAR(191) NOT NULL,
 *   email VARCHAR(191) NOT NULL,
 *   deskripsi TEXT NOT NULL
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
function submit_user_contact($conn, $nama, $email, $deskripsi)
{
    $nama_clean = trim((string) $nama);
    $email_clean = trim((string) $email);
    $deskripsi_clean = trim((string) $deskripsi);

    if ($nama_clean === '' || $email_clean === '' || $deskripsi_clean === '') {
        return ['success' => false, 'message' => 'Nama, email, dan pesan wajib diisi.'];
    }

    if (mb_strlen($nama_clean) > 191) {
        return ['success' => false, 'message' => 'Nama terlalu panjang (maks. 191 karakter).'];
    }

    if (!filter_var($email_clean, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Format email tidak valid.'];
    }

    if (mb_strlen($email_clean) > 191) {
        return ['success' => false, 'message' => 'Alamat email terlalu panjang.'];
    }

    if (mb_strlen($deskripsi_clean) > 65535) {
        return ['success' => false, 'message' => 'Pesan terlalu panjang.'];
    }

    $nama_esc = mysqli_real_escape_string($conn, $nama_clean);
    $email_esc = mysqli_real_escape_string($conn, $email_clean);
    $desk_esc = mysqli_real_escape_string($conn, $deskripsi_clean);

    $query = "INSERT INTO m_contact (nama, email, deskripsi)
              VALUES ('$nama_esc', '$email_esc', '$desk_esc')";

    if (!mysqli_query($conn, $query)) {
        $errno = mysqli_errno($conn);

        if ($errno === 1146) {
            return ['success' => false, 'message' => 'Tabel kontak belum tersedia. Hubungi administrator.'];
        }

        return ['success' => false, 'message' => 'Gagal mengirim pesan. Silakan coba lagi nanti.'];
    }

    return ['success' => true, 'message' => 'Terima kasih! Pesan Anda telah terkirim ke admin.'];
}

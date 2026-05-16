<?php

function login_user($conn, $email, $password)
{
    $email = mysqli_real_escape_string($conn, trim($email));

    $query = "SELECT * FROM users
              WHERE email = '$email'
              AND role = 'user'
              AND is_active = 1
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) !== 1) {
        return false;
    }

    $user = mysqli_fetch_assoc($result);

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nama'] = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    return true;
}

function user_email_exists($conn, $email)
{
    $email = mysqli_real_escape_string($conn, trim($email));
    $check_query = "SELECT id FROM users WHERE LOWER(email) = LOWER('$email') LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    return $check_result && mysqli_num_rows($check_result) > 0;
}

function register_user($conn, $nama, $email, $password)
{
    $nama_clean = trim($nama);
    $email_clean = trim($email);
    $nama = mysqli_real_escape_string($conn, $nama_clean);
    $email = mysqli_real_escape_string($conn, $email_clean);

    if ($nama_clean === '' || $email_clean === '' || $password === '') {
        return ['success' => false, 'message' => 'Semua field wajib diisi.'];
    }

    if (!filter_var($email_clean, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Format email tidak valid.'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password minimal 8 karakter.'];
    }

    if (user_email_exists($conn, $email_clean)) {
        return ['success' => false, 'message' => 'Email sudah terdaftar. Gunakan email lain atau masuk ke akun Anda.'];
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $password_hash = mysqli_real_escape_string($conn, $password_hash);
    $now = mysqli_real_escape_string($conn, date('Y-m-d H:i:s'));

    $query = "INSERT INTO users (nama, email, password, role, is_active, created_at, created_by)
              VALUES ('$nama', '$email', '$password_hash', 'user', 1, '$now', NULL)";

    if (!mysqli_query($conn, $query)) {
        if (mysqli_errno($conn) === 1062) {
            return ['success' => false, 'message' => 'Email sudah terdaftar. Gunakan email lain atau masuk ke akun Anda.'];
        }

        return ['success' => false, 'message' => 'Gagal mendaftar. Silakan coba lagi.'];
    }

    $user_id = (int) mysqli_insert_id($conn);

    mysqli_query($conn, "UPDATE users SET created_by = $user_id WHERE id = $user_id");

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_nama'] = $nama_clean;
    $_SESSION['user_email'] = $email_clean;
    $_SESSION['user_role'] = 'user';

    return ['success' => true];
}

function logout_user()
{
    unset(
        $_SESSION['user_id'],
        $_SESSION['user_nama'],
        $_SESSION['user_email'],
        $_SESSION['user_role']
    );
}

function is_user_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function current_user_nama()
{
    return $_SESSION['user_nama'] ?? '';
}

function user_safe_redirect_url($url)
{
    $url = trim((string) $url);

    if ($url === '' || $url === '#') {
        return HOME_URL;
    }

    if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
        return $url;
    }

    return HOME_URL;
}

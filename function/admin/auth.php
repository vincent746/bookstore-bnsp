<?php

function login_admin($conn, $email, $password)
{
    $email = mysqli_real_escape_string($conn, trim($email));

    $query = "SELECT * FROM users
              WHERE email = '$email'
              AND role = 'admin'
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) !== 1) {
        return false;
    }

    $user = mysqli_fetch_assoc($result);

    // Cek password hash
    // jika password tidak sesuai, return false
    if (!password_verify($password, $user['password'])) {
        return false;
    }
    // jika password sesuai, maka login berhasil dan set session admin
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_nama'] = $user['nama'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'];

    return true;
}

function logout_admin()
{
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_nama'],
        $_SESSION['admin_email'],
        $_SESSION['admin_role']
    );
}
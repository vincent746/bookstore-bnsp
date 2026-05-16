<?php

require_once __DIR__ . '/../includes/users/init.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/users/auth.php';

if (isset($_GET['logout'])) {
    logout_user();
    header('Location: ' . user_safe_redirect_url($_GET['redirect'] ?? HOME_URL));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . HOME_URL);
    exit;
}

$action = $_POST['action'] ?? 'login';
$redirect = user_safe_redirect_url($_POST['redirect'] ?? HOME_URL);

if ($action === 'book_buku' || $action === 'book_tiket') {
    if (!is_user_logged_in()) {
        $_SESSION['user_booking_error'] = 'Anda harus login terlebih dahulu untuk memesan buku.';
        header('Location: ' . HOME_URL);
        exit;
    }

    $id_buku = (int) ($_POST['id_buku'] ?? $_POST['id_event'] ?? 0);
    $qty = (int) ($_POST['jumlah_buku'] ?? $_POST['jumlah_tiket'] ?? 0);

    $result = create_user_booking(
        $conn,
        (int) $_SESSION['user_id'],
        $id_buku,
        $qty
    );

    if ($result['success']) {
        $_SESSION['user_ticket_success'] = $result['message'];
        header('Location: ' . HOME_URL . '#pesanan-saya');
        exit;
    }

    $_SESSION['user_booking_error'] = $result['message'];
    header('Location: ' . user_safe_redirect_url($_POST['redirect'] ?? HOME_URL));
    exit;
}

if ($action === 'upload_bukti') {
    if (!is_user_logged_in()) {
        header('Location: ' . HOME_URL);
        exit;
    }

    $result = upload_user_bukti_pembayaran(
        $conn,
        (int) $_SESSION['user_id'],
        (int) ($_POST['id_header'] ?? 0),
        $_FILES['bukti'] ?? [],
        $_POST['catatan'] ?? ''
    );

    if ($result['success']) {
        $_SESSION['user_ticket_success'] = $result['message'];
    } else {
        $_SESSION['user_ticket_error'] = $result['message'];
    }

    header('Location: ' . HOME_URL . '#pesanan-saya');
    exit;
}

if ($action === 'cancel_transaksi') {
    if (!is_user_logged_in()) {
        header('Location: ' . HOME_URL);
        exit;
    }

    $result = cancel_user_transaksi($conn, (int) $_SESSION['user_id'], (int) ($_POST['id_header'] ?? 0));

    if ($result['success']) {
        $_SESSION['user_ticket_success'] = $result['message'];
    } else {
        $_SESSION['user_ticket_error'] = $result['message'];
    }

    header('Location: ' . HOME_URL . '#pesanan-saya');
    exit;
}

if ($action === 'konfirmasi_terima_paket') {
    if (!is_user_logged_in()) {
        header('Location: ' . HOME_URL);
        exit;
    }

    $result = user_confirm_terima_paket(
        $conn,
        (int) $_SESSION['user_id'],
        (int) ($_POST['id_header'] ?? 0)
    );

    if ($result['success']) {
        $_SESSION['user_ticket_success'] = $result['message'];
    } else {
        $_SESSION['user_ticket_error'] = $result['message'];
    }

    header('Location: ' . HOME_URL . '#pesanan-saya');
    exit;
}

if ($action === 'contact_admin') {
    $result = submit_user_contact(
        $conn,
        $_POST['nama'] ?? '',
        $_POST['email'] ?? '',
        $_POST['deskripsi'] ?? ''
    );

    $redirect_raw = user_safe_redirect_url($_POST['redirect'] ?? HOME_URL);
    $redirect_base = preg_replace('/#.*$/', '', $redirect_raw);
    if ($redirect_base === '') {
        $redirect_base = HOME_URL;
    }
    $redirect_hash = '#kontak';

    if ($result['success']) {
        $_SESSION['user_contact_success'] = $result['message'];
    } else {
        $_SESSION['user_contact_error'] = $result['message'];
        $_SESSION['user_contact_old'] = [
            'nama' => trim((string) ($_POST['nama'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'deskripsi' => trim((string) ($_POST['deskripsi'] ?? '')),
        ];
    }

    header('Location: ' . $redirect_base . $redirect_hash);
    exit;
}

if ($action === 'book_cart') {
    $redirect_raw = user_safe_redirect_url($_POST['redirect'] ?? HOME_URL);
    $redirect_base = preg_replace('/#.*$/', '', $redirect_raw);
    if ($redirect_base === '') {
        $redirect_base = HOME_URL;
    }

    if (!is_user_logged_in()) {
        $_SESSION['user_booking_error'] = 'Anda harus login terlebih dahulu untuk melakukan checkout.';
        header('Location: ' . $redirect_base . '?open_cart=1');
        exit;
    }

    $raw = $_POST['cart_json'] ?? '';
    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        $_SESSION['user_booking_error'] = 'Data keranjang tidak valid.';
        header('Location: ' . $redirect_base . '?open_cart=1');
        exit;
    }

    $items = [];
    foreach ($decoded as $row) {
        if (!is_array($row)) {
            continue;
        }
        $items[] = [
            'id_buku' => (int) ($row['id_buku'] ?? $row['id'] ?? 0),
            'qty' => (int) ($row['qty'] ?? 0),
        ];
    }

    $result = create_user_booking_from_cart($conn, (int) ($_SESSION['user_id'] ?? 0), $items);

    if ($result['success']) {
        $_SESSION['user_ticket_success'] = $result['message'];
        header('Location: ' . $redirect_base . '?cart_ok=1#pesanan-saya');
        exit;
    }

    $_SESSION['user_booking_error'] = $result['message'];
    header('Location: ' . $redirect_base . '?open_cart=1');
    exit;
}

if ($action === 'register') {
    $result = register_user(
        $conn,
        $_POST['nama'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );

    if ($result['success']) {
        header('Location: ' . $redirect);
        exit;
    }

    $_SESSION['user_register_error'] = $result['message'];
    $_SESSION['user_register_old'] = [
        'nama' => trim($_POST['nama'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
    ];
    header('Location: ' . $redirect);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (login_user($conn, $email, $password)) {
    header('Location: ' . $redirect);
    exit;
}

$_SESSION['user_login_error'] = 'Email atau kata sandi salah.';
header('Location: ' . $redirect);
exit;

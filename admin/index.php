<?php

require_once __DIR__ . '/../includes/admin/init.php';

// Sudah login? langsung ke dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . admin_module_url('dashboard'));
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login — Dashboard</title>
    <link href="<?= asset_url('login/css/bootstrap.min.css') ?>" rel="stylesheet" />
    <link href="<?= asset_url('login/css/bootstrap-icons.min.css') ?>" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="<?= asset_url('login/css/style.css') ?>">
</head>

<body>
    <div class="panel">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>

        <div class="login-box" style="position:relative;z-index:1;">
            <div class="login-header">
                <h2>Selamat datang</h2>
                <p>Masuk ke akun administrator Anda untuk melanjutkan ke dashboard event management.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert-custom show" id="loginAlert">
                <i class="bi bi-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= ADMIN_URL ?>proses.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="admin@perusahaan.com" required />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kata Sandi</label>
                    <div class="input-group" style="position:relative;">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required />
                    </div>
                </div>

                <button type="submit" class="btn-login mt-5">Masuk ke Dashboard</button>
            </form>
        </div>
    </div>
</body>

</html>

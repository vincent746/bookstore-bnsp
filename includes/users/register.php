<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$user_register_error = $_SESSION['user_register_error'] ?? '';
$user_register_old = $_SESSION['user_register_old'] ?? [];
unset($_SESSION['user_register_error'], $_SESSION['user_register_old']);
$open_register_modal = $user_register_error !== '';
$register_redirect = htmlspecialchars($_SERVER['REQUEST_URI'] ?? HOME_URL, ENT_QUOTES, 'UTF-8');
$old_nama = htmlspecialchars($user_register_old['nama'] ?? '', ENT_QUOTES, 'UTF-8');
$old_email = htmlspecialchars($user_register_old['email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!-- ══════════════════ MODAL: REGISTER ══════════════════ -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
            <div class="modal-header-custom d-flex align-items-center justify-content-between">
                <div class="modal-title-custom">Buat Akun BookStore</div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body-custom">
                <?php if ($user_register_error !== ''): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.85rem;">
                        <?= htmlspecialchars($user_register_error) ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="<?= USER_PROSES_URL ?>">
                    <input type="hidden" name="action" value="register" />
                    <input type="hidden" name="redirect" value="<?= $register_redirect ?>" />
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label-custom" for="registerNama">Nama lengkap</label>
                            <input class="form-control-custom" id="registerNama" name="nama" type="text"
                                placeholder="Budi Santoso" value="<?= $old_nama ?>" required autocomplete="name" />
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom" for="registerEmail">Email</label>
                            <input class="form-control-custom" id="registerEmail" name="email" type="email"
                                placeholder="email@kamu.com" value="<?= $old_email ?>" required autocomplete="email" />
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom" for="registerPassword">Password</label>
                            <input class="form-control-custom" id="registerPassword" name="password" type="password"
                                placeholder="Min. 8 karakter" required minlength="8" autocomplete="new-password" />
                        </div>
                    </div>
                    <button type="submit" class="btn-confirm mb-3 w-100">Buat Akun</button>
                </form>
                <div class="text-center" style="font-size:0.83rem; color:var(--clr-muted);">
                    Sudah punya akun?
                    <a href="#" style="color:var(--clr-accent); text-decoration:none;" data-bs-dismiss="modal"
                        data-bs-toggle="modal" data-bs-target="#loginModal">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($open_register_modal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var registerModal = document.getElementById('registerModal');
    if (registerModal) {
        bootstrap.Modal.getOrCreateInstance(registerModal).show();
    }
});
</script>
<?php endif; ?>

<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$user_login_error = $_SESSION['user_login_error'] ?? '';
unset($_SESSION['user_login_error']);
$open_login_modal = $user_login_error !== '';
$login_redirect = htmlspecialchars($_SERVER['REQUEST_URI'] ?? HOME_URL, ENT_QUOTES, 'UTF-8');
?>
<!-- ══════════════════ MODAL: LOGIN ══════════════════ -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
            <div class="modal-header-custom d-flex align-items-center justify-content-between">
                <div class="modal-title-custom">Masuk ke BookStore</div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body-custom">
                <?php if ($user_login_error !== ''): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.85rem;">
                        <?= htmlspecialchars($user_login_error) ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="<?= USER_PROSES_URL ?>">
                    <input type="hidden" name="redirect" value="<?= $login_redirect ?>" />
                    <div class="mb-3">
                        <label class="form-label-custom" for="loginEmail">Email</label>
                        <input class="form-control-custom" id="loginEmail" name="email" type="email"
                            placeholder="email@kamu.com" required autocomplete="email" />
                    </div>
                    <div class="mb-4">
                        <label class="form-label-custom" for="loginPassword">Password</label>
                        <input class="form-control-custom" id="loginPassword" name="password" type="password"
                            placeholder="••••••••" required autocomplete="current-password" />
                    </div>
                    <button type="submit" class="btn-confirm mb-3 w-100">Masuk</button>
                </form>
                <div class="text-center" style="font-size:0.83rem; color:var(--clr-muted);">
                    Belum punya akun?
                    <a href="#" style="color:var(--clr-accent); text-decoration:none;" data-bs-dismiss="modal"
                        data-bs-toggle="modal" data-bs-target="#registerModal">Daftar sekarang</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($open_login_modal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var loginModal = document.getElementById('loginModal');
    if (loginModal) {
        bootstrap.Modal.getOrCreateInstance(loginModal).show();
    }
});
</script>
<?php endif; ?>

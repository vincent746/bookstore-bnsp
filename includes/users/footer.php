<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}
?>
<?php require_once __DIR__ . '/cart-modal.php'; ?>
<!-- ══════════════════ FOOTER ══════════════════ -->
<footer class="footer-section">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="footer-brand">BookStore</div>
                <div style="font-size:0.82rem; color:var(--clr-muted); margin-top:6px;">Toko buku online</div>
            </div>
            <div class="col-md-8">
                <ul class="footer-links justify-content-md-end">
                    <li><a href="#">Tentang Kami</a></li>
                    <li><a href="#">Kebijakan Privasi</a></li>
                    <li><a href="#">Syarat & Ketentuan</a></li>
                    <li><a href="#">Bantuan</a></li>
                    <li><a href="<?= HOME_URL ?>#kontak">Hubungi Kami</a></li>
                </ul>
            </div>
        </div>
        <div class="divider mb-3"></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div style="font-size:0.78rem; color:var(--clr-muted);">© <?= date('Y') ?> BookStore. Hak cipta dilindungi.</div>
            <div class="d-flex gap-3">
                <a href="#" style="color:var(--clr-muted); font-size:1.1rem;"><i class="bi bi-instagram"></i></a>
                <a href="#" style="color:var(--clr-muted); font-size:1.1rem;"><i class="bi bi-twitter-x"></i></a>
                <a href="#" style="color:var(--clr-muted); font-size:1.1rem;"><i class="bi bi-facebook"></i></a>
                <a href="#" style="color:var(--clr-muted); font-size:1.1rem;"><i class="bi bi-tiktok"></i></a>
            </div>
        </div>
    </div>
</footer>

<script>
    window.USER_PROSES_URL = <?= json_encode(USER_PROSES_URL) ?>;
    window.HOME_URL = <?= json_encode(HOME_URL) ?>;
    window.USER_LOGGED_IN = <?= is_user_logged_in() ? 'true' : 'false' ?>;
</script>
<script>
(function () {
    try {
        if (new URLSearchParams(window.location.search).get('cart_ok') === '1') {
            localStorage.removeItem('bookstore_cart_v1');
            var u = new URL(window.location.href);
            u.searchParams.delete('cart_ok');
            var qs = u.searchParams.toString();
            window.history.replaceState({}, '', u.pathname + (qs ? '?' + qs : '') + u.hash);
        }
    } catch (e) {}
})();
</script>
<script src="<?= user_asset_url('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= user_asset_url('js/main.js') ?>"></script>
</body>

</html>

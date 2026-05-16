<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

$active_menu = 'pesan-user';
$page_title = 'Pesan User';
$breadcrumb_title = 'Pesan User';

$per_page = 15;
$search = paginate_search_get();
$search_where = paginate_search_where($conn, $search, [
    'm_contact.nama',
    'm_contact.email',
    'm_contact.deskripsi',
]);

$count_sql = 'SELECT COUNT(m_contact.id) AS total FROM m_contact' . $search_where;
$data_sql = 'SELECT m_contact.id, m_contact.nama, m_contact.email, m_contact.deskripsi
            FROM m_contact'
            . $search_where . '
            ORDER BY m_contact.id DESC';

$pagination = paginate($conn, $count_sql, $data_sql, $per_page);
$pagination['search'] = $search;
$pesan_list = $pagination['data'];
$db_error = $pagination['error'];

require_once ADMIN_INCLUDES . 'layout.php';
?>

<?php if ($db_error): ?>
    <div class="alert alert-warning alert-dismissible fade show mx-0 mt-0" role="alert">
        <i class="bi bi-database-exclamation me-2"></i>Gagal memuat data: <?= htmlspecialchars($db_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="page active">
    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Pesan dari Pengunjung</div>
            <span class="text-muted small">Data form Hubungi Kami di halaman pengguna (hanya lihat)</span>
        </div>
        <?= render_table_search('Cari nama, email, atau isi pesan...', 'search', $search) ?>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Ringkasan pesan</th>
                        <th style="width:1%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pesan_list)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color: var(--muted2);">
                                <?php if ($search !== ''): ?>
                                    Tidak ada pesan yang cocok dengan "<strong><?= htmlspecialchars($search) ?></strong>".
                                <?php else: ?>
                                    Belum ada pesan masuk.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = (int) $pagination['from']; ?>
                        <?php foreach ($pesan_list as $item): ?>
                            <?php
                            $desk = (string) ($item['deskripsi'] ?? '');
                            $ringkas = $desk;
                            if (mb_strlen($ringkas) > 100) {
                                $ringkas = mb_substr($ringkas, 0, 100) . '…';
                            }
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="t-event-name"><?= htmlspecialchars($item['nama'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($item['email'] ?? '') ?></td>
                                <td style="max-width:320px; font-size:0.88rem; color:var(--muted2);"><?= htmlspecialchars($ringkas) ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-info btn-pesan-view"
                                        title="Lihat pesan lengkap"
                                        data-nama="<?= htmlspecialchars($item['nama'] ?? '', ENT_QUOTES) ?>"
                                        data-email="<?= htmlspecialchars($item['email'] ?? '', ENT_QUOTES) ?>"
                                        data-deskripsi="<?= htmlspecialchars($desk, ENT_QUOTES) ?>">
                                        <i class="bi bi-envelope-open"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= render_pagination($pagination) ?>
    </div>
</div>

<?php require_once __DIR__ . '/form.php'; ?>

<?php require_once ADMIN_INCLUDES . 'footer.php'; ?>

<script>
    (function () {
        const modalEl = document.getElementById('pesanUserModal');
        if (!modalEl) {
            return;
        }
        const modal = new bootstrap.Modal(modalEl);
        const fieldNama = document.getElementById('pesan_view_nama');
        const fieldEmail = document.getElementById('pesan_view_email');
        const fieldDeskripsi = document.getElementById('pesan_view_deskripsi');

        document.querySelectorAll('.btn-pesan-view').forEach(function (button) {
            button.addEventListener('click', function () {
                fieldNama.value = button.getAttribute('data-nama') || '';
                fieldEmail.value = button.getAttribute('data-email') || '';
                fieldDeskripsi.value = button.getAttribute('data-deskripsi') || '';
                modal.show();
            });
        });
    })();
</script>

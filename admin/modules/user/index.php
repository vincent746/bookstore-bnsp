<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

$active_menu = 'user';
$page_title = 'User';
$breadcrumb_title = 'User';

$per_page = 10;
$search = paginate_search_get();
$search_where = paginate_search_where($conn, $search, [
    'users.nama',
    'users.email',
    'users.role',
]);

$count_sql = 'SELECT COUNT(users.id) AS total FROM users' . $search_where;
$data_sql = 'SELECT
                users.id,
                users.nama,
                users.email,
                users.role,
                users.created_at
            FROM users'
            . $search_where . '
            ORDER BY users.id DESC';

$pagination = paginate($conn, $count_sql, $data_sql, $per_page);
$pagination['search'] = $search;
$user_list = $pagination['data'];
$db_error = $pagination['error'];

require_once ADMIN_INCLUDES . 'layout.php';
?>

<style>
    #userModal .form-control {
        background: #fff;
        color: #000;
    }

    #userModal .form-control:disabled {
        background: #e9ecef;
        color: #6c757d;
    }

    #userModal .modal-dialog {
        max-height: calc(100vh - 2rem);
    }

    #userModal .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #userModal .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 12rem);
    }

    #userModal .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
        background: #fff;
    }

    .role-pill-admin {
        background: var(--purple-dim);
        color: var(--purple);
    }

    .role-pill-user {
        background: var(--accent2-dim);
        color: var(--accent2);
    }
</style>

<?php if ($db_error): ?>
    <div class="alert alert-warning alert-dismissible fade show mx-0 mt-0" role="alert">
        <i class="bi bi-database-exclamation me-2"></i>Gagal memuat data: <?= htmlspecialchars($db_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="page active">
    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Manajemen User</div>
            <span class="text-muted small">Hanya lihat data (view)</span>
        </div>
        <?= render_table_search('Cari nama, email, atau role...', 'search', $search) ?>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($user_list)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: var(--muted2);">
                                <?php if ($search !== ''): ?>
                                    Tidak ada data yang cocok dengan pencarian "<strong><?= htmlspecialchars($search) ?></strong>".
                                <?php else: ?>
                                    Belum ada data user.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = (int) $pagination['from']; ?>
                        <?php foreach ($user_list as $item): ?>
                            <?php
                            $role = strtolower($item['role'] ?? '');
                            $role_class = $role === 'admin' ? 'role-pill-admin' : 'role-pill-user';
                            $role_label = ucfirst($role ?: '-');
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="t-event-name"><?= htmlspecialchars($item['nama'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($item['email'] ?? '') ?></td>
                                <td>
                                    <span class="status-pill <?= $role_class ?>">
                                        <span class="sp-dot"></span><?= htmlspecialchars($role_label) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item['created_at'] ?? '-') ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-info btn-user-view"
                                        title="Lihat Detail"
                                        data-nama="<?= htmlspecialchars($item['nama'] ?? '', ENT_QUOTES) ?>"
                                        data-email="<?= htmlspecialchars($item['email'] ?? '', ENT_QUOTES) ?>"
                                        data-role="<?= htmlspecialchars($role_label, ENT_QUOTES) ?>"
                                        data-created-at="<?= htmlspecialchars($item['created_at'] ?? '-', ENT_QUOTES) ?>">
                                        <i class="bi bi-eye"></i>
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
        const modalEl = document.getElementById('userModal');
        const modal = new bootstrap.Modal(modalEl);
        const fieldNama = document.getElementById('view_nama');
        const fieldEmail = document.getElementById('view_email');
        const fieldRole = document.getElementById('view_role');
        const fieldCreatedAt = document.getElementById('view_created_at');

        function openUserModal(dataset) {
            fieldNama.value = dataset.nama || '';
            fieldEmail.value = dataset.email || '';
            fieldRole.value = dataset.role || '';
            fieldCreatedAt.value = dataset.createdAt || '-';
        }

        function getDatasetFromButton(button) {
            return {
                nama: button.getAttribute('data-nama'),
                email: button.getAttribute('data-email'),
                role: button.getAttribute('data-role'),
                createdAt: button.getAttribute('data-created-at'),
            };
        }

        document.querySelectorAll('.btn-user-view').forEach(function (button) {
            button.addEventListener('click', function () {
                openUserModal(getDatasetFromButton(button));
                modal.show();
            });
        });
    })();
</script>

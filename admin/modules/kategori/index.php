<?php

    require_once __DIR__ . '/../../../includes/admin/init.php';

    $active_menu = 'kategori';
$page_title = 'Kategori Buku';
$breadcrumb_title = 'Kategori Buku';

    $success_message = $_SESSION['kategori_success'] ?? null;
    $error_message = $_SESSION['kategori_error'] ?? null;

    if ($success_message) {
        unset($_SESSION['kategori_success']);
    }

    if ($error_message) {
        unset($_SESSION['kategori_error']);
    }

    $per_page = 10;
    $search = paginate_search_get();
    $search_where = paginate_search_where($conn, $search, [
        'm_kategori.initial',
        'm_kategori.nama_kategori',
    ]);

    $count_sql = 'SELECT COUNT(m_kategori.id) AS total FROM m_kategori' . $search_where;
    $data_sql = 'SELECT
                m_kategori.id,
                m_kategori.initial,
                m_kategori.nama_kategori,
                m_kategori.is_active,
                m_kategori.created_at,
                m_kategori.updated_at,
                created_by.nama AS created_by,
                updated_by.nama AS updated_by
            FROM m_kategori
            LEFT JOIN users AS created_by ON m_kategori.created_by = created_by.id
            LEFT JOIN users AS updated_by ON m_kategori.updated_by = updated_by.id'
            . $search_where . '
            ORDER BY m_kategori.id DESC';

    $pagination = paginate($conn, $count_sql, $data_sql, $per_page);
    $pagination['search'] = $search;
    $kategori_list = $pagination['data'];
    $db_error = $pagination['error'];

    require_once ADMIN_INCLUDES . 'layout.php';
?>

<style>
    .kategori-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(7, 11, 18, 0.75);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .kategori-loading-box {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px 40px;
        text-align: center;
        color: var(--heading);
    }

    #kategoriModal .form-control,
    #kategoriModal .form-select {
        background: #fff;
        color: #000;
    }

    #kategoriModal .form-control:disabled,
    #kategoriModal .form-select:disabled {
        background: #e9ecef;
        color: #6c757d;
    }
</style>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show mx-0 mt-0" role="alert" id="alertSuccess">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-0 mt-0" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($db_error): ?>
    <div class="alert alert-warning alert-dismissible fade show mx-0 mt-0" role="alert">
        <i class="bi bi-database-exclamation me-2"></i>Gagal memuat data: <?= htmlspecialchars($db_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div id="loadingOverlay" class="kategori-loading-overlay d-none">
    <div class="kategori-loading-box">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
        <p class="mt-3 mb-0">Memproses data, mohon tunggu...</p>
    </div>
</div>

<div class="page active">
    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Kategori Buku</div>
            <button type="button" class="btn btn-primary" id="btnTambahKategori">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
            </button>
        </div>
        <?= render_table_search('Cari initial atau nama kategori...', 'search', $search) ?>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Initial</th>
                    <th>Nama Kategori</th>
                    <th>Active</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kategori_list)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4" style="color: var(--muted2);">
                            <?php if ($search !== ''): ?>
                                Tidak ada data yang cocok dengan pencarian "<strong><?= htmlspecialchars($search) ?></strong>".
                            <?php else: ?>
                                Belum ada data kategori.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = (int) $pagination['from']; ?>
                    <?php foreach ($kategori_list as $item): ?>
                        <?php
                        $is_active = (int) ($item['is_active'] ?? 0);
                        $status_class = $is_active === 1 ? 'sp-confirmed' : 'sp-cancelled';
                        $status_label = $is_active === 1 ? 'Aktif' : 'Tidak Aktif';
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="t-id"><?= htmlspecialchars($item['initial'] ?? '') ?></span></td>
                            <td>
                                <div class="t-event-name"><?= htmlspecialchars($item['nama_kategori'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="status-pill <?= $status_class ?>">
                                    <span class="sp-dot"></span><?= $status_label ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-info btn-kategori-view"
                                        title="Lihat Detail"
                                        data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                        data-initial="<?= htmlspecialchars($item['initial'] ?? '', ENT_QUOTES) ?>"
                                        data-nama-kategori="<?= htmlspecialchars($item['nama_kategori'] ?? '', ENT_QUOTES) ?>"
                                        data-is-active="<?= $is_active ?>"
                                        data-created-at="<?= htmlspecialchars($item['created_at'] ?? '-', ENT_QUOTES) ?>"
                                        data-created-by="<?= htmlspecialchars($item['created_by'] ?? '-', ENT_QUOTES) ?>"
                                        data-updated-at="<?= htmlspecialchars($item['updated_at'] ?? '-', ENT_QUOTES) ?>"
                                        data-updated-by="<?= htmlspecialchars($item['updated_by'] ?? '-', ENT_QUOTES) ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-warning btn-kategori-edit"
                                        title="Edit"
                                        data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                        data-initial="<?= htmlspecialchars($item['initial'] ?? '', ENT_QUOTES) ?>"
                                        data-nama-kategori="<?= htmlspecialchars($item['nama_kategori'] ?? '', ENT_QUOTES) ?>"
                                        data-is-active="<?= $is_active ?>"
                                        data-created-at="<?= htmlspecialchars($item['created_at'] ?? '-', ENT_QUOTES) ?>"
                                        data-created-by="<?= htmlspecialchars($item['created_by'] ?? '-', ENT_QUOTES) ?>"
                                        data-updated-at="<?= htmlspecialchars($item['updated_at'] ?? '-', ENT_QUOTES) ?>"
                                        data-updated-by="<?= htmlspecialchars($item['updated_by'] ?? '-', ENT_QUOTES) ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="post" action="<?= admin_module_url('kategori') ?>proses.php" class="d-inline form-delete-kategori">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) ($item['id'] ?? 0) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?= render_pagination($pagination) ?>
    </div>
</div>

<?php require_once __DIR__ . '/form.php'; ?>

<?php require_once ADMIN_INCLUDES . 'footer.php'; ?>

<script>
    (function () {
        const modalEl = document.getElementById('kategoriModal');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('formKategori');
        const formAction = document.getElementById('formAction');
        const formId = document.getElementById('formId');
        const modalTitle = document.getElementById('kategoriModalLabel');
        const btnSimpan = document.getElementById('btnSimpanKategori');
        const auditFields = document.getElementById('auditFields');
        const loadingOverlay = document.getElementById('loadingOverlay');

        const fieldInitial = document.getElementById('initial');
        const fieldNama = document.getElementById('nama_kategori');
        const fieldActive = document.getElementById('is_active');
        const viewCreatedAt = document.getElementById('view_created_at');
        const viewCreatedBy = document.getElementById('view_created_by');
        const viewUpdatedAt = document.getElementById('view_updated_at');
        const viewUpdatedBy = document.getElementById('view_updated_by');

        const formFields = [fieldInitial, fieldNama, fieldActive];

        function showLoading() {
            loadingOverlay.classList.remove('d-none');
        }

        function setFormDisabled(disabled) {
            formFields.forEach(function (field) {
                field.disabled = disabled;
            });

            if (disabled) {
                fieldInitial.removeAttribute('required');
                fieldNama.removeAttribute('required');
            } else {
                fieldInitial.setAttribute('required', 'required');
                fieldNama.setAttribute('required', 'required');
            }
        }

        function fillAuditFields(dataset) {
            viewCreatedAt.value = dataset.createdAt || '-';
            viewCreatedBy.value = dataset.createdBy || '-';
            viewUpdatedAt.value = dataset.updatedAt || '-';
            viewUpdatedBy.value = dataset.updatedBy || '-';
        }

        function openKategoriModal(mode, dataset) {
            form.reset();
            setFormDisabled(false);
            auditFields.classList.add('d-none');
            btnSimpan.classList.remove('d-none');

            if (mode === 'create') {
                modalTitle.textContent = 'Tambah Kategori';
                formAction.value = 'create';
                formId.value = '';
                fieldActive.value = '1';
                return;
            }

            formId.value = dataset.id || '';
            fieldInitial.value = dataset.initial || '';
            fieldNama.value = dataset.namaKategori || '';
            fieldActive.value = dataset.isActive === '1' ? '1' : '0';

            if (mode === 'view') {
                modalTitle.textContent = 'Detail Kategori';
                formAction.value = 'view';
                setFormDisabled(true);
                auditFields.classList.remove('d-none');
                fillAuditFields(dataset);
                btnSimpan.classList.add('d-none');
                return;
            }

            modalTitle.textContent = 'Edit Kategori';
            formAction.value = 'update';
        }

        function getDatasetFromButton(button) {
            return {
                id: button.getAttribute('data-id'),
                initial: button.getAttribute('data-initial'),
                namaKategori: button.getAttribute('data-nama-kategori'),
                isActive: button.getAttribute('data-is-active'),
                createdAt: button.getAttribute('data-created-at'),
                createdBy: button.getAttribute('data-created-by'),
                updatedAt: button.getAttribute('data-updated-at'),
                updatedBy: button.getAttribute('data-updated-by'),
            };
        }

        document.getElementById('btnTambahKategori').addEventListener('click', function () {
            openKategoriModal('create');
            modal.show();
        });

        document.querySelectorAll('.btn-kategori-view').forEach(function (button) {
            button.addEventListener('click', function () {
                openKategoriModal('view', getDatasetFromButton(button));
                modal.show();
            });
        });

        document.querySelectorAll('.btn-kategori-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                openKategoriModal('edit', getDatasetFromButton(button));
                modal.show();
            });
        });

        form.addEventListener('submit', function (event) {
            if (formAction.value === 'view') {
                event.preventDefault();
                return;
            }

            showLoading();
        });

        document.querySelectorAll('.form-delete-kategori').forEach(function (deleteForm) {
            deleteForm.addEventListener('submit', function (event) {
                if (!confirm('Yakin ingin menghapus kategori ini?')) {
                    event.preventDefault();
                    return;
                }

                showLoading();
            });
        });

        <?php if ($success_message): ?>
        showLoading();
        setTimeout(function () {
            window.location.href = '<?= admin_module_url('kategori') ?>';
        }, 1000);
        <?php endif; ?>
    })();
</script>

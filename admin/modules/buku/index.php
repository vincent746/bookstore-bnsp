<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

$active_menu = 'buku';
$page_title = 'Buku';
$breadcrumb_title = 'Buku';

$success_message = $_SESSION['buku_success'] ?? null;
$error_message = $_SESSION['buku_error'] ?? null;

if ($success_message) {
    unset($_SESSION['buku_success']);
}

if ($error_message) {
    unset($_SESSION['buku_error']);
}

$kategori_options = get_kategori_options($conn);

$per_page = 10;
$search = paginate_search_get();
$search_where = paginate_search_where($conn, $search, [
    'm_buku.judul_buku',
    'm_buku.penulis',
    'm_buku.penerbit',
    'm_kategori.nama_kategori',
]);

$from_join = ' FROM m_buku
            LEFT JOIN m_kategori ON m_buku.id_kategori = m_kategori.id
            LEFT JOIN users AS created_by ON m_buku.created_by = created_by.id
            LEFT JOIN users AS updated_by ON m_buku.updated_by = updated_by.id';

$count_sql = 'SELECT COUNT(m_buku.id) AS total' . $from_join . $search_where;
$data_sql = 'SELECT
                m_buku.id,
                m_buku.id_kategori,
                m_buku.judul_buku,
                m_buku.deskripsi,
                m_buku.penulis,
                m_buku.penerbit,
                m_buku.tahun_penerbit,
                m_buku.harga,
                m_buku.stok,
                m_buku.gambar,
                m_buku.is_active,
                m_buku.created_at,
                m_buku.updated_at,
                m_kategori.nama_kategori,
                created_by.nama AS created_by,
                updated_by.nama AS updated_by'
            . $from_join
            . $search_where . '
            ORDER BY m_buku.id DESC';

$pagination = paginate($conn, $count_sql, $data_sql, $per_page);
$pagination['search'] = $search;
$buku_list = $pagination['data'];
$db_error = $pagination['error'];

require_once ADMIN_INCLUDES . 'layout.php';
?>

<style>
    .buku-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(7, 11, 18, 0.75);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .buku-loading-box {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px 40px;
        text-align: center;
        color: var(--heading);
    }

    #bukuModal .form-control,
    #bukuModal .form-select,
    #bukuModal textarea {
        background: #fff;
        color: #000;
    }

    #bukuModal .form-control:disabled,
    #bukuModal .form-select:disabled,
    #bukuModal textarea:disabled {
        background: #e9ecef;
        color: #6c757d;
    }

    .buku-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    .buku-gambar-preview {
        max-width: 100%;
        max-height: 180px;
        border-radius: 10px;
        border: 1px solid var(--border);
        object-fit: cover;
    }

    #bukuModal .modal-dialog {
        max-height: calc(100vh - 2rem);
    }

    #bukuModal .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #bukuModal .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 12rem);
    }

    #bukuModal .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
        background: #fff;
    }
</style>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show mx-0 mt-0" role="alert">
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

<div id="loadingOverlay" class="buku-loading-overlay d-none">
    <div class="buku-loading-box">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
        <p class="mt-3 mb-0">Memproses data, mohon tunggu...</p>
    </div>
</div>

<div class="page active">
    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Manajemen Buku</div>
            <button type="button" class="btn btn-primary" id="btnTambahBuku">
                <i class="bi bi-plus-lg me-1"></i> Tambah Buku
            </button>
        </div>
        <?= render_table_search('Cari judul, penulis, penerbit, atau kategori...', 'search', $search) ?>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Terbit</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($buku_list)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4" style="color: var(--muted2);">
                                <?php if ($search !== ''): ?>
                                    Tidak ada data yang cocok dengan pencarian "<strong><?= htmlspecialchars($search) ?></strong>".
                                <?php else: ?>
                                    Belum ada data buku.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = (int) $pagination['from']; ?>
                        <?php foreach ($buku_list as $item): ?>
                            <?php
                            $is_active = (int) ($item['is_active'] ?? 0);
                            $status_class = $is_active === 1 ? 'sp-confirmed' : 'sp-cancelled';
                            $status_label = $is_active === 1 ? 'Aktif' : 'Tidak Aktif';
                            $gambar_url = buku_gambar_url($item['gambar'] ?? '');
                            $tahun_tampil = $item['tahun_penerbit'] ?? '';
                            if ($tahun_tampil !== '' && $tahun_tampil !== null) {
                                $tahun_tampil = date('d/m/Y', strtotime((string) $tahun_tampil));
                            } else {
                                $tahun_tampil = '-';
                            }
                            $tahun_value = date_input_value($item['tahun_penerbit'] ?? '');
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if ($gambar_url): ?>
                                        <img src="<?= htmlspecialchars($gambar_url) ?>" alt="" class="buku-thumb">
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="t-event-name"><?= htmlspecialchars($item['judul_buku'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($item['nama_kategori'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($item['penulis'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($item['penerbit'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tahun_tampil) ?></td>
                                <td><?= format_rupiah($item['harga'] ?? 0) ?></td>
                                <td><?= (int) ($item['stok'] ?? 0) ?></td>
                                <td>
                                    <span class="status-pill <?= $status_class ?>">
                                        <span class="sp-dot"></span><?= $status_label ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-info btn-buku-view"
                                            title="Lihat Detail"
                                            data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                            data-id-kategori="<?= (int) ($item['id_kategori'] ?? 0) ?>"
                                            data-judul-buku="<?= htmlspecialchars($item['judul_buku'] ?? '', ENT_QUOTES) ?>"
                                            data-deskripsi="<?= htmlspecialchars($item['deskripsi'] ?? '', ENT_QUOTES) ?>"
                                            data-penulis="<?= htmlspecialchars($item['penulis'] ?? '', ENT_QUOTES) ?>"
                                            data-penerbit="<?= htmlspecialchars($item['penerbit'] ?? '', ENT_QUOTES) ?>"
                                            data-tahun-penerbit="<?= htmlspecialchars($tahun_value, ENT_QUOTES) ?>"
                                            data-harga="<?= htmlspecialchars($item['harga'] ?? '0', ENT_QUOTES) ?>"
                                            data-stok="<?= (int) ($item['stok'] ?? 0) ?>"
                                            data-gambar="<?= htmlspecialchars($item['gambar'] ?? '', ENT_QUOTES) ?>"
                                            data-gambar-url="<?= htmlspecialchars($gambar_url, ENT_QUOTES) ?>"
                                            data-is-active="<?= $is_active ?>"
                                            data-created-at="<?= htmlspecialchars($item['created_at'] ?? '-', ENT_QUOTES) ?>"
                                            data-created-by="<?= htmlspecialchars($item['created_by'] ?? '-', ENT_QUOTES) ?>"
                                            data-updated-at="<?= htmlspecialchars($item['updated_at'] ?? '-', ENT_QUOTES) ?>"
                                            data-updated-by="<?= htmlspecialchars($item['updated_by'] ?? '-', ENT_QUOTES) ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-warning btn-buku-edit"
                                            title="Edit"
                                            data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                            data-id-kategori="<?= (int) ($item['id_kategori'] ?? 0) ?>"
                                            data-judul-buku="<?= htmlspecialchars($item['judul_buku'] ?? '', ENT_QUOTES) ?>"
                                            data-deskripsi="<?= htmlspecialchars($item['deskripsi'] ?? '', ENT_QUOTES) ?>"
                                            data-penulis="<?= htmlspecialchars($item['penulis'] ?? '', ENT_QUOTES) ?>"
                                            data-penerbit="<?= htmlspecialchars($item['penerbit'] ?? '', ENT_QUOTES) ?>"
                                            data-tahun-penerbit="<?= htmlspecialchars($tahun_value, ENT_QUOTES) ?>"
                                            data-harga="<?= htmlspecialchars($item['harga'] ?? '0', ENT_QUOTES) ?>"
                                            data-stok="<?= (int) ($item['stok'] ?? 0) ?>"
                                            data-gambar="<?= htmlspecialchars($item['gambar'] ?? '', ENT_QUOTES) ?>"
                                            data-gambar-url="<?= htmlspecialchars($gambar_url, ENT_QUOTES) ?>"
                                            data-is-active="<?= $is_active ?>"
                                            data-created-at="<?= htmlspecialchars($item['created_at'] ?? '-', ENT_QUOTES) ?>"
                                            data-created-by="<?= htmlspecialchars($item['created_by'] ?? '-', ENT_QUOTES) ?>"
                                            data-updated-at="<?= htmlspecialchars($item['updated_at'] ?? '-', ENT_QUOTES) ?>"
                                            data-updated-by="<?= htmlspecialchars($item['updated_by'] ?? '-', ENT_QUOTES) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" action="<?= admin_module_url('buku') ?>proses.php" class="d-inline form-delete-buku">
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
        </div>
        <?= render_pagination($pagination) ?>
    </div>
</div>

<?php require_once __DIR__ . '/form.php'; ?>

<?php require_once ADMIN_INCLUDES . 'footer.php'; ?>

<script>
    (function () {
        const modalEl = document.getElementById('bukuModal');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('formBuku');
        const formAction = document.getElementById('formAction');
        const formId = document.getElementById('formId');
        const modalTitle = document.getElementById('bukuModalLabel');
        const btnSimpan = document.getElementById('btnSimpanBuku');
        const auditFields = document.getElementById('auditFields');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const gambarLama = document.getElementById('gambar_lama');
        const gambarPreviewWrap = document.getElementById('gambarPreviewWrap');
        const gambarPreview = document.getElementById('gambarPreview');

        const fieldKategori = document.getElementById('id_kategori');
        const fieldJudul = document.getElementById('judul_buku');
        const fieldDeskripsi = document.getElementById('deskripsi');
        const fieldPenulis = document.getElementById('penulis');
        const fieldPenerbit = document.getElementById('penerbit');
        const fieldTahun = document.getElementById('tahun_penerbit');
        const fieldHarga = document.getElementById('harga');
        const fieldStok = document.getElementById('stok');
        const fieldGambar = document.getElementById('gambar');
        const fieldActive = document.getElementById('is_active');
        const viewCreatedAt = document.getElementById('view_created_at');
        const viewCreatedBy = document.getElementById('view_created_by');
        const viewUpdatedAt = document.getElementById('view_updated_at');
        const viewUpdatedBy = document.getElementById('view_updated_by');

        const formFields = [
            fieldKategori, fieldJudul, fieldDeskripsi, fieldPenulis, fieldPenerbit,
            fieldTahun, fieldHarga, fieldStok, fieldGambar, fieldActive,
        ];

        function showLoading() {
            loadingOverlay.classList.remove('d-none');
        }

        function setGambarPreview(url) {
            if (url) {
                gambarPreview.src = url;
                gambarPreviewWrap.classList.remove('d-none');
            } else {
                gambarPreview.src = '';
                gambarPreviewWrap.classList.add('d-none');
            }
        }

        function setFormDisabled(disabled) {
            formFields.forEach(function (field) {
                field.disabled = disabled;
            });

            const requiredFields = [
                fieldKategori, fieldJudul, fieldPenulis, fieldPenerbit,
                fieldTahun, fieldHarga, fieldStok,
            ];

            requiredFields.forEach(function (field) {
                if (disabled) {
                    field.removeAttribute('required');
                } else {
                    field.setAttribute('required', 'required');
                }
            });
        }

        function fillAuditFields(dataset) {
            viewCreatedAt.value = dataset.createdAt || '-';
            viewCreatedBy.value = dataset.createdBy || '-';
            viewUpdatedAt.value = dataset.updatedAt || '-';
            viewUpdatedBy.value = dataset.updatedBy || '-';
        }

        function openBukuModal(mode, dataset) {
            form.reset();
            setFormDisabled(false);
            auditFields.classList.add('d-none');
            btnSimpan.classList.remove('d-none');
            fieldGambar.value = '';
            gambarLama.value = '';
            setGambarPreview('');

            if (mode === 'create') {
                modalTitle.textContent = 'Tambah Buku';
                formAction.value = 'create';
                formId.value = '';
                fieldActive.value = '1';
                return;
            }

            formId.value = dataset.id || '';
            fieldKategori.value = dataset.idKategori || '';
            fieldJudul.value = dataset.judulBuku || '';
            fieldDeskripsi.value = dataset.deskripsi || '';
            fieldPenulis.value = dataset.penulis || '';
            fieldPenerbit.value = dataset.penerbit || '';
            fieldTahun.value = dataset.tahunPenerbit || '';
            fieldHarga.value = dataset.harga || '';
            fieldStok.value = dataset.stok || '';
            fieldActive.value = dataset.isActive === '1' ? '1' : '0';
            gambarLama.value = dataset.gambar || '';
            setGambarPreview(dataset.gambarUrl || '');

            if (mode === 'view') {
                modalTitle.textContent = 'Detail Buku';
                formAction.value = 'view';
                setFormDisabled(true);
                auditFields.classList.remove('d-none');
                fillAuditFields(dataset);
                btnSimpan.classList.add('d-none');
                return;
            }

            modalTitle.textContent = 'Edit Buku';
            formAction.value = 'update';
        }

        function getDatasetFromButton(button) {
            return {
                id: button.getAttribute('data-id'),
                idKategori: button.getAttribute('data-id-kategori'),
                judulBuku: button.getAttribute('data-judul-buku'),
                deskripsi: button.getAttribute('data-deskripsi'),
                penulis: button.getAttribute('data-penulis'),
                penerbit: button.getAttribute('data-penerbit'),
                tahunPenerbit: button.getAttribute('data-tahun-penerbit'),
                harga: button.getAttribute('data-harga'),
                stok: button.getAttribute('data-stok'),
                gambar: button.getAttribute('data-gambar'),
                gambarUrl: button.getAttribute('data-gambar-url'),
                isActive: button.getAttribute('data-is-active'),
                createdAt: button.getAttribute('data-created-at'),
                createdBy: button.getAttribute('data-created-by'),
                updatedAt: button.getAttribute('data-updated-at'),
                updatedBy: button.getAttribute('data-updated-by'),
            };
        }

        document.getElementById('btnTambahBuku').addEventListener('click', function () {
            openBukuModal('create');
            modal.show();
        });

        document.querySelectorAll('.btn-buku-view').forEach(function (button) {
            button.addEventListener('click', function () {
                openBukuModal('view', getDatasetFromButton(button));
                modal.show();
            });
        });

        document.querySelectorAll('.btn-buku-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                openBukuModal('edit', getDatasetFromButton(button));
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

        document.querySelectorAll('.form-delete-buku').forEach(function (deleteForm) {
            deleteForm.addEventListener('submit', function (event) {
                if (!confirm('Yakin ingin menghapus buku ini?')) {
                    event.preventDefault();
                    return;
                }

                showLoading();
            });
        });

        fieldGambar.addEventListener('change', function () {
            if (fieldGambar.files && fieldGambar.files[0]) {
                setGambarPreview(URL.createObjectURL(fieldGambar.files[0]));
            }
        });

        <?php if ($success_message): ?>
        showLoading();
        setTimeout(function () {
            window.location.href = '<?= admin_module_url('buku') ?>';
        }, 1000);
        <?php endif; ?>
    })();
</script>

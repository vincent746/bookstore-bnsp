<?php

require_once __DIR__ . '/../../../includes/admin/init.php';

$active_menu = 'transaksi';
$page_title = 'Transaksi Pesanan';
$breadcrumb_title = 'Transaksi Pesanan';

$success_message = $_SESSION['transaksi_success'] ?? null;
$error_message = $_SESSION['transaksi_error'] ?? null;

if ($success_message) {
    unset($_SESSION['transaksi_success']);
}

if ($error_message) {
    unset($_SESSION['transaksi_error']);
}

$status_pengiriman_options = get_status_pengiriman_options($conn);

$detail_map = [];
$detail_query = mysqli_query(
    $conn,
    'SELECT d.id_header, d.harga_satuan, d.jumlah_buku, d.total_harga, b.judul_buku
     FROM trans_d_pesanan d
     LEFT JOIN m_buku b ON d.id_buku = b.id'
);

if ($detail_query) {
    while ($row = mysqli_fetch_assoc($detail_query)) {
        $detail_map[(int) $row['id_header']][] = $row;
    }
}

$per_page = 10;
$search = paginate_search_get();
$search_where = paginate_search_where($conn, $search, [
    'h.kode_transaksi',
    'u.nama',
    'u.email',
    's.status',
]);

$from_join = ' FROM trans_h_pesanan h
            LEFT JOIN users u ON h.id_user = u.id
            LEFT JOIN m_status s ON h.id_status = s.id
            LEFT JOIN m_status_pengiriman sp ON h.id_pengirim = sp.id
            LEFT JOIN trans_d_pesanan d ON d.id_header = h.id';

$count_sql = 'SELECT COUNT(DISTINCT h.id) AS total' . $from_join . $search_where;

$data_sql = 'SELECT
                h.id,
                h.kode_transaksi,
                h.id_user,
                h.bukti_pembayaran,
                h.deskripsi,
                h.deskripsi_pengiriman,
                h.is_paid,
                h.id_status,
                h.id_pengirim,
                h.created_at,
                u.nama AS nama_user,
                u.email AS email_user,
                s.status AS nama_status,
                MAX(sp.status_pengiriman) AS nama_status_pengiriman,
                COALESCE(SUM(d.jumlah_buku), 0) AS total_buku,
                COALESCE(SUM(d.total_harga), 0) AS total_bayar'
            . $from_join
            . $search_where . '
            GROUP BY h.id
            ORDER BY h.id DESC';

$pagination = paginate($conn, $count_sql, $data_sql, $per_page);
$pagination['search'] = $search;
$transaksi_list = $pagination['data'];
$db_error = $pagination['error'];

function transaksi_status_class($id_status)
{
    switch ((int) $id_status) {
        case STATUS_TERKONFIRMASI:
            return 'sp-confirmed';
        case STATUS_DIBATALKAN:
            return 'sp-cancelled';
        case STATUS_MENUNGGU_KONFIRMASI:
            return 'sp-pending';
        default:
            return 'sp-pending';
    }
}

require_once ADMIN_INCLUDES . 'layout.php';
?>

<style>
    .transaksi-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(7, 11, 18, 0.75);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .transaksi-loading-box {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px 40px;
        text-align: center;
        color: var(--heading);
    }

    #transaksiViewModal .form-control,
    #transaksiViewModal textarea,
    #transaksiActionModal .form-control,
    #transaksiPengirimanModal .form-control,
    #transaksiPengirimanModal .form-select,
    #transaksiPengirimanModal textarea {
        background: #fff;
        color: #000;
    }

    #transaksiViewModal .form-control:disabled,
    #transaksiViewModal textarea:disabled {
        background: #e9ecef;
        color: #6c757d;
    }

    #transaksiViewModal .modal-dialog,
    #transaksiViewModal .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #transaksiViewModal .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 12rem);
    }

    .transaksi-bukti-preview {
        max-width: 100%;
        max-height: 280px;
        border-radius: 10px;
        border: 1px solid var(--border);
        object-fit: contain;
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

<div id="loadingOverlay" class="transaksi-loading-overlay d-none">
    <div class="transaksi-loading-box">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
        <p class="mt-3 mb-0">Memproses data, mohon tunggu...</p>
    </div>
</div>

<div class="page active">
    <div class="table-card">
        <div class="table-head">
            <div class="table-title">Transaksi Pesanan</div>
            <span class="text-muted small">Konfirmasi pembayaran, pengiriman &amp; batalkan transaksi</span>
        </div>
        <?= render_table_search('Cari kode transaksi, user, atau status...', 'search', $search) ?>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>User</th>
                        <th>Total Buku</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Pengiriman</th>
                        <th>Tanggal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4" style="color: var(--muted2);">
                                <?php if ($search !== ''): ?>
                                    Tidak ada data yang cocok dengan pencarian "<strong><?= htmlspecialchars($search) ?></strong>".
                                <?php else: ?>
                                    Belum ada data transaksi.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = (int) $pagination['from']; ?>
                        <?php foreach ($transaksi_list as $item): ?>
                            <?php
                            $id_status = (int) ($item['id_status'] ?? 0);
                            $status_class = transaksi_status_class($id_status);
                            $details_json = htmlspecialchars(
                                json_encode($detail_map[(int) ($item['id'] ?? 0)] ?? [], JSON_UNESCAPED_UNICODE),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            $bukti_url = bukti_pembayaran_url($item['bukti_pembayaran'] ?? '');
                            $nama_sp = trim((string) ($item['nama_status_pengiriman'] ?? ''));
                            $id_pengirim_row = (int) ($item['id_pengirim'] ?? 0);
                            $tampil_pengiriman = $nama_sp !== '' ? $nama_sp : ($id_pengirim_row > 0 ? '#' . $id_pengirim_row : '—');
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="t-id"><?= htmlspecialchars($item['kode_transaksi'] ?? '') ?></span></td>
                                <td>
                                    <div class="t-event-name"><?= htmlspecialchars($item['nama_user'] ?? '-') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($item['email_user'] ?? '') ?></small>
                                </td>
                                <td><?= (int) ($item['total_buku'] ?? 0) ?></td>
                                <td><?= format_rupiah($item['total_bayar'] ?? 0) ?></td>
                                <td>
                                    <span class="status-pill <?= $status_class ?>">
                                        <span class="sp-dot"></span><?= htmlspecialchars($item['nama_status'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small" style="color: var(--muted2);"><?= htmlspecialchars($tampil_pengiriman) ?></span>
                                </td>
                                <td><?= htmlspecialchars($item['created_at'] ?? '-') ?></td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-info btn-transaksi-view"
                                            title="Lihat Detail"
                                            data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                            data-kode="<?= htmlspecialchars($item['kode_transaksi'] ?? '', ENT_QUOTES) ?>"
                                            data-nama-user="<?= htmlspecialchars($item['nama_user'] ?? '', ENT_QUOTES) ?>"
                                            data-email-user="<?= htmlspecialchars($item['email_user'] ?? '', ENT_QUOTES) ?>"
                                            data-status="<?= htmlspecialchars($item['nama_status'] ?? '', ENT_QUOTES) ?>"
                                            data-total-buku="<?= (int) ($item['total_buku'] ?? 0) ?>"
                                            data-total-bayar="<?= htmlspecialchars($item['total_bayar'] ?? '0', ENT_QUOTES) ?>"
                                            data-is-paid="<?= (int) ($item['is_paid'] ?? 0) ?>"
                                            data-deskripsi="<?= htmlspecialchars($item['deskripsi'] ?? '', ENT_QUOTES) ?>"
                                            data-id-pengirim="<?= (int) ($item['id_pengirim'] ?? 0) ?>"
                                            data-status-pengiriman="<?= htmlspecialchars($nama_sp !== '' ? $nama_sp : '—', ENT_QUOTES) ?>"
                                            data-deskripsi-pengiriman="<?= htmlspecialchars($item['deskripsi_pengiriman'] ?? '', ENT_QUOTES) ?>"
                                            data-created-at="<?= htmlspecialchars($item['created_at'] ?? '-', ENT_QUOTES) ?>"
                                            data-bukti-url="<?= htmlspecialchars($bukti_url, ENT_QUOTES) ?>"
                                            data-details="<?= $details_json ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($id_status !== STATUS_MENUNGGU_PEMBAYARAN): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary btn-transaksi-pengiriman"
                                                title="Ubah status pengiriman"
                                                data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                                data-kode="<?= htmlspecialchars($item['kode_transaksi'] ?? '', ENT_QUOTES) ?>"
                                                data-id-pengirim="<?= (int) ($item['id_pengirim'] ?? 0) ?>"
                                                data-deskripsi-pengiriman="<?= htmlspecialchars($item['deskripsi_pengiriman'] ?? '', ENT_QUOTES) ?>">
                                                <i class="bi bi-truck"></i> Pengiriman
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($id_status === STATUS_MENUNGGU_KONFIRMASI): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success btn-transaksi-paid"
                                                title="Konfirmasi Paid"
                                                data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                                data-kode="<?= htmlspecialchars($item['kode_transaksi'] ?? '', ENT_QUOTES) ?>">
                                                <i class="bi bi-check2-circle"></i> Paid
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger btn-transaksi-cancel"
                                                title="Batalkan Transaksi"
                                                data-cancel-mode="pending"
                                                data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                                data-kode="<?= htmlspecialchars($item['kode_transaksi'] ?? '', ENT_QUOTES) ?>">
                                                <i class="bi bi-x-circle"></i> Batalkan
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($id_status === STATUS_TERKONFIRMASI): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger btn-transaksi-cancel"
                                                title="Batalkan Transaksi"
                                                data-cancel-mode="confirmed"
                                                data-id="<?= (int) ($item['id'] ?? 0) ?>"
                                                data-kode="<?= htmlspecialchars($item['kode_transaksi'] ?? '', ENT_QUOTES) ?>">
                                                <i class="bi bi-x-circle"></i> Batalkan
                                            </button>
                                        <?php endif; ?>
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
        const viewModalEl = document.getElementById('transaksiViewModal');
        const pengirimanModalEl = document.getElementById('transaksiPengirimanModal');
        const actionModalEl = document.getElementById('transaksiActionModal');
        const viewModal = new bootstrap.Modal(viewModalEl);
        const actionModal = new bootstrap.Modal(actionModalEl);
        const pengirimanModal = new bootstrap.Modal(pengirimanModalEl);
        const actionForm = document.getElementById('formTransaksiAction');
        const pengirimanForm = document.getElementById('formTransaksiPengiriman');
        const loadingOverlay = document.getElementById('loadingOverlay');

        const viewDetailBody = document.getElementById('viewDetailBody');
        const viewBuktiWrap = document.getElementById('viewBuktiWrap');
        const viewBuktiEmpty = document.getElementById('viewBuktiEmpty');
        const viewBuktiImg = document.getElementById('viewBuktiImg');
        const viewBuktiLink = document.getElementById('viewBuktiLink');

        function showLoading() {
            loadingOverlay.classList.remove('d-none');
        }

        function formatRupiah(amount) {
            return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
        }

        function openViewModal(button) {
            document.getElementById('view_kode_transaksi').value = button.getAttribute('data-kode') || '';
            document.getElementById('view_status').value = button.getAttribute('data-status') || '';
            document.getElementById('view_nama_user').value = button.getAttribute('data-nama-user') || '';
            document.getElementById('view_email_user').value = button.getAttribute('data-email-user') || '';
            document.getElementById('view_total_buku').value = button.getAttribute('data-total-buku') || '0';
            document.getElementById('view_total_bayar').value = formatRupiah(button.getAttribute('data-total-bayar'));
            document.getElementById('view_is_paid').value = button.getAttribute('data-is-paid') === '1' ? 'Sudah Paid' : 'Belum Paid';
            document.getElementById('view_created_at').value = button.getAttribute('data-created-at') || '-';
            document.getElementById('view_deskripsi').value = button.getAttribute('data-deskripsi') || '';
            document.getElementById('view_status_pengiriman').value = button.getAttribute('data-status-pengiriman') || '—';
            document.getElementById('view_deskripsi_pengiriman').value = button.getAttribute('data-deskripsi-pengiriman') || '';

            viewDetailBody.innerHTML = '';
            let details = [];

            try {
                details = JSON.parse(button.getAttribute('data-details') || '[]');
            } catch (e) {
                details = [];
            }

            if (details.length === 0) {
                viewDetailBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Tidak ada detail pesanan.</td></tr>';
            } else {
                details.forEach(function (item) {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + (item.judul_buku || '-') + '</td>' +
                        '<td>' + formatRupiah(item.harga_satuan) + '</td>' +
                        '<td>' + (item.jumlah_buku || 0) + '</td>' +
                        '<td>' + formatRupiah(item.total_harga) + '</td>';
                    viewDetailBody.appendChild(tr);
                });
            }

            const buktiUrl = button.getAttribute('data-bukti-url') || '';

            if (buktiUrl) {
                viewBuktiWrap.classList.remove('d-none');
                viewBuktiEmpty.classList.add('d-none');
                viewBuktiImg.src = buktiUrl;
                viewBuktiLink.href = buktiUrl;
            } else {
                viewBuktiWrap.classList.add('d-none');
                viewBuktiEmpty.classList.remove('d-none');
                viewBuktiImg.src = '';
                viewBuktiLink.href = '#';
            }
        }

        function openActionModal(type, button) {
            const id = button.getAttribute('data-id');
            const kode = button.getAttribute('data-kode') || '';

            document.getElementById('actionType').value = type;
            document.getElementById('actionTransaksiId').value = id;
            document.getElementById('deskripsi').value = '';

            const titleEl = document.getElementById('transaksiActionModalLabel');
            const infoEl = document.getElementById('actionInfoText');
            const btnSimpan = document.getElementById('btnSimpanAction');

            if (type === 'paid') {
                titleEl.textContent = 'Konfirmasi Paid';
                infoEl.textContent = 'Konfirmasi pembayaran untuk transaksi ' + kode + '. Stok buku akan dikurangi setelah disimpan.';
                btnSimpan.className = 'btn btn-success';
                btnSimpan.textContent = 'Simpan & Paid';
                return;
            }

            const cancelMode = button.getAttribute('data-cancel-mode') || 'confirmed';
            titleEl.textContent = 'Batalkan Transaksi';
            btnSimpan.className = 'btn btn-danger';
            btnSimpan.textContent = 'Simpan & Batalkan';

            if (cancelMode === 'pending') {
                infoEl.textContent = 'Batalkan transaksi ' + kode + ' (Menunggu Konfirmasi). Stok belum terpotong sehingga tidak ada pengembalian stok. Status menjadi Dibatalkan.';
            } else {
                infoEl.textContent = 'Batalkan transaksi ' + kode + ' (Terkonfirmasi). Stok buku akan dikembalikan dan status menjadi Dibatalkan.';
            }
        }

        document.querySelectorAll('.btn-transaksi-view').forEach(function (button) {
            button.addEventListener('click', function () {
                openViewModal(button);
                viewModal.show();
            });
        });

        function openPengirimanModal(button) {
            const id = button.getAttribute('data-id');
            const kode = button.getAttribute('data-kode') || '';
            const idPengirim = button.getAttribute('data-id-pengirim') || '';
            const catatan = button.getAttribute('data-deskripsi-pengiriman') || '';

            document.getElementById('pengirimanTransaksiId').value = id;
            document.getElementById('pengirimanInfoText').textContent = 'Perbarui status pengiriman untuk transaksi ' + kode + '.';
            document.getElementById('id_pengirim').value = idPengirim || '';
            document.getElementById('deskripsi_pengiriman').value = catatan;
        }

        document.querySelectorAll('.btn-transaksi-pengiriman').forEach(function (button) {
            button.addEventListener('click', function () {
                openPengirimanModal(button);
                pengirimanModal.show();
            });
        });

        document.querySelectorAll('.btn-transaksi-paid').forEach(function (button) {
            button.addEventListener('click', function () {
                openActionModal('paid', button);
                actionModal.show();
            });
        });

        document.querySelectorAll('.btn-transaksi-cancel').forEach(function (button) {
            button.addEventListener('click', function () {
                const cancelMode = button.getAttribute('data-cancel-mode') || 'confirmed';
                let confirmText = 'Yakin ingin membatalkan transaksi ini? Stok buku akan dikembalikan.';

                if (cancelMode === 'pending') {
                    confirmText = 'Yakin ingin membatalkan transaksi ini? Stok buku belum terpotong (belum paid).';
                }

                if (!confirm(confirmText)) {
                    return;
                }

                openActionModal('cancel', button);
                actionModal.show();
            });
        });

        actionForm.addEventListener('submit', function () {
            showLoading();
        });

        pengirimanForm.addEventListener('submit', function () {
            showLoading();
        });

        <?php if ($success_message): ?>
        showLoading();
        setTimeout(function () {
            window.location.href = '<?= admin_module_url('transaksi') ?>';
        }, 1000);
        <?php endif; ?>
    })();
</script>

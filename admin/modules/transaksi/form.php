<?php
if (!isset($status_pengiriman_options)) {
    require_once __DIR__ . '/../../../includes/admin/init.php';
    $status_pengiriman_options = get_status_pengiriman_options($conn);
}
?>
<!-- Modal View Detail -->
<div class="modal fade" id="transaksiViewModal" tabindex="-1" aria-labelledby="transaksiViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #000;" id="transaksiViewModalLabel">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Kode Transaksi</label>
                        <input type="text" class="form-control" id="view_kode_transaksi" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Status</label>
                        <input type="text" class="form-control" id="view_status" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Nama User</label>
                        <input type="text" class="form-control" id="view_nama_user" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Email User</label>
                        <input type="text" class="form-control" id="view_email_user" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Total Buku (qty)</label>
                        <input type="text" class="form-control" id="view_total_buku" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Total Bayar</label>
                        <input type="text" class="form-control" id="view_total_bayar" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Sudah Dibayar</label>
                        <input type="text" class="form-control" id="view_is_paid" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Tanggal Transaksi</label>
                        <input type="text" class="form-control" id="view_created_at" disabled>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted small">Deskripsi (transaksi)</label>
                        <textarea class="form-control" id="view_deskripsi" rows="2" disabled></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted small">Status pengiriman (admin)</label>
                        <input type="text" class="form-control" id="view_status_pengiriman" disabled>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted small">Catatan pengiriman (admin)</label>
                        <textarea class="form-control" id="view_deskripsi_pengiriman" rows="2" disabled></textarea>
                    </div>
                </div>

                <h6 class="mb-2" style="color: #000;">Detail Pesanan</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Judul Buku</th>
                                <th>Harga Satuan</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="viewDetailBody"></tbody>
                    </table>
                </div>

                <h6 class="mb-2" style="color: #000;">Bukti Pembayaran</h6>
                <div id="viewBuktiEmpty" class="text-muted small">Belum ada bukti pembayaran.</div>
                <div id="viewBuktiWrap" class="d-none">
                    <a id="viewBuktiLink" href="#" target="_blank" rel="noopener">
                        <img id="viewBuktiImg" src="" alt="Bukti pembayaran" class="transaksi-bukti-preview">
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Paid / Batalkan -->
<div class="modal fade" id="transaksiActionModal" tabindex="-1" aria-labelledby="transaksiActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #000;" id="transaksiActionModalLabel">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formTransaksiAction" method="post" action="<?= admin_module_url('transaksi') ?>proses.php">
                <div class="modal-body">
                    <input type="hidden" name="action" id="actionType" value="">
                    <input type="hidden" name="id" id="actionTransaksiId" value="">
                    <p class="text-muted small mb-3" id="actionInfoText"></p>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label" style="color: #000;">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required placeholder="Tulis catatan konfirmasi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanAction">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Status Pengiriman -->
<div class="modal fade" id="transaksiPengirimanModal" tabindex="-1" aria-labelledby="transaksiPengirimanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #000;" id="transaksiPengirimanModalLabel">Status pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formTransaksiPengiriman" method="post" action="<?= admin_module_url('transaksi') ?>proses.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_pengiriman">
                    <input type="hidden" name="id" id="pengirimanTransaksiId" value="">
                    <p class="text-muted small mb-3" id="pengirimanInfoText"></p>
                    <div class="mb-3">
                        <label for="id_pengirim" class="form-label" style="color: #000;">Status pengiriman</label>
                        <select class="form-select" name="id_pengirim" id="id_pengirim" required>
                            <option value="">-- Pilih status --</option>
                            <?php foreach ($status_pengiriman_options as $sp): ?>
                                <option value="<?= (int) $sp['id'] ?>"><?= htmlspecialchars($sp['status_pengiriman']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi_pengiriman" class="form-label" style="color: #000;">Catatan pengiriman</label>
                        <textarea class="form-control" name="deskripsi_pengiriman" id="deskripsi_pengiriman" rows="3" placeholder="Nomor resi, kurir, keterangan…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanPengiriman">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

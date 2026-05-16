<?php
if (!isset($kategori_options)) {
    require_once __DIR__ . '/../../../includes/admin/init.php';
    $kategori_options = get_kategori_options($conn);
}
?>
<div class="modal fade" id="bukuModal" tabindex="-1" aria-labelledby="bukuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #000;" id="bukuModalLabel">Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formBuku" method="post" action="<?= admin_module_url('buku') ?>proses.php" enctype="multipart/form-data" class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="">
                    <input type="hidden" name="gambar_lama" id="gambar_lama" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_kategori" class="form-label" style="color: #000;">Kategori</label>
                            <select class="form-select" id="id_kategori" name="id_kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_options as $kat): ?>
                                    <option value="<?= (int) $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="judul_buku" class="form-label" style="color: #000;">Judul Buku</label>
                            <input type="text" class="form-control" id="judul_buku" name="judul_buku" maxlength="255" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label" style="color: #000;">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="penulis" class="form-label" style="color: #000;">Penulis</label>
                            <input type="text" class="form-control" id="penulis" name="penulis" maxlength="200" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="penerbit" class="form-label" style="color: #000;">Penerbit</label>
                            <input type="text" class="form-control" id="penerbit" name="penerbit" maxlength="200" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tahun_penerbit" class="form-label" style="color: #000;">Tahun Terbit</label>
                            <input type="date" class="form-control" id="tahun_penerbit" name="tahun_penerbit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label" style="color: #000;">Status Aktif</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label" style="color: #000;">Harga</label>
                            <input type="number" class="form-control" id="harga" name="harga" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label" style="color: #000;">Stok</label>
                            <input type="number" class="form-control" id="stok" name="stok" min="0" step="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gambar" class="form-label" style="color: #000;">Gambar</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept=".jpg, .png, .jpeg">
                        <div id="gambarPreviewWrap" class="mt-2 d-none">
                            <img id="gambarPreview" src="" alt="Preview gambar" class="buku-gambar-preview">
                        </div>
                    </div>

                    <div id="auditFields" class="d-none">
                        <hr>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Dibuat Pada</label>
                            <input type="text" class="form-control form-control-sm" id="view_created_at" disabled>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Dibuat Oleh</label>
                            <input type="text" class="form-control form-control-sm" id="view_created_by" disabled>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Diperbarui Pada</label>
                            <input type="text" class="form-control form-control-sm" id="view_updated_at" disabled>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Diperbarui Oleh</label>
                            <input type="text" class="form-control form-control-sm" id="view_updated_by" disabled>
                        </div>
                    </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary" id="btnSimpanBuku" form="formBuku">Simpan</button>
            </div>
        </div>
    </div>
</div>

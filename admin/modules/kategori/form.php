<div class="modal fade" id="kategoriModal" tabindex="-1" aria-labelledby="kategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #000;" id="kategoriModalLabel">Kategori Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formKategori" method="post" action="<?= admin_module_url('kategori') ?>proses.php">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="">

                    <div class="mb-3">
                        <label for="initial" class="form-label" style="color: #000;">Initial</label>
                        <input type="text" class="form-control" id="initial" name="initial" maxlength="20" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label" style="color: #000;">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label for="is_active" class="form-label" style="color: #000;">Status Aktif</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanKategori">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

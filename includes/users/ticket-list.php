<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$user_tickets = $user_tickets ?? [];
$ticket_success = $_SESSION['user_ticket_success'] ?? '';
$ticket_error = $_SESSION['user_ticket_error'] ?? '';
unset($_SESSION['user_ticket_success'], $_SESSION['user_ticket_error']);
?>
<section class="section-gap" id="pesanan-saya">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <div class="section-label">📦 Riwayat Pemesanan</div>
                <h2 class="section-title">Pesanan Saya</h2>
            </div>
            <div class="section-tabs" id="tiketTabs">
                <button type="button" class="section-tab active" data-ticket-filter="semua">Semua</button>
                <button type="button" class="section-tab" data-ticket-filter="aktif">Aktif</button>
                <button type="button" class="section-tab" data-ticket-filter="selesai">Selesai</button>
            </div>
        </div>

        <?php if ($ticket_success !== ''): ?>
            <div class="alert alert-success py-2 px-3 mb-3" style="font-size:0.85rem;">
                <?= htmlspecialchars($ticket_success) ?>
            </div>
        <?php endif; ?>
        <?php if ($ticket_error !== ''): ?>
            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.85rem;">
                <?= htmlspecialchars($ticket_error) ?>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3" id="ticketList">
            <?php if (empty($user_tickets)): ?>
                <div class="text-center py-5" style="color:var(--clr-muted);">
                    Belum ada riwayat pemesanan buku.
                </div>
            <?php else: ?>
                <?php foreach ($user_tickets as $ticket): ?>
                    <?php
                    $id_status = (int) ($ticket['id_status'] ?? 0);
                    $stub = user_ticket_stub_parts($ticket['tanggal_event'] ?? '');
                    $filter_group = user_ticket_filter_group($id_status);
                    $status_class = user_ticket_status_class($id_status);
                    $status_icon = user_ticket_status_icon($id_status);
                    $stub_style = user_ticket_stub_style($id_status);
                    $show_actions = user_ticket_show_actions($id_status);
                    $card_opacity = user_ticket_card_opacity($id_status);
                    $jumlah = (int) ($ticket['jumlah_tiket'] ?? 0);
                    $jumlah_label = $jumlah . ' Buku';
                    $terbit_tampil = user_format_tanggal_card($ticket['tanggal_event'] ?? '');
                    ?>
                    <div class="ticket-card ticket-card-item"
                        data-ticket-group="<?= htmlspecialchars($filter_group) ?>"
                        data-id-header="<?= (int) ($ticket['id_header'] ?? 0) ?>"
                        data-kode="<?= htmlspecialchars($ticket['kode_transaksi'] ?? '', ENT_QUOTES) ?>"
                        data-event-name="<?= htmlspecialchars($ticket['nama_event'] ?? '', ENT_QUOTES) ?>"
                        data-total="<?= (float) ($ticket['total_harga'] ?? 0) ?>"
                        style="opacity:<?= $card_opacity ?>;">
                        <div class="ticket-stub" <?= $stub_style !== '' ? 'style="' . $stub_style . '"' : '' ?>>
                            <div class="ticket-stub-date"><?= htmlspecialchars($stub['day']) ?></div>
                            <div class="ticket-stub-month"><?= htmlspecialchars($stub['month']) ?></div>
                        </div>
                        <div class="ticket-body">
                            <div>
                                <div class="ticket-event-name"><?= htmlspecialchars($ticket['nama_event'] ?? '-') ?></div>
                                <div class="ticket-meta">
                                    <span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($terbit_tampil) ?></span>
                                    <span><i class="bi bi-person-lines-fill"></i> <?= htmlspecialchars($ticket['lokasi'] ?? '-') ?></span>
                                    <span><i class="bi bi-book"></i> <?= htmlspecialchars($jumlah_label) ?></span>
                                </div>
                            </div>
                            <div class="ticket-footer ticket-footer-stack">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 w-100">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="ticket-status <?= htmlspecialchars($status_class) ?>">
                                            <i class="bi <?= htmlspecialchars($status_icon) ?>"></i>
                                            <?= htmlspecialchars($ticket['status'] ?? '-') ?>
                                        </span>
                                        <span style="font-size:0.8rem; color:var(--clr-muted);">
                                            Total: <strong style="color:var(--clr-text);"><?= format_rupiah($ticket['total_harga'] ?? 0) ?></strong>
                                        </span>
                                    </div>
                                    <?php if ($show_actions): ?>
                                        <div class="ticket-actions">
                                            <button type="button"
                                                class="btn-book btn-upload-bukti"
                                                data-bs-toggle="modal"
                                                data-bs-target="#uploadModal"
                                                data-id-header="<?= (int) ($ticket['id_header'] ?? 0) ?>"
                                                data-kode="<?= htmlspecialchars($ticket['kode_transaksi'] ?? '', ENT_QUOTES) ?>"
                                                data-event-name="<?= htmlspecialchars($ticket['nama_event'] ?? '', ENT_QUOTES) ?>"
                                                data-total="<?= (float) ($ticket['total_harga'] ?? 0) ?>">
                                                Upload Bukti
                                            </button>
                                            <button type="button"
                                                class="btn-cancel btn-cancel-transaksi"
                                                data-id-header="<?= (int) ($ticket['id_header'] ?? 0) ?>">
                                                Batalkan Pemesanan
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $id_pengirim_row = (int) ($ticket['id_pengirim'] ?? 0);
                                $status_pengiriman_teks = trim((string) ($ticket['status_pengiriman'] ?? ''));
                                $deskripsi_pengiriman_teks = trim((string) ($ticket['deskripsi_pengiriman'] ?? ''));
                                $tampil_blok_pengiriman = $id_status === USER_STATUS_TERKONFIRMASI
                                    || $id_status === USER_STATUS_MENUNGGU_KONFIRMASI
                                    || $id_pengirim_row > 0;
                                $show_btn_terima = user_ticket_show_konfirmasi_terima($ticket);
                                ?>
                                <?php if ($tampil_blok_pengiriman): ?>
                                    <div class="ticket-pengiriman-block mt-3 pt-3 w-100" style="border-top:1px solid var(--clr-border);">
                                        <div class="ticket-pengiriman-label">
                                            <i class="bi bi-truck"></i> Status pengiriman
                                        </div>
                                        <?php if ($id_pengirim_row > 0 && $status_pengiriman_teks !== ''): ?>
                                            <div class="ticket-pengiriman-status"><?= htmlspecialchars($status_pengiriman_teks) ?></div>
                                        <?php elseif ($id_status === USER_STATUS_TERKONFIRMASI): ?>
                                            <div class="ticket-pengiriman-muted">Menunggu update pengiriman dari toko.</div>
                                        <?php else: ?>
                                            <div class="ticket-pengiriman-muted">Akan ditampilkan setelah pembayaran dikonfirmasi.</div>
                                        <?php endif; ?>
                                        <?php if ($deskripsi_pengiriman_teks !== ''): ?>
                                            <div class="ticket-pengiriman-deskripsi">
                                                <span class="ticket-pengiriman-deskripsi-label">Catatan / resi</span>
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($deskripsi_pengiriman_teks)) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($show_btn_terima): ?>
                                            <button type="button"
                                                class="btn-konfirmasi-terima btn-book mt-2"
                                                data-id-header="<?= (int) ($ticket['id_header'] ?? 0) ?>">
                                                <i class="bi bi-check2-circle"></i> Sampai Tujuan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

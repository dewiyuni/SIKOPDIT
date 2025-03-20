<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Transaksi Simpanan</h3>
        <div>
            <a href="javascript:history.back()" class="btn btn-warning">Kembali</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informasi Anggota</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Nama Anggota</th>
                            <td>: <?= $anggota->nama ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Nomor BA</th>
                            <td>: <?= $anggota->no_ba ?? '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="bg-light">
                        <th class="text-center" style="width: 5%;">No</th>
                        <th class="text-center" style="width: 15%;">Tanggal</th>
                        <th class="text-center" style="width: 8%;">SW Setor</th>
                        <th class="text-center" style="width: 8%;">SW Tarik</th>
                        <th class="text-center" style="width: 8%;">SWP Setor</th>
                        <th class="text-center" style="width: 8%;">SWP Tarik</th>
                        <th class="text-center" style="width: 8%;">SS Setor</th>
                        <th class="text-center" style="width: 8%;">SS Tarik</th>
                        <th class="text-center" style="width: 8%;">SP Setor</th>
                        <th class="text-center" style="width: 8%;">SP Tarik</th>
                        <th class="text-center" style="width: 16%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php
                    // Inisialisasi total
                    $total_setor_sw = 0;
                    $total_tarik_sw = 0;
                    $total_setor_swp = 0;
                    $total_tarik_swp = 0;
                    $total_setor_ss = 0;
                    $total_tarik_ss = 0;
                    $total_setor_sp = 0;
                    $total_tarik_sp = 0;
                    ?>

                    <!-- Riwayat Transaksi -->
                    <?php if (!empty($riwayat_transaksi)): ?>
                        <?php foreach ($riwayat_transaksi as $transaksi): ?>
                            <?php
                            // Convert to object if it's an array
                            $t = is_object($transaksi) ? $transaksi : (object) $transaksi;

                            // Ensure all properties exist
                            $id_simpanan = $t->id_simpanan ?? '';
                            $tanggal = $t->tanggal ?? date('Y-m-d');
                            $setor_sw = $t->setor_sw ?? 0;
                            $tarik_sw = $t->tarik_sw ?? 0;
                            $setor_swp = $t->setor_swp ?? 0;
                            $tarik_swp = $t->tarik_swp ?? 0;
                            $setor_ss = $t->setor_ss ?? 0;
                            $tarik_ss = $t->tarik_ss ?? 0;
                            $setor_sp = $t->setor_sp ?? 0;
                            $tarik_sp = $t->tarik_sp ?? 0;

                            // Akumulasi total
                            $total_setor_sw += $setor_sw;
                            $total_tarik_sw += $tarik_sw;
                            $total_setor_swp += $setor_swp;
                            $total_tarik_swp += $tarik_swp;
                            $total_setor_ss += $setor_ss;
                            $total_tarik_ss += $tarik_ss;
                            $total_setor_sp += $setor_sp;
                            $total_tarik_sp += $tarik_sp;
                            ?>
                            <tr data-transaction-id="<?= $id_simpanan ?>">
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= date('d M Y', strtotime($tanggal)) ?></td>

                                <td class="text-end"><?= $setor_sw > 0 ? 'Rp ' . number_format($setor_sw, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end"><?= $tarik_sw > 0 ? 'Rp ' . number_format($tarik_sw, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end">
                                    <?= $setor_swp > 0 ? 'Rp ' . number_format($setor_swp, 0, ',', '.') : '-' ?></td>
                                <td class="text-end">
                                    <?= $tarik_swp > 0 ? 'Rp ' . number_format($tarik_swp, 0, ',', '.') : '-' ?></td>

                                <td class="text-end"><?= $setor_ss > 0 ? 'Rp ' . number_format($setor_ss, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end"><?= $tarik_ss > 0 ? 'Rp ' . number_format($tarik_ss, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end"><?= $setor_sp > 0 ? 'Rp ' . number_format($setor_sp, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end"><?= $tarik_sp > 0 ? 'Rp ' . number_format($tarik_sp, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= site_url('karyawan/transaksi_simpanan/edit/' . $id_simpanan) ?>"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="<?= site_url('karyawan/transaksi_simpanan/delete/' . $id_simpanan) ?>"
                                            method="post" class="d-inline delete-form">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-danger btn-sm delete-btn">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada riwayat transaksi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td colspan="2" class="text-center"><strong>Total</strong></td>
                        <td class="text-end">Rp <?= number_format($total_setor_sw, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_tarik_sw, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_setor_swp, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_tarik_swp, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_setor_ss, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_tarik_ss, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_setor_sp, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($total_tarik_sp, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format(
                            ($total_setor_sw - $total_tarik_sw) +
                            ($total_setor_swp - $total_tarik_swp) +
                            ($total_setor_ss - $total_tarik_ss) +
                            ($total_setor_sp - $total_tarik_sp),
                            0,
                            ',',
                            '.'
                        ) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Tampilkan modal konfirmasi hapus
        $('.delete-btn').click(function () {
            // Simpan referensi ke form yang akan di-submit
            let currentForm = $(this).closest('form');

            // Tampilkan modal konfirmasi
            $('#deleteConfirmModal').modal('show');

            // Ketika tombol konfirmasi hapus diklik
            $('#confirmDeleteBtn').off('click').on('click', function () {
                // Sembunyikan modal
                $('#deleteConfirmModal').modal('hide');
                // Submit form
                currentForm.submit();
            });
        });
    });
</script>

<?= $this->endSection() ?>
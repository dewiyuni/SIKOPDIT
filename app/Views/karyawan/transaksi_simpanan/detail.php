<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Transaksi Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
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
                    <?php
                    $no = 1;

                    // Inisialisasi variabel untuk menyimpan total
                    $total_sw_setor = 0;
                    $total_sw_tarik = 0;
                    $total_swp_setor = 0;
                    $total_swp_tarik = 0;
                    $total_ss_setor = 0;
                    $total_ss_tarik = 0;
                    $total_sp_setor = 0;
                    $total_sp_tarik = 0;
                    ?>

                    <!-- Riwayat Transaksi -->
                    <?php if (!empty($riwayat_transaksi)): ?>
                        <?php foreach ($riwayat_transaksi as $transaksi): ?>
                            <?php
                            // Menambahkan nilai ke total
                            $total_sw_setor += $transaksi->setor_sw;
                            $total_sw_tarik += $transaksi->tarik_sw;
                            $total_swp_setor += $transaksi->setor_swp;
                            $total_swp_tarik += $transaksi->tarik_swp;
                            $total_ss_setor += $transaksi->setor_ss;
                            $total_ss_tarik += $transaksi->tarik_ss;
                            $total_sp_setor += $transaksi->setor_sp;
                            $total_sp_tarik += $transaksi->tarik_sp;
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= date('d M Y H:i:s', strtotime($transaksi->waktu)) ?></td>

                                <td class="text-end">
                                    <?= $transaksi->setor_sw ? 'Rp ' . number_format($transaksi->setor_sw, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end">
                                    <?= $transaksi->tarik_sw ? 'Rp ' . number_format($transaksi->tarik_sw, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end">
                                    <?= $transaksi->setor_swp ? 'Rp ' . number_format($transaksi->setor_swp, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end">
                                    <?= $transaksi->tarik_swp ? 'Rp ' . number_format($transaksi->tarik_swp, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end">
                                    <?= $transaksi->setor_ss ? 'Rp ' . number_format($transaksi->setor_ss, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end">
                                    <?= $transaksi->tarik_ss ? 'Rp ' . number_format($transaksi->tarik_ss, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end">
                                    <?= $transaksi->setor_sp ? 'Rp ' . number_format($transaksi->setor_sp, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end">
                                    <?= $transaksi->tarik_sp ? 'Rp ' . number_format($transaksi->tarik_sp, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-center">
                                    <a href="<?= site_url('karyawan/transaksi_simpanan/edit/' . ($transaksi->id_transaksi ?? '')) ?>"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= site_url('karyawan/transaksi_simpanan/delete/' . ($transaksi->id_transaksi ?? '')) ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada riwayat transaksi</td>
                        </tr>
                    <?php endif; ?>

                    <!-- Saldo Sekarang di akhir tabel -->
                    <tr class="table-warning fw-bold">
                        <td></td> <!-- Tidak ada nomor -->
                        <td><strong>Total</strong></td>
                        <td class="text-end">Rp <?= number_format($total_sw_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_sw_tarik > 0 ? 'Rp ' . number_format($total_sw_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format($total_swp_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_swp_tarik > 0 ? 'Rp ' . number_format($total_swp_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format($total_ss_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_ss_tarik > 0 ? 'Rp ' . number_format($total_ss_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format($total_sp_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_sp_tarik > 0 ? 'Rp ' . number_format($total_sp_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format(
                            ($total_sw_setor - $total_sw_tarik) +
                            ($total_swp_setor - $total_swp_tarik) +
                            ($total_ss_setor - $total_ss_tarik) +
                            ($total_sp_setor - $total_sp_tarik),
                            0,
                            ',',
                            '.'
                        ) ?></td> <!-- Total saldo -->
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
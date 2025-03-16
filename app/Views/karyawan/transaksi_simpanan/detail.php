<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Detail Transaksi Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <div style="overflow-x: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>SW Setor</th>
                        <th>SW Tarik</th>
                        <th>SWP Setor</th>
                        <th>SWP Tarik</th>
                        <th>SS Setor</th>
                        <th>SS Tarik</th>
                        <th>SP Setor</th>
                        <th>SP Tarik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <!-- Saldo Awal -->
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong>Saldo Awal</strong></td>
                        <td>Rp <?= number_format($saldo_awal->sw, 0, ',', '.') ?></td>
                        <td>-</td>
                        <td>Rp <?= number_format($saldo_awal->swp, 0, ',', '.') ?></td>
                        <td>-</td>
                        <td>Rp <?= number_format($saldo_awal->ss, 0, ',', '.') ?></td>
                        <td>-</td>
                        <td>Rp <?= number_format($saldo_awal->sp, 0, ',', '.') ?></td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    <!-- Riwayat Transaksi -->
                    <?php if (!empty($riwayat_transaksi)): ?>
                        <?php foreach ($riwayat_transaksi as $transaksi): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d M Y', strtotime($transaksi->tanggal)) ?></td>

                                <td><?= $transaksi->setor_sw ? 'Rp ' . number_format($transaksi->setor_sw, 0, ',', '.') : '-' ?>
                                </td>
                                <td><?= $transaksi->tarik_sw ? 'Rp ' . number_format($transaksi->tarik_sw, 0, ',', '.') : '-' ?>
                                </td>

                                <td><?= $transaksi->setor_swp ? 'Rp ' . number_format($transaksi->setor_swp, 0, ',', '.') : '-' ?>
                                </td>
                                <td><?= $transaksi->tarik_swp ? 'Rp ' . number_format($transaksi->tarik_swp, 0, ',', '.') : '-' ?>
                                </td>

                                <td><?= $transaksi->setor_ss ? 'Rp ' . number_format($transaksi->setor_ss, 0, ',', '.') : '-' ?>
                                </td>
                                <td><?= $transaksi->tarik_ss ? 'Rp ' . number_format($transaksi->tarik_ss, 0, ',', '.') : '-' ?>
                                </td>

                                <td><?= $transaksi->setor_sp ? 'Rp ' . number_format($transaksi->setor_sp, 0, ',', '.') : '-' ?>
                                </td>
                                <td><?= $transaksi->tarik_sp ? 'Rp ' . number_format($transaksi->tarik_sp, 0, ',', '.') : '-' ?>
                                </td>

                                <td>
                                    <a href="#" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada riwayat transaksi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
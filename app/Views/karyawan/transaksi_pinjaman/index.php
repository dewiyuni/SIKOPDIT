<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Transaksi Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/tambah') ?>" class="btn btn-success">Tambah Data</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Transaksi Pinjaman</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No BA</th>
                        <th>Tanggal Cair</th>
                        <th>Jangka Waktu</th>
                        <th>Jasa</th>
                        <th>Besar Pinjaman</th>
                        <th>Saldo Terakhir</th>
                        <th>Jaminan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pinjaman)): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pinjaman as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($row->nama ?? '-') ?></td>
                                <td><?= esc($row->no_ba ?? '-') ?></td>
                                <td><?= isset($row->tanggal_pinjaman) ? date('d M Y', strtotime($row->tanggal_pinjaman)) : '-' ?>
                                </td>
                                <td><?= esc($row->jangka_waktu ?? '-') ?> bulan</td>
                                <td>2,5%</td>
                                <td>Rp <?= number_format($row->jumlah_pinjaman ?? 0, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row->saldo_terakhir ?? 0, 0, ',', '.') ?></td>
                                <td><?= esc($row->jaminan ?? '-') ?></td>

                                <td>
                                    <a href="<?= base_url('karyawan/transaksi_pinjaman/detail/' . $row->id_pinjaman) ?>"
                                        class="btn btn-primary">Detail</a>
                                    <a href="<?= base_url('karyawan/transaksi_pinjaman/tambahAngsuran/' . $row->id_pinjaman) ?>"
                                        class="btn btn-warning btn-sm">Tambah Angsuran</a>
                                </td>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">Belum ada data pinjaman</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
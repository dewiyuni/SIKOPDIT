<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <h3>Laporan Transaksi Simpanan dan Pinjaman</h3>
    <a href="<?= base_url('karyawan/laporan_transaksi/cetak') ?>" class="btn btn-success mb-3">Cetak PDF</a>

    <div class="card p-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis Transaksi</th>
                    <th>Nama Anggota</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($laporan)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($laporan as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($row->jenis_transaksi) ?></td>
                            <td><?= esc($row->nama_anggota) ?></td>
                            <td><?= date('d M Y', strtotime($row->tanggal_transaksi)) ?></td>
                            <td>Rp <?= number_format($row->jumlah, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data transaksi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
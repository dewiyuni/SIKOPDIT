<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3>Detail Transaksi - <?= esc($anggota->nama) ?></h3>
            <p>No BA: <?= esc($anggota->no_ba) ?></p>
        </div>
        <a href="<?= base_url('karyawan/transaksi_simpanan') ?>" class="btn btn-success mb-3">Kembali</a>
    </div>
    <div class="card p-3">
        <div style="overflow-x: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jenis Simpanan</th>
                        <th>Setor</th>
                        <th>Tarik</th>
                        <th>Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($riwayat as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d M Y', strtotime($row->tanggal)) ?></td>
                            <td><?= esc($row->nama_simpanan) ?></td>
                            <td><?= number_format($row->total_setor ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($row->total_tarik ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($row->total_saldo, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
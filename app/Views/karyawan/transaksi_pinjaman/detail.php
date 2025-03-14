<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="container card p-3 mt-3">
        <!-- Bagian Judul dan Tombol -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Detail Pinjaman - <?= $pinjaman->nama ?></h3>
            <div>
                <a href="<?= base_url('karyawan/transaksi_pinjaman/tambahAngsuran/' . $pinjaman->id_pinjaman) ?>"
                    class="btn btn-success me-2">
                    Tambah Angsuran
                </a>
                <a href="<?= base_url('karyawan/transaksi_pinjaman') ?>" class="btn btn-warning">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Bagian Informasi Pinjaman -->
        <div class="row">
            <p>No BA: <?= $pinjaman->no_ba ?></p>
            <div class="col-md-6">
                <p>Tanggal Cair: <?= date('d-m-Y', strtotime($pinjaman->tanggal_pinjaman)) ?></p>
                <p>Besar Pinjaman: Rp <?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?></p>
            </div>
            <div class="col-md-6">
                <p>Jangka Waktu: <?= $pinjaman->jangka_waktu ?> bulan</p>
                <p>Jaminan: <?= $pinjaman->jaminan ?></p>
            </div>
        </div>
    </div>


    <div class="card p-3 mt-3">
        <h4>Riwayat Angsuran</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Saldo Awal</th>
                    <th>Angsuran</th>
                    <th>Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($angsuran)): ?>
                    <?php $no = 1;
                    foreach ($angsuran as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?php echo date('d M Y', strtotime($row->tanggal_angsuran)); ?></td>
                            <td>Rp <?= number_format($row->sisa_pinjaman + $row->jumlah_angsuran, 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row->jumlah_angsuran, 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row->sisa_pinjaman, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Belum ada angsuran</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
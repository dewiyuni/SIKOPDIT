<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">Buku Besar</h3>

    <form method="GET" action="">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="tahun">Pilih Tahun</label>
                <select name="tahun" id="tahun" class="form-select">
                    <?php for ($year = date("Y"); $year >= 2015; $year--): ?>
                        <option value="<?= $year; ?>" <?= $year == date('Y') ? 'selected' : '' ?>><?= $year; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="bulan">Pilih Bulan</label>
                <select name="bulan" id="bulan" class="form-select">
                    <option value="">Semua Bulan</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT); ?>"><?= date('F', mktime(0, 0, 0, $m, 10)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h5>Data Buku Besar</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Akun</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bukuBesar as $row): ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                            <td><?= $row['akun']; ?></td>
                            <td><?= number_format($row['debit'], 0, ',', '.'); ?></td>
                            <td><?= number_format($row['kredit'], 0, ',', '.'); ?></td>
                            <td><?= number_format($row['saldo'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?= base_url('admin/buku_besar/prosesJurnalKeBukuBesar'); ?>" class="btn btn-success mt-3">Proses Jurnal ke
        Buku Besar</a>
</div>

<?= $this->endSection(); ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Angsuran - <?= $pinjaman->nama ?></h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/') ?>" class="btn btn-warning">Kembali</a>
    </div>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="post" action="<?= site_url('karyawan/transaksi_pinjaman/simpan_angsuran') ?>">
        <?= csrf_field() ?>
            <input type="hidden" name="id_pinjaman" value="<?= $pinjaman->id_pinjaman ?>">

            <div class="form-group">
                <label for="tanggal_angsuran">Tanggal Angsuran</label>
                <input type="date" name="tanggal_angsuran" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="jumlah_angsuran">Jumlah Angsuran</label>
                <input type="number" name="jumlah_angsuran" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan Angsuran</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
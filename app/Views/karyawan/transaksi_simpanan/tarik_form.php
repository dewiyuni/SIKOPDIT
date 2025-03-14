<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Transaksi Tarik Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('karyawan/transaksi_simpanan/tarik') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_anggota" value="<?= esc($anggota->id_anggota ?? '') ?>">
            <input type="hidden" name="id_jenis_simpanan" value="<?= esc($id_simpanan_sukarela ?? '') ?>">

            <label for="tarik_ss">Jumlah Penarikan Simpanan Sukarela:</label>
            <input type="number" name="tarik_ss" class="form-control" placeholder="Masukkan jumlah Simpanan sukarela"
                required min="1">

            <button type="submit" class="btn btn-danger mt-3">Tarik</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
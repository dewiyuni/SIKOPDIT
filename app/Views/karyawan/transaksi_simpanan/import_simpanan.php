<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Upload File Excel Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <p style="color: green;"> <?= session()->getFlashdata('success') ?> </p>
    <?php elseif (session()->getFlashdata('error')): ?>
        <p style="color: red;"> <?= session()->getFlashdata('error') ?> </p>
    <?php endif; ?>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('karyawan/transaksi_simpanan/import_simpanan/upload') ?>" method="post"
            enctype="multipart/form-data">
            <input type="file" name="file_excel" required>
            <button type="submit">Upload</button>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
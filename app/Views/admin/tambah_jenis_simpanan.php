<?= $this->extend('layouts/main'); ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Jenis Simpanan</h3>
        <a href="<?= site_url('admin/jenis_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('admin/simpan_jenis_simpanan') ?>" method="post">
            <div class="form-group">
                <label>Nama Simpanan</label>
                <input type="text" name="nama_simpanan" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
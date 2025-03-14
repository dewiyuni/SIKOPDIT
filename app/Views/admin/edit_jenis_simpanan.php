<?= $this->extend('layouts/main'); ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Jenis Simpanan</h3>
        <a href="<?= site_url('admin/jenis_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-3">
        <form action="<?= site_url('admin/update_jenis_simpanan') ?>" method="post">
            <input type="hidden" name="id_jenis_simpanan" value="<?= $jenis_simpanan->id_jenis_simpanan ?>">

            <div class="form-group">
                <label>Nama Simpanan</label>
                <input type="text" name="nama_simpanan" value="<?= $jenis_simpanan->nama_simpanan ?>"
                    class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
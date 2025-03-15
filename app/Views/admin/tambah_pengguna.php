<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Karyawan</h3>
        <a href="<?= site_url('admin/kelola_pengguna') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-3">
        <form action="<?= site_url('admin/simpan_pengguna') ?>" method="POST">
            <?= csrf_field(); ?>

            <label for="nama">Nama:</label>
            <input type="text" name="nama" class="form-control" required>

            <label for="email">Email:</label>
            <input type="email" name="email" class="form-control" required>

            <label for="password">Password:</label>
            <input type="password" name="password" class="form-control" required>

            <label for="role">Role:</label>
            <select name="role" class="form-control" disabled required>
                <option value="karyawan">Karyawan</option>
            </select><br>

            <button type="submit" class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>
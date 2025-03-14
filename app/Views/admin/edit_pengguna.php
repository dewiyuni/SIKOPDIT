<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Pengguna</h3>
        <a href="<?= site_url('admin/kelola_pengguna') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-3">
        <form action="<?= site_url('admin/updatePengguna') ?>" method="POST">
            <input type="hidden" name="id_user" value="<?= $pengguna->id_user ?>">

            <label for="nama">Nama:</label>
            <input type="text" name="nama" value="<?= $pengguna->nama; ?>" class="form-control" required>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?= $pengguna->email; ?>" class="form-control" required>

            <label for="role">Role:</label>
            <select name="role" class="form-control" required>
                <option value="admin" <?= $pengguna->role == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="karyawan" <?= $pengguna->role == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
            </select>

            <label for="status">Status:</label>
            <select name="status" class="form-control" required>
                <option value="aktif" <?= $pengguna->status == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $pengguna->status == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select><br>

            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= $this->endSection(); ?>
<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Pengguna</h3>
        <a href="<?= site_url('admin/kelola_pengguna') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <!-- START: Notifikasi Flash Data dan Validation Errors -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    // Menampilkan validation errors jika ada
    $validationErrors = session('errors');
    if ($validationErrors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul>
                <?php foreach ($validationErrors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <!-- END: Notifikasi Flash Data dan Validation Errors -->


    <div class="card p-3">
        <form action="<?= site_url('admin/updatePengguna') ?>" method="POST">
            <?= csrf_field() ?> <!-- Tambahkan CSRF field -->
            <input type="hidden" name="id_user" value="<?= esc($pengguna->id_user) ?>">

            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <!-- Gunakan old() dengan fallback ke data dari DB -->
                <input type="text" name="nama" id="nama"
                    class="form-control <?= session('errors.nama') ? 'is-invalid' : '' ?>"
                    value="<?= old('nama', $pengguna->nama); ?>" required>
                <?php if (session('errors.nama')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.nama') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <!-- Gunakan old() dengan fallback ke data dari DB -->
                <input type="email" name="email" id="email"
                    class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                    value="<?= old('email', $pengguna->email); ?>" required>
                <?php if (session('errors.email')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.email') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password (kosongkan jika tidak diubah):</label>
                <!-- Password tidak perlu old() value untuk keamanan -->
                <input type="password" name="password" id="password"
                    class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>">
                <?php if (session('errors.password')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.password') ?>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted">Kosongkan password jika tidak ingin mengubahnya.</small>
            </div>


            <div class="mb-3">
                <label for="role_display" class="form-label">Role:</label>
                <!-- SELECT ini HANYA untuk tampilan dan disabled -->
                <select name="role_display" id="role_display" class="form-control" disabled>
                    <option value="admin" <?= old('role', $pengguna->role) == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="karyawan" <?= old('role', $pengguna->role) == 'karyawan' ? 'selected' : '' ?>>Karyawan
                    </option>
                </select>
                <!-- HIDDEN input untuk mengirim nilai 'role' ke controller -->
                <input type="hidden" name="role" value="<?= old('role', $pengguna->role) ?>">
                <?php // Validation feedback masih relevan jika rule di controller cek 'role' ?>
                <?php if (session('errors.role')): ?>
                    <div class="invalid-feedback d-block"> <!-- Use d-block to force display -->
                        <?= session('errors.role') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <!-- SELECT Status: disabled jika role pengguna adalah 'admin' -->
                <select name="status_display" id="status_display"
                    class="form-control <?= session('errors.status') ? 'is-invalid' : '' ?>"
                    <?= ($pengguna->role == 'admin') ? 'disabled' : '' ?> required>
                    <option value="aktif" <?= old('status', $pengguna->status) == 'aktif' ? 'selected' : '' ?>>Aktif
                    </option>
                    <option value="nonaktif" <?= old('status', $pengguna->status) == 'nonaktif' ? 'selected' : '' ?>>
                        Nonaktif</option>
                </select>
                <!-- HIDDEN input untuk mengirim nilai 'status' ke controller -->
                <!-- Nilai diambil dari DB atau old() -->
                <input type="hidden" name="status" value="<?= old('status', $pengguna->status) ?>">

                <?php if (session('errors.status')): ?>
                    <div class="invalid-feedback d-block"> <!-- Use d-block to force display -->
                        <?= session('errors.status') ?>
                    </div>
                <?php endif; ?>
                <?php if ($pengguna->role == 'admin'): ?>
                    <small class="form-text text-muted">Status pengguna Admin tidak dapat diubah melalui form ini.</small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-success">Update Pengguna</button>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>
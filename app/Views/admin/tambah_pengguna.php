<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Pengguna Baru</h3>
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
        <form action="<?= site_url('admin/simpan_pengguna') ?>" method="POST">
            <?= csrf_field() ?> <!-- Tambahkan CSRF field -->

            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <!-- Gunakan old() untuk mempertahankan input jika validasi gagal -->
                <input type="text" name="nama" id="nama"
                    class="form-control <?= session('errors.nama') ? 'is-invalid' : '' ?>" value="<?= old('nama') ?>"
                    required>
                <?php if (session('errors.nama')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.nama') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email"
                    class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" value="<?= old('email') ?>"
                    required>
                <?php if (session('errors.email')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.email') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <!-- Password tidak perlu old() value untuk keamanan -->
                <input type="password" name="password" id="password"
                    class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" required>
                <?php if (session('errors.password')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.password') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <!-- SELECT ini HANYA untuk tampilan. Nilai dikirim via hidden input -->
                <select name="role_display" id="role" class="form-control" required disabled>
                    <option value="karyawan" selected>Karyawan</option>
                    <!-- Hapus opsi admin atau biarkan jika hanya untuk display -->
                    <option value="admin">Admin</option>
                </select>
                <!-- HIDDEN input untuk mengirim nilai 'karyawan' ke controller -->
                <input type="hidden" name="role" value="karyawan">
                <?php // Validation feedback masih relevan jika rule di controller cek 'role' ?>
                <?php if (session('errors.role')): ?>
                    <div class="invalid-feedback d-block"> <!-- Use d-block to force display -->
                        <?= session('errors.role') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <!-- SELECT ini HANYA untuk tampilan. Nilai dikirim via hidden input -->
                <select name="status_display" id="status" class="form-control" required disabled>
                    <option value="aktif" selected>Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
                <!-- HIDDEN input untuk mengirim nilai 'aktif' ke controller -->
                <input type="hidden" name="status" value="aktif">
                <?php // Validation feedback masih relevan jika rule di controller cek 'status' ?>
                <?php if (session('errors.status')): ?>
                    <div class="invalid-feedback d-block"> <!-- Use d-block to force display -->
                        <?= session('errors.status') ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>
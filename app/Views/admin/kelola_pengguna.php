<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Daftar Karyawan</h3>
        <a href="<?= site_url('admin/tambah_pengguna') ?>" class="btn btn-success">Tambah Pengguna</a>
    </div>
    <br>
    <!-- Notifikasi Flash Data -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Karyawan</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= esc($u->id_user); ?></td>
                            <td><?= esc($u->nama); ?></td>
                            <td><?= esc($u->email); ?></td>
                            <td><?= ucfirst(esc($u->role)); ?></td>
                            <td>
                                <a href="<?= site_url('admin/edit_pengguna/' . $u->id_user) ?>"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <?php if (strtolower($u->role) !== 'admin'): ?>
                                    <a href="<?= site_url('admin/hapus_pengguna/' . $u->id_user) ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus pengguna ini?');">Hapus</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Hapus</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
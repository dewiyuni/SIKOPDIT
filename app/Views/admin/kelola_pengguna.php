<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Daftar Pengguna Sistem</h3>
        <a href="<?= site_url('admin/tambah_pengguna') ?>" class="btn btn-success">Tambah Pengguna</a>
    </div>
    <br>

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
    // Menampilkan validation errors jika ada (dari redirect back from simpan/update)
    // Cek session('errors') tanpa getFlashdata karena disimpan langsung
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


    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Daftar Pengguna</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th> <!-- Tambahkan kolom Status -->
                        <th>Dibuat</th>
                        <th>Diperbarui</th>
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
                                <span class="badge bg-<?= $u->status == 'aktif' ? 'success' : 'warning'; ?>">
                                    <?= ucfirst(esc($u->status)); ?>
                                </span>
                            </td>
                            <td><?= ($u->created_at) ? date('d-m-Y H:i:s', strtotime($u->created_at)) : '-'; ?></td>
                            <td><?= ($u->updated_at) ? date('d-m-Y H:i:s', strtotime($u->updated_at)) : '-'; ?></td>
                            <td>
                                <a href="<?= site_url('admin/edit_pengguna/' . $u->id_user) ?>"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <!-- Form Hapus - Gunakan method POST sesuai route yang diperbaiki -->
                                <form action="<?= site_url('admin/hapus_pengguna/' . $u->id_user) ?>" method="post"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus pengguna <?= esc($u->nama); ?>? Tindakan ini tidak dapat dibatalkan.');">
                                    <?= csrf_field() ?> <!-- Tambahkan CSRF field -->
                                    <!-- Tombol Hapus: Hanya aktif jika BUKAN admin DAN BUKAN pengguna yang sedang login -->
                                    <?php
                                    $currentUser = session()->get('user_id');
                                    $isCurrentUser = ($u->id_user == $currentUser);
                                    $isNotAdmin = (strtolower($u->role) !== 'admin');
                                    $canDelete = $isNotAdmin && !$isCurrentUser;
                                    ?>
                                    <button type="submit" class="btn btn-danger btn-sm" <?= $canDelete ? '' : 'disabled' ?>
                                        <?= $canDelete ? '' : 'title="Tidak dapat menghapus Admin atau akun Anda sendiri."' ?>>
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
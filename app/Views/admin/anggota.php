<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Daftar Anggota</h3>
        <a href="<?= site_url('admin/tambah_anggota') ?>" class="btn btn-success">Tambah Anggota</a>
    </div>
    <br>

    <!-- START: Notifikasi Flash Data -->
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

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <!-- END: Notifikasi Flash Data -->

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Anggota</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>No BA</th>
                        <th>Dusun</th>
                        <th>Alamat</th>
                        <th>Pekerjaan</th>
                        <th>Tanggal Lahir</th>
                        <th>Nama Pasangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($anggota as $row): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= esc($row->nama); ?></td>
                            <td><?= esc($row->nik); ?></td>
                            <td><?= esc($row->no_ba); ?></td>
                            <td><?= esc($row->dusun); ?></td>
                            <td><?= esc($row->alamat); ?></td>
                            <td><?= esc($row->pekerjaan); ?></td>
                            <td><?= date('d-m-Y', strtotime($row->tgl_lahir)); ?></td>
                            <td><?= esc($row->nama_pasangan); ?></td>
                            <td>
                                <span class="badge bg-<?= $row->status == 'aktif' ? 'success' : 'warning'; ?>">
                                    <?= ucfirst(esc($row->status)); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('admin/edit_anggota/' . $row->id_anggota) ?>"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="<?= site_url('admin/hapus_anggota/' . $row->id_anggota) ?>" method="post"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                    <?= csrf_field() ?> <!-- Tambahkan CSRF field untuk form delete -->
                                    <button type="submit" class="btn btn-danger btn-sm">
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
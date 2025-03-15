<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Daftar Anggota</h3>
        <a href="<?= site_url('admin/tambah_anggota') ?>" class="btn btn-success">Tambah Anggota</a>
    </div>
    <br>
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('message')) ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
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
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="<?= site_url('admin/hapus_anggota/' . $row->id_anggota) ?>" method="post"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
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
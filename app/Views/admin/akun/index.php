<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Data Akun Keuangan</h2>
        <a href="/admin/akun/create" class="btn btn-success mb-3">Tambah Akun</a>
    </div>

    <div style="overflow-x: auto;">
        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($akun as $a): ?>
                    <tr>
                        <td><?= esc($a['kode_akun']) ?></td>
                        <td><?= esc($a['nama_akun']) ?></td>
                        <td><?= esc($a['jenis']) ?></td>
                        <td>
                            <a href="/admin/akun/edit/<?= $a['id_akun'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="/admin/akun/delete/<?= $a['id_akun'] ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus akun ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
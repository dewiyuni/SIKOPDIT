<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <h3>Daftar Kategori Neraca</h3>
    <a href="/admin/neraca_awal/kategori_neraca/create" class="btn btn-success mb-3">Tambah Kategori</a>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kategoriNeraca as $key => $kategori): ?>
                <tr>
                    <td><?= $key + 1 ?></td>
                    <td><?= $kategori['nama_kategori'] ?></td>
                    <td>
                        <a href="/admin/neraca_awal/kategori_neraca/edit/<?= $kategori['id_kategori'] ?>"
                            class="btn btn-warning btn-sm">Edit</a>
                        <a href="/admin/neraca_awal/kategori_neraca/delete/<?= $kategori['id_kategori'] ?>"
                            class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
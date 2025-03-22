<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <h3>Tambah Kategori Neraca</h3>

    <form action="/admin/neraca/kategori_neraca/store" method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nama Kategori:</label>
            <input type="text" name="nama_kategori" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="/admin/neraca/kategori_neraca" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>
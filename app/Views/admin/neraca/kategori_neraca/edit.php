<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <h3>Edit Kategori Neraca</h3>

    <form action="/admin/neraca/kategori_neraca/update/<?= $kategori['id_kategori'] ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nama Kategori:</label>
            <input type="text" name="nama_kategori" class="form-control" value="<?= $kategori['nama_kategori'] ?>"
                required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="/admin/neraca/kategori_neraca" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>
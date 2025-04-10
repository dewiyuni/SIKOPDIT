<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <h2>Edit Akun</h2>

    <form action="<?= site_url('admin/akun/update/' . $akun['id_akun']) ?>" method="post">
        <div class="mb-3">
            <label for="kode_akun" class="form-label">Kode Akun</label>
            <input type="text" class="form-control" name="kode_akun" value="<?= $akun['kode_akun'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="nama_akun" class="form-label">Nama Akun</label>
            <input type="text" class="form-control" name="nama_akun" value="<?= $akun['nama_akun'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="jenis" class="form-label">Jenis</label>
            <select name="jenis" class="form-control">
                <option value="aktiva" <?= $akun['jenis'] == 'aktiva' ? 'selected' : '' ?>>Aktiva</option>
                <option value="pasiva" <?= $akun['jenis'] == 'pasiva' ? 'selected' : '' ?>>Pasiva</option>
                <option value="pendapatan" <?= $akun['jenis'] == 'pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                <option value="biaya" <?= $akun['jenis'] == 'biaya' ? 'selected' : '' ?>>Biaya</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="<?= site_url('akun') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?= $this->endSection() ?>
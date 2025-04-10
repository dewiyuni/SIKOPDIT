<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <h2>Tambah Akun</h2>

    <form action="<?= site_url('admin/akun/store') ?>" method="post">
        <div class="mb-3">
            <label for="kode_akun" class="form-label">Kode Akun</label>
            <input type="text" class="form-control" name="kode_akun" required>
        </div>
        <div class="mb-3">
            <label for="nama_akun" class="form-label">Nama Akun</label>
            <input type="text" class="form-control" name="nama_akun" required>
        </div>
        <div class="mb-3">
            <label for="jenis" class="form-label">Jenis</label>
            <select name="jenis" class="form-control">
                <option value="aktiva">Aktiva</option>
                <option value="pasiva">Pasiva</option>
                <option value="pendapatan">Pendapatan</option>
                <option value="biaya">Biaya</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="<?= site_url('akun') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?= $this->endSection() ?>
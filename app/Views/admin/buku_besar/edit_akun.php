<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Edit Akun</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Edit Akun</h5>
            <a href="<?= base_url('admin/buku_besar/akun') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/buku_besar/akun/update/' . $akun['id']) ?>" method="post">
                <div class="mb-3">
                    <label for="kode_akun" class="form-label">Kode Akun</label>
                    <input type="text" class="form-control" id="kode_akun" name="kode_akun"
                        value="<?= old('kode_akun', $akun['kode_akun']) ?>" required>
                    <small class="text-muted">Format: 1-1000 (Aktiva), 2-1000 (Pasiva), 3-1000 (Modal), 4-1000
                        (Pendapatan), 5-1000 (Beban)</small>
                </div>
                <div class="mb-3">
                    <label for="nama_akun" class="form-label">Nama Akun</label>
                    <input type="text" class="form-control" id="nama_akun" name="nama_akun"
                        value="<?= old('nama_akun', $akun['nama_akun']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Aktiva" <?= old('kategori', $akun['kategori']) == 'Aktiva' ? 'selected' : '' ?>>
                            Aktiva</option>
                        <option value="Pasiva" <?= old('kategori', $akun['kategori']) == 'Pasiva' ? 'selected' : '' ?>>
                            Pasiva</option>
                        <option value="Modal" <?= old('kategori', $akun['kategori']) == 'Modal' ? 'selected' : '' ?>>Modal
                        </option>
                        <option value="Pendapatan" <?= old('kategori', $akun['kategori']) == 'Pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                        <option value="Beban" <?= old('kategori', $akun['kategori']) == 'Beban' ? 'selected' : '' ?>>Beban
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <select class="form-select" id="jenis" name="jenis" required>
                        <option value="">Pilih Jenis</option>
                        <option value="Debit" <?= old('jenis', $akun['jenis']) == 'Debit' ? 'selected' : '' ?>>Debit
                        </option>
                        <option value="Kredit" <?= old('jenis', $akun['jenis']) == 'Kredit' ? 'selected' : '' ?>>Kredit
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="saldo_awal" class="form-label">Saldo Awal</label>
                    <input type="number" step="0.01" class="form-control" id="saldo_awal" name="saldo_awal"
                        value="<?= old('saldo_awal', $akun['saldo_awal']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
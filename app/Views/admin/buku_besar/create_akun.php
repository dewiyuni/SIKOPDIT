<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Tambah Akun Baru</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Tambah Akun</h5>
            <?php
            // Ambil URL sebelumnya, atau default ke halaman daftar akun
            $previousUrl = previous_url() ?? base_url('admin/buku_besar/akun');
            // Jangan arahkan kembali ke halaman create itu sendiri
            if (strpos($previousUrl, 'akun/create') !== false) {
                 $previousUrl = base_url('admin/buku_besar/akun');
            }
            ?>
            <a href="<?= $previousUrl ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading">Error Validasi:</h6>
                    <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->has('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
             <?php if (session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/buku_besar/akun/store') ?>" method="post">
                 <?= csrf_field() ?> <!-- Jangan lupa tambahkan CSRF field -->

                <div class="mb-3 row">
                    <label for="kode_akun" class="col-sm-3 col-form-label">Kode Akun <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control <?= (isset(session('errors')['kode_akun'])) ? 'is-invalid' : '' ?>"
                               id="kode_akun" name="kode_akun" value="<?= old('kode_akun') ?>" required
                               placeholder="Contoh: PEM001, BIA001, AKM001">
                        <?php if (isset(session('errors')['kode_akun'])): ?>
                            <div class="invalid-feedback"><?= session('errors')['kode_akun'] ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Harus unik. Contoh: PEMxxx (Pemasukan), PNGxxx (Pengeluaran/Aset), BIAxxx (Biaya), dll.</small>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="nama_akun" class="col-sm-3 col-form-label">Nama Akun <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control <?= (isset(session('errors')['nama_akun'])) ? 'is-invalid' : '' ?>"
                               id="nama_akun" name="nama_akun" value="<?= old('nama_akun') ?>" required>
                         <?php if (isset(session('errors')['nama_akun'])): ?>
                            <div class="invalid-feedback"><?= session('errors')['nama_akun'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="kategori" class="col-sm-3 col-form-label">Kategori <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select class="form-select <?= (isset(session('errors')['kategori'])) ? 'is-invalid' : '' ?>"
                                id="kategori" name="kategori" required>
                            <option value="" disabled <?= old('kategori') == '' ? 'selected' : '' ?>>-- Pilih Kategori --</option>
                            <option value="PEMASUKAN" <?= old('kategori') == 'PEMASUKAN' ? 'selected' : '' ?>>PEMASUKAN</option>
                            <option value="AKUMULASI PENYUSUTAN" <?= old('kategori') == 'AKUMULASI PENYUSUTAN' ? 'selected' : '' ?>>AKUMULASI PENYUSUTAN</option>
                            <option value="PENYISIHAN PENYISIHAN" <?= old('kategori') == 'PENYISIHAN PENYISIHAN' ? 'selected' : '' ?>>PENYISIHAN PENYISIHAN (Dana/Cadangan)</option>
                            <option value="PENGELUARAN" <?= old('kategori') == 'PENGELUARAN' ? 'selected' : '' ?>>PENGELUARAN (Aset/Aktivitas)</option>
                            <option value="BIAYA BIAYA" <?= old('kategori') == 'BIAYA BIAYA' ? 'selected' : '' ?>>BIAYA BIAYA</option>
                            <option value="PENYISIHAN BEBAN DANA" <?= old('kategori') == 'PENYISIHAN BEBAN DANA' ? 'selected' : '' ?>>PENYISIHAN BEBAN DANA</option>
                            <option value="PENYUSUTAN PENYUSUTAN" <?= old('kategori') == 'PENYUSUTAN PENYUSUTAN' ? 'selected' : '' ?>>PENYUSUTAN PENYUSUTAN (Beban)</option>
                            <option value="BIAYA PAJAK" <?= old('kategori') == 'BIAYA PAJAK' ? 'selected' : '' ?>>BIAYA PAJAK</option>
                             <!-- Tambahkan kategori lain jika ada -->
                             <!-- <option value="EKUITAS" <?= old('kategori') == 'EKUITAS' ? 'selected' : '' ?>>EKUITAS (Modal)</option> -->
                             <!-- <option value="ASET_LANCAR" <?= old('kategori') == 'ASET_LANCAR' ? 'selected' : '' ?>>ASET LANCAR</option> -->
                             <!-- <option value="LIABILITAS_PENDEK" <?= old('kategori') == 'LIABILITAS_PENDEK' ? 'selected' : '' ?>>LIABILITAS JANGKA PENDEK</option> -->
                             <!-- dst... -->
                        </select>
                         <?php if (isset(session('errors')['kategori'])): ?>
                            <div class="invalid-feedback"><?= session('errors')['kategori'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="jenis" class="col-sm-3 col-form-label">Saldo Normal <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select class="form-select <?= (isset(session('errors')['jenis'])) ? 'is-invalid' : '' ?>"
                                id="jenis" name="jenis" required>
                            <option value="" disabled <?= old('jenis') == '' ? 'selected' : '' ?>>-- Pilih Saldo Normal --</option>
                            <option value="Debit" <?= old('jenis') == 'Debit' ? 'selected' : '' ?>>Debit (Aset, Biaya, Pengeluaran Kas)</option>
                            <option value="Kredit" <?= old('jenis') == 'Kredit' ? 'selected' : '' ?>>Kredit (Liabilitas, Ekuitas, Pendapatan, Akum. Peny.)</option>
                        </select>
                         <?php if (isset(session('errors')['jenis'])): ?>
                            <div class="invalid-feedback"><?= session('errors')['jenis'] ?></div>
                        <?php endif; ?>
                         <small class="text-muted">Menentukan penambahan (+) atau pengurangan (-) saldo.</small>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="saldo_awal" class="col-sm-3 col-form-label">Saldo Awal</label>
                     <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control text-end <?= (isset(session('errors')['saldo_awal'])) ? 'is-invalid' : '' ?>"
                               id="saldo_awal" name="saldo_awal" value="<?= old('saldo_awal', '0.00') ?>" required>
                        <?php if (isset(session('errors')['saldo_awal'])): ?>
                            <div class="invalid-feedback"><?= session('errors')['saldo_awal'] ?></div>
                        <?php endif; ?>
                         <small class="text-muted">Masukkan saldo awal jika ada (misal saat migrasi data).</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Akun</button>
                        <a href="<?= $previousUrl ?>" class="btn btn-light">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    // Optional: Script untuk menyarankan jenis berdasarkan kategori
    document.getElementById('kategori').addEventListener('change', function() {
        const kategori = this.value;
        const jenisSelect = document.getElementById('jenis');
        const jenisOptions = jenisSelect.options;

        // Logika sederhana (bisa disesuaikan)
        let suggestedJenis = '';
        if (['PEMASUKAN', 'AKUMULASI PENYUSUTAN', 'PENYISIHAN PENYISIHAN'].includes(kategori)) {
            suggestedJenis = 'Kredit';
        } else if (['PENGELUARAN', 'BIAYA BIAYA', 'PENYISIHAN BEBAN DANA', 'PENYUSUTAN PENYUSUTAN', 'BIAYA PAJAK'].includes(kategori)) {
            suggestedJenis = 'Debit';
        }

        // Pilih jenis yang disarankan jika belum ada yg dipilih sebelumnya
        if (suggestedJenis && jenisSelect.value === '') {
             for (let i = 0; i < jenisOptions.length; i++) {
                if (jenisOptions[i].value === suggestedJenis) {
                    jenisOptions[i].selected = true;
                    break;
                }
            }
        } else if (!suggestedJenis && jenisSelect.value === '') {
            // Jika tidak ada saran dan belum dipilih, reset ke default
             jenisOptions[0].selected = true;
        }
    });
</script>
<?= $this->endSection(); ?>
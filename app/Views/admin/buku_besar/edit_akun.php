<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Edit Akun: <?= esc($akun['nama_akun']) ?></h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Edit Akun</h5>
            <?php
            // Ambil URL sebelumnya, atau default ke halaman daftar akun
            $previousUrl = previous_url() ?? base_url('admin/buku_besar/akun'); // Adjust default URL if needed
            // Jangan arahkan kembali ke halaman edit itu sendiri atau create
            if (strpos($previousUrl, 'akun/edit') !== false || strpos($previousUrl, 'akun/create') !== false) {
                 $previousUrl = base_url('admin/buku_besar/akun'); // Adjust default URL if needed
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

            <form action="<?= base_url('admin/buku_besar/akun/update/' . $akun['id']) ?>" method="post">
                 <?= csrf_field() ?> <!-- Jangan lupa tambahkan CSRF field -->
                 <input type="hidden" name="_method" value="POST"> <!-- Or PUT/PATCH if your route requires it -->

                <div class="mb-3 row">
                    <label for="kode_akun" class="col-sm-3 col-form-label">Kode Akun <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control <?= (isset(session('errors')['kode_akun'])) ? 'is-invalid' : '' ?>"
                               id="kode_akun" name="kode_akun" value="<?= old('kode_akun', $akun['kode_akun']) ?>" required
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
                               id="nama_akun" name="nama_akun" value="<?= old('nama_akun', $akun['nama_akun']) ?>" required>
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
                            <option value="" disabled>-- Pilih Kategori --</option>
                            <option value="PEMASUKAN" <?= old('kategori', $akun['kategori']) == 'PEMASUKAN' ? 'selected' : '' ?>>PEMASUKAN</option>
                            <option value="AKUMULASI PENYUSUTAN" <?= old('kategori', $akun['kategori']) == 'AKUMULASI PENYUSUTAN' ? 'selected' : '' ?>>AKUMULASI PENYUSUTAN</option>
                            <option value="PENYISIHAN PENYISIHAN" <?= old('kategori', $akun['kategori']) == 'PENYISIHAN PENYISIHAN' ? 'selected' : '' ?>>PENYISIHAN PENYISIHAN (Dana/Cadangan)</option>
                            <option value="PENGELUARAN" <?= old('kategori', $akun['kategori']) == 'PENGELUARAN' ? 'selected' : '' ?>>PENGELUARAN (Aset/Aktivitas)</option>
                            <option value="BIAYA BIAYA" <?= old('kategori', $akun['kategori']) == 'BIAYA BIAYA' ? 'selected' : '' ?>>BIAYA BIAYA</option>
                            <option value="PENYISIHAN BEBAN DANA" <?= old('kategori', $akun['kategori']) == 'PENYISIHAN BEBAN DANA' ? 'selected' : '' ?>>PENYISIHAN BEBAN DANA</option>
                            <option value="PENYUSUTAN PENYUSUTAN" <?= old('kategori', $akun['kategori']) == 'PENYUSUTAN PENYUSUTAN' ? 'selected' : '' ?>>PENYUSUTAN PENYUSUTAN (Beban)</option>
                            <option value="BIAYA PAJAK" <?= old('kategori', $akun['kategori']) == 'BIAYA PAJAK' ? 'selected' : '' ?>>BIAYA PAJAK</option>
                            <!-- Pastikan semua kategori yang mungkin ada di $akun['kategori'] tercantum di sini -->
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
                            <option value="" disabled>-- Pilih Saldo Normal --</option>
                            <option value="Debit" <?= old('jenis', $akun['jenis']) == 'Debit' ? 'selected' : '' ?>>Debit (Aset, Biaya, Pengeluaran Kas)</option>
                            <option value="Kredit" <?= old('jenis', $akun['jenis']) == 'Kredit' ? 'selected' : '' ?>>Kredit (Liabilitas, Ekuitas, Pendapatan, Akum. Peny.)</option>
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
                               id="saldo_awal" name="saldo_awal" value="<?= old('saldo_awal', $akun['saldo_awal']) ?>" required>
                        <?php if (isset(session('errors')['saldo_awal'])): ?>
                            <div class="invalid-feedback"><?= session('errors')['saldo_awal'] ?></div>
                        <?php endif; ?>
                         <small class="text-muted">Saldo awal akun ini (biasanya tidak diubah setelah transaksi berjalan).</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Akun</button>
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
    // Optional: Script untuk menyarankan jenis berdasarkan kategori (sama seperti di view Tambah)
    // Script ini hanya akan mengubah pilihan jika Saldo Normal belum dipilih (value === '')
    document.getElementById('kategori').addEventListener('change', function() {
        const kategori = this.value;
        const jenisSelect = document.getElementById('jenis');
        const jenisOptions = jenisSelect.options;

        // Logika sederhana (sama seperti di form tambah)
        let suggestedJenis = '';
        if (['PEMASUKAN', 'AKUMULASI PENYUSUTAN', 'PENYISIHAN PENYISIHAN'].includes(kategori)) {
            suggestedJenis = 'Kredit';
        } else if (['PENGELUARAN', 'BIAYA BIAYA', 'PENYISIHAN BEBAN DANA', 'PENYUSUTAN PENYUSUTAN', 'BIAYA PAJAK'].includes(kategori)) {
            suggestedJenis = 'Debit';
        }

        // Hanya ubah jika Saldo Normal saat ini kosong/belum dipilih
        if (suggestedJenis && jenisSelect.value === '') {
             for (let i = 0; i < jenisOptions.length; i++) {
                if (jenisOptions[i].value === suggestedJenis) {
                    jenisOptions[i].selected = true;
                    break;
                }
            }
        }
        // Tidak perlu else untuk mereset, karena di form edit sudah ada value awal
    });
</script>
<?= $this->endSection(); ?>
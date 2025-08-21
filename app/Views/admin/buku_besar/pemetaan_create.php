<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />

<div class="container-fluid px-4">
    <h3 class="mt-4"><?= $title ?? 'Tambah Aturan Pemetaan' ?></h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/buku_besar/pemetaan') ?>">Pemetaan Akun</a></li>
        <li class="breadcrumb-item active">Tambah Aturan</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i> Form Tambah Aturan Pemetaan
        </div>
        <div class="card-body">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->get('errors')): ?>
                <div class="alert alert-danger">
                    <strong>Gagal menyimpan data:</strong>
                    <ul>
                        <?php foreach (session()->get('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>


            <form action="<?= base_url('admin/buku_besar/pemetaan/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3 row">
                    <label for="pola_uraian" class="col-sm-3 col-form-label">Pola Uraian Jurnal <span
                            class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text"
                            class="form-control <?= (isset(session('errors')['pola_uraian'])) ? 'is-invalid' : '' ?>"
                            id="pola_uraian" name="pola_uraian" value="<?= old('pola_uraian') ?>" required>
                        <div class="invalid-feedback">
                            <?= session('errors')['pola_uraian'] ?? '' ?>
                        </div>
                        <div class="form-text">Contoh: <code>Bayar Gaji Karyawan%</code> atau <code>Setor Tunai</code>
                            (Gunakan <code>%</code> sebagai wildcard di akhir jika perlu).</div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="kategori_jurnal" class="col-sm-3 col-form-label">Kategori Jurnal <span
                            class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select
                            class="form-select <?= (isset(session('errors')['kategori_jurnal'])) ? 'is-invalid' : '' ?>"
                            id="kategori_jurnal" name="kategori_jurnal" required>
                            <option value="" <?= (old('kategori_jurnal') == '') ? 'selected' : '' ?>>-- Pilih Kategori --
                            </option>
                            <option value="DUM" <?= (old('kategori_jurnal') == 'DUM') ? 'selected' : '' ?>>DUM (Debet Uang
                                Masuk)</option>
                            <option value="DUK" <?= (old('kategori_jurnal') == 'DUK') ? 'selected' : '' ?>>DUK (Debet Uang
                                Keluar)</option>
                        </select>
                        <div class="invalid-feedback">
                            <?= session('errors')['kategori_jurnal'] ?? '' ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="id_akun_debit" class="col-sm-3 col-form-label">Akun Debit <span
                            class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select
                            class="form-select select2 <?= (isset(session('errors')['id_akun_debit'])) ? 'is-invalid' : '' ?>"
                            id="id_akun_debit" name="id_akun_debit" required>
                            <option value="">-- Pilih Akun Debit --</option>
                            <?php foreach ($akun_list as $akun): ?>
                                <option value="<?= $akun['id'] ?>" <?= (old('id_akun_debit') == $akun['id']) ? 'selected' : '' ?>>
                                    <?= esc($akun['kode_akun']) ?> - <?= esc($akun['nama_akun']) ?>
                                    (<?= esc($akun['jenis']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <?= session('errors')['id_akun_debit'] ?? '' ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="id_akun_kredit" class="col-sm-3 col-form-label">Akun Kredit <span
                            class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select
                            class="form-select select2 <?= (isset(session('errors')['id_akun_kredit'])) ? 'is-invalid' : '' ?>"
                            id="id_akun_kredit" name="id_akun_kredit" required>
                            <option value="">-- Pilih Akun Kredit --</option>
                            <?php foreach ($akun_list as $akun): ?>
                                <option value="<?= $akun['id'] ?>" <?= (old('id_akun_kredit') == $akun['id']) ? 'selected' : '' ?>>
                                    <?= esc($akun['kode_akun']) ?> - <?= esc($akun['nama_akun']) ?>
                                    (<?= esc($akun['jenis']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <?= session('errors')['id_akun_kredit'] ?? '' ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="prioritas" class="col-sm-3 col-form-label">Prioritas</label>
                    <div class="col-sm-9">
                        <input type="number"
                            class="form-control <?= (isset(session('errors')['prioritas'])) ? 'is-invalid' : '' ?>"
                            id="prioritas" name="prioritas" value="<?= old('prioritas', 0) ?>">
                        <div class="invalid-feedback">
                            <?= session('errors')['prioritas'] ?? '' ?>
                        </div>
                        <div class="form-text">Angka lebih tinggi diproses lebih dulu (Default: 0). Gunakan angka
                            negatif untuk aturan default.</div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi (Opsional)</label>
                    <div class="col-sm-9">
                        <textarea
                            class="form-control <?= (isset(session('errors')['deskripsi'])) ? 'is-invalid' : '' ?>"
                            id="deskripsi" name="deskripsi" rows="3"><?= old('deskripsi') ?></textarea>
                        <div class="invalid-feedback">
                            <?= session('errors')['deskripsi'] ?? '' ?>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">Simpan Aturan</button>
                        <a href="<?= base_url('admin/buku_besar/pemetaan') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Tambahkan JS untuk Select2 jika belum ada di layout utama -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: "bootstrap-5" // Sesuaikan dengan versi Bootstrap Anda
        });
    });
</script>
<?= $this->endSection(); ?>
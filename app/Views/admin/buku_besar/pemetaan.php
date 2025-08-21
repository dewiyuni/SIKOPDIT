<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />

<div class="container-fluid px-4">
    <h3 class="mt-4 mb-4">Pemetaan Akun</h3>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Form Pemetaan Akun -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Form Pemetaan Akun</h5>
                </div>
                <div class="card-body">
                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('admin/buku_besar/pemetaan/store') ?>" method="post">
                        <div class="mb-3">
                            <label for="kategori_jurnal" class="form-label">Kategori Jurnal</label>
                            <select class="form-select" id="kategori_jurnal" name="kategori_jurnal" required>
                                <option value="">Pilih Kategori</option>
                                <option value="DUM">DUM (Debit Uang Masuk)</option>
                                <option value="DUK">DUK (Debit Uang Keluar)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="uraian_jurnal" class="form-label">Uraian Jurnal</label>
                            <input type="text" class="form-control" id="uraian_jurnal" name="uraian_jurnal" required>
                            <small class="text-muted">Masukkan uraian persis seperti di jurnal kas</small>
                        </div>
                        <div class="mb-3">
                            <label for="id_akun_debit" class="form-label">Akun Debit</label>
                            <select class="form-select" id="id_akun_debit" name="id_akun_debit" required>
                                <option value="">Pilih Akun Debit</option>
                                <?php foreach ($akun as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= $a['kode_akun'] ?> - <?= $a['nama_akun'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_akun_kredit" class="form-label">Akun Kredit</label>
                            <select class="form-select" id="id_akun_kredit" name="id_akun_kredit" required>
                                <option value="">Pilih Akun Kredit</option>
                                <?php foreach ($akun as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= $a['kode_akun'] ?> - <?= $a['nama_akun'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Simpan Pemetaan</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Pemetaan -->
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
                    <h5 class="mb-0">Daftar Pemetaan</h5>
                    <div>
                        <a href="<?= base_url('admin/buku_besar/pemetaan/otomatis') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-magic"></i> Buat Otomatis
                        </a>
                        <a href="<?= base_url('admin/buku_besar') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kategori</th>
                                    <th>Uraian</th>
                                    <th>Akun Debit</th>
                                    <th>Akun Kredit</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($pemetaan) > 0): ?>
                                    <?php foreach ($pemetaan as $p): ?>
                                        <tr>
                                            <td><?= $p['kategori_jurnal'] ?></td>
                                            <td><?= $p['uraian_jurnal'] ?></td>
                                            <td><?= $p['kode_akun_debit'] ?> - <?= $p['nama_akun_debit'] ?></td>
                                            <td><?= $p['kode_akun_kredit'] ?> - <?= $p['nama_akun_kredit'] ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('admin/buku_besar/pemetaan/delete/' . $p['id']) ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus pemetaan ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada pemetaan akun</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#id_akun_debit, #id_akun_kredit').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Pilih akun",
            allowClear: true
        });
    });
</script>

<?= $this->endSection(); ?>
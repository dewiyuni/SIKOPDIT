<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4"><?= $title ?? 'Pemetaan Jurnal ke Akun' ?></h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Pemetaan Akun</li>
    </ol>

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

    <div class="card mb-4 table-responsive">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pemetaan</h5>
            <div>
                <a href="<?= base_url('admin/buku_besar') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="<?= base_url('admin/buku_besar/pemetaan/generate-otomatis') ?>" class="btn btn-success btn-sm"
                    onclick="return confirm('Apakah Anda yakin ingin membuat pemetaan otomatis? Ini akan mencoba menambahkan aturan baru berdasarkan pencocokan nama Jurnal Kas dengan Nama Akun. Aturan yang sudah ada tidak akan ditimpa. Proses ini mungkin memerlukan waktu.');">
                    <i class="fas fa-magic me-1"></i> Buat Otomatis
                </a>
                <a href="<?= base_url('admin/buku_besar/pemetaan/create') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Aturan Manual
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Prioritas</th>
                            <th>Pola Uraian Jurnal</th>
                            <th>Kategori Jurnal</th>
                            <th>Akun Debit</th>
                            <th>Akun Kredit</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pemetaan)): ?>
                            <?php foreach ($pemetaan as $item): ?>
                                <tr>
                                    <td><?= esc($item['prioritas']) ?></td>
                                    <td><?= esc($item['pola_uraian']) ?></td>
                                    <td>
                                        <?php if ($item['kategori_jurnal'] == 'DUM'): ?>
                                            <span class="badge bg-success">DUM</span>
                                        <?php elseif ($item['kategori_jurnal'] == 'DUK'): ?>
                                            <span class="badge bg-danger">DUK</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= esc($item['kategori_jurnal']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($item['kode_akun_debit'] ?? '') ?> -
                                        <?= esc($item['nama_akun_debit'] ?? 'N/A') ?>
                                    </td>
                                    <td><?= esc($item['kode_akun_kredit'] ?? '') ?> -
                                        <?= esc($item['nama_akun_kredit'] ?? 'N/A') ?>
                                    </td>
                                    <td><?= esc($item['deskripsi']) ?></td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="<?= base_url('admin/buku_besar/pemetaan/edit/' . $item['id']) ?>"
                                                class="btn btn-warning btn-sm me-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?= base_url('admin/buku_besar/pemetaan/delete/' . $item['id']) ?>"
                                                method="post" class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan pemetaan ini?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <!-- Opsional, bisa pakai route delete -->
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada aturan pemetaan. Silakan tambahkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 small text-muted">
                <strong>Penting:</strong> Isi tabel ini dengan cermat. Gunakan <code>%</code> sebagai wildcard di 'Pola
                Uraian' (misal: <code>Bayar Gaji%</code>). Aturan dengan 'Prioritas' lebih tinggi akan diproses terlebih
                dahulu jika ada pola yang tumpang tindih. Pastikan semua kemungkinan uraian di Jurnal Kas memiliki
                aturan pemetaan.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Tambahkan JS untuk DataTables jika belum ada di layout utama -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script>
    window.addEventListener('DOMContentLoaded', event => {
        const datatablesSimple = document.getElementById('datatablesSimple');
        if (datatablesSimple) {
            new simpleDatatables.DataTable(datatablesSimple);
        }
    });
</script>
<?= $this->endSection(); ?>
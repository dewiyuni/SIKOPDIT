<?php
/**
 * View untuk halaman manajemen anggota.
 *
 * Halaman ini menampilkan daftar anggota, menyediakan fungsionalitas untuk
 * menambah anggota baru, mengimpor anggota dari file Excel, mengedit,
 * dan menghapus data anggota. Menggunakan DataTables untuk tabel.
 *
 * @var array $anggota Data semua anggota yang akan ditampilkan.
 */
?>
<?= $this->extend('layouts/main'); // Menggunakan layout utama 'main.php' ?>

<?= $this->section('styles'); // Section khusus untuk CSS tambahan ?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* Optional: Sesuaikan padding atau style DataTables jika perlu */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em;
    }

    .dataTables_length select {
        padding-right: 20px;
        /* Agar ada ruang untuk panah dropdown */
    }

    .alert ul {
        margin-bottom: 0;
        /* Menghilangkan margin bawah default dari <ul> di dalam alert */
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); // Memulai section 'content' yang akan diisi ke layout utama ?>

<div class="container-fluid mt-4">
    <?php $session = session(); // Inisialisasi session sekali di atas ?>

    <!-- Card untuk fungsionalitas Impor Data Anggota dari Excel -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-excel me-2"></i>Impor Data Anggota dari Excel</h5>
        </div>
        <div class="card-body">
            <!-- START: Notifikasi Impor (menggunakan key success_import, error, dan error_html) -->
            <?php if ($session->getFlashdata('success_import')): // Khusus untuk sukses impor, tetap gunakan key berbeda ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $session->getFlashdata('success_import') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php
            // Menangkap 'error' (bisa dari impor atau CRUD biasa)
            // Jika Anda ingin pesan 'error' dari impor tampil di sini, biarkan.
            // Jika tidak, Anda harus membedakan key di controller.
            $generalError = $session->getFlashdata('error');
            if ($generalError):
                ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc($generalError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php
            // Menangkap 'error_html' (bisa dari impor)
            // Jika Anda ingin pesan 'error_html' dari impor tampil di sini, biarkan.
            $htmlError = $session->getFlashdata('error_html');
            if ($htmlError):
                ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $htmlError // Ini HTML, jadi JANGAN di-escape ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <!-- END: Notifikasi Impor -->

            <form action="<?= site_url('admin/anggota/import-excel') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-5 col-lg-4">
                        <label for="file_excel_anggota" class="form-label">Pilih File Excel (.xlsx, .xls)</label>
                        <input type="file" class="form-control form-control-sm" name="file_excel_anggota"
                            id="file_excel_anggota" accept=".xls,.xlsx" required>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload me-1"></i> Unggah dan Impor
                        </button>
                    </div>
                    <div class="col-md-auto ms-md-auto">
                        <a href="<?= base_url('template/template_anggota.xlsx') ?>" class="btn btn-info btn-sm"
                            download>
                            <i class="fas fa-download me-1"></i> Download Template
                        </a>
                    </div>
                </div>
                <small class="form-text text-muted mt-2 d-block">
                    Pastikan format file Excel sesuai dengan template yang disediakan.
                </small>
            </form>
        </div>
    </div>

    <!-- START: Notifikasi Umum (CRUD, Validasi Form, dll.) -->
    <?php if ($session->getFlashdata('success')): // Untuk sukses CRUD biasa ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc($session->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    // Jika 'error' dari impor sudah ditampilkan di atas,
    // Anda mungkin tidak ingin menampilkannya lagi di sini.
    // Atau, jika Anda ingin semua 'error' (impor & CRUD) tampil di sini,
    // maka Anda bisa menghapus blok 'error' dari dalam Card Impor.
    // Untuk sekarang, saya biarkan duplikasi ini agar Anda bisa memilih.
    // $generalError sudah didefinisikan di atas.
    /*
    if ($generalError): // Ini akan menampilkan 'error' lagi jika ada
    ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc($generalError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif;
    */
    ?>


    <?php
    // Notifikasi untuk error validasi bawaan CodeIgniter dari with('errors', $validation->getErrors())
    $validationErrors = $session->getFlashdata('errors') ?? ($errors ?? []);
    if (!empty($validationErrors) && is_array($validationErrors)):
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul>
                <?php foreach ($validationErrors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <!-- END: Notifikasi Umum -->


    <!-- Card untuk menampilkan Daftar Anggota -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Anggota</h5>
            <a href="<?= site_url('admin/tambah_anggota') ?>" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah Anggota
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelAnggota" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>No BA</th>
                            <th>Dusun</th>
                            <th>Alamat</th>
                            <th>Pekerjaan</th>
                            <th>Tgl Lahir</th>
                            <th>Nama Pasangan</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anggota)): ?>
                            <?php // DataTables akan menampilkan "No data available in table" jika kosong ?>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($anggota as $row): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= esc($row->nama); ?></td>
                                    <td><?= esc($row->nik); ?></td>
                                    <td><?= esc($row->no_ba); ?></td>
                                    <td><?= esc($row->dusun); ?></td>
                                    <td><?= esc($row->alamat); ?></td>
                                    <td><?= esc($row->pekerjaan); ?></td>
                                    <td>
                                        <?php
                                        if (!empty($row->tgl_lahir) && $row->tgl_lahir != '0000-00-00' && $row->tgl_lahir != null) {
                                            try {
                                                echo (new DateTime($row->tgl_lahir))->format('d-m-Y');
                                            } catch (Exception $e) {
                                                echo '-';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?= esc($row->nama_pasangan); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'secondary';
                                        $statusText = ucfirst(esc($row->status));
                                        if ($row->status == 'aktif') {
                                            $statusClass = 'success';
                                        } elseif (in_array($row->status, ['tidak aktif', 'nonaktif', 'keluar'])) {
                                            $statusClass = 'danger';
                                        } elseif ($row->status == 'menunggu verifikasi' || $row->status == 'pending') {
                                            $statusClass = 'warning';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $statusClass; ?>">
                                            <?= $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('admin/edit_anggota/' . $row->id_anggota) ?>"
                                            class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= site_url('admin/hapus_anggota/' . $row->id_anggota) ?>" method="post"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus anggota <?= esc($row->nama, 'js') ?>?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); // Section khusus untuk JavaScript tambahan ?>
<!-- jQuery (diperlukan oleh DataTables Bootstrap 5) -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tabelAnggota').DataTable({
            "language": { // Opsional: Terjemahan ke Bahasa Indonesia
                "lengthMenu": "Tampilkan _MENU_ entri per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total entri)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            },
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]], // Opsi jumlah data per halaman
            "pageLength": 10, // Default jumlah data per halaman
            "columnDefs": [
                { "orderable": false, "targets": [0, 10] } // Menonaktifkan pengurutan untuk kolom No dan Aksi
            ]
        });
    });
</script>
<?= $this->endSection(); ?>
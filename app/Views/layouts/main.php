<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIKOPDIT</title>

    <!-- Bootstrap CSS via CDN -->
    <link href="<?= base_url('dist/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('dist/font/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('dist/css/all.min.css') ?>" rel="stylesheet">

    <!-- SB Admin 2 CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/sb-admin-2.min.css') ?>">

    <!-- CSS KUSTOM DARI SEBELUMNYA -->
    <style>
        /* Targetkan span di dalam link utama sidebar */
        ul.navbar-nav.sidebar .nav-item .nav-link span {
            font-size: 1rem !important;
            font-weight: bold !important;
        }

        ul.navbar-nav.sidebar .nav-item .dropdown-menu .dropdown-item,
        ul.navbar-nav.sidebar .nav-item .dropdown-menu .dropdown-item span {
            font-size: 0.95rem !important;
            font-weight: normal !important;
        }
    </style>
    <!-- AKHIR CSS KUSTOM -->

    <?= $this->renderSection('styles') ?>
</head>


<body id="page-top">
    <?= $this->include('layouts/navbar'); ?>
    <?= $this->renderSection('content'); ?>

    <!-- JAVASCRIPT LIBRARIES -->
    <!-- SB Admin biasanya sudah menyertakan jQuery, jadi CDN jQuery bisa jadi tidak perlu jika vendor SB Admin di-load -->
    <!-- Jika Anda yakin SB Admin sudah punya jQuery, Anda bisa menghapus baris jQuery CDN di bawah -->
    <script src="<?= base_url('dist/js/jquery-3.6.0.min.js') ?>"></script>

    <!-- Bootstrap JS via CDN (jika SB Admin tidak menyertakan versi yang sama) -->
    <script src="<?= base_url('dist/js/bootstrap.bundle.min.js') ?>"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Bootstrap core JavaScript dari SB Admin (biasanya termasuk jQuery & Bootstrap bundle) -->
    <!-- Jika baris di atas sudah ada, ini mungkin duplikasi. Pilih salah satu set jQuery & Bootstrap. -->
    <!-- Jika vendor/jquery/jquery.min.js adalah versi yang Anda inginkan, aktifkan ini dan hapus CDN jQuery di atas. -->
    <!-- <script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script> -->
    <!-- <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script> -->

    <!-- SB Admin 2 JS -->
    <script src="<?= base_url('dist/js/sb-admin-2.min.js') ?>"></script>

    <!-- Chart.js Library (INI YANG PENTING DAN HILANG) -->
    <script src="<?= base_url('dist/js/chart.js') ?>"></script>

    <!-- DataTables & Buttons (jika masih digunakan di halaman lain) -->
    <script src="<?= base_url('dist/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('dist/js/dataTables.buttons.min.js') ?>"></script>
    <script src="<?= base_url('dist/js/jszip.min.js') ?>"></script>
    <script src="<?= base_url('dist/js/pdfmake.min.js') ?>"></script>
    <script src="<?= base_url('dist/js/vfs_fonts.js') ?>"></script>
    <script src="<?= base_url('dist/js/buttons.html5.min.js') ?>"></script>

    <?= $this->renderSection('scripts') // Skrip kustom halaman, termasuk inisialisasi chart, HARUS SETELAH Chart.js ?>
</body>

</html>
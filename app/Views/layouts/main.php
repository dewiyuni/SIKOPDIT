<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIKOPDIT</title>

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS via CDN (jika SB Admin tidak menyertakan versi yang sama) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap core JavaScript dari SB Admin (biasanya termasuk jQuery & Bootstrap bundle) -->
    <!-- Jika baris di atas sudah ada, ini mungkin duplikasi. Pilih salah satu set jQuery & Bootstrap. -->
    <!-- Jika vendor/jquery/jquery.min.js adalah versi yang Anda inginkan, aktifkan ini dan hapus CDN jQuery di atas. -->
    <!-- <script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script> -->
    <!-- <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script> -->

    <!-- SB Admin 2 JS -->
    <script src="<?= base_url('assets/js/sb-admin-2.min.js') ?>"></script>

    <!-- Chart.js Library (INI YANG PENTING DAN HILANG) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- DataTables & Buttons (jika masih digunakan di halaman lain) -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

    <?= $this->renderSection('scripts') // Skrip kustom halaman, termasuk inisialisasi chart, HARUS SETELAH Chart.js ?>
</body>

</html>
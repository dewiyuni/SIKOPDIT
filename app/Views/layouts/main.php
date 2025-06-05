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

    <!-- Tambahkan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

    <!-- CSS KUSTOM UNTUK MEMPERBESAR DAN MENEBALKAN FONT SIDEBAR -->
    <style>
        /* Targetkan span di dalam link utama sidebar */
        ul.navbar-nav.sidebar .nav-item .nav-link span {
            font-size: 1rem !important;
            /* Ukuran font, sesuaikan jika perlu */
            font-weight: bold !important;
            /* Tambahkan ini untuk menebalkan */
        }

        /* Targetkan teks langsung di dalam dropdown item (jika tidak ada span)
           dan juga span di dalam dropdown item (jika ada) */
        ul.navbar-nav.sidebar .nav-item .dropdown-menu .dropdown-item,
        ul.navbar-nav.sidebar .nav-item .dropdown-menu .dropdown-item span {
            font-size: 0.95rem !important;
            /* Ukuran font submenu, sesuaikan jika perlu */
            font-weight: normal !important;
            /* Atau 'bold' jika submenu juga ingin tebal. 'normal' agar tidak ikut tebal jika parentnya tebal */
            /* Jika ingin submenu juga tebal, ganti 'normal' menjadi 'bold' */
        }

        /* Jika ingin spesifik hanya span di dropdown item yang bold (dan ada span-nya) */
        /* ul.navbar-nav.sidebar .nav-item .dropdown-menu .dropdown-item span {
            font-weight: bold !important;
        } */
    </style>
    <!-- AKHIR CSS KUSTOM -->


    <?= $this->renderSection('styles') ?>
</head>


<body id="page-top">
    <?= $this->include('layouts/navbar'); ?>
    <?= $this->renderSection('content'); ?>
    <!-- Bootstrap JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap core JavaScript -->
    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- SB Admin 2 JS -->
    <script src="<?= base_url('assets/js/sb-admin-2.min.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>
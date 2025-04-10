<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/admin/dashboard">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="bi bi-emoji-wink"></i>
            </div>
            <div class="sidebar-brand-text mx-3">SIKOPDIT</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">
        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Menu untuk Admin -->
        <?php if (session()->get('role') == 'admin'): ?>
            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="/admin/dashboard">🏠 <span>Dashboard</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/jenis_simpanan">💰 <span>Jenis Simpanan</span></a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="kelolaUserDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    👥 <span>Kelola User</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="kelolaUserDropdown">
                    <li><a class="dropdown-item" href="/admin/anggota">👬 Kelola Anggota</a></li>
                    <li><a class="dropdown-item" href="/admin/kelola_pengguna">📋 Kelola Karyawan</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="jurnalKasDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    📒 <span>Jurnal Kas</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="jurnalKasDropdown">
                    <li><a class="dropdown-item" href="/admin/jurnal/jurnal_kas">📋 Semua Jurnal Kas</a></li>
                    <li><a class="dropdown-item" href="/admin/jurnal/monthly">📅 Jurnal Bulan</a></li>
                </ul>
            </li>
            <!-- Nav Item - Buku Besar -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="bukuBesarDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    📘 <span>Buku Besar</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="bukuBesarDropdown">
                    <li><a class="dropdown-item" href="/admin/buku_besar">📖 Lihat Buku Besar</a></li>
                    <li><a class="dropdown-item" href="/admin/buku_besar/akun">📖 Akun</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="neracaDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    📋 <span>Neraca</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="neracaDropdown">
                    <li><a class="dropdown-item" href="/admin/buku_besar/neraca-saldo">📊 Neraca Saldo</a></li>
                    <li><a class="dropdown-item" href="/admin/buku_besar/laba-rugi">📆 Laba Rugi</a></li>
                    <li><a class="dropdown-item" href="/admin/buku_besar/neraca">📂 Neraca</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Menu untuk Karyawan -->
        <?php if (session()->get('role') == 'karyawan'): ?>
            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="/karyawan/dashboard">🏠 <span>Dashboard</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/karyawan/transaksi_simpanan">💰 <span>Transaksi Simpanan</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/karyawan/transaksi_pinjaman">🏦 <span>Transaksi Pinjaman</span></a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link" href="/karyawan/laporan_transaksi">📄 <span>Laporan Transaksi</span></a>
            </li> -->
        <?php endif; ?>
    </ul>


    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fas fa-arrow-down"></i>
                </button>
                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">

                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?= session()->get('role') == 'admin' ? 'Admin' : 'Karyawan'; ?>
                            </span>
                            <img class="img-profile rounded-circle"
                                src="<?= base_url('assets/img/undraw_profile_3.svg'); ?>" alt="Profile Image">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">🔒 Keluar</a></li>
                        </ul>
                    </li>

                </ul>

            </nav>
            <!-- End of Topbar -->
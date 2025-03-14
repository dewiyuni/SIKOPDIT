<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard admin</h1>
            </div>

            <!-- Content Row -->
            <div class="row">

                <!-- Total Anggota Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Anggota</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAnggota; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Simpanan Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-container">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Simpanan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($totalSimpanan, 0, ',', '.') ?>
                                </div>
                            </div>
                            <div class="icon-container">
                                <i class="fas fa-wallet fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Pinjaman Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Total Pinjaman</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPinjaman; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Total Kas</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Grafik Simpanan dan Pinjaman -->
            <div class="row">
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Grafik Simpanan dan Pinjaman</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="chartSimpananPinjaman"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Grafik Kas Perbulan</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="chartSimpananPinjaman"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; Your Website 2025</span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

</div>
<?= $this->endSection(); ?>
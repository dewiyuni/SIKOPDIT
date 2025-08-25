<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Wrapper -->
<div id="wrapper" class="d-flex flex-column min-vh-100">

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="flex-fill">
        <!-- Main Content -->
        <div id="content">

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard Anggota</h1>
                </div>

                <div class="row">
                    <!-- Total Simpanan Card -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Saldo Simpanan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($totalSimpanan, 0, ',', '.'); ?>
                                </div>
                                <ul class="mt-2 mb-0 small">
                                    <li>Simpanan Pokok: Rp
                                        <?= number_format($simpanan['total_sp'] ?? 0, 0, ',', '.'); ?></li>
                                    <li>Simpanan Wajib: Rp
                                        <?= number_format($simpanan['total_sw'] ?? 0, 0, ',', '.'); ?></li>
                                    <li>Simpanan Wajib Pinjaman: Rp
                                        <?= number_format($simpanan['total_swp'] ?? 0, 0, ',', '.'); ?></li>
                                    <li>Simpanan Sukarela: Rp
                                        <?= number_format($simpanan['total_ss'] ?? 0, 0, ',', '.'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pinjaman Aktif Card -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Pinjaman Aktif
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($totalPinjaman, 0, ',', '.'); ?>
                                </div>
                                <div class="mt-3">
                                    <h6 class="text-gray-600">Pinjaman Terakhir</h6>
                                    <ul class="small">
                                        <?php if (!empty($pinjamanTerakhir)): ?>
                                            <?php foreach ($pinjamanTerakhir as $p): ?>
                                                <li>
                                                    <?= $p['tanggal_pinjaman'] ?> - Rp
                                                    <?= number_format($p['jumlah_pinjaman'], 0, ',', '.') ?>
                                                    <?php if (!empty($p['angsuran'])): ?>
                                                        <ul class="small ms-3">
                                                            <?php foreach ($p['angsuran'] as $a): ?>
                                                                <li><?= $a['tanggal_angsuran'] ?> - Rp
                                                                    <?= number_format($a['jumlah_angsuran'], 0, ',', '.') ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <br><small>Tidak ada angsuran</small>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>Tidak ada pinjaman terbaru</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Page Content -->

        </div>
    </div>
    <!-- End Content Wrapper -->

    <!-- Footer -->
    <footer class="sticky-footer bg-white mt-auto">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright © SIkopdit 2025</span>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

</div>
<!-- End Wrapper -->
<?= $this->endSection(); ?>
<link rel="stylesheet" href="<?= base_url('dist/font/bootstrap-icons.css') ?>">

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
                <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Total Anggota Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-container">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Anggota Aktif
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $totalAnggota; ?>
                                </div>
                            </div>
                            <div class="icon-container">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                    Total Saldo Simpanan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($totalSimpanan, 0, ',', '.') ?>
                                </div>
                            </div>
                            <div class="icon-container">
                                <i class="fas fa-wallet fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Pinjaman Aktif Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-container">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Pinjaman Aktif
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($totalPinjaman, 0, ',', '.') ?>
                                </div>
                            </div>
                            <div class="icon-container">
                                <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Kas Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-container">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Kas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($totalKas, 0, ',', '.') // Tampilkan total kas ?>
                                </div>
                            </div>
                            <div class="icon-container">
                                <i class="fas fa-cash-register fa-2x text-gray-300"></i>
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
                            <h6 class="m-0 font-weight-bold text-primary">Grafik Simpanan (Setoran) & Pinjaman
                                (Pencairan) Bulanan</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="chartSimpananPinjaman"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5"> <!-- PERBAIKAN: col-lg-5 -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Grafik Kas Per Bulan</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="chartKasBulanan"></canvas> <!-- PERBAIKAN: ID diubah -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- End of Main Content -->

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright © SIkopdit 2025</span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->
<?= $this->endSection(); ?>


<?= $this->section('scripts'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Grafik Simpanan dan Pinjaman
        var ctxSimpananPinjaman = document.getElementById("chartSimpananPinjaman");
        if (ctxSimpananPinjaman) {
            try {
                var chartSimpananPinjaman = new Chart(ctxSimpananPinjaman, {
                    type: 'line',
                    data: {
                        labels: JSON.parse('<?= $grafikSimpananPinjamanLabels; ?>'),
                        datasets: [{
                            label: "Total Setoran Simpanan",
                            lineTension: 0.3,
                            backgroundColor: "rgba(78, 115, 223, 0.05)",
                            borderColor: "rgba(78, 115, 223, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointBorderColor: "rgba(78, 115, 223, 1)",
                            pointHoverRadius: 4,
                            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: JSON.parse('<?= $grafikSimpananData; ?>'),
                        }, {
                            label: "Total Pencairan Pinjaman",
                            lineTension: 0.3,
                            backgroundColor: "rgba(28, 200, 138, 0.05)",
                            borderColor: "rgba(28, 200, 138, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(28, 200, 138, 1)",
                            pointBorderColor: "rgba(28, 200, 138, 1)",
                            pointHoverRadius: 4,
                            pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                            pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: JSON.parse('<?= $grafikPinjamanData; ?>'),
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: { left: 10, right: 25, top: 25, bottom: 0 }
                        },
                        scales: {
                            x: { // Diganti dari xAxes
                                // type: 'time', // Aktifkan jika label adalah objek Date JavaScript
                                // time: { unit: 'month' }, // Sesuaikan unit jika menggunakan type 'time'
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    maxTicksLimit: 7
                                }
                            },
                            y: { // Diganti dari yAxes
                                ticks: {
                                    maxTicksLimit: 5, padding: 10,
                                    callback: function (value, index, values) { return 'Rp ' + number_format(value); }
                                },
                                grid: {
                                    color: "rgb(234, 236, 244)",
                                    // zeroLineColor: "rgb(234, 236, 244)", // Tidak ada lagi
                                    drawBorder: false,
                                    borderDash: [2],
                                    // zeroLineBorderDash: [2] // Tidak ada lagi
                                }
                            }
                        },
                        plugins: { // Legend dan Tooltip sekarang di bawah 'plugins'
                            legend: {
                                display: true
                            },
                            tooltip: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyColor: "#858796", // bodyFontColor -> bodyColor
                                titleMarginBottom: 10,
                                titleColor: '#6e707e', // titleFontColor -> titleColor
                                titleFont: { size: 14 }, // titleFontSize -> titleFont.size
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                padding: { x: 15, y: 15 }, // xPadding dan yPadding menjadi objek padding
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                                callbacks: {
                                    label: function (tooltipItem) { // context menggantikan chart
                                        var datasetLabel = tooltipItem.dataset.label || '';
                                        return datasetLabel + ': Rp ' + number_format(tooltipItem.parsed.y); // yLabel -> parsed.y
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error("Error saat inisialisasi Chart SimpananPinjaman (DOMContentLoaded):", e);
            }
        }

        // Grafik Kas Bulanan
        var ctxKasBulanan = document.getElementById("chartKasBulanan");
        if (ctxKasBulanan) {
            try {
                var chartKasBulanan = new Chart(ctxKasBulanan, {
                    type: 'bar',
                    data: {
                        labels: JSON.parse('<?= $grafikKasLabels; ?>'),
                        datasets: [{
                            label: "Kas Masuk",
                            backgroundColor: "#4e73df",
                            hoverBackgroundColor: "#2e59d9",
                            borderColor: "#4e73df",
                            data: JSON.parse('<?= $grafikKasMasukData; ?>'),
                        }, {
                            label: "Kas Keluar",
                            backgroundColor: "#e74a3b",
                            hoverBackgroundColor: "#c9302c",
                            borderColor: "#e74a3b",
                            data: JSON.parse('<?= $grafikKasKeluarData; ?>'),
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: { left: 10, right: 25, top: 25, bottom: 0 }
                        },
                        scales: {
                            x: { // Diganti dari xAxes
                                // type: 'time',
                                // time: { unit: 'month' },
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    maxTicksLimit: 6
                                },
                                // stacked: false, // pindah ke konfigurasi dataset jika diperlukan
                            },
                            y: { // Diganti dari yAxes
                                ticks: {
                                    beginAtZero: true, // min:0 -> beginAtZero: true
                                    maxTicksLimit: 5, padding: 10,
                                    callback: function (value, index, values) { return 'Rp ' + number_format(value); }
                                },
                                grid: {
                                    color: "rgb(234, 236, 244)",
                                    drawBorder: false,
                                    borderDash: [2],
                                },
                                // stacked: false, // pindah ke konfigurasi dataset jika diperlukan
                            }
                        },
                        plugins: { // Legend dan Tooltip sekarang di bawah 'plugins'
                            legend: {
                                display: true
                            },
                            tooltip: {
                                titleMarginBottom: 10,
                                titleColor: '#6e707e',
                                titleFont: { size: 14 },
                                backgroundColor: "rgb(255,255,255)",
                                bodyColor: "#858796",
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                padding: { x: 15, y: 15 },
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                                callbacks: {
                                    label: function (tooltipItem) {
                                        var datasetLabel = tooltipItem.dataset.label || '';
                                        return datasetLabel + ': Rp ' + number_format(tooltipItem.parsed.y);
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error("Error saat inisialisasi Chart KasBulanan (DOMContentLoaded):", e);
            }
        }
    });
</script>
<?= $this->endSection(); ?>
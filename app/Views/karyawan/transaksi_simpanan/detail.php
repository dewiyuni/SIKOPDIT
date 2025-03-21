<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Calculate totals and balances directly in the view -->
            <?php
            // Initialize variables for totals
            $totalSetorSW = 0;
            $totalTarikSW = 0;
            $totalSetorSWP = 0;
            $totalTarikSWP = 0;
            $totalSetorSS = 0;
            $totalTarikSS = 0;
            $totalSetorSP = 0;
            $totalTarikSP = 0;

            // Calculate totals
            foreach ($transaksiSimpanan as $transaksi) {
                $totalSetorSW += $transaksi->setor_sw ?? 0;
                $totalTarikSW += $transaksi->tarik_sw ?? 0;
                $totalSetorSWP += $transaksi->setor_swp ?? 0;
                $totalTarikSWP += $transaksi->tarik_swp ?? 0;
                $totalSetorSS += $transaksi->setor_ss ?? 0;
                $totalTarikSS += $transaksi->tarik_ss ?? 0;
                $totalSetorSP += $transaksi->setor_sp ?? 0;
                $totalTarikSP += $transaksi->tarik_sp ?? 0;
            }

            // Calculate balances
            $saldoSW = $totalSetorSW - $totalTarikSW;
            $saldoSWP = $totalSetorSWP - $totalTarikSWP;
            $saldoSS = $totalSetorSS - $totalTarikSS;
            $saldoSP = $totalSetorSP - $totalTarikSP;
            $totalSaldo = $saldoSW + $saldoSWP + $saldoSS + $saldoSP;
            ?>

            <!-- Header Section -->
            <div class="card p-3 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Detail Simpanan Anggota</h3>
                    <div>
                        <a href="<?= base_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Member Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Anggota</h5>
                            </div>
                            <div class="card-body">
                                <h4><?= esc($anggota->nama) ?></h4>
                                <p class="mb-1"><strong>No BA:</strong> <?= esc($anggota->no_ba) ?></p>
                                <p class="mb-1"><strong>NIK:</strong> <?= esc($anggota->nik) ?></p>
                                <p class="mb-0"><strong>Alamat:</strong> <?= esc($anggota->alamat) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Tambahan</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Status:</strong> <?= esc($anggota->status) ?></p>
                                <p class="mb-1"><strong>Pekerjaan:</strong> <?= esc($anggota->pekerjaan) ?></p>
                                <p class="mb-0"><strong>Terdaftar Sejak:</strong>
                                    <?= date('d-m-Y', strtotime($anggota->created_at)) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Savings Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Ringkasan Simpanan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Simpanan Wajib</div>
                                        <div class="h3 text-primary">Rp <?= number_format($saldoSW, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Simpanan Wajib Plus</div>
                                        <div class="h3 text-success">Rp <?= number_format($saldoSWP, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Simpanan Sukarela</div>
                                        <div class="h3 text-info">Rp <?= number_format($saldoSS, 0, ',', '.') ?></div>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Simpanan Pokok</div>
                                        <div class="h3 text-warning">Rp <?= number_format($saldoSP, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <div class="h4 mt-3">Total Saldo Simpanan</div>
                                        <div class="h2 text-success">Rp <?= number_format($totalSaldo, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Savings Transaction History -->
            <div class="card p-3 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Riwayat Transaksi Simpanan</h4>
                    <?php if (!empty($transaksiSimpanan)): ?>
                        <button class="btn btn-outline-primary" onclick="printTable()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                    <?php endif; ?>
                </div>

                <div style="overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="tabelSimpanan">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Simpanan Wajib</th>
                                <th>Penarikan Wajib</th>
                                <th>Simpanan Wajib Plus</th>
                                <th>Penarikan Wajib Plus</th>
                                <th>Simpanan Sukarela</th>
                                <th>Penarikan Sukarela</th>
                                <th>Simpanan Pokok</th>
                                <th>Penarikan Pokok</th>
                                <th class="action-column">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transaksiSimpanan)): ?>
                                <?php $no = 1; ?>
                                <?php foreach ($transaksiSimpanan as $transaksi): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d M Y', strtotime($transaksi->tanggal)) ?></td>
                                        <td><?= ($transaksi->setor_sw > 0) ? 'Rp ' . number_format($transaksi->setor_sw, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->tarik_sw > 0) ? 'Rp ' . number_format($transaksi->tarik_sw, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->setor_swp > 0) ? 'Rp ' . number_format($transaksi->setor_swp, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->tarik_swp > 0) ? 'Rp ' . number_format($transaksi->tarik_swp, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->setor_ss > 0) ? 'Rp ' . number_format($transaksi->setor_ss, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->tarik_ss > 0) ? 'Rp ' . number_format($transaksi->tarik_ss, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->setor_sp > 0) ? 'Rp ' . number_format($transaksi->setor_sp, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td><?= ($transaksi->tarik_sp > 0) ? 'Rp ' . number_format($transaksi->tarik_sp, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td class="action-column">
                                            <div class="btn-group">
                                                <!-- <a href="<?= base_url('karyawan/transaksi_simpanan/edit/' . $transaksi->id_simpanan) ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a> -->
                                                <a href="<?= base_url('karyawan/transaksi_simpanan/delete/' . $transaksi->id_simpanan) ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi tanggal <?= date('d M Y', strtotime($transaksi->tanggal)) ?>?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Total Row -->
                                <tr class="table-primary font-weight-bold">
                                    <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
                                    <td><strong><?= ($totalSetorSW > 0) ? 'Rp ' . number_format($totalSetorSW, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalTarikSW > 0) ? 'Rp ' . number_format($totalTarikSW, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalSetorSWP > 0) ? 'Rp ' . number_format($totalSetorSWP, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalTarikSWP > 0) ? 'Rp ' . number_format($totalTarikSWP, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalSetorSS > 0) ? 'Rp ' . number_format($totalSetorSS, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalTarikSS > 0) ? 'Rp ' . number_format($totalTarikSS, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalSetorSP > 0) ? 'Rp ' . number_format($totalSetorSP, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td><strong><?= ($totalTarikSP > 0) ? 'Rp ' . number_format($totalTarikSP, 0, ',', '.') : '-' ?></strong>
                                    </td>
                                    <td class="action-column"></td>
                                </tr>
                                <!-- Balance Row -->
                                <tr class="table-success font-weight-bold">
                                    <td colspan="2" class="text-center"><strong>SALDO</strong></td>
                                    <td colspan="2" class="text-center"><strong>Rp
                                            <?= number_format($saldoSW, 0, ',', '.') ?></strong></td>
                                    <td colspan="2" class="text-center"><strong>Rp
                                            <?= number_format($saldoSWP, 0, ',', '.') ?></strong></td>
                                    <td colspan="2" class="text-center"><strong>Rp
                                            <?= number_format($saldoSS, 0, ',', '.') ?></strong></td>
                                    <td colspan="2" class="text-center"><strong>Rp
                                            <?= number_format($saldoSP, 0, ',', '.') ?></strong></td>
                                    <td class="action-column"></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">Belum ada transaksi simpanan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loan History Related to Savings -->
            <?php
            $relatedLoans = [];
            foreach ($transaksiSimpanan as $transaksi) {
                if (!empty($transaksi->id_pinjaman)) {
                    $relatedLoans[] = $transaksi->id_pinjaman;
                }
            }
            $relatedLoans = array_unique($relatedLoans);

            if (!empty($relatedLoans)):
                ?>
                <div class="card p-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Pinjaman Terkait</h4>
                    </div>

                    <div class="alert alert-info">
                        <p class="mb-0">User Pinjaman:
                            <?php foreach ($relatedLoans as $index => $loanId): ?>
                                <a href="<?= base_url('karyawan/transaksi_pinjaman/detail/' . $loanId) ?>"
                                    class="badge bg-primary">
                                    Memiliki Pinjaman
                                </a><?= ($index < count($relatedLoans) - 1) ? ', ' : '' ?>
                            <?php endforeach; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    @media print {
        .action-column {
            display: none;
        }

        /* Additional print styles */
        body {
            font-size: 12pt;
        }

        .card,
        .table {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .btn,
        .no-print {
            display: none !important;
        }
    }
</style>

<script>
    $(document).ready(function () {
        // DataTable initialization
        $('#tabelSimpanan').DataTable({
            "responsive": true,
            "ordering": true,
            "order": [[1, 'desc']], // Sort by date descending
            "info": true,
            "paging": true,
            "searching": true,
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });

    function printTable() {
        var printContents = document.getElementById("tabelSimpanan").outerHTML;
        var originalContents = document.body.innerHTML;

        var printHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h2>Riwayat Transaksi Simpanan</h2>
            <h3>${<?= json_encode($anggota->nama) ?>}</h3>
            <p>No BA: ${<?= json_encode($anggota->no_ba) ?>} | Tanggal Cetak: ${new Date().toLocaleDateString()}</p>
        </div>
        <style>
            @media print {
                .action-column {
                    display: none;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid black;
                }
                th, td {
                    padding: 8px;
                    text-align: left;
                }
                tr.table-primary, tr.table-success {
                    background-color: #f2f2f2 !important;
                    font-weight: bold;
                }
            }
        </style>
    `;

        document.body.innerHTML = printHeader + printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
<?= $this->endSection() ?>
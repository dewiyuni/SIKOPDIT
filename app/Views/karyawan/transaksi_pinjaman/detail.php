<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="card p-3 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Detail Pinjaman</h3>
                    <div>
                        <?php if ($sisaPinjaman > 0): ?>
                            <a href="<?= base_url('karyawan/transaksi_pinjaman/tambahAngsuran/' . $pinjaman->id_pinjaman) ?>"
                                class="btn btn-success me-2">
                                <i class="fas fa-plus-circle"></i> Tambah Angsuran
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('karyawan/transaksi_pinjaman') ?>" class="btn btn-warning">
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
                                <h4><?= esc($pinjaman->nama) ?></h4>
                                <p class="mb-1"><strong>No BA:</strong> <?= esc($pinjaman->no_ba) ?></p>
                                <p class="mb-1"><strong>NIK:</strong> <?= esc($pinjaman->nik) ?></p>
                                <p class="mb-0"><strong>Alamat:</strong> <?= esc($pinjaman->alamat) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Pinjaman</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Tanggal Cair:</strong>
                                            <?= date('d-m-Y', strtotime($pinjaman->tanggal_pinjaman)) ?></p>
                                        <p class="mb-1"><strong>Jangka Waktu:</strong> <?= $pinjaman->jangka_waktu ?>
                                            bulan</p>
                                        <?php
                                        // Format the interest rate to remove trailing zeros
                                        $bungaDisplay = (float) $bungaPerbulan;
                                        if (floor($bungaDisplay) == $bungaDisplay) {
                                            // If it's a whole number, show without decimal
                                            $bungaDisplay = number_format($bungaDisplay, 0);
                                        } else {
                                            // If it has decimal part, remove trailing zeros
                                            $bungaDisplay = rtrim(rtrim(number_format($bungaDisplay, 2), '0'), '.');
                                        }
                                        ?>
                                        <p class="mb-1"><strong>Bunga:</strong> <?= $bungaDisplay ?>% (Rp
                                            <?= number_format($totalBungaAwal, 0, ',', '.') ?>)
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Besar Pinjaman:</strong> Rp
                                            <?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?>
                                        </p>
                                        <p class="mb-1"><strong>Jaminan:</strong> <?= esc($pinjaman->jaminan) ?></p>
                                        <p class="mb-1"><strong>Angsuran/bulan:</strong> Rp
                                            <?= number_format($angsuranPerBulan + $totalBungaAwal, 0, ',', '.') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Ringkasan Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Total Dibayar</div>
                                        <div class="h3 text-success">Rp
                                            <?= number_format($totalAngsuran, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Sisa Pinjaman</div>
                                        <div class="h3 text-danger">Rp <?= number_format($sisaPinjaman, 0, ',', '.') ?>
                                        </div>
                                    </div>

                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Total Bunga</div>
                                        <div class="h3 text-primary">Rp <?= number_format($totalBunga, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="h5">Status</div>
                                        <?php if ($sisaPinjaman <= 0): ?>
                                            <div class="h3"><span class="badge bg-success">LUNAS</span></div>
                                        <?php else: ?>
                                            <div class="h3"><span class="badge bg-warning">BELUM LUNAS</span></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: <?= $persentaseLunas ?>%;" aria-valuenow="<?= $persentaseLunas ?>"
                                        aria-valuemin="0" aria-valuemax="100"> <?= $persentaseLunas ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Installment History -->
            <div class="card p-3 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Riwayat Angsuran</h4>
                    <?php if ($angsuran): ?>
                        <button class="btn btn-outline-primary" onclick="printTable()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                    <?php endif; ?>
                </div>

                <div style="overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="tabelAngsuran">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Saldo Awal</th>
                                <th>Angsuran</th>
                                <th>Bunga (%)</th>
                                <th>Jumlah Bunga</th>
                                <th>Total Bayar</th>
                                <th>Saldo Akhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($angsuran)): ?>
                                <?php $no = 1;
                                $saldo_awal = $pinjaman->jumlah_pinjaman; ?>
                                <?php foreach ($angsuran as $row): ?>
                                    <?php
                                    // Calculate interest amount based on the loan amount (not the installment amount)
                                    // KODE KALKULASI TETAP SAMA
                                    $jumlah_bunga = ($row->bunga / 100) * $pinjaman->jumlah_pinjaman;
                                    $total_bayar = $row->jumlah_angsuran + $jumlah_bunga;
                                    $saldo_akhir = $saldo_awal - $row->jumlah_angsuran;

                                    // Format the interest rate to remove trailing zeros
                                    $bungaDisplay = (float) $row->bunga;
                                    if (floor($bungaDisplay) == $bungaDisplay) {
                                        $bungaDisplay = number_format($bungaDisplay, 0);
                                    } else {
                                        $bungaDisplay = rtrim(rtrim(number_format($bungaDisplay, 2), '0'), '.');
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d M Y', strtotime($row->tanggal_angsuran)) ?></td>
                                        <td>Rp <?= number_format($saldo_awal, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($row->jumlah_angsuran, 0, ',', '.') ?></td>
                                        <td><?= $bungaDisplay ?>%</td>
                                        <td>Rp <?= number_format($jumlah_bunga, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($total_bayar, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= base_url('karyawan/transaksi_pinjaman/edit/' . $row->id_angsuran) ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                    data-id="<?= $row->id_angsuran ?>"
                                                    data-created="<?= $row->tanggal_angsuran ?>" data-bs-toggle="modal"
                                                    data-bs-target="#deleteConfirmModal">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php $saldo_awal = $saldo_akhir; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <!-- HAPUS BAGIAN ELSE INI -->
                            <?php /* else: ?>
<tr>
  <td colspan="9" class="text-center">Belum ada angsuran</td>
</tr>
<?php endif; */ ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus angsuran ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Show delete confirmation modal
        $('.delete-btn').click(function () {
            const id = $(this).data('id');
            const deleteUrl = `<?= site_url('karyawan/transaksi_pinjaman/delete/') ?>${id}`;

            $('#confirmDeleteBtn').attr('href', deleteUrl);
            $('#deleteConfirmModal').modal('show');
        });
    });

    $(document).ready(function () {
        $('#tabelAngsuran').DataTable({
            "responsive": true,
            "ordering": false,
            "info": false,
            "paging": false,
            "searching": false
        });
    });

    function printTable() {
        var printContents = document.getElementById("tabelAngsuran").outerHTML;
        var originalContents = document.body.innerHTML;

        var printHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h2>Riwayat Angsuran Pinjaman</h2>
            <h3>${<?= json_encode($pinjaman->nama) ?>}</h3>
            <p>No BA: ${<?= json_encode($pinjaman->no_ba) ?>} | Tanggal Cetak: ${new Date().toLocaleDateString()}</p>
        </div>
    `;

        document.body.innerHTML = printHeader + printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
<?= $this->endSection() ?>
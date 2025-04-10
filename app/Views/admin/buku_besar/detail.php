<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Detail Buku Besar</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Buku Besar: <?= $akun['kode_akun'] ?> - <?= $akun['nama_akun'] ?></h5>
            <div class="d-flex gap-2">
                <a href="<?= base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="<?= base_url('admin/buku_besar/export/buku-besar/' . $akun['id'] . '?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Kode Akun</th>
                            <td><?= $akun['kode_akun'] ?></td>
                        </tr>
                        <tr>
                            <th>Nama Akun</th>
                            <td><?= $akun['nama_akun'] ?></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td><?= $akun['kategori'] ?></td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td><?= $akun['jenis'] ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Periode</th>
                            <td>
                                <?php
                                $bulanNames = [
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember'
                                ];
                                echo $bulanNames[$bulan] . ' ' . $tahun;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Saldo Awal</th>
                            <td class="text-end"><?= number_format($saldo_awal, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Total Debit</th>
                            <td class="text-end">
                                <?php
                                $totalDebit = 0;
                                foreach ($transaksi as $t) {
                                    $totalDebit += $t['debit'];
                                }
                                echo number_format($totalDebit, 2, ',', '.');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Kredit</th>
                            <td class="text-end">
                                <?php
                                $totalKredit = 0;
                                foreach ($transaksi as $t) {
                                    $totalKredit += $t['kredit'];
                                }
                                echo number_format($totalKredit, 2, ',', '.');
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td><?= date('01-m-Y', strtotime($tahun . '-' . $bulan . '-01')) ?></td>
                            <td>Saldo Awal</td>
                            <td></td>
                            <td></td>
                            <td class="text-end"><?= number_format($saldo_awal, 2, ',', '.') ?></td>
                        </tr>
                        <?php
                        $no = 1;
                        $currentSaldo = $saldo_awal;
                        foreach ($transaksi as $t):
                            $currentSaldo = $t['saldo'];
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
                                <td><?= $t['keterangan'] ?></td>
                                <td class="text-end"><?= $t['debit'] > 0 ? number_format($t['debit'], 2, ',', '.') : '' ?>
                                </td>
                                <td class="text-end"><?= $t['kredit'] > 0 ? number_format($t['kredit'], 2, ',', '.') : '' ?>
                                </td>
                                <td class="text-end"><?= number_format($t['saldo'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-success">
                            <td colspan="3" class="text-end"><strong>Saldo Akhir</strong></td>
                            <td class="text-end"><strong><?= number_format($totalDebit, 2, ',', '.') ?></strong></td>
                            <td class="text-end"><strong><?= number_format($totalKredit, 2, ',', '.') ?></strong></td>
                            <td class="text-end"><strong><?= number_format($currentSaldo, 2, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
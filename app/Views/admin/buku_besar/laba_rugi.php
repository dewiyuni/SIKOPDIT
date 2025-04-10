<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Laporan Laba Rugi</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Laporan Laba Rugi Periode
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
            </h5>
            <div class="d-flex gap-2">
                <form action="<?= base_url('admin/buku_besar/laba-rugi') ?>" method="get" class="d-flex gap-2">
                    <select name="bulan" class="form-select form-select-sm">
                        <?php foreach ($bulanNames as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $bulan == $key ? 'selected' : '' ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="tahun" class="form-select form-select-sm">
                        <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                            <option value="<?= $year ?>" <?= $tahun == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </form>

                <a href="<?= base_url('admin/buku_besar') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <a href="<?= base_url('admin/buku_besar/export/laba-rugi?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr class="table-primary">
                            <th colspan="3">PENDAPATAN</th>
                        </tr>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pendapatan_items = array_filter($laba_rugi, function ($item) {
                            return $item['kategori'] == 'Pendapatan';
                        });

                        if (empty($pendapatan_items)):
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data pendapatan</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendapatan_items as $item): ?>
                                <tr>
                                    <td><?= $item['kode_akun'] ?></td>
                                    <td><?= $item['nama_akun'] ?></td>
                                    <td class="text-end"><?= number_format($item['saldo'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="2" class="text-end">Total Pendapatan</th>
                            <th class="text-end"><?= number_format($total_pendapatan, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>

                <table class="table table-bordered mt-4">
                    <thead>
                        <tr class="table-danger">
                            <th colspan="3">BEBAN</th>
                        </tr>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $beban_items = array_filter($laba_rugi, function ($item) {
                            return $item['kategori'] == 'Beban';
                        });

                        if (empty($beban_items)):
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data beban</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($beban_items as $item): ?>
                                <tr>
                                    <td><?= $item['kode_akun'] ?></td>
                                    <td><?= $item['nama_akun'] ?></td>
                                    <td class="text-end"><?= number_format($item['saldo'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <th colspan="2" class="text-end">Total Beban</th>
                            <th class="text-end"><?= number_format($total_beban, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>

                <table class="table table-bordered mt-4">
                    <tr class="table-info">
                        <th>LABA (RUGI) BERSIH</th>
                        <th class="text-end"><?= number_format($laba_rugi_bersih, 2, ',', '.') ?></th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
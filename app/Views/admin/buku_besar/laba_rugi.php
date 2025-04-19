<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Laporan Laba Rugi</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Laporan Laba Rugi Periode
                <?php
                // Ambil $bulanNames dari controller jika ada, atau definisikan di sini
                if (!isset($bulanNames)) {
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
                }
                echo esc($bulanNames[$bulan] ?? $bulan) . ' ' . esc($tahun);
                ?>
            </h5>
            <div class="d-flex gap-2 flex-wrap"> <!-- Tambah flex-wrap untuk responsif -->
                <form action="<?= base_url('admin/buku_besar/laba-rugi') ?>" method="get" class="d-flex gap-2">
                    <select name="bulan" class="form-select form-select-sm">
                        <?php foreach ($bulanNames as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $bulan == $key ? 'selected' : '' ?>><?= esc($value) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="tahun" class="form-select form-select-sm">
                        <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                            <option value="<?= $year ?>" <?= $tahun == $year ? 'selected' : '' ?>><?= esc($year) ?></option>
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
                        // HAPUS BLOK ARRAY_FILTER DI SINI
                        // Langsung gunakan $pendapatan_items yang dikirim controller
                        
                        // Pengecekan apakah variabel $pendapatan_items ada dan tidak kosong
                        if (empty($pendapatan_items)):
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data pendapatan</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendapatan_items as $item): ?>
                                <tr>
                                    <td><?= esc($item['kode_akun'] ?? '-') ?></td>
                                    <td><?= esc($item['nama_akun'] ?? 'N/A') ?></td>
                                    <td class="text-end"><?= number_format(floatval($item['saldo'] ?? 0), 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="2" class="text-end">Total Pendapatan</th>
                            <th class="text-end"><?= number_format(floatval($total_pendapatan ?? 0), 2, ',', '.') ?>
                            </th>
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
                        // HAPUS BLOK ARRAY_FILTER DI SINI
                        // Langsung gunakan $beban_items yang dikirim controller
                        
                        // Pengecekan apakah variabel $beban_items ada dan tidak kosong
                        if (empty($beban_items)):
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data beban</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($beban_items as $item): ?>
                                <tr>
                                    <td><?= esc($item['kode_akun'] ?? '-') ?></td>
                                    <td><?= esc($item['nama_akun'] ?? 'N/A') ?></td>
                                    <td class="text-end"><?= number_format(floatval($item['saldo'] ?? 0), 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <th colspan="2" class="text-end">Total Beban</th>
                            <th class="text-end"><?= number_format(floatval($total_beban ?? 0), 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>

                <table class="table table-bordered mt-4" style="width: 50%; margin-left:auto; margin-right:0;">
                    <!-- Styling agar lebih mirip laporan -->
                    <tbody>
                        <tr class="table-info">
                            <th>LABA (RUGI) BERSIH</th>
                            <th class="text-end"><?= number_format(floatval($laba_rugi_bersih ?? 0), 2, ',', '.') ?>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
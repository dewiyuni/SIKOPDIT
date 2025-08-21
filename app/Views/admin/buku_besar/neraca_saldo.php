<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Neraca Saldo</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Neraca Saldo Periode
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
                echo esc($bulanNames[$bulan] ?? $bulan) . ' ' . esc($tahun);
                ?>
            </h5>
            <div class="d-flex gap-2">
                <form action="<?= base_url('admin/buku_besar/neraca-saldo') ?>" method="get" class="d-flex gap-2">
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

                <a href="<?= base_url('admin/buku_besar/export/neraca-saldo?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $adaBaris = false;
                        if (!empty($neraca_saldo)):
                            foreach ($neraca_saldo as $neraca):
                                // Normalisasi nilai
                                $debit = isset($neraca['debit']) && is_numeric($neraca['debit']) ? floatval($neraca['debit']) : 0.0;
                                $kredit = isset($neraca['kredit']) && is_numeric($neraca['kredit']) ? floatval($neraca['kredit']) : 0.0;

                                // SKIP baris jika keduanya 0 / kosong
                                if ($debit == 0.0 && $kredit == 0.0) {
                                    continue;
                                }
                                $adaBaris = true;
                                ?>
                                <tr>
                                    <td><?= esc($neraca['kode_akun'] ?? '-') ?></td>
                                    <td><?= esc($neraca['nama_akun'] ?? 'N/A') ?></td>
                                    <td class="text-end"><?= $debit > 0 ? number_format($debit, 2, ',', '.') : '' ?></td>
                                    <td class="text-end"><?= $kredit > 0 ? number_format($kredit, 2, ',', '.') : '' ?></td>
                                </tr>
                                <?php
                            endforeach;
                        endif;

                        if (!$adaBaris): ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data neraca saldo untuk periode ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="2" class="text-end">TOTAL</th>
                            <th class="text-end"><?= number_format(floatval($total_debit ?? 0), 2, ',', '.') ?></th>
                            <th class="text-end"><?= number_format(floatval($total_kredit ?? 0), 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
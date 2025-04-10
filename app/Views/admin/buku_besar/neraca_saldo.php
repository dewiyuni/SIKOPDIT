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
                echo $bulanNames[$bulan] . ' ' . $tahun;
                ?>
            </h5>
            <div class="d-flex gap-2">
                <form action="<?= base_url('admin/buku_besar/neraca-saldo') ?>" method="get" class="d-flex gap-2">
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
                        <?php foreach ($neraca_saldo as $neraca): ?>
                            <tr>
                                <td><?= $neraca['kode_akun'] ?></td>
                                <td><?= $neraca['nama_akun'] ?></td>
                                <td class="text-end">
                                    <?= $neraca['debit'] > 0 ? number_format($neraca['debit'], 2, ',', '.') : '' ?></td>
                                <td class="text-end">
                                    <?= $neraca['kredit'] > 0 ? number_format($neraca['kredit'], 2, ',', '.') : '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="2" class="text-end">TOTAL</th>
                            <th class="text-end"><?= number_format($total_debit, 2, ',', '.') ?></th>
                            <th class="text-end"><?= number_format($total_kredit, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
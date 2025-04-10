<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Neraca</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Neraca Periode
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
                <form action="<?= base_url('admin/buku_besar/neraca') ?>" method="get" class="d-flex gap-2">
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

                <a href="<?= base_url('admin/buku_besar/export/neraca?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-primary">
                                    <th colspan="3">AKTIVA</th>
                                </tr>
                                <tr>
                                    <th>Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $aktiva_items = array_filter($neraca, function ($item) {
                                    return $item['kategori'] == 'Aktiva';
                                });

                                if (empty($aktiva_items)):
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data aktiva</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($aktiva_items as $item): ?>
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
                                    <th colspan="2" class="text-end">Total Aktiva</th>
                                    <th class="text-end"><?= number_format($total_aktiva, 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-danger">
                                    <th colspan="3">PASIVA</th>
                                </tr>
                                <tr>
                                    <th>Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pasiva_items = array_filter($neraca, function ($item) {
                                    return $item['kategori'] == 'Pasiva';
                                });

                                if (empty($pasiva_items)):
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data pasiva</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pasiva_items as $item): ?>
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
                                    <th colspan="2" class="text-end">Total Pasiva</th>
                                    <th class="text-end"><?= number_format($total_pasiva, 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>

                        <table class="table table-bordered mt-4">
                            <thead>
                                <tr class="table-info">
                                    <th colspan="3">MODAL</th>
                                </tr>
                                <tr>
                                    <th>Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $modal_items = array_filter($neraca, function ($item) {
                                    return $item['kategori'] == 'Modal';
                                });

                                if (empty($modal_items)):
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data modal</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($modal_items as $item): ?>
                                        <tr>
                                            <td><?= $item['kode_akun'] ?></td>
                                            <td><?= $item['nama_akun'] ?></td>
                                            <td class="text-end"><?= number_format($item['saldo'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- Laba Rugi Berjalan -->
                                <tr>
                                    <td></td>
                                    <td>Laba (Rugi) Berjalan</td>
                                    <td class="text-end"><?= number_format($laba_rugi_bersih, 2, ',', '.') ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-warning">
                                    <th colspan="2" class="text-end">Total Modal</th>
                                    <th class="text-end">
                                        <?= number_format($total_modal + $laba_rugi_bersih, 2, ',', '.') ?></th>
                                </tr>
                                <tr class="table-primary">
                                    <th colspan="2" class="text-end">TOTAL PASIVA & MODAL</th>
                                    <th class="text-end">
                                        <?= number_format($total_pasiva + $total_modal + $laba_rugi_bersih, 2, ',', '.') ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
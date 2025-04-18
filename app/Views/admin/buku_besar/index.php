<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Buku Besar</h3>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Akun Buku Besar</h5>
            <div class="d-flex gap-2">
                <form action="<?= base_url('admin/buku_besar') ?>" method="get" class="d-flex gap-2">
                    <select name="bulan" class="form-select form-select-sm">
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
                        foreach ($bulanNames as $key => $value): ?>
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

                <a href="<?= base_url('admin/buku_besar/proses?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-sync"></i> Proses Jurnal ke Buku Besar
                </a>

                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="reportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt"></i> Laporan
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="reportDropdown">
                        <li><a class="dropdown-item"
                                href="<?= base_url('admin/buku_besar/neraca-saldo?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Neraca
                                Saldo</a></li>
                        <li><a class="dropdown-item"
                                href="<?= base_url('admin/buku_besar/laba-rugi?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Laba
                                Rugi</a></li>
                        <li><a class="dropdown-item"
                                href="<?= base_url('admin/buku_besar/neraca?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Neraca</a>
                        </li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="settingsDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Pengaturan
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                        <li><a class="dropdown-item" href="<?= base_url('admin/buku_besar/akun') ?>">Kelola Akun</a>
                        </li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/buku_besar/pemetaan') ?>">Pemetaan
                                Akun</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Saldo Awal</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo Akhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($akun as $a): ?>
                            <tr>
                                <td><?= $a['kode_akun'] ?></td>
                                <td><?= $a['nama_akun'] ?></td>
                                <td><?= $a['kategori'] ?></td>
                                <td><?= $a['jenis'] ?></td>
                                <td class="text-end">
                                    <?= rtrim(rtrim(number_format($a['saldo_bulan_ini'], 2, ',', '.'), '0'), ',') ?></td>
                                <td class="text-end">
                                    <?= rtrim(rtrim(number_format($a['total_debit'], 2, ',', '.'), '0'), ',') ?></td>
                                <td class="text-end">
                                    <?= rtrim(rtrim(number_format($a['total_kredit'], 2, ',', '.'), '0'), ',') ?></td>
                                <td class="text-end">
                                    <?= rtrim(rtrim(number_format($a['saldo_akhir'], 2, ',', '.'), '0'), ',') ?></td>
                                <td>
                                    <a href="<?= base_url('admin/buku_besar/detail/' . $a['id'] . '?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                                        class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="<?= base_url('admin/buku_besar/export/buku-besar/' . $a['id'] . '?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-file-excel"></i> Export
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
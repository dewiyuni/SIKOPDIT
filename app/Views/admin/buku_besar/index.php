<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Buku Besar per Kategori</h3>

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
            <div class="d-flex gap-2 flex-wrap"> <!-- Tambahkan flex-wrap jika tombol banyak -->
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

                <!-- Tombol Proses Jurnal -->
                <a href="<?= base_url('admin/buku_besar/proses?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-sync"></i> Proses Jurnal
                </a>

                <!-- Dropdown Laporan -->
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
                                href="<?= base_url('admin/laporan/laba_rugi?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Laba
                                Rugi</a></li>
                        <li><a class="dropdown-item"
                                href="<?= base_url('admin/laporan/neraca?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Neraca</a>
                        </li>
                    </ul>
                </div>

                <!-- Dropdown Pengaturan -->
                <div class="dropdown">
                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="settingsDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Pengaturan
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                        <li><a class="dropdown-item" href="<?= base_url('admin/akun') ?>">Kelola Akun</a></li>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Gunakan Accordion untuk menampilkan per kategori -->
            <div class="accordion" id="accordionBukuBesar">
                <?php $index = 0; ?>
                <?php foreach ($kategoriList as $kat): ?>
                    <?php $namaKategori = $kat['kategori']; ?>
                    <?php $akunDalamKategori = $akunPerKategori[$namaKategori] ?? []; ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $index ?>">
                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>"
                                aria-expanded="<?= $index == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                <?= esc($namaKategori) ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>"
                            aria-labelledby="heading<?= $index ?>" data-bs-parent="#accordionBukuBesar">
                            <div class="accordion-body">
                                <?php if (!empty($akunDalamKategori)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Nama Akun</th>
                                                    <th>Jenis</th>
                                                    <th>Saldo Awal</th>
                                                    <th>Debit</th>
                                                    <th>Kredit</th>
                                                    <th>Saldo Akhir</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($akunDalamKategori as $a): ?>
                                                    <tr>
                                                        <td><?= esc($a['kode_akun']) ?></td>
                                                        <td><?= esc($a['nama_akun']) ?></td>
                                                        <td><?= esc($a['jenis']) ?></td>
                                                        <td class="text-end">
                                                            <?= number_to_currency($a['saldo_bulan_ini'], 'IDR', 'id', 2) ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <?= number_to_currency($a['total_debit'], 'IDR', 'id', 2) ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <?= number_to_currency($a['total_kredit'], 'IDR', 'id', 2) ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <?= number_to_currency($a['saldo_akhir'], 'IDR', 'id', 2) ?>
                                                        </td>
                                                        <td>
                                                            <a href="<?= base_url('admin/buku_besar/detail/' . $a['id'] . '?bulan=' . $bulan . '&tahun=' . $tahun) ?>"
                                                                class="btn btn-info btn-sm" title="Lihat Detail Transaksi">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <!-- Tombol Export per akun jika masih relevan -->
                                                            <!-- <a href="<?= base_url('admin/buku_besar/export/buku-besar/' . $a['id'] . '?bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="btn btn-success btn-sm" title="Export Excel">
                                                                <i class="fas fa-file-excel"></i>
                                                            </a> -->
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>Tidak ada akun dalam kategori ini.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php $index++; ?>
                <?php endforeach; ?>
                <?php if (empty($kategoriList)): ?>
                    <p class="text-center">Belum ada data akun.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
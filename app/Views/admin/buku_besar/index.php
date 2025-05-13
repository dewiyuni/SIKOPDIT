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
                    class="btn btn-success btn-sm"
                    onclick="return confirm('Apakah Anda yakin ingin memproses jurnal ke buku besar menggunakan pemetaan? Data buku besar bulan ini akan dihapus dan dibuat ulang.');">
                    <i class="fas fa-sync"></i> Proses Jurnal (Mapping)
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
                                href="<?= base_url('admin/buku_besar/laba-rugi?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Laba
                                Rugi</a></li>
                        <li><a class="dropdown-item"
                                href="<?= base_url('admin/buku_besar/neraca?bulan=' . $bulan . '&tahun=' . $tahun) ?>">Neraca</a>
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
                        <li><a class="dropdown-item" href="<?= base_url('admin/buku_besar/akun') ?>">Kelola Akun</a>
                        </li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/buku_besar/pemetaan') ?>">Kelola Pemetaan
                                Jurnal</a></li>
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
                                        <table class="table table-bordered table-striped table-sm" id="akunTable">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Nama Akun</th>
                                                    <th>Jenis</th>
                                                    <th>Saldo Awal</th>
                                                    <th>Debit</th>
                                                    <th>Kredit</th>
                                                    <th>Saldo Akhir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $totalSaldoAwal = 0;
                                                $totalDebit = 0;
                                                $totalKredit = 0;
                                                $totalSaldoAkhir = 0;
                                                foreach ($akunDalamKategori as $a):
                                                    $totalSaldoAwal += $a['saldo_bulan_ini'];
                                                    $totalDebit += $a['total_debit'];
                                                    $totalKredit += $a['total_kredit'];
                                                    $totalSaldoAkhir += $a['saldo_akhir'];
                                                    ?>
                                                    <tr>
                                                        <td><?= esc($a['kode_akun']) ?></td>
                                                        <td><?= esc($a['nama_akun']) ?></td>
                                                        <td><?= esc($a['jenis']) ?></td>
                                                        <td class="saldo-awal text-end">
                                                            <?= number_to_currency($a['saldo_bulan_ini'], 'IDR', 'id', 0) ?>
                                                        </td>
                                                        <td class="total-debit text-end">
                                                            <?= number_to_currency($a['total_debit'], 'IDR', 'id', 0) ?>
                                                        </td>
                                                        <td class="total-kredit text-end">
                                                            <?= number_to_currency($a['total_kredit'], 'IDR', 'id', 0) ?>
                                                        </td>
                                                        <td class="saldo-akhir text-end">
                                                            <?= number_to_currency($a['saldo_akhir'], 'IDR', 'id', 0) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <!-- Baris total per kategori -->
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                                    <td class="text-end">
                                                        <?= number_to_currency($totalSaldoAwal, 'IDR', 'id', 0) ?>
                                                    </td>
                                                    <td class="text-end"><?= number_to_currency($totalDebit, 'IDR', 'id', 0) ?>
                                                    </td>
                                                    <td class="text-end"><?= number_to_currency($totalKredit, 'IDR', 'id', 0) ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?= number_to_currency($totalSaldoAkhir, 'IDR', 'id', 0) ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
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

<script>
    // Fungsi untuk menghitung total kolom
    function hitungTotal() {
        let totalSaldoAwal = 0;
        let totalDebit = 0;
        let totalKredit = 0;
        let totalSaldoAkhir = 0;

        // Ambil semua baris data
        const rows = document.querySelectorAll('#akunTable tbody tr');
        rows.forEach(row => {
            const saldoAwal = parseFloat(row.querySelector('.saldo-awal').textContent.replace(/[^\d.-]/g, '')) || 0;
            const debit = parseFloat(row.querySelector('.total-debit').textContent.replace(/[^\d.-]/g, '')) || 0;
            const kredit = parseFloat(row.querySelector('.total-kredit').textContent.replace(/[^\d.-]/g, '')) || 0;
            const saldoAkhir = parseFloat(row.querySelector('.saldo-akhir').textContent.replace(/[^\d.-]/g, '')) || 0;

            // Jumlahkan nilai-nilai
            totalSaldoAwal += saldoAwal;
            totalDebit += debit;
            totalKredit += kredit;
            totalSaldoAkhir += saldoAkhir;
        });

        // Update total di footer
        document.getElementById('total-saldo-awal').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalSaldoAwal);
        document.getElementById('total-debit').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalDebit);
        document.getElementById('total-kredit').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalKredit);
        document.getElementById('total-saldo-akhir').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalSaldoAkhir);
    }

    // Panggil fungsi untuk menghitung total saat dokumen dimuat
    document.addEventListener('DOMContentLoaded', function () {
        hitungTotal();
    });


    // Panggil fungsi untuk menghitung total saat dokumen dimuat
    document.addEventListener('DOMContentLoaded', function () {
        hitungTotal();
    });
    // Fungsi untuk membersihkan nilai (menghapus format ribuan) dan mengonversi ke angka
    function cleanNumber(value) {
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Panggil fungsi ini setelah data tabel dimuat
    window.addEventListener('DOMContentLoaded', function () {
        hitungTotalAkun();
    });


    // Fungsi untuk membersihkan nilai (menghapus format ribuan) dan mengonversi ke angka
    function cleanNumber(value) {
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Panggil fungsi ini setelah data tabel dimuat
    window.addEventListener('DOMContentLoaded', function () {
        hitungTotalAkun();
    });

</script>
<?= $this->endSection(); ?>
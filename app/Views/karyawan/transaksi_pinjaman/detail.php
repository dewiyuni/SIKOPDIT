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
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Besar Pinjaman:</strong> Rp
                                            <?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?>
                                        </p>
                                        <?php
                                        // Format the interest rate to remove trailing zeros
                                        $bungaDisplayInfo = (float) $bungaPerbulan;
                                        if (floor($bungaDisplayInfo) == $bungaDisplayInfo) {
                                            $bungaDisplayInfo = number_format($bungaDisplayInfo, 0);
                                        } else {
                                            $bungaDisplayInfo = rtrim(rtrim(number_format($bungaDisplayInfo, 2), '0'), '.');
                                        }
                                        ?>
                                        <p class="mb-1"><strong>Bunga:</strong> <?= $bungaDisplayInfo ?>% (Rp
                                            <?= number_format($totalBungaAwal, 0, ',', '.') ?>)
                                        </p>
                                        <p class="mb-1"><strong>Jaminan:</strong> <?= esc($pinjaman->jaminan) ?></p>
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
                                        aria-valuemin="0" aria-valuemax="100">
                                        <?= number_format($persentaseLunas, 2) ?>%
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
                        <button class="btn btn-outline-primary" onclick="printRiwayatAngsuran()">
                            <i class="fas fa-print"></i> Cetak Riwayat
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
                                <th>Angsuran Pokok</th>
                                <th>Bunga (%)</th>
                                <th>Jumlah Bunga</th>
                                <th>Total Bayar</th>
                                <th>Saldo Akhir</th>
                                <th class="no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($angsuran)): ?>
                                <?php $no = 1;
                                $saldo_awal_iterasi = $pinjaman->jumlah_pinjaman;
                                ?>
                                <?php foreach ($angsuran as $row): ?>
                                    <?php
                                    // Bunga dihitung dari pokok pinjaman awal
                                    $jumlah_bunga = ($row->bunga / 100) * $pinjaman->jumlah_pinjaman;
                                    $total_bayar = $row->jumlah_angsuran + $jumlah_bunga;
                                    $saldo_akhir_iterasi = $saldo_awal_iterasi - $row->jumlah_angsuran;

                                    // Format the interest rate to remove trailing zeros
                                    $bungaDisplayRow = (float) $row->bunga;
                                    if (floor($bungaDisplayRow) == $bungaDisplayRow) {
                                        $bungaDisplayRow = number_format($bungaDisplayRow, 0);
                                    } else {
                                        $bungaDisplayRow = rtrim(rtrim(number_format($bungaDisplayRow, 2), '0'), '.');
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d M Y', strtotime($row->tanggal_angsuran)) ?></td>
                                        <td>Rp <?= number_format($saldo_awal_iterasi, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($row->jumlah_angsuran, 0, ',', '.') ?></td>
                                        <td><?= $bungaDisplayRow ?>%</td>
                                        <td>Rp <?= number_format($jumlah_bunga, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($total_bayar, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($saldo_akhir_iterasi, 0, ',', '.') ?></td>
                                        <td class="no-print">
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
                                    <?php $saldo_awal_iterasi = $saldo_akhir_iterasi; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada riwayat angsuran.</td>
                                </tr>
                            <?php endif; ?>
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

        // Initialize DataTable
        $('#tabelAngsuran').DataTable({
            "responsive": true,
            "ordering": false,
            "info": false,
            "paging": false,
            "searching": false,
            "columnDefs": [
                { "targets": 'no-print', "visible": true, "searchable": false }
            ]
        });
    });

    function printRiwayatAngsuran() {
        // Data untuk header cetakan
        const namaKoperasi = "KOPERASI SIDOMANUNGGAL";
        const alamatKoperasi = "Sedan, Sidorejo, Lendah, Kulon Progo, D.I.Yogyakarta";
        const telpKoperasi = ""; // Isi jika ada, atau biarkan kosong.

        const namaAnggota = <?= json_encode(esc($pinjaman->nama)) ?>;
        const noBa = <?= json_encode(esc($pinjaman->no_ba)) ?>;
        const nik = <?= json_encode(esc($pinjaman->nik)) ?>;
        // const idPinjaman = <?= json_encode(esc($pinjaman->id_pinjaman)) ?>; // DIHAPUS/DIKOMENTARI
        const tglPinjaman = <?= json_encode(date('d-m-Y', strtotime($pinjaman->tanggal_pinjaman))) ?>;
        const jumlahPinjaman = <?= json_encode(number_format($pinjaman->jumlah_pinjaman, 0, ',', '.')) ?>;
        const jangkaWaktu = <?= json_encode($pinjaman->jangka_waktu . ' bulan') ?>;

        let tableHtml = document.getElementById("tabelAngsuran").outerHTML;
        let tempDiv = document.createElement('div');
        tempDiv.innerHTML = tableHtml;

        let thAksi = tempDiv.querySelector('th.no-print');
        if (thAksi) thAksi.remove();

        let tdAksi = tempDiv.querySelectorAll('td.no-print');
        tdAksi.forEach(td => td.remove());

        let emptyRow = tempDiv.querySelector('td[colspan="9"]');
        if (emptyRow) emptyRow.setAttribute('colspan', '8');

        tableHtml = tempDiv.innerHTML;


        const styles = `
            <style>
                body { font-family: 'Arial', sans-serif; font-size: 10pt; margin: 0; padding:0; }
                .print-container { margin: 20px; }
                .header-print { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .header-print h2 { margin: 0 0 5px 0; font-size: 16pt; }
                .header-print h3 { margin: 0 0 5px 0; font-size: 14pt; }
                .header-print p { margin: 0; font-size: 9pt; }
                .info-section { margin-bottom: 15px; font-size: 10pt; }
                .info-section table { width: 100%; border-collapse: collapse; }
                .info-section td { padding: 3px 0px; vertical-align: top;}
                .info-section td:nth-child(1) { width: 120px; font-weight: bold; }
                .info-section td:nth-child(3) { width: 120px; font-weight: bold; }


                table.table-print { width: 100%; border-collapse: collapse; margin-top: 15px; }
                table.table-print th, table.table-print td {
                    border: 1px solid #333;
                    padding: 6px;
                    text-align: left;
                    font-size: 9pt;
                }
                table.table-print th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
                table.table-print td:nth-child(1) { text-align: center; } /* No */
                table.table-print td:nth-child(3), /* Saldo Awal */
                table.table-print td:nth-child(4), /* Angsuran Pokok */
                table.table-print td:nth-child(6), /* Jumlah Bunga */
                table.table-print td:nth-child(7), /* Total Bayar */
                table.table-print td:nth-child(8)  /* Saldo Akhir */
                { text-align: right; }
                table.table-print td:nth-child(5) { text-align: center; } /* Bunga % */

                .footer-print { text-align: right; margin-top: 30px; font-size: 9pt; padding-top:10px; border-top: 1px solid #ccc;}
                .footer-print p { margin: 0; }

                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .no-print { display: none !important; }
                    .print-container { margin: 0.5in; }
                }
            </style>
        `;

        const printWindow = window.open('', '_blank', 'width=1000,height=700,scrollbars=yes,resizable=yes');
        if (!printWindow) {
            alert('Gagal membuka jendela cetak. Pastikan pop-up blocker tidak aktif.');
            return;
        }
        printWindow.document.write('<html><head><title>Cetak Riwayat Angsuran</title>');
        printWindow.document.write(styles);
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="print-container">');

        printWindow.document.write(`
            <div class="header-print">
                <h2>${namaKoperasi}</h2>
                <p>${alamatKoperasi}</p>
                ${telpKoperasi ? `<p>${telpKoperasi}</p>` : ''}
                <h3>Riwayat Angsuran Pinjaman</h3>
            </div>
        `);

        // Modifikasi di sini untuk menghapus ID Pinjaman dan mengatur ulang tata letak jika perlu
        printWindow.document.write(`
            <div class="info-section">
                <table>
                    <tr>
                        <td>Nama Anggota</td><td>: ${namaAnggota}</td>
                        <td>Tgl Pinjaman</td><td>: ${tglPinjaman}</td>
                    </tr>
                    <tr>
                        <td>No. BA</td><td>: ${noBa}</td>
                        <td>Jumlah Pinjaman</td><td>: Rp ${jumlahPinjaman}</td>
                    </tr>
                    <tr>
                        <td>NIK</td><td>: ${nik}</td>
                        <td>Jangka Waktu</td><td>: ${jangkaWaktu}</td>
                    </tr>
                </table>
            </div>
        `);

        let styledTableHtml = tableHtml.replace('<table class="table table-bordered table-hover dataTable no-footer"', '<table class="table-print"');
        styledTableHtml = styledTableHtml.replace('id="tabelAngsuran"', '');
        styledTableHtml = styledTableHtml.replace('<thead class="table-light">', '<thead>');

        printWindow.document.write(styledTableHtml);

        const tglCetak = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        printWindow.document.write(`
            <div class="footer-print">
                <p>Dicetak pada: ${tglCetak}</p>
            </div>
        `);

        printWindow.document.write('</div>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    }
</script>
<?= $this->endSection() ?>
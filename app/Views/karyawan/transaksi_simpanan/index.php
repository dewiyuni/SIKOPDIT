<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em;
    }

    .dataTables_length select {
        padding-right: 20px;
    }

    .table tfoot th {
        font-weight: bold;
    }

    #tabelTransaksiSimpanan td:last-child,
    /* Kolom aksi */
    #tabelTransaksiSimpanan th:last-child {
        white-space: nowrap;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Transaksi Simpanan</h3>
    </div>

    <?php $session = session(); ?>
    <?php if ($session->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc($session->getFlashdata('message')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($session->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc($session->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php
    $validationErrors = $session->getFlashdata('errors') ?? ($errors ?? []);
    if (!empty($validationErrors) && is_array($validationErrors)):
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul class="mb-0">
                <?php foreach ($validationErrors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Transaksi Simpanan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelTransaksiSimpanan" class="table table-bordered table-striped table-hover"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Anggota</th>
                            <th>No BA</th>
                            <th>Saldo SW</th>
                            <th>Saldo SWP</th>
                            <th>Saldo SS</th>
                            <th>Saldo SP</th>
                            <th><strong>Saldo Akhir</strong></th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $grand_total_sw = 0;
                        $grand_total_swp = 0;
                        $grand_total_ss = 0;
                        $grand_total_sp = 0;
                        $grand_total_saldo = 0;

                        if (!empty($transaksi)):
                            foreach ($transaksi as $row):
                                $saldo_sw_val = (float) ($row->saldo_sw ?? 0);
                                $saldo_swp_val = (float) ($row->saldo_swp ?? 0);
                                $saldo_ss_val = (float) ($row->saldo_ss ?? 0);
                                $saldo_sp_val = (float) ($row->saldo_sp ?? 0);
                                $saldo_total_val = (float) ($row->saldo_total ?? 0);

                                $grand_total_sw += $saldo_sw_val;
                                $grand_total_swp += $saldo_swp_val;
                                $grand_total_ss += $saldo_ss_val;
                                $grand_total_sp += $saldo_sp_val;
                                $grand_total_saldo += $saldo_total_val;
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($row->nama ?? 'Tidak Diketahui') ?></td>
                                    <td><?= esc($row->no_ba ?? '-') ?></td>
                                    <td data-sort="<?= $saldo_sw_val ?>">
                                        <?= 'Rp ' . number_format($saldo_sw_val, 0, ',', '.') ?>
                                    </td>
                                    <td data-sort="<?= $saldo_swp_val ?>">
                                        <?= 'Rp ' . number_format($saldo_swp_val, 0, ',', '.') ?>
                                    </td>
                                    <td data-sort="<?= $saldo_ss_val ?>">
                                        <?= 'Rp ' . number_format($saldo_ss_val, 0, ',', '.') ?>
                                    </td>
                                    <td data-sort="<?= $saldo_sp_val ?>">
                                        <?= 'Rp ' . number_format($saldo_sp_val, 0, ',', '.') ?>
                                    </td>
                                    <td data-sort="<?= $saldo_total_val ?>">
                                        <strong><?= 'Rp ' . number_format($saldo_total_val, 0, ',', '.') ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('karyawan/transaksi_simpanan/detail/' . $row->id_anggota) ?>"
                                            class="btn btn-info btn-sm" title="Detail Transaksi">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('karyawan/transaksi_simpanan/setor_form/' . $row->id_anggota) ?>"
                                            class="btn btn-success btn-sm" title="Setor Simpanan">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        <a href="<?= site_url('karyawan/transaksi_simpanan/tarik_form/' . $row->id_anggota) ?>"
                                            class="btn btn-warning btn-sm" title="Tarik Simpanan">
                                            <i class="fas fa-minus-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light"> <?php // Baris untuk Grand Total (dari PHP) ?>
                            <th colspan="3" style="text-align:right;">Saldo Total:</th>
                            <th><?= 'Rp ' . number_format($grand_total_sw, 0, ',', '.') ?></th>
                            <th><?= 'Rp ' . number_format($grand_total_swp, 0, ',', '.') ?></th>
                            <th><?= 'Rp ' . number_format($grand_total_ss, 0, ',', '.') ?></th>
                            <th><?= 'Rp ' . number_format($grand_total_sp, 0, ',', '.') ?></th>
                            <th><strong><?= 'Rp ' . number_format($grand_total_saldo, 0, ',', '.') ?></strong></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        var table = $('#tabelTransaksiSimpanan').DataTable({ // Simpan instance DataTables ke variabel
            "language": {
                "lengthMenu": "Tampilkan _MENU_ entri per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _PAGE_ dari _PAGES_ (Total _TOTAL_ entri)",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total entri)",
                "search": "Cari:",
                "paginate": { "first": "Pertama", "last": "Terakhir", "next": "Berikutnya", "previous": "Sebelumnya" }
            },
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]], // Ini akan memunculkan opsi 10, 25, 50, 100, Semua
            "pageLength": 10, // Default akan menampilkan 10 data per halaman
            "scrollX": true,
            "columnDefs": [
                { "orderable": false, "targets": [0, 8] }, // No dan Aksi
                { "className": "text-end", "targets": [3, 4, 5, 6, 7] } // Saldo rata kanan
            ],
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();

                var formatRupiah = function (angkaInput) {
                    var number_string = String(angkaInput).replace(/[^,\d]/g, '').toString(),
                        split = number_string.split(','),
                        sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa),
                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                    if (ribuan) { separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
                    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                    return 'Rp ' + (rupiah ? rupiah : '0');
                };

                var saldoSWCol = 3, saldoSWPCol = 4, saldoSSCol = 5, saldoSPCol = 6, saldoAkhirCol = 7;

                var calculatePageTotalPrecise = function (columnIndex) {
                    let total = 0;
                    api.column(columnIndex, { page: 'current' }).nodes().each(function (node, index) {
                        // Mengambil dari atribut data-sort pada elemen <td>
                        let cellDataSort = $(node).attr('data-sort');
                        total += parseFloat(cellDataSort) || 0;
                    });
                    return total;
                };

                var pageTotalSW = calculatePageTotalPrecise(saldoSWCol);
                var pageTotalSWP = calculatePageTotalPrecise(saldoSWPCol);
                var pageTotalSS = calculatePageTotalPrecise(saldoSSCol);
                var pageTotalSP = calculatePageTotalPrecise(saldoSPCol);
                var pageTotalAkhir = calculatePageTotalPrecise(saldoAkhirCol);

                // Update footer untuk total halaman ini, menargetkan elemen dengan class spesifik
                // di dalam baris dengan ID 'totalPageRow'
                $('#totalPageRow').find('.total-sw-page').html(formatRupiah(pageTotalSW));
                $('#totalPageRow').find('.total-swp-page').html(formatRupiah(pageTotalSWP));
                $('#totalPageRow').find('.total-ss-page').html(formatRupiah(pageTotalSS));
                $('#totalPageRow').find('.total-sp-page').html(formatRupiah(pageTotalSP));
                $('#totalPageRow').find('.total-saldo-akhir-page').html('<strong>' + formatRupiah(pageTotalAkhir) + '</strong>');
            }
        });
    });
</script>
<?= $this->endSection() ?>
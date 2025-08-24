<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Transaksi Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/tambah') ?>" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Data
        </a>
    </div>

    <!-- ✅ Flash message -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Banner notif telat bayar -->
    <?php if (!empty($overdueCount) && $overdueCount > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Ada <strong><?= $overdueCount ?></strong> pinjaman yang <strong>telat bayar</strong> (lebih dari 30 hari sejak
            tanggal cair).
        </div>
    <?php endif; ?>

    <?php
    // Setup bunga & koneksi DB
    $db = \Config\Database::connect();
    $bunga_persen = 2 / 100; // 2% per bulan
    $total_pendapatan_bunga = 0;
    ?>

    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaksi Pinjaman</h5>
            <div class="input-group" style="max-width: 300px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari data...">
                <button class="btn btn-light" type="button" id="searchButton">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table table-bordered table-striped" id="tabelPinjaman">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="min-width: 150px;">Nama Anggota</th>
                        <th style="min-width: 80px;">No BA</th>
                        <th style="min-width: 100px;">Tgl Cair</th>
                        <th style="min-width: 80px;">Jangka</th>
                        <th style="min-width: 120px;">Pinjaman</th>
                        <th style="min-width: 120px;">Bunga Berjalan</th>
                        <th style="min-width: 120px;">Saldo</th>
                        <th style="min-width: 120px;">Status</th>
                        <th style="min-width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pinjaman)): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pinjaman as $row): ?>
                            <?php
                            // === Hitung jumlah bayar aktual
                            $jumlah_bayar = $db->table('angsuran')
                                ->where('id_pinjaman', $row->id_pinjaman)
                                ->countAllResults();

                            // === Hitung bunga berjalan
                            $pokok = $row->jumlah_pinjaman ?? 0;
                            $bunga_per_bulan = $pokok * $bunga_persen;
                            $total_bunga = $jumlah_bayar * $bunga_per_bulan;
                            $total_pendapatan_bunga += $total_bunga;

                            // === Tentukan badge status
                            if (($row->saldo_terakhir ?? 0) <= 0 || strtolower($row->status_pembayaran ?? '') === 'lunas') {
                                $statusBadge = '<span class="badge bg-success">Lunas</span>';
                            } elseif (($row->saldo_terakhir ?? 0) == $pokok || strtolower($row->status_pembayaran ?? '') === 'belum bayar') {
                                $statusBadge = '<span class="badge bg-danger">Belum Bayar</span>';
                            } else {
                                $statusBadge = '<span class="badge bg-warning text-dark">Cicilan</span>';
                            }

                            // === Tambahkan badge Telat jika flag TRUE
                            if (!empty($row->is_overdue)) {
                                $statusBadge .= ' <span class="badge bg-dark text-white ms-1">Telat</span>';
                            }
                            ?>
                            <tr class="data-row <?= !empty($row->is_overdue) ? 'row-overdue' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td class="searchable"><?= esc($row->nama ?? '-') ?></td>
                                <td class="searchable"><?= esc($row->no_ba ?? '-') ?></td>
                                <td class="searchable">
                                    <?= isset($row->tanggal_pinjaman) ? date('d/m/Y', strtotime($row->tanggal_pinjaman)) : '-' ?>
                                </td>
                                <td><?= esc($row->jangka_waktu ?? '-') ?> bln</td>
                                <td>Rp <?= number_format($pokok, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($total_bunga, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row->saldo_terakhir ?? 0, 0, ',', '.') ?></td>
                                <td class="text-center"><?= $statusBadge ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= site_url('karyawan/transaksi_pinjaman/detail/' . $row->id_pinjaman) ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <?php if (($row->saldo_terakhir ?? 0) > 0 && strtolower($row->status_pembayaran ?? '') !== 'lunas'): ?>
                                            <a href="<?= base_url('karyawan/transaksi_pinjaman/tambahAngsuran/' . $row->id_pinjaman) ?>"
                                                class="btn btn-warning btn-sm">
                                                <i class="fas fa-plus"></i> Angsuran
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noDataRow">
                            <td colspan="10" class="text-center">Belum ada data pinjaman</td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noSearchResults" style="display: none;">
                        <td colspan="10" class="text-center">Tidak ditemukan data yang cocok</td>
                    </tr>
                </tbody>

                <!-- ✅ Total pendapatan bunga -->
                <?php if (!empty($pinjaman)): ?>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Total Pendapatan Bunga:</th>
                            <th colspan="4" class="text-start text-success">
                                Rp <?= number_format($total_pendapatan_bunga, 0, ',', '.') ?>
                            </th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<style>
    /* Tabel rapih */
    #tabelPinjaman thead th {
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
    }

    #tabelPinjaman td {
        vertical-align: middle;
        padding: 0.5rem;
    }

    /* Badge status */
    .badge {
        padding: .35em .65em;
        font-size: .75em;
        font-weight: 700;
        border-radius: .25rem;
    }

    .bg-success {
        background-color: #198754 !important;
        color: #fff;
    }

    .bg-danger {
        background-color: #dc3545 !important;
        color: #fff;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #212529;
    }

    /* Highlight pinjaman telat */
    .row-overdue {
        background-color: rgba(255, 0, 0, 0.08);
    }

    /* Pencarian */
    .highlight {
        background-color: #ffff99;
        font-weight: bold;
    }

    .hidden-row {
        display: none !important;
    }
</style>

<script>
    $(document).ready(function () {
        function performSearch() {
            const searchTerm = $('#searchInput').val().toLowerCase().trim();
            let matchCount = 0;

            $('#noSearchResults').hide();

            if (searchTerm === '') {
                $('.data-row').removeClass('hidden-row');
                $('.searchable').each(function () {
                    const originalText = $(this).data('original-text') || $(this).text();
                    $(this).html(originalText);
                });
                $('#searchResults').hide();
                return;
            }

            $('.data-row').each(function () {
                let rowMatches = false;

                $(this).find('.searchable').each(function () {
                    if (!$(this).data('original-text')) {
                        $(this).data('original-text', $(this).html());
                    }

                    const cellText = $(this).text().toLowerCase();

                    if (cellText.includes(searchTerm)) {
                        const regex = new RegExp(`(${searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi');
                        const highlightedText = $(this).text().replace(regex, '<span class="highlight">$1</span>');
                        $(this).html(highlightedText);
                        rowMatches = true;
                    } else {
                        if ($(this).find('.highlight').length > 0) {
                            $(this).html($(this).data('original-text'));
                        }
                    }
                });

                if (rowMatches) {
                    $(this).removeClass('hidden-row');
                    matchCount++;
                } else {
                    $(this).addClass('hidden-row');
                }
            });

            if (matchCount > 0) {
                $('#searchCount').text(`Ditemukan ${matchCount} data yang cocok dengan "${searchTerm}"`);
                $('#noSearchResults').hide();
            } else {
                $('#searchCount').text(`Tidak ditemukan data yang cocok dengan "${searchTerm}"`);
                $('#noSearchResults').show();
            }

            $('#searchResults').show();
        }

        $('#searchInput').on('keyup', function (e) {
            performSearch();
            if (e.key === 'Enter') performSearch();
        });

        $('#searchButton').on('click', performSearch);

        $('#clearSearch').on('click', function () {
            $('#searchInput').val('');
            $('.data-row').removeClass('hidden-row');
            $('.searchable').each(function () {
                const originalText = $(this).data('original-text');
                if (originalText) $(this).html(originalText);
            });
            $('#searchResults').hide();
            $('#noSearchResults').hide();
        });

        $('#tabelPinjaman tbody').on('mouseenter', 'tr', function () {
            $(this).addClass('table-active');
        }).on('mouseleave', 'tr', function () {
            $(this).removeClass('table-active');
        });

        $('.searchable').each(function () {
            $(this).data('original-text', $(this).html());
        });
    });
</script>
<?= $this->endSection() ?>
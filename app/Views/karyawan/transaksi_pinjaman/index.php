<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Transaksi Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/tambah') ?>" class="btn btn-success">Tambah Data</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

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
                        <th style="min-width: 120px;">Saldo</th>
                        <th style="min-width: 80px;">Status</th>
                        <th style="min-width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pinjaman)): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pinjaman as $row): ?>
                            <?php
                            // Determine payment status based on the status field from latest payment
                            $statusClass = '';
                            $statusBadge = '';
                            if ($row->saldo_terakhir <= 0 || strtolower($row->status_pembayaran ?? '') == 'lunas') {
                                $paymentStatus = 'Lunas';
                                $statusBadge = '<span class="badge bg-success w-100">Lunas</span>';
                            } else if ($row->saldo_terakhir == $row->jumlah_pinjaman || strtolower($row->status_pembayaran ?? '') == 'belum bayar') {
                                $paymentStatus = 'Belum Bayar';
                                $statusBadge = '<span class="badge bg-danger w-100">Belum Bayar</span>';
                            } else {
                                $paymentStatus = 'Cicilan';
                                $statusBadge = '<span class="badge bg-warning text-dark w-100">Cicilan</span>';
                            }
                            ?>
                            <tr class="data-row">
                                <td><?= $no++ ?></td>
                                <td class="searchable"><?= esc($row->nama ?? '-') ?></td>
                                <td class="searchable"><?= esc($row->no_ba ?? '-') ?></td>
                                <td class="searchable">
                                    <?= isset($row->tanggal_pinjaman) ? date('d/m/Y', strtotime($row->tanggal_pinjaman)) : '-' ?>
                                </td>
                                <td><?= esc($row->jangka_waktu ?? '-') ?> bln</td>
                                <td>Rp <?= number_format($row->jumlah_pinjaman ?? 0, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row->saldo_terakhir ?? 0, 0, ',', '.') ?></td>
                                <td class="searchable text-center"><?= $statusBadge ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= site_url('karyawan/transaksi_pinjaman/detail/' . $row->id_pinjaman) ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>

                                        <?php if ($row->saldo_terakhir > 0 && strtolower($row->status_pembayaran ?? '') != 'lunas'): ?>
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
            </table>
        </div>

        <!-- Search Results Count -->
        <div class="card-footer" id="searchResults" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <span id="searchCount"></span>
                <button id="clearSearch" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i> Hapus Pencarian
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ensure table headers don't wrap */
    #tabelPinjaman thead th {
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
    }

    /* Make the table more compact */
    #tabelPinjaman td {
        vertical-align: middle;
        padding: 0.5rem;
    }

    /* Responsive container */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Badge styling */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .w-100 {
        width: 100%;
    }

    .bg-success {
        background-color: #198754 !important;
        color: white;
    }

    .bg-danger {
        background-color: #dc3545 !important;
        color: white;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #212529;
    }

    /* Highlight search matches */
    .highlight {
        background-color: #ffff99;
        font-weight: bold;
    }

    /* Hidden row styling */
    .hidden-row {
        display: none !important;
    }
</style>

<script>
    $(document).ready(function () {
        // Custom search functionality
        function performSearch() {
            const searchTerm = $('#searchInput').val().toLowerCase().trim();
            let matchCount = 0;

            // Remove any existing "no results" message
            $('#noSearchResults').hide();

            if (searchTerm === '') {
                // If search is empty, show all rows and remove highlights
                $('.data-row').removeClass('hidden-row');
                $('.searchable').each(function () {
                    const originalText = $(this).data('original-text') || $(this).text();
                    $(this).html(originalText);
                });
                $('#searchResults').hide();
                return;
            }

            // Process each row
            $('.data-row').each(function () {
                let rowMatches = false;

                // Check searchable cells in this row
                $(this).find('.searchable').each(function () {
                    // Store original content if not already stored
                    if (!$(this).data('original-text')) {
                        $(this).data('original-text', $(this).html());
                    }

                    const cellText = $(this).text().toLowerCase();

                    if (cellText.includes(searchTerm)) {
                        // For cells with badges, we need special handling
                        if ($(this).find('.badge').length > 0) {
                            // Don't modify the badge HTML, just mark the row as matching
                            rowMatches = true;
                        } else {
                            // Highlight the matching text for regular cells
                            const regex = new RegExp(`(${searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi');
                            const highlightedText = $(this).text().replace(regex, '<span class="highlight">$1</span>');
                            $(this).html(highlightedText);
                            rowMatches = true;
                        }
                    } else {
                        // If this cell doesn't match but has been modified, restore original
                        if ($(this).find('.highlight').length > 0) {
                            $(this).html($(this).data('original-text'));
                        }
                    }
                });

                // Show/hide row based on match
                if (rowMatches) {
                    $(this).removeClass('hidden-row');
                    matchCount++;
                } else {
                    $(this).addClass('hidden-row');
                }
            });

            // Update search results info
            if (matchCount > 0) {
                $('#searchCount').text(`Ditemukan ${matchCount} data yang cocok dengan "${searchTerm}"`);
                $('#noSearchResults').hide();
            } else {
                $('#searchCount').text(`Tidak ditemukan data yang cocok dengan "${searchTerm}"`);
                $('#noSearchResults').show();
            }

            $('#searchResults').show();
        }

        // Bind search function to input and button
        $('#searchInput').on('keyup', function (e) {
            performSearch();
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        $('#searchButton').on('click', function () {
            performSearch();
        });

        // Clear search function
        $('#clearSearch').on('click', function () {
            $('#searchInput').val('');
            $('.data-row').removeClass('hidden-row');
            $('.searchable').each(function () {
                const originalText = $(this).data('original-text');
                if (originalText) {
                    $(this).html(originalText);
                }
            });
            $('#searchResults').hide();
            $('#noSearchResults').hide();
        });

        // Highlight rows on hover
        $('#tabelPinjaman tbody').on('mouseenter', 'tr', function () {
            $(this).addClass('table-active');
        }).on('mouseleave', 'tr', function () {
            $(this).removeClass('table-active');
        });

        // Store original HTML for searchable cells
        $('.searchable').each(function () {
            $(this).data('original-text', $(this).html());
        });
    });
</script>
<?= $this->endSection() ?>
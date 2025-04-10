<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">Detail Jurnal Kas - <?= $nama_bulan ?> <?= $year ?></h3>

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
            <h5 class="mb-0">Data Transaksi <?= $nama_bulan ?> <?= $year ?></h5>
            <div class="d-flex gap-2">
                <a href="<?= base_url('admin/jurnal/monthly') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <!-- Import Button and Form -->
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                    data-bs-target="#importModal">
                    <i class="fas fa-file-import"></i> Import Data
                </button>

                <a href="<?= base_url("admin/jurnal/monthly/export/{$year}/{$month}") ?>"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-file-export"></i> Export Data
                </a>

            </div>
        </div>
        <div class="card-body">
            <!-- Summary Box -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total DUM</h5>
                            <h3 class="card-text">Rp <?= number_format($total_dum, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total DUK</h5>
                            <h3 class="card-text">Rp <?= number_format($total_duk, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Saldo</h5>
                            <h3 class="card-text">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DUM Data -->
            <h4 class="mt-4">Data DUM</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $dumData = array_filter($jurnal_kas, function ($item) {
                            return $item['kategori'] == 'DUM';
                        });

                        if (empty($dumData)):
                            ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data DUM</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dumData as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d-m-Y', strtotime($item['tanggal'])) ?></td>
                                    <td><?= $item['uraian'] ?></td>
                                    <td class="text-end">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total DUM</th>
                            <th class="text-end">Rp <?= number_format($total_dum, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- DUK Data -->
            <h4 class="mt-4">Data DUK</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $dukData = array_filter($jurnal_kas, function ($item) {
                            return $item['kategori'] == 'DUK';
                        });

                        if (empty($dukData)):
                            ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data DUK</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dukData as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d-m-Y', strtotime($item['tanggal'])) ?></td>
                                    <td><?= $item['uraian'] ?></td>
                                    <td class="text-end">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total DUK</th>
                            <th class="text-end">Rp <?= number_format($total_duk, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data <?= $nama_bulan ?> <?= $year ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/jurnal/import_excel') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file_excel" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" name="file_excel" id="file_excel" accept=".xls,.xlsx"
                            required>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Panduan Format Excel:</h6>
                        <ul class="mb-0">
                            <li>File harus memiliki kolom: <b>Tanggal</b>, <b>Uraian</b>, <b>DUM</b>, dan <b>DUK</b>
                            </li>
                            <li>Format tanggal yang didukung: DD/MM/YYYY, MM/DD/YYYY, YYYY-MM-DD</li>
                            <li>Jika tanggal tidak valid (seperti 31 Februari), akan digunakan tanggal terakhir bulan
                                tersebut</li>
                            <li>Semua data akan disesuaikan untuk bulan <?= $nama_bulan ?> <?= $year ?></li>
                            <li>Nilai DUM dan DUK harus angka. Format ribuan dengan titik (1.000) atau koma (1,000)
                                didukung</li>
                        </ul>
                    </div>
                    <!-- Hidden fields to preserve month and year -->
                    <input type="hidden" name="target_year" value="<?= $year ?>">
                    <input type="hidden" name="target_month" value="<?= $month ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
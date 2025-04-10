<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">Jurnal Kas - Data Bulanan</h3>

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
            <h5 class="mb-0">Data Jurnal Kas Per Bulan</h5>
            <div>
                <a href="<?= base_url('admin/jurnal') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke Jurnal
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Total DUM</th>
                            <th>Total DUK</th>
                            <th>Saldo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthly_data)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($monthly_data as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $item['nama_bulan'] ?>         <?= $item['tahun'] ?></td>
                                    <td class="text-end"><?= number_format($item['total_dum'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($item['total_duk'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($item['saldo'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/jurnal/monthly/details/' . $item['tahun'] . '/' . $item['bulan']) ?>"
                                            class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
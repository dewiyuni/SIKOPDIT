<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Transaksi Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Form Edit Transaksi</h5>
        </div>
        <div class="card-body">
            <form action="<?= site_url('karyawan/transaksi_simpanan/update/' . $transaksi->id_transaksi_simpanan) ?>"
                method="post">
                <?= csrf_field() ?>

                <input hidden type="date" name="tanggal" class="form-control" value="<?= esc($transaksi->tanggal) ?>"
                    required>

                <?php
                // Jenis simpanan: SW = 1, SWP = 2, SS = 3, SP = 4
                $jenis_simpanan = [
                    'SW' => 1,
                    'SWP' => 2,
                    'SS' => 3,
                    'SP' => 4
                ];
                ?>

                <?php foreach ($jenis_simpanan as $nama => $id): ?>
                    <?php
                    $detail = isset($details[$id]) ? $details[$id] : null;
                    $setor = $detail ? $detail->setor : 0;
                    $tarik = $detail ? $detail->tarik : 0;
                    $id_detail = $detail ? $detail->id_detail : '';
                    ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <input type="checkbox" id="edit_<?= strtolower($nama) ?>" name="edit_<?= strtolower($nama) ?>"
                                value="1">
                            <label for="edit_<?= strtolower($nama) ?>">Edit <?= $nama ?></label>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $nama ?> Setor</label>
                            <input type="number" name="setor_<?= strtolower($nama) ?>" class="form-control"
                                value="<?= esc($setor) ?>" min="0" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $nama ?> Tarik</label>
                            <input type="number" name="tarik_<?= strtolower($nama) ?>" class="form-control"
                                value="<?= esc($tarik) ?>" min="0" disabled>
                        </div>
                    </div>
                    <input type="hidden" name="id_detail_<?= strtolower($nama) ?>" value="<?= esc($id_detail) ?>">
                <?php endforeach; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php foreach ($jenis_simpanan as $nama => $id): ?>
            document.getElementById('edit_<?= strtolower($nama) ?>').addEventListener('change', function () {
                let setorInput = document.querySelector('input[name="setor_<?= strtolower($nama) ?>"]');
                let tarikInput = document.querySelector('input[name="tarik_<?= strtolower($nama) ?>"]');
                setorInput.disabled = !this.checked;
                tarikInput.disabled = !this.checked;
            });
        <?php endforeach; ?>
    });
</script>
<?= $this->endSection() ?>
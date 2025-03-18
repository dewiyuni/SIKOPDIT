<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Transaksi Simpanan</h3>
        <a href="javascript:history.back()" class="btn btn-warning">Kembali</a>
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
                ];
                ?>

                <?php foreach ($jenis_simpanan as $nama => $id): ?>
                    <?php
                    $detail = isset($details[$id]) ? $details[$id] : null;
                    $setor = $detail ? intval($detail->setor) : 0;
                    $tarik = $detail ? intval($detail->tarik) : 0;
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
                            <input type="text" name="setor_<?= strtolower($nama) ?>" class="form-control format-number"
                                value="<?= number_format($setor, 0, ',', '.') ?>" min="0" disabled
                                data-original-value="<?= $setor ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $nama ?> Tarik</label>
                            <input type="text" name="tarik_<?= strtolower($nama) ?>" class="form-control format-number"
                                value="<?= number_format($tarik, 0, ',', '.') ?>" min="0" disabled
                                data-original-value="<?= $tarik ?>">
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
        // Handle checkboxes for enabling/disabling inputs
        <?php foreach ($jenis_simpanan as $nama => $id): ?>
            document.getElementById('edit_<?= strtolower($nama) ?>').addEventListener('change', function () {
                let setorInput = document.querySelector('input[name="setor_<?= strtolower($nama) ?>"]');
                let tarikInput = document.querySelector('input[name="tarik_<?= strtolower($nama) ?>"]');
                setorInput.disabled = !this.checked;
                tarikInput.disabled = !this.checked;

                // Reset to original value when enabling to avoid formatting issues
                if (this.checked) {
                    setorInput.value = formatRupiah(setorInput.dataset.originalValue);
                    tarikInput.value = formatRupiah(tarikInput.dataset.originalValue);
                }
            });
        <?php endforeach; ?>

        // Format inputs and prepare for submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            // Remove formatting before submitting
            document.querySelectorAll('.format-number').forEach(function (input) {
                if (!input.disabled) {
                    // Only process enabled fields (those being edited)
                    input.value = input.value.replace(/\./g, '');
                }
            });
        });

        // Format input as user types
        document.querySelectorAll('.format-number').forEach(function (input) {
            input.addEventListener('input', function () {
                // Remove non-numeric characters first
                let value = this.value.replace(/[^0-9]/g, '');
                // Then format with thousand separators
                this.value = formatRupiah(value);
            });
        });

        function formatRupiah(angka) {
            if (!angka) return "0";
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    });
</script>
<?= $this->endSection() ?>
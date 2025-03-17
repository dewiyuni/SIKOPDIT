<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Angsuran</h3>
        <a href="<?= base_url('karyawan/transaksi_pinjaman/detail/' . $pinjaman->id_pinjaman) ?>"
            class="btn btn-warning">
            Kembali
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/update/' . $angsuran->id_angsuran) ?>" method="post">
            <div class="card p-3">
                <table class="table table-bordered">
                    <tr>
                        <th>Tanggal Angsuran</th>
                        <td>
                            <input type="date" name="tanggal_angsuran" value="<?= $angsuran->tanggal_angsuran ?>"
                                required>
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Angsuran</th>
                        <td>
                            <input type="number" name="jumlah_angsuran" value="<?= $angsuran->jumlah_angsuran ?>"
                                required>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="btn btn-success mt-3">Update</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
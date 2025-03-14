<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Transaksi Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan/import_simpanan') ?>" class="btn btn-success">upload
            Excel</a>


    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <div style="overflow-x: auto;">
            <table class="table table-bordered">
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($transaksi as $row):
                        $saldo_akhir = $row->saldo_sw + $row->saldo_swp + $row->saldo_ss + $row->saldo_sp; ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row->nama_anggota ?? 'Tidak Diketahui' ?></td>
                            <td><?= number_format($row->no_ba, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row->saldo_sw, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row->saldo_swp, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row->saldo_ss, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row->saldo_sp, 0, ',', '.'); ?></td>
                            <td><strong>Rp <?= number_format($saldo_akhir, 0, ',', '.'); ?></strong></td>
                            <td>
                                <a href="<?= base_url('karyawan/transaksi_simpanan/detail/' . $row->id_anggota) ?>"
                                    class="btn btn-info btn-sm">Detail</a>

                                <a href="<?= site_url('karyawan/transaksi_simpanan/setor_form/' . $row->id_anggota) ?>"
                                    class="btn btn-success btn-sm">
                                    Setor
                                </a>
                                <a href="<?= site_url('karyawan/transaksi_simpanan/tarik_form/' . $row->id_anggota) ?>"
                                    class="btn btn-danger btn-sm">
                                    Tarik
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript untuk menampilkan form upload saat tombol diklik -->
<script>
    document.getElementById("btnImport").addEventListener("click", function () {
        document.getElementById("formImport").style.display = "block";
        this.style.display = "none"; // Sembunyikan tombol setelah diklik
    });
</script>

<?= $this->endSection() ?>
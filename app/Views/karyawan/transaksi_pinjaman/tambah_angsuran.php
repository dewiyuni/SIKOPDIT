<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Angsuran - <?= esc($pinjaman->nama) ?></h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/') ?>" class="btn btn-warning">Kembali</a>
    </div>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="post" action="<?= site_url('karyawan/transaksi_pinjaman/simpan_angsuran') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id_pinjaman" value="<?= esc($pinjaman->id_pinjaman) ?>">

            <div class="form-group">
                <label for="tanggal_angsuran">Tanggal Angsuran</label>
                <input type="date" name="tanggal_angsuran" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="jumlah_angsuran">Jumlah Angsuran</label>
                <input type="text" id="jumlah_angsuran" class="form-control" required oninput="formatRibuan(this)"
                    autocomplete="off">
                <input type="hidden" name="jumlah_angsuran" id="jumlah_angsuran_hidden">
            </div>

            <button type="submit" class="btn btn-success">Simpan Angsuran</button>
        </form>
    </div>
</div>

<script>
    function formatRibuan(input) {
        let angka = input.value.replace(/\D/g, ""); // Hapus semua non-angka
        input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Tambah titik sebagai pemisah ribuan

        // Simpan nilai asli tanpa pemisah ke input hidden untuk dikirim ke server
        document.getElementById("jumlah_angsuran_hidden").value = angka;
    }
</script>

<?= $this->endSection() ?>
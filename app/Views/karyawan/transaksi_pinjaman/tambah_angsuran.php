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
                <input type="text" id="jumlah_angsuran" class="form-control" required
                    oninput="formatRibuan(this, 'jumlah_angsuran_hidden')" autocomplete="off">
                <input type="hidden" name="jumlah_angsuran" id="jumlah_angsuran_hidden">
            </div>

            <div class="form-group">
                <label for="bunga">Bunga</label>
                <input type="text" id="bunga" class="form-control" required
                    oninput="formatRibuan(this, 'bunga_hidden')">
                <input type="hidden" name="bunga" id="bunga_hidden">
            </div>

            <button type="submit" class="btn btn-success">Simpan Angsuran</button>
        </form>
    </div>
</div>

<script>
    function formatRibuan(input, hiddenFieldId) {
        let angka = input.value.replace(/\D/g, ""); // Hapus semua non-angka
        input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Tambah titik pemisah ribuan
        document.getElementById(hiddenFieldId).value = angka; // Simpan tanpa titik
    }

    document.getElementById("bunga").addEventListener("input", function () {
        document.getElementById("bunga_hidden").value = this.value.replace(/\D/g, "");
    });

</script>

<?= $this->endSection() ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Transaksi Tarik Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('karyawan/transaksi_simpanan/tarik') ?>" method="post" onsubmit="return cekSaldo()">
            <?= csrf_field() ?>
            <input type="hidden" name="id_anggota" value="<?= esc($anggota->id_anggota ?? '') ?>">
            <input type="hidden" name="id_jenis_simpanan" value="<?= esc($id_simpanan_sukarela ?? '') ?>">

            <!-- Jumlah Penarikan -->
            <label for="tarik_ss">Jumlah Penarikan Simpanan Sukarela:</label>
            <input type="text" id="tarik_ss" class="form-control" placeholder="Masukkan jumlah Simpanan Sukarela"
                required oninput="formatRibuan(this)">
            <input type="hidden" name="tarik_ss" id="tarik_ss_hidden">

            <button type="submit" class="btn btn-danger mt-3">Tarik</button>
        </form>
    </div>
</div>

<!-- JavaScript untuk Format Ribuan & Cek Saldo -->
<script>
    function formatRibuan(input) {
        // Menghapus karakter selain angka
        let angka = input.value.replace(/\D/g, '');

        // Format angka dengan pemisah ribuan
        let formatted = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Tampilkan angka yang sudah diformat
        input.value = formatted;

        // Simpan angka asli (tanpa titik) di input hidden
        document.getElementById("tarik_ss_hidden").value = angka;
    }

    function cekSaldo() {
        let saldo = parseInt(document.getElementById("saldo_ss_hidden").value);
        let tarik = parseInt(document.getElementById("tarik_ss_hidden").value);

        if (tarik > saldo) {
            alert("Saldo tidak mencukupi untuk penarikan!");
            return false; // Mencegah form terkirim
        }
        return true;
    }
</script>

<?= $this->endSection() ?>
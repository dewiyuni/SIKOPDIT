<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Transaksi Setor Simpanan</h3>
        <a href="<?= site_url('karyawan/transaksi_simpanan') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('karyawan/transaksi_simpanan/setor') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_anggota" value="<?= esc($anggota->id_anggota ?? '') ?>">

            <!-- Ambil ID Jenis Simpanan dari Database -->
            <input type="hidden" name="id_jenis_simpanan_sw" value="<?= esc($id_simpanan_wajib ?? '') ?>">
            <input type="hidden" name="id_jenis_simpanan_ss" value="<?= esc($id_simpanan_sukarela ?? '') ?>">

            <!-- Simpanan Wajib -->
            <label for="setor_sw">Simpanan Wajib:</label>
            <input type="text" id="setor_sw" class="form-control" placeholder="Masukkan jumlah setoran Wajib" required
                oninput="formatRibuan(this)">
            <input type="hidden" name="setor_sw" id="setor_sw_hidden">

            <!-- Simpanan Sukarela -->
            <label for="setor_ss">Simpanan Sukarela:</label>
            <input type="text" id="setor_ss" class="form-control" placeholder="Masukkan jumlah setoran Sukarela"
                required oninput="formatRibuan(this)">
            <input type="hidden" name="setor_ss" id="setor_ss_hidden">

            <button type="submit" class="btn btn-primary mt-3">Setor</button>
        </form>
    </div>
</div>

<!-- JavaScript untuk Memisahkan Ribuan -->
<script>
    function formatRibuan(input) {
        // Menghapus semua karakter selain angka
        let angka = input.value.replace(/\D/g, '');

        // Format angka dengan pemisah ribuan
        let formatted = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Tampilkan angka yang sudah diformat
        input.value = formatted;

        // Simpan angka asli (tanpa titik) di input hidden
        let hiddenInput = document.getElementById(input.id + "_hidden");
        if (hiddenInput) {
            hiddenInput.value = angka;
        }
    }
</script>

<?= $this->endSection() ?>
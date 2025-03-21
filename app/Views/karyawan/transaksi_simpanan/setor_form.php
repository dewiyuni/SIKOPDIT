<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
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
            <div class="mb-3">
                <label for="setor_ss" class="form-label">Simpanan Wajib:</label>
                <input type="text" id="setor_ss" class="form-control" placeholder="Masukkan jumlah setoran Wajib"
                    required oninput="formatRibuan(this)" value="5000">
                <input type="hidden" name="setor_ss" id="setor_ss_hidden" value="5000">
            </div>

            <!-- Simpanan Sukarela -->
            <div class="mb-3">
                <label for="setor_sp" class="form-label">Simpanan Sukarela:</label>
                <input type="text" id="setor_sp" class="form-control" placeholder="Masukkan jumlah setoran Sukarela"
                    oninput="formatRibuan(this)">
                <input type="hidden" name="setor_sp" id="setor_sp_hidden">
            </div>

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

    // Pastikan nilai default sudah diformat dengan benar saat halaman dimuat
    window.onload = function () {
        // Set default value for Simpanan Wajib
        let setorSwInput = document.getElementById('setor_sw');
        if (setorSwInput && setorSwInput.value === '') {
            setorSwInput.value = '5.000';
            document.getElementById('setor_sw_hidden').value = '5000';
        }

        // Initialize other fields if needed
        formatRibuan(document.getElementById('setor_sw'));
    };
</script>

<?= $this->endSection() ?>
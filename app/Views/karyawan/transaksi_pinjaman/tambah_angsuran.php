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
    <br>
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
                    oninput="formatRibuan(this, 'jumlah_angsuran_hidden'); hitungTotalBayar();" autocomplete="off">
                <input type="hidden" name="jumlah_angsuran" id="jumlah_angsuran_hidden">
            </div>

            <div class="form-group">
                <label for="bunga">Bunga (%)</label>
                <div class="input-group">
                    <input type="text" id="bunga" class="form-control" required
                        oninput="formatBunga(this); hitungTotalBayar();">
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                    <input type="hidden" name="bunga" id="bunga_hidden">
                </div>
            </div>

            <div class="form-group">
                <label for="total_bayar">Total Bayar</label>
                <input type="text" id="total_bayar" class="form-control" readonly>
                <input type="hidden" name="total_bayar" id="total_bayar_hidden">
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
        return angka;
    }

    function formatBunga(input) {
        // Hanya izinkan angka dan titik desimal
        let nilai = input.value.replace(/[^\d.]/g, "");

        // Pastikan hanya ada satu titik desimal
        let parts = nilai.split('.');
        if (parts.length > 2) {
            nilai = parts[0] + '.' + parts.slice(1).join('');
        }

        // Batasi hingga 2 digit desimal
        if (parts.length > 1 && parts[1].length > 2) {
            nilai = parts[0] + '.' + parts[1].substring(0, 2);
        }

        input.value = nilai;
        document.getElementById("bunga_hidden").value = nilai;
        return nilai;
    }

    function hitungTotalBayar() {
        // Ambil nilai jumlah angsuran
        const jumlahAngsuran = parseInt(document.getElementById("jumlah_angsuran_hidden").value || 0);

        // Ambil nilai bunga (dalam persen)
        const bungaPersen = parseFloat(document.getElementById("bunga_hidden").value || 0);

        // Hitung jumlah bunga
        const jumlahBunga = jumlahAngsuran * (bungaPersen / 100);

        // Hitung total bayar
        const totalBayar = jumlahAngsuran + jumlahBunga;

        // Tampilkan total bayar dengan format ribuan
        document.getElementById("total_bayar").value = totalBayar.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        document.getElementById("total_bayar_hidden").value = totalBayar;
    }

    // Inisialisasi perhitungan saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function () {
        hitungTotalBayar();
    });
</script>

<?= $this->endSection() ?>
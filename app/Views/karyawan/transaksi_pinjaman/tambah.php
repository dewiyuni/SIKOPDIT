<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Tambah Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/simpan') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="id_anggota" class="form-label">Nama Anggota</label>
                        <select name="id_anggota" class="form-control" required>
                            <option value="" disabled selected>-- Pilih Anggota --</option>
                            <?php foreach ($anggota as $a): ?>
                                <option value="<?= esc($a->id_anggota) ?>" <?= old('id_anggota') == $a->id_anggota ? 'selected' : '' ?>>
                                    <?= esc($a->nama) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (session('errors.id_anggota')): ?>
                            <small class="text-danger"><?= session('errors.id_anggota') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="tanggal_pinjaman" class="form-label">Tanggal Cair</label>
                        <input type="date" name="tanggal_pinjaman" class="form-control" required
                            value="<?= old('tanggal_pinjaman') ?>">
                        <?php if (session('errors.tanggal_pinjaman')): ?>
                            <small class="text-danger"><?= session('errors.tanggal_pinjaman') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jangka_waktu" class="form-label">Jangka Waktu (bulan)</label>
                        <input type="number" name="jangka_waktu" id="jangka_waktu" class="form-control" required min="1"
                            value="<?= old('jangka_waktu') ?>" onchange="hitungAngsuranBulanan()">
                        <?php if (session('errors.jangka_waktu')): ?>
                            <small class="text-danger"><?= session('errors.jangka_waktu') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jaminan" class="form-label">Jaminan (Opsional)</label>
                        <input type="text" name="jaminan" class="form-control" value="<?= old('jaminan') ?>">
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="jumlah_pinjaman" class="form-label">Besar Pinjaman</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="jumlah_pinjaman" class="form-control" required
                                oninput="formatRibuan(this); hitungBunga(); hitungAngsuranBulanan();"
                                autocomplete="off">
                        </div>
                        <input type="hidden" name="jumlah_pinjaman" id="jumlah_pinjaman_hidden"
                            value="<?= old('jumlah_pinjaman') ?>">
                        <?php if (session('errors.jumlah_pinjaman')): ?>
                            <small class="text-danger"><?= session('errors.jumlah_pinjaman') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="bunga" class="form-label">Bunga (2.5%)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="bunga_display" class="form-control" disabled
                                style="background-color: #f8f9fa;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Format angka dengan pemisah ribuan
    function formatRibuan(input) {
        let angka = input.value.replace(/\D/g, ""); // Hapus semua non-angka
        input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Tambah titik pemisah ribuan

        // Simpan nilai asli tanpa titik ke input hidden untuk dikirim ke server
        document.getElementById("jumlah_pinjaman_hidden").value = angka;
        return angka;
    }

    // Format angka untuk display
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Hitung bunga dan total pinjaman
    function hitungBunga() {
        const pinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const bungaPersentase = 2.5;
        const bungaRupiah = pinjaman * (bungaPersentase / 100);
        const totalPinjaman = pinjaman + bungaRupiah;

        document.getElementById("bunga_display").value = formatNumber(Math.round(bungaRupiah));
        document.getElementById("total_pinjaman").value = formatNumber(Math.round(totalPinjaman));
    }

    // Hitung angsuran bulanan
    function hitungAngsuranBulanan() {
        const pinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const jangkaWaktu = parseInt(document.getElementById("jangka_waktu").value || 0);

        if (pinjaman > 0 && jangkaWaktu > 0) {
            const angsuranPokok = pinjaman / jangkaWaktu;
            const bungaPerBulan = pinjaman * (2.5 / 100);
            const totalAngsuranPerBulan = angsuranPokok + bungaPerBulan;

            document.getElementById("angsuran_bulanan").value = formatNumber(Math.round(totalAngsuranPerBulan));
        } else {
            document.getElementById("angsuran_bulanan").value = "0";
        }
    }

    // Initialize form if there are old values
    document.addEventListener("DOMContentLoaded", function () {
        const oldPinjaman = "<?= old('jumlah_pinjaman') ?>";
        if (oldPinjaman) {
            document.getElementById("jumlah_pinjaman").value = formatNumber(oldPinjaman);
            document.getElementById("jumlah_pinjaman_hidden").value = oldPinjaman;
            hitungBunga();
            hitungAngsuranBulanan();
        }
    });
</script>

<?= $this->endSection() ?>
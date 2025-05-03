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
                            value="<?= old('jangka_waktu') ?>" onchange="hitungSimulasi();">
                        <?php if (session('errors.jangka_waktu')): ?>
                            <small class="text-danger"><?= session('errors.jangka_waktu') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jaminan" class="form-label">Jaminan (Opsional) | >3 Juta (Wajib)</label>
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
                                oninput="formatRibuan(this); hitungSimulasi();" autocomplete="off">
                        </div>
                        <input type="hidden" name="jumlah_pinjaman" id="jumlah_pinjaman_hidden"
                            value="<?= old('jumlah_pinjaman') ?>">
                        <?php if (session('errors.jumlah_pinjaman')): ?>
                            <small class="text-danger"><?= session('errors.jumlah_pinjaman') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="bunga_simpanan" class="form-label">Bunga Simpanan (2.5%)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="bunga_simpanan_display" class="form-control" disabled
                                style="background-color: #f8f9fa;">
                            <input type="hidden" name="bunga_simpanan" id="bunga_simpanan_hidden">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_diterima" class="form-label">Jumlah Diterima</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="jumlah_diterima" class="form-control" disabled
                                style="background-color: #f8f9fa;">
                            <input type="hidden" name="jumlah_diterima" id="jumlah_diterima_hidden">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Simulasi Pembayaran -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Simulasi Pembayaran Angsuran</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong>Angsuran Pokok:</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="angsuran_pokok" class="form-control" disabled
                                                style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong>Bunga Per Angsuran (2%):</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="bunga_angsuran" class="form-control" disabled
                                                style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong>Total Angsuran Per Bulan:</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="total_angsuran" class="form-control" disabled
                                                style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Angsuran Pokok</th>
                                            <th>Bunga (2%)</th>
                                            <th>Total Bayar</th>
                                            <th>Sisa Pinjaman</th>
                                        </tr>
                                    </thead>
                                    <tbody id="simulasi_body">
                                        <!-- Data simulasi akan diisi oleh JavaScript -->
                                    </tbody>
                                    <tfoot class="table-info">
                                        <tr>
                                            <th colspan="1">Total</th>
                                            <th id="total_pokok">Rp 0</th>
                                            <th id="total_bunga">Rp 0</th>
                                            <th id="total_pembayaran">Rp 0</th>
                                            <th>-</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
        // Pastikan input adalah angka, jika bukan kembalikan "0" atau string kosong
        if (isNaN(number) || number === null) {
            return "0"; // Atau ""
        }
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Hitung semua simulasi
    function hitungSimulasi() {
        const pinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const jangkaWaktu = parseInt(document.getElementById("jangka_waktu").value || 0);

        if (pinjaman <= 0 || jangkaWaktu <= 0) {
            resetSimulasi();
            return;
        }

        // Hitung bunga simpanan (2.5%) - Potongan di awal
        const bungaSimpananPersen = 2.5;
        const bungaSimpanan = Math.round(pinjaman * (bungaSimpananPersen / 100));
        const jumlahDiterima = pinjaman - bungaSimpanan;

        // Tampilkan bunga simpanan dan jumlah diterima
        document.getElementById("bunga_simpanan_display").value = formatNumber(bungaSimpanan);
        document.getElementById("bunga_simpanan_hidden").value = bungaSimpanan;
        document.getElementById("jumlah_diterima").value = formatNumber(jumlahDiterima);
        document.getElementById("jumlah_diterima_hidden").value = jumlahDiterima;

        // Hitung angsuran pokok per bulan (Jumlah Pinjaman Awal / Jangka Waktu)
        const angsuranPokok = Math.round(pinjaman / jangkaWaktu);

        // Hitung bunga angsuran (2%) - 2% dari jumlah pinjaman awal, nilai ini TETAP setiap bulan
        const bungaPersen = 2;
        // --- PERUBAHAN LOGIKA DI SINI ---
        const bungaAngsuran = Math.round(pinjaman * (bungaPersen / 100)); // 2% dari pinjaman awal
        // -------------------------------


        // Hitung total angsuran per bulan (Angsuran Pokok + Bunga Angsuran Tetap)
        const totalAngsuran = angsuranPokok + bungaAngsuran;

        // Tampilkan informasi angsuran
        document.getElementById("angsuran_pokok").value = formatNumber(angsuranPokok);
        document.getElementById("bunga_angsuran").value = formatNumber(bungaAngsuran); // Menampilkan bunga angsuran tetap per bulan
        document.getElementById("total_angsuran").value = formatNumber(totalAngsuran);

        // Buat tabel simulasi
        let simulasiBody = document.getElementById("simulasi_body");
        simulasiBody.innerHTML = '';

        let sisaPinjaman = pinjaman;
        let totalPokokSum = 0; // Menggunakan nama berbeda agar tidak bentrok dengan id elemen
        let totalBungaSum = 0;
        let totalPembayaranSum = 0;

        for (let i = 1; i <= jangkaWaktu; i++) {
            // Untuk simulasi per bulan, sisa pinjaman dikurangi pokok, tapi total bunga diakumulasi
            // Sisa pinjaman hanya berkurang sejumlah pokok
            sisaPinjaman -= angsuranPokok;

            // Akumulasi total untuk footer
            totalPokokSum += angsuranPokok;
            totalBungaSum += bungaAngsuran; // Akumulasikan bunga tetap setiap bulan
            totalPembayaranSum += totalAngsuran; // Akumulasikan total angsuran tetap setiap bulan


            let row = document.createElement('tr');
            row.innerHTML = `
                <td>${i}</td>
                <td>Rp ${formatNumber(angsuranPokok)}</td>
                <td>Rp ${formatNumber(bungaAngsuran)}</td> <!-- Tampilkan bunga angsuran tetap -->
                <td>Rp ${formatNumber(totalAngsuran)}</td> <!-- Tampilkan total angsuran tetap -->
                <td>Rp ${formatNumber(Math.max(0, sisaPinjaman))}</td>
            `;
            simulasiBody.appendChild(row);
        }

        // Update totals di footer
        document.getElementById("total_pokok").innerText = `Rp ${formatNumber(totalPokokSum)}`;
        document.getElementById("total_bunga").innerText = `Rp ${formatNumber(totalBungaSum)}`;
        document.getElementById("total_pembayaran").innerText = `Rp ${formatNumber(totalPembayaranSum)}`;
    }

    // Reset simulasi
    function resetSimulasi() {
        document.getElementById("bunga_simpanan_display").value = "0";
        document.getElementById("bunga_simpanan_hidden").value = "0";
        document.getElementById("jumlah_diterima").value = "0";
        document.getElementById("jumlah_diterima_hidden").value = "0";
        document.getElementById("angsuran_pokok").value = "0";
        document.getElementById("bunga_angsuran").value = "0";
        document.getElementById("total_angsuran").value = "0";
        document.getElementById("simulasi_body").innerHTML = '';
        document.getElementById("total_pokok").innerText = "Rp 0";
        document.getElementById("total_bunga").innerText = "Rp 0";
        document.getElementById("total_pembayaran").innerText = "Rp 0";
    }

    // Initialize form if there are old values
    document.addEventListener("DOMContentLoaded", function () {
        const oldPinjaman = "<?= old('jumlah_pinjaman') ?>";
        if (oldPinjaman) {
            // Format input display
            document.getElementById("jumlah_pinjaman").value = formatNumber(parseInt(oldPinjaman));
            // Set hidden value (already numeric from old())
            document.getElementById("jumlah_pinjaman_hidden").value = oldPinjaman;
            // Hitung ulang simulasi
            hitungSimulasi();
        } else {
            // Reset simulasi if no old value (initial load)
            resetSimulasi();
        }
    });

    // Panggil hitungSimulasi() saat halaman pertama kali dimuat
    // Ini penting jika ada nilai default atau old() yang sudah terisi di input
    // document.addEventListener('DOMContentLoaded', hitungSimulasi); // Sudah dihandle di listener lain, tapi bisa juga begini jika logic init terpisah
</script>

<?= $this->endSection() ?>
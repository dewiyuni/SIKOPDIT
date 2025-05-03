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
                            value="<?= old('jangka_waktu') ?>" onchange="hitungAngsuranBulanan(); updateSimulasi();">
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
                                oninput="formatRibuan(this); hitungBunga(); hitungAngsuranBulanan(); updateSimulasi();"
                                autocomplete="off">
                        </div>
                        <input type="hidden" name="jumlah_pinjaman" id="jumlah_pinjaman_hidden"
                            value="<?= old('jumlah_pinjaman') ?>">
                        <?php if (session('errors.jumlah_pinjaman')): ?>
                            <small class="text-danger"><?= session('errors.jumlah_pinjaman') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="bunga" class="form-label">Bunga Simpanan (2.5%)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="bunga_display" class="form-control" disabled
                                style="background-color: #f8f9fa;">
                            <input type="hidden" name="bunga" id="bunga_hidden">
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

                    <div class="form-group mb-3">
                        <label for="angsuran_pokok" class="form-label">Angsuran Pokok per Bulan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="angsuran_pokok" class="form-control" disabled
                                style="background-color: #f8f9fa;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Simulasi Pembayaran -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Simulasi Pembayaran Angsuran</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th>Bulan</th>
                                    <th>Angsuran Pokok</th>
                                    <th>Bunga Transaksi (2%)</th>
                                    <th>Total Bayar</th>
                                    <th>Sisa Pinjaman</th>
                                </tr>
                            </thead>
                            <tbody id="simulasi_body">
                                <!-- Data simulasi akan diisi oleh JavaScript -->
                            </tbody>
                            <tfoot class="table-info">
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th id="total_bunga_transaksi">Rp 0</th>
                                    <th id="total_pembayaran">Rp 0</th>
                                    <th>-</th>
                                </tr>
                            </tfoot>
                        </table>
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

    // Hitung bunga dan jumlah diterima
    function hitungBunga() {
        const pinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const bungaPersentase = 2.5;
        const bungaRupiah = Math.round(pinjaman * (bungaPersentase / 100));
        const jumlahDiterima = pinjaman - bungaRupiah;

        document.getElementById("bunga_display").value = formatNumber(bungaRupiah);
        document.getElementById("bunga_hidden").value = bungaRupiah;
        document.getElementById("jumlah_diterima").value = formatNumber(jumlahDiterima);
        document.getElementById("jumlah_diterima_hidden").value = jumlahDiterima;
    }

    // Hitung angsuran bulanan
    function hitungAngsuranBulanan() {
        const pinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const jangkaWaktu = parseInt(document.getElementById("jangka_waktu").value || 0);

        if (pinjaman > 0 && jangkaWaktu > 0) {
            const angsuranPokok = Math.round(pinjaman / jangkaWaktu);
            document.getElementById("angsuran_pokok").value = formatNumber(angsuranPokok);
        } else {
            document.getElementById("angsuran_pokok").value = "0";
        }
    }

    // Update tabel simulasi pembayaran
    function updateSimulasi() {
        const pinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const jangkaWaktu = parseInt(document.getElementById("jangka_waktu").value || 0);
        let simulasiBody = document.getElementById("simulasi_body");
        simulasiBody.innerHTML = '';

        if (pinjaman > 0 && jangkaWaktu > 0) {
            const angsuranPokok = Math.round(pinjaman / jangkaWaktu);
            let sisaPinjaman = pinjaman;
            let totalBungaTransaksi = 0;
            let totalPembayaran = 0;

            for (let i = 1; i <= jangkaWaktu; i++) {
                const bungaTransaksi = Math.round(angsuranPokok * 0.02); // Bunga 2% per transaksi
                const totalBayar = angsuranPokok + bungaTransaksi;
                sisaPinjaman -= angsuranPokok;

                totalBungaTransaksi += bungaTransaksi;
                totalPembayaran += totalBayar;

                let row = document.createElement('tr');
                row.innerHTML = `
                    <td>${i}</td>
                    <td>Rp ${formatNumber(angsuranPokok)}</td>
                    <td>Rp ${formatNumber(bungaTransaksi)}</td>
                    <td>Rp ${formatNumber(totalBayar)}</td>
                    <td>Rp ${formatNumber(Math.max(0, sisaPinjaman))}</td>
                `;
                simulasiBody.appendChild(row);
            }

            document.getElementById("total_bunga_transaksi").innerText = `Rp ${formatNumber(totalBungaTransaksi)}`;
            document.getElementById("total_pembayaran").innerText = `Rp ${formatNumber(totalPembayaran)}`;
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
            updateSimulasi();
        }
    });
</script>

<?= $this->endSection() ?>
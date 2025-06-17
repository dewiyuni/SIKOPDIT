<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Angsuran</h3>
        <a href="<?= base_url('karyawan/transaksi_pinjaman/detail/' . $pinjaman->id_pinjaman) ?>"
            class="btn btn-warning">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Loan Information Card -->
    <div class="card p-3 mb-3 bg-light">
        <h5>Informasi Pinjaman</h5>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 150px;">Total Pinjaman</th>
                        <td>: Rp <?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pinjaman</th>
                        <td>: <?= date('d M Y', strtotime($pinjaman->tanggal_pinjaman)) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 150px;">Jangka Waktu</th>
                        <td>: <?= $pinjaman->jangka_waktu ?> bulan</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>:
                            <span class="badge bg-<?= ($pinjaman->status == 'lunas') ? 'success' : 'warning' ?>">
                                <?= strtoupper($pinjaman->status) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Formulir Edit Angsuran</h5>
        </div>
        <div class="card-body">
            <form action="<?= base_url('karyawan/transaksi_pinjaman/update/' . $angsuran->id_angsuran) ?>"
                method="post">
                <?= csrf_field() ?>
                <input type="hidden" id="jumlah_pinjaman_val" value="<?= $pinjaman->jumlah_pinjaman ?>">

                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%;">Tanggal Angsuran</th>
                        <td>
                            <input type="date" name="tanggal_angsuran" class="form-control"
                                value="<?= esc($angsuran->tanggal_angsuran) ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Angsuran (Pokok)</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <!-- PERBAIKAN: type="text" dan value diformat -->
                                <input type="text" id="jumlah_angsuran" name="jumlah_angsuran" class="form-control"
                                    value="<?= number_format($angsuran->jumlah_angsuran, 0, ',', '.') ?>" required>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Bunga (%)</th>
                        <td>
                            <div class="input-group">
                                <input type="text" id="bunga" name="bunga" class="form-control"
                                    value="<?= rtrim(rtrim(number_format($angsuran->bunga, 2, ',', '.'), '0'), ',') ?>"
                                    required placeholder="Contoh: 2,5">
                                <span class="input-group-text">%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Bunga</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="jumlah_bunga_display" class="form-control" readonly
                                    style="background-color: #e9ecef;">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Bayar (Pokok + Bunga)</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="total_angsuran_display" class="form-control" readonly
                                    style="background-color: #e9ecef;">
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="text-end">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Ambil elemen-elemen yang dibutuhkan
        const inputAngsuran = document.getElementById('jumlah_angsuran');
        const inputBunga = document.getElementById('bunga');
        const jumlahPinjaman = parseFloat(document.getElementById('jumlah_pinjaman_val').value) || 0;

        // Fungsi untuk membersihkan nilai dari format Rupiah dan mengubah koma desimal
        function parseValue(valueStr) {
            if (typeof valueStr !== 'string') return 0;
            return parseFloat(valueStr.replace(/\./g, '').replace(',', '.')) || 0;
        }

        // Fungsi utama untuk kalkulasi
        function calculateTotal() {
            const pokokAngsuran = parseValue(inputAngsuran.value);
            const bungaPersen = parseValue(inputBunga.value);

            const jumlahBunga = (bungaPersen / 100) * jumlahPinjaman;
            const totalBayar = pokokAngsuran + jumlahBunga;

            document.getElementById('jumlah_bunga_display').value = formatToCurrency(jumlahBunga);
            document.getElementById('total_angsuran_display').value = formatToCurrency(totalBayar);
        }

        // Fungsi untuk memformat angka menjadi format Rupiah (IDR)
        function formatToCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(Math.round(value));
        }

        // Fungsi untuk memformat input jumlah angsuran saat diketik
        function formatNumberInput(event) {
            let value = event.target.value.replace(/[^0-9]/g, '');
            event.target.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        }

        // Tambahkan event listener
        inputAngsuran.addEventListener('input', formatNumberInput);
        inputAngsuran.addEventListener('input', calculateTotal);
        inputBunga.addEventListener('input', calculateTotal);

        // Panggil fungsi kalkulasi saat halaman pertama kali dimuat
        calculateTotal();
    });
</script>
<?= $this->endSection() ?>
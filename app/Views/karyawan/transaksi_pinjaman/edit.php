<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Angsuran</h3>
        <a href="<?= base_url('karyawan/transaksi_pinjaman/detail/' . $pinjaman->id_pinjaman) ?>"
            class="btn btn-warning">
            Kembali
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <!-- Loan Information Card -->
    <div class="card p-3 mb-3">
        <h5>Informasi Pinjaman</h5>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th>Total Pinjaman</th>
                        <td>Rp <?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pinjaman</th>
                        <td><?= date('d M Y', strtotime($pinjaman->tanggal_pinjaman)) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th>Jangka Waktu</th>
                        <td><?= $pinjaman->jangka_waktu ?> bulan</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php if ($pinjaman->status == 'lunas'): ?>
                                <span class="badge bg-success">LUNAS</span>
                            <?php else: ?>
                                <span class="badge bg-warning">AKTIF</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/update/' . $angsuran->id_angsuran) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" id="jumlah_pinjaman" value="<?= $pinjaman->jumlah_pinjaman ?>">
            <div class="card p-3">
                <table class="table table-bordered">
                    <tr>
                        <th>Tanggal Angsuran</th>
                        <td>
                            <input type="date" name="tanggal_angsuran" class="form-control"
                                value="<?= esc($angsuran->tanggal_angsuran) ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Angsuran</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="jumlah_angsuran" class="form-control"
                                    value="<?= esc($angsuran->jumlah_angsuran) ?>" required oninput="calculateTotal();">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Bunga (%)</th>
                        <td>
                            <input type="text" name="bunga" class="form-control" value="<?= esc($angsuran->bunga) ?>"
                                required placeholder="Contoh: 2.5" oninput="calculateTotal();">
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Bunga</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="jumlah_bunga" class="form-control" readonly
                                    style="background-color: #f8f9fa;">
                                <input type="hidden" name="jumlah_bunga_hidden" id="jumlah_bunga_hidden" readonly>
                            </div>
                            <small class="text-muted">*Dihitung dari <?= esc($angsuran->bunga) ?>% dari total pinjaman
                                (Rp <?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?>)</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Angsuran (Pokok + Bunga)</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="total_angsuran" class="form-control" readonly
                                    style="background-color: #f8f9fa;">
                                <input type="hidden" name="total_angsuran_hidden" id="total_angsuran_hidden">
                            </div>
                            <small class="text-muted">*Jumlah angsuran + jumlah bunga</small>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="btn btn-success mt-3">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to calculate total payment including interest
    function calculateTotal() {
        const jumlahAngsuran = parseFloat(document.querySelector('input[name="jumlah_angsuran"]').value) || 0;
        const bunga = parseFloat(document.querySelector('input[name="bunga"]').value) || 0;
        const jumlahPinjaman = parseFloat(document.getElementById('jumlah_pinjaman').value) || 0;

        // Calculate interest based on the total loan amount (not the installment amount)
        const jumlahBunga = (bunga / 100) * jumlahPinjaman;

        // Calculate total payment (principal + interest)
        const totalPayment = jumlahAngsuran + jumlahBunga;

        // Update interest amount field
        document.getElementById('jumlah_bunga').value = formatCurrency(jumlahBunga);
        document.getElementById('jumlah_bunga_hidden').value = jumlahBunga.toFixed(2);

        // Update total fields
        document.getElementById('total_angsuran').value = formatCurrency(totalPayment);
        document.getElementById('total_angsuran_hidden').value = totalPayment.toFixed(2);
    }

    // Function to format numbers as currency
    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    // Event listeners to calculate total when input changes
    document.querySelector('input[name="jumlah_angsuran"]').addEventListener('input', calculateTotal);
    document.querySelector('input[name="bunga"]').addEventListener('input', calculateTotal);

    // Initialize calculation on page load
    document.addEventListener("DOMContentLoaded", function () {
        calculateTotal();
    });
</script>

<?= $this->endSection() ?>
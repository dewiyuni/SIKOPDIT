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

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/update/' . $angsuran->id_angsuran) ?>" method="post">
            <?= csrf_field() ?>
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
                        <th>Total Angsuran (Pokok + Bunga)</th>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="total_angsuran" class="form-control" readonly
                                    style="background-color: #f8f9fa;">
                                <input type="hidden" name="total_angsuran_hidden" id="total_angsuran_hidden">
                            </div>
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

        // Calculate total interest
        const totalBunga = (bunga / 100) * jumlahAngsuran;

        // Calculate total payment
        const totalPayment = jumlahAngsuran + totalBunga;

        // Update total fields
        document.getElementById('total_angsuran').value = formatCurrency(totalPayment);
        document.getElementById('total_angsuran_hidden').value = totalPayment.toFixed(2);
    }

    // Function to format numbers as currency
    function formatCurrency(value) {
        return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
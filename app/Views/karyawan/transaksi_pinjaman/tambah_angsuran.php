<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Tambah Angsuran - <?= esc($pinjaman->nama) ?></h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="post" action="<?= site_url('karyawan/transaksi_pinjaman/simpan_angsuran') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id_pinjaman" value="<?= esc($pinjaman->id_pinjaman) ?>">

            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="tanggal_angsuran">Tanggal Angsuran</label>
                        <input type="date" name="tanggal_angsuran" class="form-control" required
                            value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_pinjaman">Jumlah Pinjaman</label>
                        <input type="text" id="jumlah_pinjaman" class="form-control"
                            value="<?= number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') ?>" readonly>
                        <input type="hidden" name="jumlah_pinjaman" id="jumlah_pinjaman_hidden"
                            value="<?= esc($pinjaman->jumlah_pinjaman) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_angsuran">Jumlah Angsuran</label>
                        <input type="text" id="jumlah_angsuran" class="form-control" required
                            oninput="formatRibuan(this, 'jumlah_angsuran_hidden'); hitungTotalBayar();"
                            autocomplete="off">
                        <input type="hidden" name="jumlah_angsuran" id="jumlah_angsuran_hidden">
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="bunga">Bunga (%)</label>
                        <div class="input-group">
                            <input type="text" id="bunga" class="form-control" required
                                oninput="formatBunga(this); hitungTotalBayar();" value="2">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                            <input type="hidden" name="bunga" id="bunga_hidden" value="2">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_bunga">Jumlah Bunga</label>
                        <input type="text" id="jumlah_bunga" class="form-control" readonly>
                    </div>



                    <div class="form-group mb-3">
                        <label for="total_bayar">Total Bayar</label>
                        <input type="text" id="total_bayar" class="form-control" readonly>
                        <input type="hidden" name="total_bayar" id="total_bayar_hidden">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="ada_denda" name="ada_denda">
                        <label class="form-check-label" for="ada_denda">Ada Denda</label>
                    </div>
                    <div class="form-group mb-3" id="form_denda" style="display: none;">
                        <label for="denda">Nilai Denda</label>
                        <input type="text" id="denda" class="form-control"
                            oninput="formatRibuan(this, 'denda_hidden'); hitungTotalBayar();" value="0"
                            autocomplete="off" name="denda" onfocus="if (this.value === '0') this.value = '';">
                        <input type="hidden" name="denda_hidden" id="denda_hidden" value="0">
                    </div>

                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-success btn-block w-100">Simpan Angsuran</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function formatRibuan(input, hiddenFieldId) {
        let angka = input.value.replace(/\D/g, "");
        input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        document.getElementById(hiddenFieldId).value = angka;
        return angka;
    }

    function formatBunga(input) {
        let nilai = input.value.replace(/[^\d.]/g, "");
        let parts = nilai.split('.');
        if (parts.length > 2) {
            nilai = parts[0] + '.' + parts.slice(1).join('');
        }
        if (parts.length > 1 && parts[1].length > 2) {
            nilai = parts[0] + '.' + parts[1].substring(0, 2);
        }
        input.value = nilai;
        document.getElementById("bunga_hidden").value = nilai;
        return nilai;
    }

    function hitungTotalBayar() {
        const jumlahAngsuran = parseInt(document.getElementById("jumlah_angsuran_hidden").value || 0);
        const jumlahPinjaman = parseInt(document.getElementById("jumlah_pinjaman_hidden").value || 0);
        const bungaPersen = parseFloat(document.getElementById("bunga_hidden").value || 0);
        const jumlahBunga = Math.round(jumlahPinjaman * (bungaPersen / 100));
        let denda = 0;
        if (document.getElementById("ada_denda").checked) {
            denda = parseInt(document.getElementById("denda_hidden").value || 0);
        }

        document.getElementById("jumlah_bunga").value = jumlahBunga.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        const totalBayar = jumlahAngsuran + jumlahBunga + denda;
        document.getElementById("total_bayar").value = totalBayar.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        document.getElementById("total_bayar_hidden").value = totalBayar;
    }

    document.addEventListener("DOMContentLoaded", function () {
        const adaDendaCheckbox = document.getElementById("ada_denda");
        const formDenda = document.getElementById("form_denda");

        adaDendaCheckbox.addEventListener("change", function () {
            formDenda.style.display = this.checked ? "block" : "none";
            hitungTotalBayar();
        });

        // Set nilai default untuk bunga
        document.getElementById("bunga").value = "2";
        document.getElementById("bunga_hidden").value = "2";
        hitungTotalBayar();
    });
</script>

<?= $this->endSection() ?>
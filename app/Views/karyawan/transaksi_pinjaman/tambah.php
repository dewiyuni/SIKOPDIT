<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Tambah Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <?php
    $errorMessage = session()->getFlashdata('error');
    $errorHtmlMessage = session()->getFlashdata('error_html');

    // siapkan map untuk JS (id <-> label)
    $mapIdToLabel = [];
    $mapLabelToId = [];
    foreach ($anggota as $a) {
        $label = $a->nama . ' (' . $a->no_ba . ')';
        $mapIdToLabel[$a->id_anggota] = $label;
        $mapLabelToId[$label] = $a->id_anggota;
    }
    $oldId = old('id_anggota'); // untuk prefilling
    ?>

    <?php if ($errorHtmlMessage): ?>
        <div class="alert alert-danger"><?= $errorHtmlMessage ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="alert alert-danger"><?= esc($errorMessage) ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/simpan') ?>" method="post"
            onsubmit="return validateAnggota();">
            <?= csrf_field() ?>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="anggota_input" class="form-label">Nama Anggota</label>

                        <!-- Input + Datalist (pencarian sederhana) -->
                        <input type="text" id="anggota_input" class="form-control"
                            placeholder="Ketik nama atau No. BA, lalu pilih…" list="list_anggota" autocomplete="off">
                        <datalist id="list_anggota">
                            <?php foreach ($anggota as $a): ?>
                                <option value="<?= esc($a->nama . ' (' . $a->no_ba . ')') ?>"
                                    data-id="<?= esc($a->id_anggota) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>

                        <!-- id yang dikirim ke server -->
                        <input type="hidden" name="id_anggota" id="id_anggota_hidden" value="<?= esc($oldId) ?>">

                        <small id="anggota_help" class="text-muted d-block mt-1">
                            Pilih salah satu dari daftar saran. Jika tidak muncul, lanjutkan mengetik.
                        </small>
                        <?php if (session('errors.id_anggota')): ?>
                            <small class="text-danger d-block"><?= esc(session('errors.id_anggota')) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="tanggal_pinjaman" class="form-label">Tanggal Cair</label>
                        <input type="date" name="tanggal_pinjaman" class="form-control" required
                            value="<?= old('tanggal_pinjaman', date('Y-m-d')) ?>">
                        <?php if (session('errors.tanggal_pinjaman')): ?>
                            <small class="text-danger"><?= esc(session('errors.tanggal_pinjaman')) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jangka_waktu" class="form-label">Jangka Waktu (bulan)</label>
                        <input type="number" name="jangka_waktu" id="jangka_waktu" class="form-control" required min="1"
                            value="<?= old('jangka_waktu') ?>" onchange="hitungSimulasi();">
                        <?php if (session('errors.jangka_waktu')): ?>
                            <small class="text-danger"><?= esc(session('errors.jangka_waktu')) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jaminan" class="form-label">Jaminan (Opsional) | >2 Juta (Wajib)</label>
                        <input type="text" name="jaminan" class="form-control" value="<?= old('jaminan') ?>">
                        <?php if (session('errors.jaminan')): ?>
                            <small class="text-danger"><?= esc(session('errors.jaminan')) ?></small>
                        <?php endif; ?>
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
                            <small class="text-danger"><?= esc(session('errors.jumlah_pinjaman')) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label for="bunga_simpanan" class="form-label">Potongan Awal (SWP 2.5%)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="bunga_simpanan_display" class="form-control" disabled
                                style="background-color:#f8f9fa;">
                            <input type="hidden" name="bunga_simpanan" id="bunga_simpanan_hidden">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jumlah_diterima" class="form-label">Jumlah Diterima</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="jumlah_diterima" class="form-control" disabled
                                style="background-color:#f8f9fa;">
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
                                        <label class="form-label"><strong>Angsuran Pokok /bulan:</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="angsuran_pokok_display" class="form-control" disabled
                                                style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong id="label_bunga_angsuran">Bunga Per Angsuran
                                                (2%):</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="bunga_angsuran_display" class="form-control" disabled
                                                style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong id="label_total_angsuran">Total Angsuran
                                                /bulan:</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="total_angsuran_display" class="form-control" disabled
                                                style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Bulan Ke-</th>
                                            <th>Angsuran Pokok</th>
                                            <th>Bunga (2%)</th>
                                            <th>Total Bayar</th>
                                            <th>Sisa Pinjaman</th>
                                        </tr>
                                    </thead>
                                    <tbody id="simulasi_body"></tbody>
                                    <tfoot class="table-info">
                                        <tr>
                                            <th>Total</th>
                                            <th id="total_pokok_simulasi">Rp 0</th>
                                            <th id="total_bunga_simulasi">Rp 0</th>
                                            <th id="total_pembayaran_simulasi">Rp 0</th>
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
    /* ====== ANGGOTA: datalist binding sederhana ====== */
    const mapIdToLabel = <?= json_encode($mapIdToLabel, JSON_UNESCAPED_UNICODE) ?>;
    const mapLabelToId = <?= json_encode($mapLabelToId, JSON_UNESCAPED_UNICODE) ?>;

    const anggotaInput = document.getElementById('anggota_input');
    const idHidden = document.getElementById('id_anggota_hidden');
    const anggotaHelp = document.getElementById('anggota_help');

    // Prefill label jika ada old('id_anggota')
    (function prefillAnggota() {
        const oldId = idHidden.value;
        if (oldId && mapIdToLabel[oldId]) {
            anggotaInput.value = mapIdToLabel[oldId];
        }
    })();

    anggotaInput.addEventListener('change', syncAnggota);
    anggotaInput.addEventListener('blur', syncAnggota);
    anggotaInput.addEventListener('input', function () {
        // Saat user mengetik, kosongkan id sampai memilih yang valid
        idHidden.value = '';
    });

    function syncAnggota() {
        const label = anggotaInput.value.trim();
        const id = mapLabelToId[label] || '';
        idHidden.value = id;
        if (!id) {
            anggotaHelp.classList.remove('text-muted');
            anggotaHelp.classList.add('text-danger');
            anggotaHelp.textContent = 'Nama tidak ditemukan. Pilih dari saran yang muncul.';
        } else {
            anggotaHelp.classList.remove('text-danger');
            anggotaHelp.classList.add('text-muted');
            anggotaHelp.textContent = 'Pilih salah satu dari daftar saran. Jika tidak muncul, lanjutkan mengetik.';
        }
    }

    function validateAnggota() {
        if (!idHidden.value) {
            anggotaInput.focus();
            syncAnggota();
            return false;
        }
        return true;
    }

    /* ====== FORMAT & SIMULASI (punyamu, tanpa perubahan) ====== */
    function formatRibuan(input) {
        let angka = input.value.replace(/\D/g, "");
        input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        document.getElementById("jumlah_pinjaman_hidden").value = angka;
        return angka;
    }

    function formatNumber(number) {
        if (isNaN(number) || number === null || number === undefined) return "0";
        return parseFloat(number).toLocaleString('id-ID');
    }

    function hitungSimulasi() {
        const pinjamanInput = document.getElementById("jumlah_pinjaman_hidden").value;
        const jangkaWaktuInput = document.getElementById("jangka_waktu").value;

        const pinjaman = parseFloat(pinjamanInput || 0);
        const jangkaWaktu = parseInt(jangkaWaktuInput || 0);

        const labelBungaAngsuran = document.getElementById("label_bunga_angsuran");
        const labelTotalAngsuran = document.getElementById("label_total_angsuran");

        if (pinjaman <= 0 || jangkaWaktu <= 0) {
            resetSimulasi();
            labelBungaAngsuran.innerHTML = "<strong>Bunga Per Angsuran (2%):</strong>";
            labelTotalAngsuran.innerHTML = "<strong>Total Angsuran /bulan:</strong>";
            return;
        }

        const bungaSimpananPersen = 2.5;
        const bungaSimpanan = Math.round(pinjaman * (bungaSimpananPersen / 100));
        const jumlahDiterima = pinjaman - bungaSimpanan;

        document.getElementById("bunga_simpanan_display").value = formatNumber(bungaSimpanan);
        document.getElementById("bunga_simpanan_hidden").value = bungaSimpanan;
        document.getElementById("jumlah_diterima").value = formatNumber(jumlahDiterima);
        document.getElementById("jumlah_diterima_hidden").value = jumlahDiterima;

        const angsuranPokokAwal = Math.ceil(pinjaman / jangkaWaktu);
        document.getElementById("angsuran_pokok_display").value = formatNumber(angsuranPokokAwal);

        let simulasiBody = document.getElementById("simulasi_body");
        simulasiBody.innerHTML = '';

        let sisaPinjaman = pinjaman;
        let totalPokokTerbayar = 0;
        let totalBungaTerbayar = 0;
        let totalPembayaranKeseluruhan = 0;

        const batasBungaMenurun = 2000000;
        const bungaAngsuranPersen = 2;

        if (pinjaman > batasBungaMenurun) {
            labelBungaAngsuran.innerHTML = "<strong>Bunga Per Angsuran (2% dari Sisa):</strong>";
            labelTotalAngsuran.innerHTML = "<strong>Total Angsuran /bulan (Variatif):</strong>";
            document.getElementById("bunga_angsuran_display").value = "Menurun";
            document.getElementById("total_angsuran_display").value = "Variatif";
        } else {
            labelBungaAngsuran.innerHTML = "<strong>Bunga Per Angsuran (2% Tetap):</strong>";
            labelTotalAngsuran.innerHTML = "<strong>Total Angsuran /bulan (Tetap):</strong>";
            const bungaAngsuranTetap = Math.round(pinjaman * (bungaAngsuranPersen / 100));
            document.getElementById("bunga_angsuran_display").value = formatNumber(bungaAngsuranTetap);
            document.getElementById("total_angsuran_display").value = formatNumber(angsuranPokokAwal + bungaAngsuranTetap);
        }

        for (let i = 1; i <= jangkaWaktu; i++) {
            let angsuranPokokBulanIni;
            let bungaBulanIni;

            if (sisaPinjaman <= 0) {
                angsuranPokokBulanIni = 0;
                bungaBulanIni = 0;
            } else if (pinjaman > batasBungaMenurun) {
                bungaBulanIni = Math.round(sisaPinjaman * (bungaAngsuranPersen / 100));
            } else {
                bungaBulanIni = Math.round(pinjaman * (bungaAngsuranPersen / 100));
            }

            if (i === jangkaWaktu) {
                angsuranPokokBulanIni = sisaPinjaman;
            } else {
                angsuranPokokBulanIni = Math.min(angsuranPokokAwal, sisaPinjaman);
            }
            if (sisaPinjaman - angsuranPokokBulanIni < 0 && i < jangkaWaktu) {
                angsuranPokokBulanIni = sisaPinjaman;
            }

            let totalAngsuranBulanIni = angsuranPokokBulanIni + bungaBulanIni;
            let sisaPinjamanSetelahBayar = sisaPinjaman - angsuranPokokBulanIni;

            if (angsuranPokokBulanIni > 0 || bungaBulanIni > 0) {
                totalPokokTerbayar += angsuranPokokBulanIni;
                totalBungaTerbayar += bungaBulanIni;
                totalPembayaranKeseluruhan += totalAngsuranBulanIni;
            }

            let row = document.createElement('tr');
            row.innerHTML = `
            <td>${i}</td>
            <td>Rp ${formatNumber(angsuranPokokBulanIni)}</td>
            <td>Rp ${formatNumber(bungaBulanIni)}</td>
            <td>Rp ${formatNumber(totalAngsuranBulanIni)}</td>
            <td>Rp ${formatNumber(Math.max(0, sisaPinjamanSetelahBayar))}</td>
        `;
            simulasiBody.appendChild(row);

            sisaPinjaman = sisaPinjamanSetelahBayar;
            if (sisaPinjaman <= 0 && i < jangkaWaktu) break;
        }

        document.getElementById("total_pokok_simulasi").innerText = `Rp ${formatNumber(totalPokokTerbayar)}`;
        document.getElementById("total_bunga_simulasi").innerText = `Rp ${formatNumber(totalBungaTerbayar)}`;
        document.getElementById("total_pembayaran_simulasi").innerText = `Rp ${formatNumber(totalPembayaranKeseluruhan)}`;
    }

    function resetSimulasi() {
        document.getElementById("jumlah_pinjaman").value = "";
        document.getElementById("jumlah_pinjaman_hidden").value = "0";
        document.getElementById("bunga_simpanan_display").value = "0";
        document.getElementById("bunga_simpanan_hidden").value = "0";
        document.getElementById("jumlah_diterima").value = "0";
        document.getElementById("jumlah_diterima_hidden").value = "0";
        document.getElementById("angsuran_pokok_display").value = "0";
        document.getElementById("bunga_angsuran_display").value = "0";
        document.getElementById("total_angsuran_display").value = "0";
        document.getElementById("simulasi_body").innerHTML = '';
        document.getElementById("total_pokok_simulasi").innerText = "Rp 0";
        document.getElementById("total_bunga_simulasi").innerText = "Rp 0";
        document.getElementById("total_pembayaran_simulasi").innerText = "Rp 0";
        document.getElementById("label_bunga_angsuran").innerHTML = "<strong>Bunga Per Angsuran (2%):</strong>";
        document.getElementById("label_total_angsuran").innerHTML = "<strong>Total Angsuran /bulan:</strong>";
    }

    document.addEventListener("DOMContentLoaded", function () {
        const oldPinjaman = "<?= old('jumlah_pinjaman') ?>";
        const oldJangkaWaktu = "<?= old('jangka_waktu') ?>";

        if (oldPinjaman) {
            document.getElementById("jumlah_pinjaman_hidden").value = oldPinjaman;
            document.getElementById("jumlah_pinjaman").value = parseFloat(oldPinjaman).toLocaleString('id-ID');
        }
        if (oldJangkaWaktu) {
            document.getElementById("jangka_waktu").value = oldJangkaWaktu;
        }

        if (oldPinjaman || oldJangkaWaktu) {
            hitungSimulasi();
        } else {
            resetSimulasi();
        }

        const tanggalPinjamanInput = document.querySelector('input[name="tanggal_pinjaman"]');
        if (!tanggalPinjamanInput.value) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            tanggalPinjamanInput.value = `${yyyy}-${mm}-${dd}`;
        }
    });
</script>
<?= $this->endSection() ?>
<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">Jurnal Kas Harian</h3>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Kas Harian</h5>
            <div class="d-flex gap-3 flex-column flex-md-row align-items-center">
                <!-- Tombol Ekspor -->
                <a href="<?= base_url('export-excel'); ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Ekspor ke Excel
                </a>

                Form Upload Excel
                <form action="<?= base_url('admin/jurnal_neraca/import_excel') ?>" method="post"
                    enctype="multipart/form-data" class="d-flex flex-column flex-md-row align-items-center gap-2">
                    <div>
                        <input type="file" class="form-control form-control-sm" name="file_excel" id="file_excel"
                            accept=".xls,.xlsx" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 ">
                    <label for="tahunSelect">Pilih Tahun</label>
                    <select id="tahunSelect" class="form-select" onchange="filterData()">
                        <option value="">Semua Tahun</option>
                        <?php
                        // Ganti dengan tahun yang relevan sesuai data Anda
                        for ($year = date("Y"); $year >= 2000; $year--): ?>
                            <option value="<?= $year; ?>"><?= $year; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 ">
                    <label for="bulanSelect">Pilih Bulan</label>
                    <select id="bulanSelect" class="form-select" onchange="filterData()">
                        <option value="">Semua Bulan</option>
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mt-4">Data DUM</h4>
            </div>
            <div style="overflow-x: auto;">
                <table class="table table-bordered table-striped mt-3">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th>DUM</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dumBody">
                        <?php $no = 1;
                        foreach ($jurnal_kas_harian as $k): ?>
                            <?php if ($k['kategori'] == 'DUM'): ?>
                                <tr data-id="<?= $k['id'] ?>">
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <input type="date" class="form-control date-dum"
                                            value="<?= date('Y-m-d', strtotime($k['tanggal'])) ?>" required
                                            oninput="hitungTotalPerHari()">
                                    </td>
                                    <td><input type="text" class="form-control" value="<?= $k['uraian'] ?>"
                                            data-id="<?= $k['id'] ?>"></td>
                                    <td>
                                        <input type="text" class="form-control dum"
                                            value="<?= number_format($k['jumlah'], 0, ',', '.') ?>" data-id="<?= $k['id'] ?>"
                                            oninput="formatRibuan(this)">
                                    </td>
                                    <td><button class="btn btn-danger" onclick="hapusBaris(this, 'dum')">Hapus</button></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total DUM</th>
                            <th id="totalDUM">0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button class="btn btn-info" style="width: 100%; display: block;" onclick="tambahDUM()">Tambah DUM</button>

            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mt-4">Data DUK</h4>
            </div>

            <div style="overflow-x: auto;">
                <table class="table table-bordered table-striped mt-3">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th>DUK</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dukBody">
                        <?php $no = 1;
                        foreach ($jurnal_kas_harian as $k): ?>
                            <?php if ($k['kategori'] == 'DUK'): ?>
                                <tr data-id="<?= $k['id'] ?>">
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <input type="date" class="form-control date-duk"
                                            value="<?= date('Y-m-d', strtotime($k['tanggal'])) ?>" required
                                            oninput="hitungTotalPerHari()">
                                    </td>
                                    <td><input type="text" class="form-control" value="<?= $k['uraian'] ?>"
                                            data-id="<?= $k['id'] ?>"></td>
                                    <td>
                                        <input type="text" class="form-control duk"
                                            value="<?= number_format($k['jumlah'], 0, ',', '.') ?>" data-id="<?= $k['id'] ?>"
                                            oninput="formatRibuan(this)">
                                    </td>
                                    <td><button class="btn btn-danger" onclick="hapusBaris(this, 'duk')">Hapus</button></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total DUK</th>
                            <th id="totalDUK">0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button class="btn btn-info" style="width: 100%; display: block;" onclick="tambahDUK()">Tambah DUK</button>

            <h4 class="mt-4">Total Per Hari</h4>
            <table class="table table-bordered table-striped mt-3">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Total DUM</th>
                        <th>Total DUK</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody id="totalPerHariBody">
                </tbody>
            </table>
            <button class="btn btn-success" style="width: 100%; display: block;" onclick="simpanKeDatabase()">Simpan ke
                Database</button>
        </div>
    </div>
</div>

<script>
    let counterDUM = 1;
    let counterDUK = 1;

    function tambahDUM() {
        let tbody = document.getElementById("dumBody");
        let row = tbody.insertRow();
        let nomor = tbody.children.length; // Ambil nomor berdasarkan jumlah baris

        row.classList.add("data-row");
        row.innerHTML = `
        <td>${nomor}</td>
        <td><input type="date" class="form-control date-dum" required oninput="hitungTotalPerHari()"></td>
        <td><input type="text" class="form-control" placeholder="Uraian"></td>
        <td><input type="text" class="form-control dum" value="0" oninput="formatRibuan(this); hitungTotal();"></td>
        <td><button class="btn btn-danger" onclick="hapusBaris(this, 'dum')">Hapus</button></td>
    `;
    }

    function tambahDUK() {
        let tbody = document.getElementById("dukBody");
        let row = tbody.insertRow();
        let nomor = tbody.children.length;

        row.classList.add("data-row");
        row.innerHTML = `
        <td>${nomor}</td>
        <td><input type="date" class="form-control date-duk" required oninput="hitungTotalPerHari()"></td>
        <td><input type="text" class="form-control" placeholder="Uraian"></td>
        <td><input type="text" class="form-control duk" value="0" oninput="formatRibuan(this); hitungTotal();"></td>
        <td><button class="btn btn-danger" onclick="hapusBaris(this, 'duk')">Hapus</button></td>
    `;
    }

    // Fungsi untuk membersihkan angka dari format ribuan sebelum dihitung
    function cleanNumber(value) {
        return parseFloat(value.replace(/\./g, "").replace(",", ".")) || 0;
    }

    // Fungsi untuk memformat angka kembali ke format ribuan
    function formatRibuan(input) {
        let number = input.value.replace(/\D/g, ""); // Hapus karakter non-angka
        if (number === "") number = "0"; // Jika kosong, jadikan 0
        input.value = new Intl.NumberFormat("id-ID").format(number);
    }

    // Fungsi menghitung total DUM & DUK
    function hitungTotal() {
        let totalDUM = 0;
        let totalDUK = 0;

        document.querySelectorAll(".dum").forEach(input => {
            totalDUM += cleanNumber(input.value);
        });

        document.querySelectorAll(".duk").forEach(input => {
            totalDUK += cleanNumber(input.value);
        });

        document.getElementById("totalDUM").textContent = totalDUM.toLocaleString("id-ID");
        document.getElementById("totalDUK").textContent = totalDUK.toLocaleString("id-ID");

        hitungTotalPerHari();
    }

    // Fungsi menghitung total per hari
    function hitungTotalPerHari() {
        let totals = {};

        document.querySelectorAll(".date-dum, .date-duk").forEach(tanggalInput => {
            let row = tanggalInput.closest("tr");
            let tanggal = tanggalInput.value.trim();
            if (tanggal === "") return; // Lewati jika tanggal kosong

            let dum = cleanNumber(row.querySelector(".dum")?.value || "0");
            let duk = cleanNumber(row.querySelector(".duk")?.value || "0");

            if (!totals[tanggal]) totals[tanggal] = { dum: 0, duk: 0 };
            totals[tanggal].dum += dum;
            totals[tanggal].duk += duk;
        });

        let tbody = document.getElementById("totalPerHariBody");
        tbody.innerHTML = "";

        if (Object.keys(totals).length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>`;
            return;
        }

        Object.keys(totals).forEach(tanggal => {
            let saldo = totals[tanggal].dum - totals[tanggal].duk;
            let row = tbody.insertRow();
            row.innerHTML = `
            <td>${tanggal}</td>
            <td>${totals[tanggal].dum.toLocaleString("id-ID")}</td>
            <td>${totals[tanggal].duk.toLocaleString("id-ID")}</td>
            <td>${saldo.toLocaleString("id-ID")}</td>
        `;
        });
    }

    // Fungsi untuk menghapus baris & update total
    function hapusBaris(button, tipe) {
        let row = button.closest("tr");
        let id = row.getAttribute('data-id');

        if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
            fetch(`<?= base_url('admin/jurnal_kas_harian/delete/') ?>${id}`, {
                method: "DELETE",
            })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        alert(result.message);
                        row.remove(); // Menghapus baris dari tampilan
                        hitungTotal(); // Memperbarui total
                        hitungTotalPerHari(); // Memperbarui total per hari
                    } else {
                        alert(result.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        }
    }


    // Fungsi menyimpan ke database
    function simpanKeDatabase() {
        let data = [];

        document.querySelectorAll("#dumBody tr, #dukBody tr").forEach(row => {
            let tanggalInput = row.querySelector(".date-dum, .date-duk")?.value;
            let tanggal = tanggalInput; // Pastikan format tanggal YYYY-MM-DD untuk database
            let uraian = row.querySelector("input[type='text']")?.value;
            let jumlah = cleanNumber(row.querySelector(".dum, .duk")?.value || "0");

            // Perbaiki cara menentukan kategori
            let kategori = row.querySelector(".dum") ? "DUM" : (row.querySelector(".duk") ? "DUK" : null);
            if (!kategori) return; // Jika kategori tidak dikenali, lewati

            if (tanggal && uraian && jumlah) {
                data.push({ tanggal, uraian, jumlah, kategori });
            }
        });

        if (data.length === 0) {
            alert("Tidak ada data yang disimpan.");
            return;
        }

        fetch("<?= base_url('admin/jurnal_kas_harian/simpan') ?>", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data), // Jangan bungkus dalam objek lain
        })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                location.reload();
            })
            .catch(error => console.error("Error:", error));
    }

    // Jalankan hitungTotal() setelah halaman dimuat
    document.addEventListener("DOMContentLoaded", function () {
        hitungTotal();
    });

    function filterData() {
        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;

        document.querySelectorAll("#dumBody tr, #dukBody tr").forEach(row => {
            let dateInput = row.querySelector("input[type='date']");
            if (dateInput) {
                let rowDate = new Date(dateInput.value);
                let rowYear = rowDate.getFullYear();
                let rowMonth = rowDate.getMonth() + 1; // Bulan dimulai dari 0

                row.style.display = (selectedYear === "" || rowYear == selectedYear) &&
                    (selectedMonth === "" || rowMonth == selectedMonth) ? "" : "none";
            }
        });
    }

    // Panggil filter ulang setelah update data
    document.getElementById("tahunSelect").addEventListener("change", filterData);
    document.getElementById("bulanSelect").addEventListener("change", filterData);
</script>
<?= $this->endSection(); ?>
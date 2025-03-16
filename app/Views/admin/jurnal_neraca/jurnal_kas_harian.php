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
                        <?php for ($year = date("Y"); $year >= 2015; $year--): ?>
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
                            <th style="text-align: center;">Aksi</th>
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
                                    <td style="text-align: center;">
                                        <button class="btn btn-danger" onclick="hapusBaris(this, 'dum')">Hapus</button>
                                        <button class="btn btn-primary" onclick="simpanBaris(this, 'dum')">Simpan</button>
                                    </td>
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
                            <th style="text-align: center;">No</th>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th>DUK</th>
                            <th style="text-align: center;">Aksi</th>
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
                                    <td style="text-align: center;">
                                        <button class="btn btn-danger" onclick="hapusBaris(this, 'duk')">Hapus</button>
                                        <button class="btn btn-primary" onclick="simpanBaris(this, 'duk')">Simpan</button>
                                    </td>
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
        let nomor = tbody.children.length; // Get row number based on total rows

        row.classList.add("data-row");
        row.innerHTML = `
            <td>${nomor}</td>
            <td><input type="date" class="form-control date-dum" required oninput="hitungTotalPerHari()"></td>
            <td><input type="text" class="form-control" placeholder="Uraian"></td>
            <td><input type="text" class="form-control dum" value="0" oninput="formatRibuan(this); hitungTotal();"></td>
            <td>
                <button class="btn btn-danger" onclick="hapusBaris(this, 'dum')">Hapus</button>
                <button class="btn btn-primary" onclick="simpanBaris(this, 'dum')">Simpan</button>
            </td>
        `;

        // Trigger calculations after adding a new row
        hitungTotal();
        hitungTotalPerHari();
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
            <td>
                <button class="btn btn-danger" onclick="hapusBaris(this, 'duk')">Hapus</button>
                <button class="btn btn-primary" onclick="simpanBaris(this, 'duk')">Simpan</button>
            </td>
        `;

        // Trigger calculations after adding a new row
        hitungTotal();
        hitungTotalPerHari();
    }

    // Function to clean number from thousand format before calculation
    function cleanNumber(value) {
        return parseFloat(value.replace(/\./g, "").replace(",", ".")) || 0;
    }

    // Function to format number back to thousand format
    function formatRibuan(input) {
        let number = input.value.replace(/\D/g, ""); // Remove non-numeric characters
        if (number === "") number = "0"; // If empty, set to 0
        input.value = new Intl.NumberFormat("id-ID").format(number);
        hitungTotal(); // Update totals after formatting
    }

    // Function to calculate total DUM & DUK
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

    // Function to calculate total per month (previously per day)
    function hitungTotalPerHari() {
        let totals = {};

        document.querySelectorAll(".date-dum, .date-duk").forEach(tanggalInput => {
            let row = tanggalInput.closest("tr");
            let tanggal = tanggalInput.value.trim();
            if (tanggal === "") return; // Skip if date is empty

            // Extract year and month for grouping
            let yearMonth = tanggal.substring(0, 7); // Format: YYYY-MM

            let dum = cleanNumber(row.querySelector(".dum")?.value || "0");
            let duk = cleanNumber(row.querySelector(".duk")?.value || "0");

            if (!totals[yearMonth]) totals[yearMonth] = { dum: 0, duk: 0 };

            // Add values based on which table the row belongs to
            if (row.closest("#dumBody")) {
                totals[yearMonth].dum += dum;
            } else if (row.closest("#dukBody")) {
                totals[yearMonth].duk += duk;
            }
        });

        let tbody = document.getElementById("totalPerHariBody");
        tbody.innerHTML = "";

        if (Object.keys(totals).length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>`;
            return;
        }

        // Sort by year and month
        Object.keys(totals).sort().forEach(yearMonth => {
            let [year, month] = yearMonth.split('-');
            let monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            let monthName = monthNames[parseInt(month) - 1];

            let saldo = totals[yearMonth].dum - totals[yearMonth].duk;
            let row = tbody.insertRow();
            row.innerHTML = `
            <td>${monthName} ${year}</td>
            <td>${totals[yearMonth].dum.toLocaleString("id-ID")}</td>
            <td>${totals[yearMonth].duk.toLocaleString("id-ID")}</td>
            <td>${saldo.toLocaleString("id-ID")}</td>
        `;
        });
    }

    function simpanBaris(button, tipe) {
        let row = button.closest("tr");
        let tanggal = row.querySelector("input[type='date']").value;
        let uraian = row.querySelector("input[type='text']").value;
        let jumlah = cleanNumber(row.querySelector(`.${tipe}`)?.value || "0");

        // Validate the inputs
        if (!tanggal || !uraian || !jumlah) {
            alert("Mohon lengkapi semua field sebelum menyimpan.");
            return;
        }

        // Prepare the data to send to the server
        let data = {
            tanggal: tanggal,
            uraian: uraian,
            jumlah: jumlah,
            kategori: tipe === 'dum' ? "DUM" : "DUK"
        };

        console.log("Saving row data:", data); // Log data being sent

        // Send the save request to the backend
        fetch("<?= base_url('admin/jurnal_kas_harian/simpan') ?>", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify([data]), // Wrap in an array for batch processing
        })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.status === 'success') {
                    location.reload(); // Reload to see updates
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Terjadi kesalahan saat menyimpan data.");
            });
    }

    // Function to delete row & update total
    function hapusBaris(button, tipe) {
        let row = button.closest("tr");
        let id = row.getAttribute('data-id');

        if (id) {
            // If the row has an ID, it exists in the database
            if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                fetch(`<?= base_url('admin/jurnal_kas_harian/delete/') ?>${id}`, {
                    method: "DELETE",
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            alert(result.message);
                            row.remove(); // Remove row from view
                            hitungTotal(); // Update totals
                            hitungTotalPerHari(); // Update totals per month
                        } else {
                            alert(result.message);
                        }
                    })
                    .catch(error => console.error("Error:", error));
            }
        } else {
            // If the row doesn't have an ID, it's a new row that hasn't been saved
            row.remove();
            hitungTotal();
            hitungTotalPerHari();
        }
    }

    // Function to save to database
    function simpanKeDatabase() {
        let data = [];

        // Process DUM entries
        document.querySelectorAll("#dumBody tr").forEach(row => {
            let tanggal = row.querySelector(".date-dum")?.value;
            let uraian = row.querySelector("input[type='text']")?.value;
            let jumlah = cleanNumber(row.querySelector(".dum")?.value || "0");
            let id = row.getAttribute('data-id');

            if (tanggal && uraian && jumlah) {
                data.push({
                    id: id || null,
                    tanggal,
                    uraian,
                    jumlah,
                    kategori: "DUM"
                });
            }
        });

        // Process DUK entries
        document.querySelectorAll("#dukBody tr").forEach(row => {
            let tanggal = row.querySelector(".date-duk")?.value;
            let uraian = row.querySelector("input[type='text']")?.value;
            let jumlah = cleanNumber(row.querySelector(".duk")?.value || "0");
            let id = row.getAttribute('data-id');

            if (tanggal && uraian && jumlah) {
                data.push({
                    id: id || null,
                    tanggal,
                    uraian,
                    jumlah,
                    kategori: "DUK"
                });
            }
        });

        if (data.length === 0) {
            alert("Tidak ada data yang disimpan.");
            return;
        }

        fetch("<?= base_url('admin/jurnal_kas_harian/simpan') ?>", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                location.reload();
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Terjadi kesalahan saat menyimpan data.");
            });
    }

    // Run hitungTotal() after page loads
    document.addEventListener("DOMContentLoaded", function () {
        hitungTotal();

        // Set default date to today for new entries
        let today = new Date().toISOString().split('T')[0];
        document.querySelectorAll(".date-dum, .date-duk").forEach(input => {
            if (!input.value) {
                input.value = today;
            }
        });
    });

    function filterData() {
        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;

        document.querySelectorAll("#dumBody tr, #dukBody tr").forEach(row => {
            let dateInput = row.querySelector("input[type='date']");
            if (dateInput) {
                let rowDate = new Date(dateInput.value);
                let rowYear = rowDate.getFullYear();
                let rowMonth = rowDate.getMonth() + 1; // Month starts from 0

                let monthStr = rowMonth < 10 ? '0' + rowMonth : '' + rowMonth;

                row.style.display = (selectedYear === "" || rowYear == selectedYear) &&
                    (selectedMonth === "" || monthStr == selectedMonth) ? "" : "none";
            }
        });

        // Recalculate totals after filtering
        hitungTotal();
    }

    // Call filter again after data update
    document.getElementById("tahunSelect").addEventListener("change", filterData);
    document.getElementById("bulanSelect").addEventListener("change", filterData);

</script>
<?= $this->endSection(); ?>
<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">Jurnal Kas</h3>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Data Kas</h5>
            <div class="d-flex gap-3 flex-column flex-md-row align-items-center">
                <!-- Tombol Ekspor -->
                <a href="<?= base_url('export-excel'); ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Ekspor ke Excel
                </a>

                <!-- Form Upload Excel -->
                <form action="<?= base_url('admin/jurnal/import_excel') ?>" method="post" enctype="multipart/form-data"
                    class="d-flex flex-column flex-md-row align-items-center gap-2" id="importExcelForm">
                    <?= csrf_field() ?> <!-- Tambahkan CSRF field jika diaktifkan -->
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
            <!-- Area Notifikasi -->
            <div id="notificationArea" class="mb-3">
                <?php
                // Cek flashdata dari session (ini memerlukan pengaturan flashdata di controller setelah upload)
                if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row mb-3 g-2">
                <div class="col-md-3">
                    <label for="tahunSelect">Pilih Tahun</label>
                    <select id="tahunSelect" class="form-select">
                        <option value="">Pilih Tahun</option>
                        <?php for ($year = date("Y"); $year >= 2015; $year--): ?>
                            <option value="<?= $year; ?>"><?= $year; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bulanSelect">Pilih Bulan</label>
                    <select id="bulanSelect" class="form-select">
                        <option value="">Pilih Bulan</option>
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
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary" onclick="filterData()">Tampilkan Data</button>
                </div>
            </div>

            <!-- Tombol Simpan dan Refresh -->
            <div class="d-flex gap-2 mb-3">
                <button class="btn btn-success" onclick="simpanKeDatabase()">Simpan Semua Perubahan</button>
                <button class="btn btn-secondary" onclick="confirmRefresh()">Refresh / Batalkan Perubahan</button>
            </div>


            <div id="dataContainer" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mt-2 mb-2">Data DUM</h4>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th>Uraian</th>
                                <th style="width: 150px;">DUM</th>
                                <th style="width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dumBody">
                            <?php foreach ($jurnal_kas as $k): ?>
                                <?php if ($k['kategori'] == 'DUM'): ?>
                                    <tr data-id="<?= $k['id'] ?>" class="data-row" style="display: none;">
                                        <td></td> <!-- Placeholder for JS numbering -->
                                        <td>
                                            <input type="date" class="form-control form-control-sm date-input"
                                                value="<?= date('Y-m-d', strtotime($k['tanggal'])) ?>" disabled required>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm uraian-input"
                                                value="<?= $k['uraian'] ?>" disabled></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm dum"
                                                value="<?= number_format($k['jumlah'], 0, ',', '.') ?>" disabled>
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-danger btn-sm"
                                                onclick="hapusBaris(this, 'dum')">Hapus</button>
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
                <button class="btn btn-info btn-sm" style="width: 100%; display: block;" onclick="tambahDUM()">Tambah
                    DUM</button>

                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mt-4 mb-2">Data DUK</h4>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th>Uraian</th>
                                <th style="width: 150px;">DUK</th>
                                <th style="width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dukBody">
                            <?php foreach ($jurnal_kas as $k): ?>
                                <?php if ($k['kategori'] == 'DUK'): ?>
                                    <tr data-id="<?= $k['id'] ?>" class="data-row" style="display: none;">
                                        <td></td> <!-- Placeholder for JS numbering -->
                                        <td>
                                            <input type="date" class="form-control form-control-sm date-input"
                                                value="<?= date('Y-m-d', strtotime($k['tanggal'])) ?>" disabled required>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm uraian-input"
                                                value="<?= $k['uraian'] ?>" disabled></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm duk"
                                                value="<?= number_format($k['jumlah'], 0, ',', '.') ?>" disabled>
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-danger btn-sm"
                                                onclick="hapusBaris(this, 'duk')">Hapus</button>
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
                <button class="btn btn-info btn-sm" style="width: 100%; display: block;" onclick="tambahDUK()">Tambah
                    DUK</button>


                <h4 class="mt-4 mb-2">Total Per Bulan</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Total DUM</th>
                            <th>Total DUK</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody id="totalPerHariBody">
                        <!-- Data akan diisi oleh Javascript -->
                    </tbody>
                </table>
            </div>

            <div id="noDataMessage" class="alert alert-info mt-3 text-center">
                Silakan pilih tahun dan bulan terlebih dahulu untuk menampilkan data.
            </div>
        </div>
    </div>
</div>

<!-- Tombol Scroll to Top -->
<a href="#" id="scroll-to-top" class="btn btn-secondary btn-sm rounded-circle" title="Kembali ke Atas">
    <i class="fas fa-arrow-up"></i> <!-- Memerlukan Font Awesome -->
</a>

<style>
    /* CSS untuk tombol scroll to top */
    #scroll-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: none;
        z-index: 1000;
        padding: 0.5rem 0.75rem;
        line-height: 1;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* CSS untuk menandai baris baru */
    .data-row.row-new {
        background-color: #e9f7ef !important;
        /* Hijau muda */
    }

    /* CSS untuk menandai baris duplikat */
    .data-row.row-duplicate {
        background-color: #fde8ec !important;
        /* Merah muda */
    }

    /* Prioritaskan warna merah jika baris baru sekaligus duplikat */
    .data-row.row-new.row-duplicate {
        background-color: #fde8ec !important;
    }
</style>

<script>
    // --- Helper Function for Bootstrap Alerts ---
    // Ensure you have Bootstrap JS loaded for alerts to be dismissible.
    function displayAlert(message, type = 'info', duration = 5000) {
        const notificationArea = document.getElementById('notificationArea');
        if (!notificationArea) {
            console.error("Notification area element not found!");
            return;
        }

        // Remove all alerts before adding a new non-persistent one
        if (duration > 0 || type === 'danger' || type === 'warning') {
            // Keep persistent alerts (danger, warning, or specific duration 0)
            // Remove others when a new one appears
            notificationArea.querySelectorAll('.alert').forEach(alert => {
                // Check if the alert has a data-persistent attribute or is danger/warning
                if (alert.getAttribute('data-persistent') === null && !alert.classList.contains('alert-danger') && !alert.classList.contains('alert-warning')) {
                    try { const bsAlert = new bootstrap.Alert(alert); bsAlert.close(); } catch (e) { alert.remove(); }
                }
            });
        } else {
            // If duration is 0, it means persistent, clear only non-persistent ones
            notificationArea.querySelectorAll('.alert').forEach(alert => {
                if (alert.getAttribute('data-persistent') === null) { // Only remove non-persistent ones
                    try { const bsAlert = new bootstrap.Alert(alert); bsAlert.close(); } catch (e) { alert.remove(); }
                }
            });
        }


        // Create the alert element
        const alertElement = document.createElement('div');
        alertElement.classList.add('alert', `alert-${type}`, 'alert-dismissible', 'fade', 'show');
        alertElement.setAttribute('role', 'alert');

        // Add data-persistent attribute if duration is 0 or type is danger/warning
        if (duration === 0 || type === 'danger' || type === 'warning') {
            alertElement.setAttribute('data-persistent', 'true');
        }


        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        notificationArea.appendChild(alertElement);

        // Automatically close non-persistent alerts after a duration
        if (duration > 0 && type !== 'danger' && type !== 'warning') {
            setTimeout(() => {
                try {
                    const bsAlert = new bootstrap.Alert(alertElement);
                    bsAlert.close();
                } catch (e) {
                    alertElement.remove();
                }
            }, duration);
        }
        console.log(`Displayed alert: ${type} - ${message.replace(/<br>/g, ' | ')}`);
    }
    // --- End Helper Function for Bootstrap Alerts ---


    // Flag to track if there are unsaved changes
    let hasUnsavedChanges = false;

    // Function to mark that changes have occurred
    function markAsChanged() {
        if (!hasUnsavedChanges) {
            hasUnsavedChanges = true;
            console.log("Unsaved changes detected.");
            // Optional: Visual indicator for the save button or page title
            document.title = "* Jurnal Kas - Unsaved Changes";
        }
    }

    // Override addRowEventListeners to call markAsChanged
    function addRowEventListeners(row) {
        const dateInput = row.querySelector('.date-input');
        const uraianInput = row.querySelector('.uraian-input');
        const amountInput = row.querySelector('.dum') || row.querySelector('.duk');

        if (dateInput) {
            dateInput.addEventListener('change', () => {
                console.log('Event: Date input changed.');
                markAsChanged(); // Mark changes
                checkAndHighlightRows(); // Check duplicates when date changes
                hitungTotal(); // Date change might affect monthly total
            });
        } else { console.warn("Date input not found for row:", row); }

        if (uraianInput) {
            uraianInput.addEventListener('input', () => {
                console.log('Event: Uraian input changed.');
                markAsChanged(); // Mark changes
                checkAndHighlightRows(); // Check duplicates when uraian changes
            });
        } else { console.warn("Uraian input not found for row:", row); }

        if (amountInput) {
            amountInput.addEventListener('input', function () {
                markAsChanged(); // Mark changes
                formatRibuan(this); // formatRibuan calls hitungTotal()
            });
            // Apply formatRibuan initially to the amount input value
            formatRibuan(amountInput);
        } else { console.warn("Amount input (.dum/.duk) not found for row:", row); }
    }


    // Fungsi untuk membersihkan angka dari format ribuan
    function cleanNumber(value) {
        if (typeof value !== 'string' || value.trim() === '') return 0;
        let cleaned = value.replace(/\./g, "").replace(",", ".");
        const parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    // Fungsi untuk memformat angka ke format ribuan (IDR)
    function formatRibuan(input) {
        let number = input.value.replace(/\D/g, "");
        if (number === "") {
            input.value = "";
        } else {
            const intNumber = parseInt(number);
            input.value = new Intl.NumberFormat("id-ID").format(isNaN(intNumber) ? 0 : intNumber);
        }
        hitungTotal();
    }

    // Fungsi untuk memformat angka numerik ke string format ribuan
    function formatNumberString(number) {
        if (typeof number !== 'number' || isNaN(number)) {
            return "0";
        }
        return new Intl.NumberFormat("id-ID").format(number);
    }

    // Fungsi untuk menambah baris DUM
    function tambahDUM() {
        let tbody = document.getElementById("dumBody");
        let row = tbody.insertRow();

        row.classList.add("data-row", "row-new");
        row.innerHTML = `
            <td></td>
            <td><input type="date" class="form-control form-control-sm date-input" required></td>
            <td><input type="text" class="form-control form-control-sm uraian-input" placeholder="Uraian"></td>
            <td><input type="text" class="form-control form-control-sm dum" value="0"></td>
            <td style="text-align: center;">
                <button class="btn btn-danger btn-sm" onclick="hapusBaris(this, 'dum')">Hapus</button>
            </td>
        `;

        markAsChanged(); // Adding a new row is a change!

        const dateInput = row.querySelector(".date-input");
        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;

        if (selectedYear && selectedMonth) {
            let defaultDate = `${selectedYear}-${selectedMonth}-01`;
            try {
                const checkDate = new Date(defaultDate);
                if (!isNaN(checkDate.getTime())) {
                    dateInput.value = defaultDate;
                } else {
                    dateInput.value = `${selectedYear}-${selectedMonth}-01`;
                }
            } catch (e) {
                dateInput.value = `${selectedYear}-${selectedMonth}-01`;
            }
        } else {
            let today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }

        const rowDateInput = row.querySelector(".date-input");
        let shouldBeVisible = false;
        if (rowDateInput && rowDateInput.value) {
            const rowDate = new Date(rowDateInput.value);
            const rowYear = rowDate.getFullYear();
            const rowMonth = (rowDate.getMonth() + 1).toString().padStart(2, '0');

            if (selectedYear && selectedMonth && selectedYear == rowYear && selectedMonth == rowMonth) {
                shouldBeVisible = true;
            } else if (!selectedYear || !selectedMonth) {
                shouldBeVisible = false;
            }
        }

        if (shouldBeVisible) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

        addRowEventListeners(row);
        checkAndHighlightRows();
        hitungTotal(); // This also calls hitungTotalPerHari()
        renumberRows("#dumBody");
        console.log("DUM row added.", { id: row.getAttribute('data-id'), classes: row.classList, display: row.style.display, date: dateInput.value });
    }

    function tambahDUK() {
        let tbody = document.getElementById("dukBody");
        let row = tbody.insertRow();

        row.classList.add("data-row", "row-new");
        row.innerHTML = `
            <td></td>
            <td><input type="date" class="form-control form-control-sm date-input" required></td>
            <td><input type="text" class="form-control form-control-sm uraian-input" placeholder="Uraian"></td>
            <td><input type="text" class="form-control form-control-sm duk" value="0"></td>
            <td style="text-align: center;">
                <button class="btn btn-danger btn-sm" onclick="hapusBaris(this, 'duk')">Hapus</button>
            </td>
        `;

        markAsChanged(); // Adding a new row is a change!

        const dateInput = row.querySelector(".date-input");
        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;

        if (selectedYear && selectedMonth) {
            let defaultDate = `${selectedYear}-${selectedMonth}-01`;
            try {
                const checkDate = new Date(defaultDate);
                if (!isNaN(checkDate.getTime())) {
                    dateInput.value = defaultDate;
                } else {
                    dateInput.value = `${selectedYear}-${selectedMonth}-01`;
                }
            } catch (e) {
                dateInput.value = `${selectedYear}-${selectedMonth}-01`;
            }
        } else {
            let today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }

        const rowDateInput = row.querySelector(".date-input");
        let shouldBeVisible = false;
        if (rowDateInput && rowDateInput.value) {
            const rowDate = new Date(rowDateInput.value);
            const rowYear = rowDate.getFullYear();
            const rowMonth = (rowDate.getMonth() + 1).toString().padStart(2, '0');

            if (selectedYear && selectedMonth && selectedYear == rowYear && selectedMonth == rowMonth) {
                shouldBeVisible = true;
            } else if (!selectedYear || !selectedMonth) {
                shouldBeVisible = false;
            }
        }

        if (shouldBeVisible) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }


        addRowEventListeners(row);
        checkAndHighlightRows();
        hitungTotal(); // This also calls hitungTotalPerHari()
        renumberRows("#dukBody");
        console.log("DUK row added.", { id: row.getAttribute('data-id'), classes: row.classList, display: row.style.display, date: dateInput.value });
    }


    // Fungsi untuk menghitung total DUM & DUK dari baris yang *terlihat*
    function hitungTotal() {
        let totalDUM = 0;
        let totalDUK = 0;

        document.querySelectorAll("#dumBody tr.data-row:not([style*='display: none'])").forEach(row => {
            let input = row.querySelector(".dum");
            if (input) {
                const value = cleanNumber(input.value);
                if (!isNaN(value)) totalDUM += value;
            }
        });

        document.querySelectorAll("#dukBody tr.data-row:not([style*='display: none'])").forEach(row => {
            let input = row.querySelector(".duk");
            if (input) {
                const value = cleanNumber(input.value);
                if (!isNaN(value)) totalDUK += value;
            }
        });

        document.getElementById("totalDUM").textContent = formatNumberString(totalDUM);
        document.getElementById("totalDUK").textContent = formatNumberString(totalDUK);

        hitungTotalPerHari();
    }

    // Fungsi untuk menghitung total per bulan dari data yang *terlihat*
    function hitungTotalPerHari() {
        let totals = {};
        let tbody = document.getElementById("totalPerHariBody");
        tbody.innerHTML = "";

        document.querySelectorAll("#dumBody tr.data-row:not([style*='display: none']), #dukBody tr.data-row:not([style*='display: none'])").forEach(row => {
            let tanggalInput = row.querySelector(".date-input");
            let dumInput = row.querySelector(".dum");
            let dukInput = row.querySelector(".duk");

            if (tanggalInput && tanggalInput.value) {
                let tanggal = tanggalInput.value.trim();
                let yearMonth = tanggal.substring(0, 7);

                if (!totals[yearMonth]) totals[yearMonth] = { dum: 0, duk: 0 };

                const dumValue = cleanNumber(dumInput?.value || "0");
                const dukValue = cleanNumber(dukInput?.value || "0");

                if (!isNaN(dumValue)) totals[yearMonth].dum += dumValue;
                if (!isNaN(dukValue)) totals[yearMonth].duk += dukValue;

            }
        });

        if (Object.keys(totals).length === 0) {
            let selectedYear = document.getElementById("tahunSelect").value;
            let selectedMonth = document.getElementById("bulanSelect").value;
            if (selectedYear && selectedMonth) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada data untuk periode ini</td></tr>`;
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center">Pilih periode untuk melihat total</td></tr>`;
            }
            return;
        }

        Object.keys(totals).sort().forEach(yearMonth => {
            let [year, month] = yearMonth.split('-');
            let monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            let monthName = monthNames[parseInt(month) - 1];

            let saldo = totals[yearMonth].dum - totals[yearMonth].duk;
            let row = tbody.insertRow();
            row.innerHTML = `
                <td>${monthName} ${year}</td>
                <td>${formatNumberString(totals[yearMonth].dum)}</td>
                <td>${formatNumberString(totals[yearMonth].duk)}</td>
                <td>${formatNumberString(saldo)}</td>
            `;
        });
    }


    // Fungsi untuk menghapus baris
    function hapusBaris(button, tipe) {
        let row = button.closest("tr");
        let id = row.getAttribute('data-id');
        let rowNumber = row.cells[0] ? row.cells[0].textContent : 'N/A';

        if (confirm(`Apakah Anda yakin ingin menghapus baris No. ${rowNumber} ini?`)) {
            row.remove();
            console.log(`Row ${rowNumber} (${tipe}, ID: ${id || 'new'}) removed from DOM.`);

            markAsChanged(); // Deleting a row is a change!

            if (id) {
                fetch(`<?= base_url('admin/jurnal/delete/') ?>${id}`, {
                    method: "DELETE",
                    headers: {
                        // Tambahkan header jika CI4 CSRF diaktifkan
                        // 'X-CSRF-Token': '<?php // echo csrf_hash(); ?>'
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json();
                        } else {
                            console.error("Server response was not JSON during delete:", response);
                            return response.text().then(text => { throw new Error("Unexpected server response during delete: " + text) });
                        }
                    })
                    .then(result => {
                        console.log("Delete backend result:", result);
                        displayAlert(result.message, result.status === 'success' ? 'success' : 'danger');
                    })
                    .catch(error => {
                        console.error("Error deleting row:", error);
                        displayAlert(`Terjadi kesalahan saat menghapus data: ${error.message}`, 'danger');
                    });
            } else {
                displayAlert("Baris baru berhasil dihapus.", 'success');
            }

            checkAndHighlightRows();
            hitungTotal();
            renumberRows("#" + tipe + "Body");
            console.log("Delete complete. Highlight, totals, renumbering updated.");
        } else {
            console.log("Delete cancelled.");
        }
    }

    // Fungsi untuk menandai baris baru dan mendeteksi & menandai duplikat
    // Hanya mendeteksi duplikat Tgl + Uraian + Kategori
    function checkAndHighlightRows() {
        console.log("Checking and highlighting rows (Date + Uraian + Kategori)...");
        // Hapus semua penanda duplikat yang ada dari SEMUA BARIS data-row
        // Note: row-new class is *not* removed here, only on page reload
        document.querySelectorAll("tr.data-row").forEach(row => {
            row.classList.remove("row-duplicate");
        });


        // Cek duplikat hanya di antara baris yang *terlihat*
        const duplicatesCheckMap = {}; // Format: { "date|uraian|kategori": [row1, row2, ...] }
        const visibleRows = document.querySelectorAll("#dumBody tr.data-row:not([style*='display: none']), #dukBody tr.data-row:not([style*='display: none'])");
        console.log(`Found ${visibleRows.length} visible data rows for duplicate check.`);


        visibleRows.forEach(row => {
            const dateInput = row.querySelector('.date-input');
            const uraianInput = row.querySelector('.uraian-input');
            const kategori = row.closest("#dumBody") ? "DUM" : "DUK";

            if (dateInput && dateInput.value && uraianInput && uraianInput.value.trim() !== '') {
                const date = dateInput.value;
                const uraian = uraianInput.value.trim().toLowerCase();
                const key = `${date}|${uraian}|${kategori}`;

                if (!duplicatesCheckMap[key]) {
                    duplicatesCheckMap[key] = [];
                }
                duplicatesCheckMap[key].push(row);
            }
        });

        // Iterasi melalui map dan tandai baris yang memiliki duplikat, kumpulkan pesan duplikat
        let foundDuplicates = false;
        const duplicateMessages = []; // Array to store detailed messages

        Object.keys(duplicatesCheckMap).forEach(key => {
            const rowsWithSameKey = duplicatesCheckMap[key];
            if (rowsWithSameKey.length > 1) {
                console.warn(`Found duplicate key: "${key}". Rows involved: ${rowsWithSameKey.length}`);
                foundDuplicates = true;

                // Get row numbers for the detailed message *from the visible rows*
                const rowNumbers = rowsWithSameKey.map(row => row.cells[0].textContent || 'N/A').join(', ');

                // Extract original date and uraian from a sample row
                const sampleRow = rowsWithSameKey[0];
                const date = sampleRow.querySelector('.date-input').value;
                const uraian = sampleRow.querySelector('.uraian-input').value.trim();
                const kategori = sampleRow.closest("#dumBody") ? "DUM" : "DUK";

                duplicateMessages.push(`Kategori: ${kategori}, Tanggal: ${date}, Uraian: "${uraian}" di baris No. ${rowNumbers}`);

                rowsWithSameKey.forEach(row => {
                    row.classList.add("row-duplicate");
                    console.log("Marked row as duplicate:", row);
                });
            }
        });

        // Display or clear the duplicate warning alert
        // Remove previous warning alerts first
        document.getElementById('notificationArea').querySelectorAll('.alert-warning').forEach(alert => alert.remove());

        if (foundDuplicates) {
            const fullMessage = "Terdapat duplikasi (Tanggal + Uraian + Kategori sama) pada data yang sedang ditampilkan:<br>" + duplicateMessages.join("<br>");
            displayAlert(fullMessage, 'warning', 0); // Display indefinitely
        }

        console.log("Highlighting complete. Duplicates found:", foundDuplicates);
        return foundDuplicates; // Return boolean indicating if duplicates were found
    }


    // Fungsi untuk menyimpan SEMUA perubahan
    function simpanKeDatabase() {
        console.log("Initiating save to database...");
        let dataToSave = [];
        let incompleteRows = 0;
        let hasVisibleDuplicates = false;

        // Re-check highlights just before saving
        hasVisibleDuplicates = checkAndHighlightRows();


        if (hasVisibleDuplicates) {
            displayAlert("Penyimpanan dibatalkan karena duplikasi yang terlihat. Mohon perbaiki data yang berwarna merah.", 'danger');
            return;
        }

        document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
            let tanggalInput = row.querySelector(".date-input");
            let uraianInput = row.querySelector(".uraian-input");
            let jumlahInput = row.querySelector(".dum") || row.querySelector(".duk");

            let id = row.getAttribute('data-id');
            let kategori = row.closest("#dumBody") ? "DUM" : "DUK";

            if (tanggalInput && tanggalInput.value && uraianInput && uraianInput.value.trim() !== '' && jumlahInput) {
                const cleanedJumlah = cleanNumber(jumlahInput.value || "0");
                if (isNaN(cleanedJumlah)) {
                    console.error("Invalid number format in row, skipping save:", row);
                    incompleteRows++;
                } else {
                    dataToSave.push({
                        id: id || null,
                        tanggal: tanggalInput.value,
                        uraian: uraianInput.value.trim(),
                        jumlah: cleanedJumlah,
                        kategori: kategori
                    });
                }

            } else {
                incompleteRows++;
                console.warn("Skipping incomplete row during save due to missing date, uraian, or amount input:", row);
            }
        });

        if (dataToSave.length === 0) {
            if (incompleteRows > 0) {
                displayAlert(`Tidak ada data lengkap untuk disimpan. Ditemukan ${incompleteRows} baris tidak lengkap yang dilewati.`, 'warning');
            } else {
                displayAlert("Tidak ada data baru atau perubahan yang terdeteksi.", 'info');
            }
            console.log("No data to save.");
            return;
        }

        if (!confirm(`Anda akan menyimpan ${dataToSave.length} data (termasuk update dan data baru). Lanjutkan?`)) {
            console.log("Save cancelled by user.");
            return;
        }

        const saveButton = document.querySelector("button.btn-success");
        saveButton.disabled = true;
        saveButton.textContent = "Menyimpan...";
        console.log("Sending data to backend:", dataToSave);


        fetch("<?= base_url('admin/jurnal/simpan') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                // Tambahkan header jika CI4 CSRF diaktifkan
                // 'X-CSRF-Token': '<?php // echo csrf_hash(); ?>'
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(dataToSave),
        })
            .then(response => {
                console.log("Backend response received. Status:", response.status);
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, body: ${text}`) });
                }
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    return response.text().then(text => { throw new Error("Unexpected server response (not JSON): " + text) });
                }
            })
            .then(result => {
                console.log("Simpan backend result:", result);
                displayAlert(result.message, result.status === 'success' || result.status === 'partial' ? 'success' : 'danger');
                if (result.status === 'success' || result.status === 'partial') {
                    console.log("Save successful or partial. Reloading page...");
                    // === START CACHE FILTER ===
                    sessionStorage.setItem('lastYearFilter', document.getElementById("tahunSelect").value);
                    sessionStorage.setItem('lastMonthFilter', document.getElementById("bulanSelect").value);
                    // === END CACHE FILTER ===

                    hasUnsavedChanges = false;
                    document.title = "Jurnal Kas";

                    location.reload();
                } else {
                    console.error("Save reported failure:", result.message);
                }
            })
            .catch(error => {
                console.error("Error during save fetch:", error);
                const errorMessage = error.message || "Terjadi kesalahan yang tidak diketahui saat menyimpan data.";
                displayAlert(`Terjadi kesalahan saat menyimpan data: ${errorMessage}`, 'danger');
            })
            .finally(() => {
                saveButton.disabled = false;
                saveButton.textContent = "Simpan Semua Perubahan";
                console.log("Save process finished.");
            });
    }

    // Function to filter data
    function filterData() {
        console.log("Executing filterData...");
        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;

        if (selectedYear === "" || selectedMonth === "") {
            console.log("Filter criteria incomplete (Year or Month is empty). Hiding data container.");
            document.getElementById("dataContainer").style.display = "none";
            document.getElementById("noDataMessage").style.display = "block";
            document.getElementById("totalDUM").textContent = formatNumberString(0);
            document.getElementById("totalDUK").textContent = formatNumberString(0);
            document.getElementById("totalPerHariBody").innerHTML = `<tr><td colspan="4" class="text-center">Pilih periode untuk melihat total</td></tr>`;

            document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
                row.style.display = "none";
            });

        } else {
            console.log(`Applying filter: Year=${selectedYear}, Month=${selectedMonth}. Showing data container.`);
            document.getElementById("dataContainer").style.display = "block";
            document.getElementById("noDataMessage").style.display = "none";

            document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
                let dateInput = row.querySelector(".date-input");
                if (dateInput && dateInput.value) {
                    let rowDate = new Date(dateInput.value);
                    if (!isNaN(rowDate.getTime())) {
                        let rowYear = rowDate.getFullYear();
                        let rowMonth = (rowDate.getMonth() + 1).toString().padStart(2, '0');

                        if (rowYear == selectedYear && rowMonth == selectedMonth) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    } else {
                        row.style.display = "none";
                        console.warn("Invalid date value in row, hiding:", dateInput.value, row);
                    }
                } else {
                    row.style.display = "none";
                    console.log("Hiding row due to missing/empty date input:", row);
                }
            });
        }

        checkAndHighlightRows();
        hitungTotal();
        renumberRows("#dumBody");
        renumberRows("#dukBody");
        console.log("filterData complete. Rows filtered, highlighted, totals updated, renumbered.");
    }

    // Fungsi untuk memberi nomor ulang pada baris yang *terlihat* dalam sebuah tbody
    function renumberRows(tableSelector) {
        let visibleRows = document.querySelectorAll(`${tableSelector} tr.data-row:not([style*="display: none"])`);
        visibleRows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });
    }

    // === START SCROLL TO TOP LOGIC ===
    const scrollToTopButton = document.getElementById('scroll-to-top');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 200) {
            scrollToTopButton.style.display = 'block';
        } else {
            scrollToTopButton.style.display = 'none';
        }
    });

    scrollToTopButton.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    // === END SCROLL TO TOP LOGIC ===

    // --- Function to confirm refresh if changes exist ---
    function confirmRefresh() {
        if (hasUnsavedChanges) {
            const userConfirmed = confirm("Anda memiliki perubahan yang belum disimpan. Yakin ingin me-refresh? Perubahan akan hilang.");
            if (userConfirmed) {
                console.log("User confirmed refresh despite unsaved changes.");
                // === START CACHE FILTER ===
                // Simpan filter saat ini ke sessionStorage SEBELUM reload
                sessionStorage.setItem('lastYearFilter', document.getElementById("tahunSelect").value);
                sessionStorage.setItem('lastMonthFilter', document.getElementById("bulanSelect").value);
                // === END CACHE FILTER ===

                // Clear the unsaved changes flag just before reloading
                hasUnsavedChanges = false;
                document.title = "Jurnal Kas"; // Reset title

                location.reload(); // Perform the refresh
            } else {
                console.log("User cancelled refresh.");
            }
        } else {
            // No unsaved changes, just refresh
            console.log("No unsaved changes. Performing refresh.");

            // === START CACHE FILTER ===
            // Simpan filter saat ini ke sessionStorage SEBELUM reload
            sessionStorage.setItem('lastYearFilter', document.getElementById("tahunSelect").value);
            sessionStorage.setItem('lastMonthFilter', document.getElementById("bulanSelect").value);
            // === END CACHE FILTER ===

            location.reload();
        }
    }

    // --- Prevent leaving page with unsaved changes ---
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = ''; // Chrome requires returnValue to be set
        }
    });


    // Jalankan saat dokumen selesai dimuat
    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOMContentLoaded fired. Starting initial setup.");
        const tahunSelect = document.getElementById("tahunSelect");
        const bulanSelect = document.getElementById("bulanSelect");
        const notificationArea = document.getElementById('notificationArea');

        // --- Handle Flashdata Messages from PHP (e.g., after file upload) ---
        notificationArea.querySelectorAll('.alert').forEach(alertElement => {
            if (!alertElement.classList.contains('alert-danger') && !alertElement.classList.contains('alert-warning') && alertElement.getAttribute('data-persistent') === null) {
                setTimeout(() => {
                    try { const bsAlert = new bootstrap.Alert(alertElement); bsAlert.close(); } catch (e) { alertElement.remove(); }
                }, 7000);
            }
        });
        // --- End Handle Flashdata ---


        // === START CACHE FILTER vs CURRENT DATE DEFAULT ===
        const lastYear = sessionStorage.getItem('lastYearFilter');
        const lastMonth = sessionStorage.getItem('lastMonthFilter');

        let yearToFilter, monthToFilter;

        if (lastYear && lastMonth) {
            console.log(`Found cached filter (from previous save/reload or refresh): Year=${lastYear}, Month=${lastMonth}. Using cache.`);
            yearToFilter = lastYear;
            monthToFilter = lastMonth;
            sessionStorage.removeItem('lastYearFilter');
            sessionStorage.removeItem('lastMonthFilter');
        } else {
            console.log("No cached filter found. Using current date for default filter.");
            const today = new Date();
            yearToFilter = today.getFullYear().toString();
            monthToFilter = (today.getMonth() + 1).toString().padStart(2, '0');
        }
        // === END CACHE FILTER vs CURRENT DATE DEFAULT ===

        if (tahunSelect.querySelector(`option[value="${yearToFilter}"]`)) {
            tahunSelect.value = yearToFilter;
            console.log(`Set default Year select to ${yearToFilter}`);
        } else {
            tahunSelect.value = "";
            console.log(`Year ${yearToFilter} not found in options or filter was empty. Resetting year select.`);
        }

        if (bulanSelect.querySelector(`option[value="${monthToFilter}"]`)) {
            bulanSelect.value = monthToFilter;
            console.log(`Set default Month select to ${monthToFilter}`);
        } else {
            bulanSelect.value = "";
            console.log(`Month ${monthToFilter} not found in options or filter was empty. Resetting month select.`);
        }

        // === INITIAL SETUP AFTER FILTER IS SET ===
        document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
            addRowEventListeners(row);
        });
        console.log(`Added event listeners to all existing ${document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").length} data rows loaded from PHP.`);


        console.log("Calling filterData for initial display.");
        filterData();
        // === END INITIAL SETUP ===


        tahunSelect.addEventListener("change", filterData);
        bulanSelect.addEventListener("change", filterData);
        console.log("Added listeners to filter selects.");


        console.log("DOMContentLoaded setup complete.");

        // Optional: Re-run filterData after a small delay
        setTimeout(() => {
            console.log("Running delayed post-load filterData check...");
            filterData();
            console.log("Delayed post-load filterData check finished.");
        }, 200);

    });
</script>
<?= $this->endSection(); ?>
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
                    class="d-flex flex-column flex-md-row align-items-center gap-2">
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

            <!-- Tombol Simpan Semua Perubahan dipindah ke sini -->
            <button class="btn btn-success mb-3" onclick="simpanKeDatabase()">Simpan Semua Perubahan</button>


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
                            <?php $no = 1; foreach ($jurnal_kas as $k): ?>
                                <?php if ($k['kategori'] == 'DUM'): ?>
                                    <tr data-id="<?= $k['id'] ?>" class="data-row" style="display: none;">
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <input type="date" class="form-control form-control-sm date-dum"
                                                value="<?= date('Y-m-d', strtotime($k['tanggal'])) ?>" required
                                                oninput="hitungTotal()">
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm" value="<?= $k['uraian'] ?>"></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm dum"
                                                value="<?= number_format($k['jumlah'], 0, ',', '.') ?>"
                                                oninput="formatRibuan(this)">
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-danger btn-sm" onclick="hapusBaris(this, 'dum')">Hapus</button>
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
                <button class="btn btn-info btn-sm" style="width: 100%; display: block;" onclick="tambahDUM()">Tambah DUM</button>

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
                            <?php $no = 1; foreach ($jurnal_kas as $k): ?>
                                <?php if ($k['kategori'] == 'DUK'): ?>
                                    <tr data-id="<?= $k['id'] ?>" class="data-row" style="display: none;">
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <input type="date" class="form-control form-control-sm date-duk"
                                                value="<?= date('Y-m-d', strtotime($k['tanggal'])) ?>" required
                                                oninput="hitungTotal()">
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm" value="<?= $k['uraian'] ?>"></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm duk"
                                                value="<?= number_format($k['jumlah'], 0, ',', '.') ?>"
                                                oninput="formatRibuan(this)">
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-danger btn-sm" onclick="hapusBaris(this, 'duk')">Hapus</button>
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
                <button class="btn btn-info btn-sm" style="width: 100%; display: block;" onclick="tambahDUK()">Tambah DUK</button>


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

<script>
    // Fungsi untuk membersihkan angka dari format ribuan
    function cleanNumber(value) {
        return parseFloat(value.replace(/\./g, "").replace(",", ".")) || 0;
    }

    // Fungsi untuk memformat angka ke format ribuan (IDR)
    function formatRibuan(input) {
        let number = input.value.replace(/\D/g, "");
         if (number === "") {
             input.value = "";
        } else {
             input.value = new Intl.NumberFormat("id-ID").format(number);
        }
        hitungTotal(); // Perbarui total setelah format
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

        row.classList.add("data-row");
        row.innerHTML = `
            <td></td> <!-- Placeholder for row number -->
            <td><input type="date" class="form-control form-control-sm date-dum" required oninput="hitungTotal()"></td>
            <td><input type="text" class="form-control form-control-sm" placeholder="Uraian"></td>
            <td><input type="text" class="form-control form-control-sm dum" value="0" oninput="formatRibuan(this)"></td>
            <td style="text-align: center;">
                <button class="btn btn-danger btn-sm" onclick="hapusBaris(this, 'dum')">Hapus</button>
            </td>
        `;

        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;
        if (selectedYear && selectedMonth) {
            let defaultDate = `${selectedYear}-${selectedMonth}-01`;
             try {
                 const checkDate = new Date(defaultDate);
                 if (!isNaN(checkDate.getTime())) {
                      row.querySelector(".date-dum").value = defaultDate;
                 } else {
                      row.querySelector(".date-dum").value = `${selectedYear}-${selectedMonth}-01`;
                 }
             } catch (e) {
                  row.querySelector(".date-dum").value = `${selectedYear}-${selectedMonth}-01`;
             }
        } else {
             let today = new Date().toISOString().split('T')[0];
             row.querySelector(".date-dum").value = today;
        }

         const rowDate = new Date(row.querySelector(".date-dum").value);
         const rowYear = rowDate.getFullYear();
         const rowMonth = (rowDate.getMonth() + 1).toString().padStart(2, '0');

         if (selectedYear == rowYear && selectedMonth == rowMonth) {
             row.style.display = "";
         } else {
              row.style.display = "none";
         }

        hitungTotal();
        renumberRows("#dumBody");
    }

    // Fungsi untuk menambah baris DUK
    function tambahDUK() {
        let tbody = document.getElementById("dukBody");
        let row = tbody.insertRow();

        row.classList.add("data-row");
        row.innerHTML = `
            <td></td>
            <td><input type="date" class="form-control form-control-sm date-duk" required oninput="hitungTotal()"></td>
            <td><input type="text" class="form-control form-control-sm" placeholder="Uraian"></td>
            <td><input type="text" class="form-control form-control-sm duk" value="0" oninput="formatRibuan(this)"></td>
            <td style="text-align: center;">
                <button class="btn btn-danger btn-sm" onclick="hapusBaris(this, 'duk')">Hapus</button>
            </td>
        `;

        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;
        if (selectedYear && selectedMonth) {
            let defaultDate = `${selectedYear}-${selectedMonth}-01`;
             try {
                 const checkDate = new Date(defaultDate);
                 if (!isNaN(checkDate.getTime())) {
                      row.querySelector(".date-duk").value = defaultDate;
                 } else {
                     row.querySelector(".date-duk").value = `${selectedYear}-${selectedMonth}-01`;
                 }
             } catch (e) {
                  row.querySelector(".date-duk").value = `${selectedYear}-${selectedMonth}-01`;
             }
        } else {
             let today = new Date().toISOString().split('T')[0];
             row.querySelector(".date-duk").value = today;
        }

         const rowDate = new Date(row.querySelector(".date-duk").value);
         const rowYear = rowDate.getFullYear();
         const rowMonth = (rowDate.getMonth() + 1).toString().padStart(2, '0');

         if (selectedYear == rowYear && selectedMonth == rowMonth) {
             row.style.display = "";
         } else {
              row.style.display = "none";
         }

        hitungTotal();
        renumberRows("#dukBody");
    }

    // Fungsi untuk menghitung total DUM & DUK dari baris yang *terlihat*
    function hitungTotal() {
        let totalDUM = 0;
        let totalDUK = 0;

        document.querySelectorAll("#dumBody tr.data-row").forEach(row => {
            if (row.style.display !== 'none') {
                 let input = row.querySelector(".dum");
                 if (input) {
                    totalDUM += cleanNumber(input.value);
                 }
            }
        });

        document.querySelectorAll("#dukBody tr.data-row").forEach(row => {
            if (row.style.display !== 'none') {
                let input = row.querySelector(".duk");
                if (input) {
                    totalDUK += cleanNumber(input.value);
                }
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

        document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
             if (row.style.display !== 'none') {
                let tanggalInput = row.querySelector("input[type='date']");
                let dumInput = row.querySelector(".dum");
                let dukInput = row.querySelector(".duk");

                if (tanggalInput && tanggalInput.value) {
                    let tanggal = tanggalInput.value.trim();
                    let yearMonth = tanggal.substring(0, 7);

                    if (!totals[yearMonth]) totals[yearMonth] = { dum: 0, duk: 0 };

                    if (dumInput) {
                        totals[yearMonth].dum += cleanNumber(dumInput.value || "0");
                    }
                    if (dukInput) {
                        totals[yearMonth].duk += cleanNumber(dukInput.value || "0");
                    }
                }
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
        let rowNumber = row.cells[0].textContent;

        if (confirm(`Apakah Anda yakin ingin menghapus baris No. ${rowNumber} ini?`)) {
            row.remove();

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
                    console.log("Delete result:", result);
                })
                .catch(error => {
                    console.error("Error deleting row:", error);
                    alert("Terjadi kesalahan saat menghapus data. Detail di console log.");
                });
            }

            hitungTotal();
            renumberRows("#" + tipe + "Body");
        }
    }

    // Fungsi untuk menyimpan SEMUA perubahan
    function simpanKeDatabase() {
        let dataToSave = [];
        let incompleteRows = 0;

        // Proses SEMUA baris .data-row di DOM, terlepas dari visibility
        document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
            let tanggalInput = row.querySelector("input[type='date']");
            let uraianInput = row.querySelector("input[type='text']");
            let jumlahInput = row.querySelector(".dum") || row.querySelector(".duk");

            let id = row.getAttribute('data-id');
            let kategori = row.closest("#dumBody") ? "DUM" : "DUK";

            if (tanggalInput && tanggalInput.value && uraianInput && uraianInput.value.trim() !== '' && jumlahInput) {
                dataToSave.push({
                    id: id || null,
                    tanggal: tanggalInput.value,
                    uraian: uraianInput.value.trim(),
                    jumlah: cleanNumber(jumlahInput.value || "0"),
                    kategori: kategori
                });
             } else {
                 incompleteRows++;
                 console.warn("Skipping incomplete row during save:", row);
             }
        });

        if (dataToSave.length === 0) {
             if (incompleteRows > 0) {
                  alert(`Tidak ada data lengkap untuk disimpan. Ditemukan ${incompleteRows} baris tidak lengkap yang dilewati.`);
             } else {
                  alert("Tidak ada data baru atau perubahan yang terdeteksi.");
             }
            return;
        }

        if (!confirm(`Anda akan menyimpan ${dataToSave.length} data (termasuk update dan data baru). Lanjutkan?`)) {
             return;
        }

        const saveButton = document.querySelector("button.btn-success.mb-3");
        saveButton.disabled = true;
        saveButton.textContent = "Menyimpan...";

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
            console.log("Simpan result:", result);
            alert(result.message);
            if (result.status === 'success' || result.status === 'partial') {
                // === START CACHE FILTER ===
                // Simpan filter saat ini ke sessionStorage SEBELUM reload
                sessionStorage.setItem('lastYearFilter', document.getElementById("tahunSelect").value);
                sessionStorage.setItem('lastMonthFilter', document.getElementById("bulanSelect").value);
                // === END CACHE FILTER ===
                location.reload(); // Reload halaman
            }
        })
        .catch(error => {
            console.error("Error saving data:", error);
             const errorMessage = error.message || "Terjadi kesalahan yang tidak diketahui saat menyimpan data.";
            alert(`Terjadi kesalahan saat menyimpan data: ${errorMessage}. Detail lebih lanjut di console log.`);
        })
         .finally(() => {
             saveButton.disabled = false;
             saveButton.textContent = "Simpan Semua Perubahan";
         });
    }

    // Fungsi untuk memfilter data
    function filterData() {
        let selectedYear = document.getElementById("tahunSelect").value;
        let selectedMonth = document.getElementById("bulanSelect").value;

        if (selectedYear === "" || selectedMonth === "") {
            document.getElementById("dataContainer").style.display = "none";
            document.getElementById("noDataMessage").style.display = "block";
            document.getElementById("totalDUM").textContent = formatNumberString(0);
            document.getElementById("totalDUK").textContent = formatNumberString(0);
            document.getElementById("totalPerHariBody").innerHTML = `<tr><td colspan="4" class="text-center">Pilih periode untuk melihat total</td></tr>`;

            document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
                row.style.display = "none";
            });

            return;
        }

        document.getElementById("dataContainer").style.display = "block";
        document.getElementById("noDataMessage").style.display = "none";

        document.querySelectorAll("#dumBody tr.data-row, #dukBody tr.data-row").forEach(row => {
            let dateInput = row.querySelector("input[type='date']");
            if (dateInput && dateInput.value) {
                let rowDate = new Date(dateInput.value);
                let rowYear = rowDate.getFullYear();
                let rowMonth = (rowDate.getMonth() + 1).toString().padStart(2, '0');

                if (rowYear == selectedYear && rowMonth == selectedMonth) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            } else {
                 row.style.display = "none";
            }
        });

        hitungTotal();
        renumberRows("#dumBody");
        renumberRows("#dukBody");
    }

    // Fungsi untuk memberi nomor ulang pada baris yang *terlihat*
    function renumberRows(tableSelector) {
        let visibleRows = document.querySelectorAll(`${tableSelector} tr.data-row:not([style*="display: none"])`);
        visibleRows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });
    }

    // Jalankan saat dokumen selesai dimuat
    document.addEventListener("DOMContentLoaded", function () {
        const tahunSelect = document.getElementById("tahunSelect");
        const bulanSelect = document.getElementById("bulanSelect");

        // === START CACHE FILTER ===
        // Coba ambil filter dari sessionStorage
        const lastYear = sessionStorage.getItem('lastYearFilter');
        const lastMonth = sessionStorage.getItem('lastMonthFilter');

        let yearToFilter, monthToFilter;

        if (lastYear && lastMonth) {
            // Jika ada di sessionStorage, gunakan nilai tersebut
            yearToFilter = lastYear;
            monthToFilter = lastMonth;

            // Hapus dari sessionStorage setelah digunakan agar tidak persist
            sessionStorage.removeItem('lastYearFilter');
            sessionStorage.removeItem('lastMonthFilter');
        } else {
            // Jika tidak ada di sessionStorage, gunakan tanggal hari ini
            const today = new Date();
            yearToFilter = today.getFullYear().toString();
            monthToFilter = (today.getMonth() + 1).toString().padStart(2, '0');
        }
        // === END CACHE FILTER ===


        // Setel nilai dropdown berdasarkan yearToFilter dan monthToFilter
        // Pastikan nilainya ada dalam opsi dropdown sebelum disetel
        if (tahunSelect.querySelector(`option[value="${yearToFilter}"]`)) {
             tahunSelect.value = yearToFilter;
        } else {
             tahunSelect.value = ""; // Reset jika nilai tidak valid
        }

        if (bulanSelect.querySelector(`option[value="${monthToFilter}"]`)) {
            bulanSelect.value = monthToFilter;
        } else {
             bulanSelect.value = ""; // Reset jika nilai tidak valid
        }


        // Panggil filterData() untuk menampilkan data berdasarkan pilihan yang sudah disetel
        filterData();

        // Tambahkan event listener untuk perubahan manual pada dropdown filter
        tahunSelect.addEventListener("change", filterData);
        bulanSelect.addEventListener("change", filterData);

        // Tambahkan event listener untuk input jumlah dan tanggal
        document.querySelectorAll(".dum, .duk").forEach(input => {
             // formatRibuan sudah memanggil hitungTotal()
        });
        document.querySelectorAll(".date-dum, .date-duk").forEach(input => {
             input.addEventListener("change", hitungTotal);
        });

    });
</script>
<?= $this->endSection(); ?>
<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Anggota</h3>
        <div>
            <button type="button" id="cetakKartuBtn" class="btn btn-info me-2">Cetak Kartu Anggota</button>
            <a href="<?= site_url('admin/anggota') ?>" class="btn btn-warning">Kembali</a>
        </div>
    </div>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('admin/updateAnggota') ?>" method="POST" id="anggotaEditForm">
            <?= csrf_field(); ?> <!-- Tambahkan CSRF untuk keamanan -->
            <input type="hidden" name="id_anggota" value="<?= $anggota->id_anggota ?>">

            <div class="row">
                <div class="col-md-6">
                    <label for="nama" class="form-label">Nama:</label>
                    <input type="text" id="nama" name="nama" value="<?= old('nama', $anggota->nama); ?>"
                        class="form-control text-only" required>
                    <small class="text-muted">Masukkan nama lengkap tanpa gelar (hanya huruf, min. 3 karakter)</small>
                    <div class="invalid-feedback">Nama hanya boleh berisi huruf dan spasi</div>
                </div>
                <div class="col-md-6">
                    <label for="nik" class="form-label">NIK:</label>
                    <input type="text" id="nik" name="nik" value="<?= old('nik', $anggota->nik); ?>"
                        class="form-control numbers-only" maxlength="16" required>
                    <small class="text-muted">Masukkan 16 digit NIK tanpa spasi atau karakter khusus</small>
                    <div class="invalid-feedback">NIK hanya boleh berisi angka (16 digit)</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="no_ba" class="form-label">No BA:</label>
                    <input type="text" id="no_ba" name="no_ba" value="<?= old('no_ba', $anggota->no_ba); ?>"
                        class="form-control" required>
                    <small class="text-muted">Sesuaikan BA</small>
                </div>
                <div class="col-md-6">
                    <label for="dusun" class="form-label">Dusun:</label>
                    <select id="dusun" name="dusun" class="form-control" required>
                        <option value="" disabled <?= old('dusun', $anggota->dusun) == '' ? 'selected' : '' ?>>Pilih Dusun
                        </option>
                        <option value="Sapon" <?= old('dusun', $anggota->dusun) == 'Sapon' ? 'selected' : '' ?>>Sapon
                        </option>
                        <option value="Jekeling" <?= old('dusun', $anggota->dusun) == 'Jekeling' ? 'selected' : '' ?>>
                            Jekeling</option>
                        <option value="Gerjen" <?= old('dusun', $anggota->dusun) == 'Gerjen' ? 'selected' : '' ?>>Gerjen
                        </option>
                        <option value="Tubin" <?= old('dusun', $anggota->dusun) == 'Tubin' ? 'selected' : '' ?>>Tubin
                        </option>
                        <option value="Senden" <?= old('dusun', $anggota->dusun) == 'Senden' ? 'selected' : '' ?>>Senden
                        </option>
                        <option value="Karang" <?= old('dusun', $anggota->dusun) == 'Karang' ? 'selected' : '' ?>>Karang
                        </option>
                        <option value="Kwarakan" <?= old('dusun', $anggota->dusun) == 'Kwarakan' ? 'selected' : '' ?>>
                            Kwarakan</option>
                        <option value="Diran" <?= old('dusun', $anggota->dusun) == 'Diran' ? 'selected' : '' ?>>Diran
                        </option>
                        <option value="Geden" <?= old('dusun', $anggota->dusun) == 'Geden' ? 'selected' : '' ?>>Geden
                        </option>
                        <option value="Bekelan" <?= old('dusun', $anggota->dusun) == 'Bekelan' ? 'selected' : '' ?>>Bekelan
                        </option>
                        <option value="Sedan" <?= old('dusun', $anggota->dusun) == 'Sedan' ? 'selected' : '' ?>>Sedan
                        </option>
                        <option value="Jurug" <?= old('dusun', $anggota->dusun) == 'Jurug' ? 'selected' : '' ?>>Jurug
                        </option>
                        <option value="Ledok" <?= old('dusun', $anggota->dusun) == 'Ledok' ? 'selected' : '' ?>>Ledok
                        </option>
                        <option value="Gentan" <?= old('dusun', $anggota->dusun) == 'Gentan' ? 'selected' : '' ?>>Gentan
                        </option>
                        <option value="Pleret" <?= old('dusun', $anggota->dusun) == 'Pleret' ? 'selected' : '' ?>>Pleret
                        </option>
                        <option value="Tuksono" <?= old('dusun', $anggota->dusun) == 'Tuksono' ? 'selected' : '' ?>>Tuksono
                        </option>
                        <option value="Kelompok" <?= old('dusun', $anggota->dusun) == 'Kelompok' ? 'selected' : '' ?>>
                            Kelompok</option>
                        <option value="Luar" <?= old('dusun', $anggota->dusun) == 'Luar' ? 'selected' : '' ?>>Luar</option>
                    </select>
                    <small class="text-muted">Pilih dusun tempat tinggal anggota</small>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="alamat" class="form-label">Alamat Lengkap:</label>
                    <textarea id="alamat" name="alamat" class="form-control"
                        required><?= old('alamat', $anggota->alamat) ?></textarea>
                    <small class="text-muted">Masukkan alamat lengkap (min. 10 karakter)</small>
                </div>
                <div class="col-md-6">
                    <label for="pekerjaan" class="form-label">Pekerjaan:</label>
                    <input type="text" id="pekerjaan" name="pekerjaan"
                        value="<?= old('pekerjaan', $anggota->pekerjaan); ?>" class="form-control text-only" required>
                    <small class="text-muted">Masukkan pekerjaan utama anggota (hanya huruf)</small>
                    <div class="invalid-feedback">Pekerjaan hanya boleh berisi huruf dan spasi</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="tgl_lahir" class="form-label">Tanggal Lahir:</label>
                    <input type="date" id="tgl_lahir" name="tgl_lahir"
                        value="<?= old('tgl_lahir', $anggota->tgl_lahir); ?>" class="form-control" required>
                    <small class="text-muted">Format: YYYY-MM-DD</small>
                </div>
                <div class="col-md-6">
                    <label for="nama_pasangan" class="form-label">Nama Pasangan:</label>
                    <input type="text" id="nama_pasangan" name="nama_pasangan"
                        value="<?= old('nama_pasangan', $anggota->nama_pasangan); ?>" class="form-control text-only">
                    <small class="text-muted">Opsional - masukkan nama suami/istri jika ada</small>
                    <div class="invalid-feedback">Nama pasangan hanya boleh berisi huruf dan spasi</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="aktif" <?= old('status', $anggota->status) == 'aktif' ? 'selected' : '' ?>>Aktif
                        </option>
                        <option value="nonaktif" <?= old('status', $anggota->status) == 'nonaktif' ? 'selected' : '' ?>>
                            Nonaktif</option>
                    </select>
                    <small class="text-muted">Pilih status keanggotaan saat ini</small>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col text-end">
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-3">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p><?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validate text-only fields (no numbers allowed)
        const textOnlyInputs = document.querySelectorAll('.text-only');
        textOnlyInputs.forEach(input => {
            input.addEventListener('input', function () {
                // Remove any numbers from the input
                this.value = this.value.replace(/[0-9]/g, '');

                // Check if the input is valid (contains only letters, spaces, and common punctuation)
                const isValid = /^[a-zA-Z\s.,'-]*$/.test(this.value);

                if (!isValid && this.value.length > 0) { // Show invalid only if there's content and it's bad
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Validate numbers-only fields (no letters allowed)
        const numbersOnlyInputs = document.querySelectorAll('.numbers-only');
        numbersOnlyInputs.forEach(input => {
            input.addEventListener('input', function () {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');

                // For NIK, check if it's exactly 16 digits
                if (this.id === 'nik') {
                    const isValid = this.value.length === 16;
                    if (this.value.length > 0 && !isValid && this.value.length <= 16) { // Check length for invalid only if not empty and not over max
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        });

        // Form submission validation
        const form = document.getElementById('anggotaEditForm');
        form.addEventListener('submit', function (event) {
            let formIsValid = true;

            // Check for any invalid inputs (custom validation for text-only and numbers-only)
            const invalidCustomInputs = document.querySelectorAll('.is-invalid');
            if (invalidCustomInputs.length > 0) {
                event.preventDefault();
                alert('Mohon perbaiki data yang tidak valid sebelum menyimpan.');
                invalidCustomInputs[0].focus();
                formIsValid = false;
                return;
            }

            // Check minimum length for nama
            const namaInput = document.getElementById('nama');
            if (namaInput.value.trim().length < 3) {
                event.preventDefault();
                namaInput.classList.add('is-invalid');
                if (formIsValid) alert('Nama harus memiliki minimal 3 karakter.'); // Only alert once
                namaInput.focus();
                formIsValid = false;
            } else {
                namaInput.classList.remove('is-invalid');
            }

            // Check NIK length
            const nikInput = document.getElementById('nik');
            if (nikInput.value.length !== 16) {
                event.preventDefault();
                nikInput.classList.add('is-invalid');
                if (formIsValid) alert('NIK harus terdiri dari 16 digit angka.'); // Only alert once
                nikInput.focus();
                formIsValid = false;
            } else {
                nikInput.classList.remove('is-invalid');
            }

            // Check Alamat length
            const alamatInput = document.getElementById('alamat');
            if (alamatInput.value.trim().length < 10) {
                event.preventDefault();
                alamatInput.classList.add('is-invalid'); // Bootstrap will show its message if you add one
                if (formIsValid) alert('Alamat lengkap harus memiliki minimal 10 karakter.');
                alamatInput.focus();
                formIsValid = false;
            } else {
                alamatInput.classList.remove('is-invalid');
            }


            // Check all required fields using HTML5 validation API
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                if (formIsValid) alert('Mohon lengkapi semua field yang wajib diisi.'); // Only alert once
                // Find first invalid element not yet focused
                let firstInvalid = form.querySelector(':invalid:not(.is-invalid)');
                if (firstInvalid) firstInvalid.focus();
                formIsValid = false;
            }
            form.classList.add('was-validated'); // Trigger Bootstrap's native validation styles

            if (!formIsValid) {
                event.preventDefault(); // Ensure form doesn't submit if any validation failed
            }
        });


        // --- SCRIPT UNTUK CETAK KARTU ---
        const cetakKartuBtn = document.getElementById('cetakKartuBtn');
        if (cetakKartuBtn) {
            cetakKartuBtn.addEventListener('click', function () {
                const nama = document.getElementById('nama').value;
                const nik = document.getElementById('nik').value;
                const noBa = document.getElementById('no_ba').value;
                const dusun = document.getElementById('dusun').value;
                const tglLahir = document.getElementById('tgl_lahir').value; // Ambil tanggal lahir
                const alamat = document.getElementById('alamat').value;

                // Validasi sederhana sebelum mencetak
                if (!nama.trim() || !nik.trim() || !noBa.trim() || !dusun.trim()) {
                    alert('Pastikan data Nama, NIK, No BA, dan Dusun sudah terisi dengan benar untuk mencetak kartu.');
                    return;
                }
                if (nik.trim().length !== 16) {
                    alert('NIK harus 16 digit untuk mencetak kartu.');
                    return;
                }

                // Format tanggal lahir jika ada
                let tglLahirFormatted = 'N/A';
                if (tglLahir) {
                    try {
                        const dateObj = new Date(tglLahir);
                        const day = String(dateObj.getDate()).padStart(2, '0');
                        const month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
                        const year = dateObj.getFullYear();
                        tglLahirFormatted = `${day}-${month}-${year}`;
                    } catch (e) {
                        console.error("Error formatting date:", e);
                        tglLahirFormatted = tglLahir; // fallback to original if parsing fails
                    }
                }


                const cardHtml = `
                    <html>
                    <head>
                        <title>Kartu Anggota - ${nama}</title>
                        <style>
                            body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f0f0f0; }
                            .card-container {
                                width: 330px; /* Sekitar 8.7 cm */
                                height: 205px; /* Sekitar 5.4 cm - ukuran standar kartu */
                                border: 1px solid #333;
                                background-color: #fff;
                                padding: 15px;
                                box-shadow: 0 0 10px rgba(0,0,0,0.15);
                                display: flex;
                                flex-direction: column;
                                box-sizing: border-box;
                            }
                            .card-header { text-align: center; border-bottom: 1px dashed #ccc; padding-bottom: 8px; margin-bottom: 8px; }
                            .card-header h4 { margin: 0; font-size: 16px; font-weight: bold; color: #2c3e50; }
                            .card-header p { margin: 2px 0 0; font-size: 11px; color: #555; }
                            .card-body { font-size: 11px; line-height: 1.5; }
                            .card-body table { width: 100%; border-collapse: collapse; }
                            .card-body td { padding: 2px 0; vertical-align: top;}
                            .card-body td:first-child { font-weight: bold; width: 80px; } /* Label column */
                            .card-footer { text-align: center; font-size: 9px; color: #777; margin-top: auto; padding-top: 8px; border-top: 1px dashed #ccc;}

                            @media print {
                                body { background-color: #fff; margin:0; padding:0; display: block;}
                                .card-container {
                                    margin: 0 auto; /* Center on page for single print */
                                    box-shadow: none;
                                    border: 1px solid #000; /* Make sure border prints */
                                    page-break-inside: avoid;
                                }
                                /* Sembunyikan tombol print jika ada di halaman print preview */
                                .no-print { display: none !important; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="card-container">
                            <div class="card-header">
                                <h4>KOPERASI SIDOMANUNGGAL</h4>
                                <p>KARTU TANDA ANGGOTA</p>
                            </div>
                            <div class="card-body">
                                <table>
                                    <tr><td>Nama</td><td>: ${nama.toUpperCase()}</td></tr>
                                    <tr><td>NIK</td><td>: ${nik}</td></tr>
                                    <tr><td>No. BA</td><td>: ${noBa}</td></tr>
                                    <tr><td>Tgl. Lahir</td><td>: ${tglLahirFormatted}</td></tr>
                                    <tr><td>Dusun</td><td>: ${dusun}</td></tr>
                                    <tr><td>Alamat</td><td>: ${alamat}</td></tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                Dicetak pada: ${new Date().toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })}
                            </div>
                        </div>
                        <script type="text/javascript">
                            window.onload = function() {
                                window.print();
                                // Anda bisa tambahkan window.close(); di sini jika ingin otomatis menutup setelah print,
                                // tapi kadang lebih baik user yang menutup manual.
                                // setTimeout(function(){ window.close(); }, 500);
                            }
                        <\/script>
                    </body>
                    </html>
                `;

                const printWindow = window.open('', '_blank', 'width=500,height=400,scrollbars=yes,resizable=yes');
                if (printWindow) {
                    printWindow.document.open();
                    printWindow.document.write(cardHtml);
                    printWindow.document.close();
                    printWindow.focus(); // Bring the new window to the front
                } else {
                    alert('Gagal membuka jendela cetak. Pastikan pop-up blocker tidak aktif.');
                }
            });
        }

        // Perbaikan kecil untuk validasi NIK agar kelas is-invalid dihapus jika valid
        const nikInputForValidation = document.getElementById('nik');
        if (nikInputForValidation) {
            nikInputForValidation.addEventListener('input', function () {
                if (this.value.length === 16 && /^[0-9]+$/.test(this.value)) {
                    this.classList.remove('is-invalid');
                } else if (this.value.length > 0) { // Hanya tambahkan is-invalid jika ada input dan tidak valid
                    this.classList.add('is-invalid');
                }
            });
        }
    });
</script>

<?= $this->endSection(); ?>
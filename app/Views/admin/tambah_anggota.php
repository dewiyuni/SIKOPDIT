<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Anggota</h3>
        <a href="<?= site_url('admin/anggota') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="card p-4 mt-3">
        <form action="<?= site_url('admin/simpanAnggota') ?>" method="POST" id="anggotaForm">
            <?= csrf_field(); ?>

            <div class="row">
                <div class="col-md-6">
                    <label for="nama" class="form-label">Nama:</label>
                    <input type="text" id="nama" name="nama" class="form-control text-only" required>
                    <small class="text-muted">Masukkan nama lengkap tanpa gelar (hanya huruf, min. 3 karakter)</small>
                    <div class="invalid-feedback">Nama hanya boleh berisi huruf dan spasi</div>
                </div>
                <div class="col-md-6">
                    <label for="nik" class="form-label">NIK:</label>
                    <input type="text" id="nik" name="nik" class="form-control numbers-only" maxlength="16" required>
                    <small class="text-muted">Masukkan 16 digit NIK tanpa spasi atau karakter khusus</small>
                    <div class="invalid-feedback">NIK hanya boleh berisi angka (16 digit)</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="no_ba" class="form-label">No BA:</label>
                    <input type="text" id="no_ba" name="no_ba" class="form-control" required>
                    <small class="text-muted">Sesuaikan BA</small>
                </div>
                <div class="col-md-6">
                    <label for="dusun" class="form-label">Dusun:</label>
                    <select id="dusun" name="dusun" class="form-control" required>
                        <option value="" disabled selected>Pilih Dusun</option>
                        <option value="Sapon">Sapon</option>
                        <option value="Jekeling">Jekeling</option>
                        <option value="Gerjen">Gerjen</option>
                        <option value="Tubin">Tubin</option>
                        <option value="Senden">Senden</option>
                        <option value="Karang">Karang</option>
                        <option value="Kwarakan">Kwarakan</option>
                        <option value="Diran">Diran</option>
                        <option value="Geden">Geden</option>
                        <option value="Bekelan">Bekelan</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Jurug">Jurug</option>
                        <option value="Ledok">Ledok</option>
                        <option value="Gentan">Gentan</option>
                        <option value="Pleret">Pleret</option>
                        <option value="Tuksono">Tuksono</option>
                        <option value="Kelompok">Kelompok</option>
                        <option value="Luar">Luar</option>
                    </select>
                    <small class="text-muted">Pilih dusun tempat tinggal anggota</small>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="alamat" class="form-label">Alamat:</label>
                    <textarea id="alamat" name="alamat" class="form-control" required minlength="10"></textarea>
                    <small class="text-muted">Masukkan alamat lengkap termasuk RT/RW (min. 10 karakter)</small>
                </div>
                <div class="col-md-6">
                    <label for="pekerjaan" class="form-label">Pekerjaan:</label>
                    <input type="text" id="pekerjaan" name="pekerjaan" class="form-control text-only" required>
                    <small class="text-muted">Masukkan pekerjaan utama anggota (hanya huruf)</small>
                    <div class="invalid-feedback">Pekerjaan hanya boleh berisi huruf dan spasi</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="tgl_lahir" class="form-label">Tanggal Lahir:</label>
                    <input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control" required>
                    <small class="text-muted">Format: YYYY-MM-DD (anggota harus berusia minimal 17 tahun)</small>
                    <div class="invalid-feedback" id="age-feedback">Anggota harus berusia minimal 17 tahun</div>
                </div>
                <div class="col-md-6">
                    <label for="nama_pasangan" class="form-label">Nama Pasangan:</label>
                    <input type="text" id="nama_pasangan" name="nama_pasangan" class="form-control text-only">
                    <small class="text-muted">Opsional - masukkan nama suami/istri jika ada</small>
                    <div class="invalid-feedback">Nama pasangan hanya boleh berisi huruf dan spasi</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <small class="text-muted">Pilih status keanggotaan saat ini</small>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col text-end">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </form>
    </div>
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

                if (!isValid) {
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
                    if (this.value.length > 0 && !isValid) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        });

        // Validate age (minimum 17 years)
        const birthDateInput = document.getElementById('tgl_lahir');
        birthDateInput.addEventListener('change', function () {
            const birthDate = new Date(this.value);
            const today = new Date();

            // Calculate age
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age < 17) {
                this.classList.add('is-invalid');
                document.getElementById('age-feedback').style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                document.getElementById('age-feedback').style.display = 'none';
            }
        });

        // Form submission validation
        const form = document.getElementById('anggotaForm');
        form.addEventListener('submit', function (event) {
            // Check for any invalid inputs
            const invalidInputs = document.querySelectorAll('.is-invalid');
            if (invalidInputs.length > 0) {
                event.preventDefault();
                alert('Mohon perbaiki data yang tidak valid sebelum menyimpan.');
                invalidInputs[0].focus();
            }

            // Check minimum length for nama
            const namaInput = document.getElementById('nama');
            if (namaInput.value.length < 3) {
                event.preventDefault();
                namaInput.classList.add('is-invalid');
                alert('Nama harus memiliki minimal 3 karakter.');
                namaInput.focus();
            }

            // Check NIK length
            const nikInput = document.getElementById('nik');
            if (nikInput.value.length !== 16) {
                event.preventDefault();
                nikInput.classList.add('is-invalid');
                alert('NIK harus terdiri dari 16 digit angka.');
                nikInput.focus();
            }
        });
    });
</script>

<?= $this->endSection(); ?>
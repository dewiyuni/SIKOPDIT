<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Anggota</h3>
        <a href="<?= site_url('admin/anggota') ?>" class="btn btn-warning">Kembali</a>
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
                        <option value="" disabled selected>Pilih Dusun</option>
                        <option value="Sapon" <?= $anggota->dusun == 'Sapon' ? 'selected' : '' ?>>Sapon</option>
                        <option value="Jekeling" <?= $anggota->dusun == 'Jekeling' ? 'selected' : '' ?>>Jekeling</option>
                        <option value="Gerjen" <?= $anggota->dusun == 'Gerjen' ? 'selected' : '' ?>>Gerjen</option>
                        <option value="Tubin" <?= $anggota->dusun == 'Tubin' ? 'selected' : '' ?>>Tubin</option>
                        <option value="Senden" <?= $anggota->dusun == 'Senden' ? 'selected' : '' ?>>Senden</option>
                        <option value="Karang" <?= $anggota->dusun == 'Karang' ? 'selected' : '' ?>>Karang</option>
                        <option value="Kwarakan" <?= $anggota->dusun == 'Kwarakan' ? 'selected' : '' ?>>Kwarakan</option>
                        <option value="Diran" <?= $anggota->dusun == 'Diran' ? 'selected' : '' ?>>Diran</option>
                        <option value="Geden" <?= $anggota->dusun == 'Geden' ? 'selected' : '' ?>>Geden</option>
                        <option value="Bekelan" <?= $anggota->dusun == 'Bekelan' ? 'selected' : '' ?>>Bekelan</option>
                        <option value="Sedan" <?= $anggota->dusun == 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                        <option value="Jurug" <?= $anggota->dusun == 'Jurug' ? 'selected' : '' ?>>Jurug</option>
                        <option value="Ledok" <?= $anggota->dusun == 'Ledok' ? 'selected' : '' ?>>Ledok</option>
                        <option value="Gentan" <?= $anggota->dusun == 'Gentan' ? 'selected' : '' ?>>Gentan</option>
                        <option value="Pleret" <?= $anggota->dusun == 'Pleret' ? 'selected' : '' ?>>Pleret</option>
                        <option value="Tuksono" <?= $anggota->dusun == 'Tuksono' ? 'selected' : '' ?>>Tuksono</option>
                        <option value="Kelompok" <?= $anggota->dusun == 'Kelompok' ? 'selected' : '' ?>>Kelompok</option>
                        <option value="Luar" <?= $anggota->dusun == 'Luar' ? 'selected' : '' ?>>Luar</option>
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
                        <option value="aktif" <?= $anggota->status == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $anggota->status == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
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

        // Form submission validation
        const form = document.getElementById('anggotaEditForm');
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
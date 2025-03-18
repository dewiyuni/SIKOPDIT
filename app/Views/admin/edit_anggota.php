<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Anggota</h3>
        <a href="<?= site_url('admin/anggota') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <div class="card p-3">
        <form action="<?= site_url('admin/updateAnggota') ?>" method="POST">
            <?= csrf_field(); ?> <!-- Tambahkan CSRF untuk keamanan -->
            <input type="hidden" name="id_anggota" value="<?= $anggota->id_anggota ?>">

            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" name="nama" value="<?= old('nama', $anggota->nama); ?>" class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label for="nik" class="form-label">NIK:</label>
                <input type="text" name="nik" value="<?= old('nik', $anggota->nik); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="no_ba" class="form-label">No BA:</label>
                <input type="text" name="no_ba" value="<?= old('no_ba', $anggota->no_ba); ?>" class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label for="dusun" class="form-label">Dusun:</label>
                <select name="dusun" class="form-control" required>
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
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat Lengkap:</label>
                <textarea name="alamat" class="form-control" required><?= old('alamat', $anggota->alamat) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="pekerjaan" class="form-label">Pekerjaan:</label>
                <input type="text" name="pekerjaan" value="<?= old('pekerjaan', $anggota->pekerjaan); ?>"
                    class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="tgl_lahir" class="form-label">Tanggal Lahir:</label>
                <input type="date" name="tgl_lahir" value="<?= old('tgl_lahir', $anggota->tgl_lahir); ?>"
                    class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="nama_pasangan" class="form-label">Nama Pasangan:</label>
                <input type="text" name="nama_pasangan" value="<?= old('nama_pasangan', $anggota->nama_pasangan); ?>"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select name="status" class="form-control" required>
                    <option value="aktif" <?= $anggota->status == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $anggota->status == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>

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

<?= $this->endSection(); ?>
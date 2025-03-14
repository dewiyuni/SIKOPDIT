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
        <form action="<?= site_url('admin/simpanAnggota') ?>" method="POST">
            <?= csrf_field(); ?>

            <label for="nama" class="form-label">Nama:</label>
            <input type="text" id="nama" name="nama" class="form-control" required>

            <label for="nik" class="form-label">NIK:</label>
            <input type="text" id="nik" name="nik" class="form-control" required>

            <label for="no_ba" class="form-label">No BA:</label>
            <input type="text" id="no_ba" name="no_ba" class="form-control" required>

            <label for="dusun" class="form-label mt-2">Dusun:</label>
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
            </select>

            <label for="alamat" class="form-label">Alamat:</label>
            <textarea id="alamat" name="alamat" class="form-control" required></textarea>

            <label for="pekerjaan" class="form-label">Pekerjaan:</label>
            <input type="text" id="pekerjaan" name="pekerjaan" class="form-control" required>

            <label for="tgl_lahir" class="form-label">Tanggal Lahir:</label>
            <input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control" required>

            <label for="nama_pasangan" class="form-label">Nama Pasangan:</label>
            <input type="text" id="nama_pasangan" name="nama_pasangan" class="form-control">

            <label for="status" class="form-label">Status:</label>
            <select id="status" name="status" class="form-control" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>

            <button type="submit" class="btn btn-success mt-3">Simpan</button>
        </form>

    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/') ?>" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/simpan') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="id_anggota">Nama Anggota</label>
                <select name="id_anggota" class="form-control" required>
                    <option value="" disabled selected>-- Pilih Anggota --</option>
                    <?php foreach ($anggota as $a): ?>
                        <option value="<?= esc($a->id_anggota) ?>" <?= old('id_anggota') == $a->id_anggota ? 'selected' : '' ?>>
                            <?= esc($a->nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.id_anggota')): ?>
                    <small class="text-danger"><?= session('errors.id_anggota') ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="tanggal_pinjaman">Tanggal Cair</label>
                <input type="date" name="tanggal_pinjaman" class="form-control" required
                    value="<?= old('tanggal_pinjaman') ?>">
                <?php if (session('errors.tanggal_pinjaman')): ?>
                    <small class="text-danger"><?= session('errors.tanggal_pinjaman') ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="jangka_waktu">Jangka Waktu (bulan)</label>
                <input type="number" name="jangka_waktu" class="form-control" required min="1"
                    value="<?= old('jangka_waktu') ?>">
                <?php if (session('errors.jangka_waktu')): ?>
                    <small class="text-danger"><?= session('errors.jangka_waktu') ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="jumlah_pinjaman">Besar Pinjaman</label>
                <input type="text" id="jumlah_pinjaman" class="form-control" required oninput="formatRibuan(this)"
                    autocomplete="off">
                <input type="hidden" name="jumlah_pinjaman" id="jumlah_pinjaman_hidden"
                    value="<?= old('jumlah_pinjaman') ?>">
                <?php if (session('errors.jumlah_pinjaman')): ?>
                    <small class="text-danger"><?= session('errors.jumlah_pinjaman') ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="jaminan">Jaminan (Opsional)</label>
                <input type="text" name="jaminan" class="form-control" value="<?= old('jaminan') ?>">
            </div>

            <button type="submit" class="btn btn-primary mt-3">Simpan</button>
        </form>
    </div>
</div>

<script>
    function formatRibuan(input) {
        let angka = input.value.replace(/\D/g, ""); // Hapus semua non-angka
        input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Tambah titik pemisah ribuan

        // Simpan nilai asli tanpa titik ke input hidden untuk dikirim ke server
        document.getElementById("jumlah_pinjaman_hidden").value = angka;
    }
</script>

<?= $this->endSection() ?>
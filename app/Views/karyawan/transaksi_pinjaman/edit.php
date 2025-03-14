<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Edit Pinjaman</h3>
        <a href="<?= site_url('karyawan/transaksi_pinjaman/index') ?>" class="btn btn-warning">Kembali</a>

    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <form action="<?= base_url('karyawan/transaksi_pinjaman/update/' . $pinjaman->id_pinjaman) ?>" method="post">
            <label for="id_anggota">Nama Anggota</label>
            <select name="id_anggota">
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= $a->id_anggota ?>" <?= ($a->id_anggota == $pinjaman->id_anggota) ? 'selected' : '' ?>>
                        <?= $a->nama ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tanggal_pinjaman">Tanggal Cair</label>
            <input type="date" name="tanggal_pinjaman" value="<?= $pinjaman->tanggal_pinjaman ?>">

            <label for="jangka_waktu">Jangka Waktu</label>
            <input type="number" name="jangka_waktu" value="<?= $pinjaman->jangka_waktu ?>">

            <label for="jumlah_pinjaman">Besar Pinjaman</label>
            <input type="number" name="jumlah_pinjaman" value="<?= $pinjaman->jumlah_pinjaman ?>">

            <label for="jaminan">Jaminan</label>
            <input type="text" name="jaminan" value="<?= $pinjaman->jaminan ?>">

            <button type="submit">Update</button>
        </form>
    </div>
    <?= $this->endSection() ?>
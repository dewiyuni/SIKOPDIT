<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">

        <h3>Edit Neraca Awal</h3>

        <a href="/admin/neraca" class="btn btn-warning">Kenbali</a>
    </div>
    <div class="card p-3">
        <form action="<?= base_url('admin/neraca/update/' . $neraca['id_neraca']) ?>" method="post">
            <label>Tanggal:</label>
            <input type="date" name="tanggal" value="<?= $neraca['tanggal']; ?>" required><br>

            <label>Kas:</label>
            <input type="number" name="kas" step="0.01" value="<?= $neraca['kas']; ?>" required><br>

            <label>Piutang:</label>
            <input type="number" name="piutang" step="0.01" value="<?= $neraca['piutang']; ?>" required><br>

            <label>Inventaris:</label>
            <input type="number" name="inventaris" step="0.01" value="<?= $neraca['inventaris']; ?>" required><br>

            <label>Utang:</label>
            <input type="number" name="utang" step="0.01" value="<?= $neraca['utang']; ?>" required><br>

            <label>Simpanan:</label>
            <input type="number" name="simpanan" step="0.01" value="<?= $neraca['simpanan']; ?>" required><br>

            <label>Ekuitas:</label>
            <input type="number" name="ekuitas" step="0.01" value="<?= $neraca['ekuitas']; ?>" required><br>

            <button type="submit">Update</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
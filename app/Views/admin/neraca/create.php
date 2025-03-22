<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Tambah Neraca Awal</h3>
        <a href="/admin/neraca" class="btn btn-warning">Kembali</a>
    </div>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <div class="card p-3">
        <div style="overflow-x: auto;">
            <form action="<?= base_url('/admin/neraca/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Bulan:</label>
                    <select name="bulan" class="form-control" required>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= ($i == $bulan) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $i, 10)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tahun:</label>
                    <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>" required>
                </div>

                <h3>Rincian Kategori</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Uraian</th>
                            <th>Nilai</th>
                            <th><button type="button" class="btn btn-sm btn-primary" id="addRow">+</button></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td>
                                <select name="kategori[]" class="form-control" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($kategoriNeraca as $kategori): ?>
                                        <option value="<?= esc($kategori['id_kategori']) ?>">
                                            <?= esc($kategori['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="uraian[]" class="form-control" required></td>
                            <td><input type="number" name="nilai[]" class="form-control" step="0.01" required></td>
                            <td><button type="button" class="btn btn-sm btn-danger removeRow">-</button></td>
                        </tr>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?= base_url('/admin/neraca') ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<!-- Load Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // Inisialisasi Select2 untuk dropdown kategori
        $('.select2').select2({
            width: '100%',
            placeholder: "Pilih Kategori",
            allowClear: true
        });

        // Event listener untuk menambah baris baru
        $('#addRow').on('click', function () {
            let tableBody = $('#tableBody');
            let newRow = $(`
                <tr>
                    <td>
                        <select name="kategori[]" class="form-control select2" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriNeraca as $kategori): ?>
                                <option value="<?= $kategori['id_kategori'] ?>"><?= $kategori['nama_kategori'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="uraian[]" class="form-control" required></td>
                    <td><input type="number" name="nilai[]" class="form-control" step="0.01" required></td>
                    <td><button type="button" class="btn btn-sm btn-danger removeRow">-</button></td>
                </tr>
            `);
            tableBody.append(newRow);

            // Re-inisialisasi Select2 untuk dropdown yang baru ditambahkan
            $('.select2').select2({
                width: '100%',
                placeholder: "Pilih Kategori",
                allowClear: true
            });
        });

        // Event listener untuk menghapus baris
        $(document).on('click', '.removeRow', function () {
            $(this).closest('tr').remove();
        });
    });
</script>

<?= $this->endSection() ?>
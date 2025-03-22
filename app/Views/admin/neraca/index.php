<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Neraca Awal</h3>
        <a href="<?= base_url('admin/neraca/create') ?>" class="btn btn-success">Tambah Neraca Awal</a>
    </div>
    <!-- Form Pilihan Bulan & Tahun -->
    <div class="card p-3">
        <form action="<?= base_url('admin/neraca') ?>" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label>Bulan:</label>
                    <select name="bulan" class="form-control">
                        <?php foreach (range(1, 12) as $b): ?>
                            <option value="<?= $b ?>" <?= ($b == $bulan) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $b, 1)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Tahun:</label>
                    <select name="tahun" class="form-control">
                        <?php for ($t = date('Y') - 5; $t <= date('Y'); $t++): ?>
                            <option value="<?= $t ?>" <?= ($t == $tahun) ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-4">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card p-3">

        <?php if ($neraca): ?>
            <!-- Tabel Neraca -->
            <div class="table-responsive">
                <?php
                $kategoriAktiva = ['Aset Lancar', 'Aset Tidak Lancar', 'Aset Tetap'];
                $kategoriKewajiban = ['Kewajiban Jangka Pendek', 'Kewajiban Jangka Panjang'];
                $kategoriEkuitas = ['Ekuitas'];

                // Inisialisasi total saldo
                $totalSaldoLalu = 0;
                $totalSaldoSekarang = 0;
                ?>

                <table class="table table-bordered">
                    <thead>
                        <tr class="table-primary">
                            <th>Kategori</th>
                            <th>Uraian</th>
                            <th>Saldo Bulan Lalu</th>
                            <th>Saldo Sekarang</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Aktiva -->
                        <tr class="table-primary">
                            <td colspan="4"><strong>AKTIVA</strong></td>
                        </tr>
                        <?php foreach ($detailNeraca as $item): ?>
                            <?php if (isset($item['kategori']) && ($item['kategori'] == 'Aktiva' || in_array($item['kategori'], $kategoriAktiva))): ?>
                                <tr>
                                    <td><?= $item['kategori']; ?></td>
                                    <td><?= $item['uraian']; ?></td>
                                    <td><?= number_format($saldoBulanLalu[$item['uraian']] ?? 0); ?></td>
                                    <td><?= number_format($item['nilai']); ?></td>
                                </tr>
                                <?php
                                $totalSaldoLalu += $saldoBulanLalu[$item['uraian']] ?? 0;
                                $totalSaldoSekarang += $item['nilai'];
                                ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- Kewajiban -->
                        <tr class="table-primary">
                            <td colspan="4"><strong>KEWAJIBAN</strong></td>
                        </tr>
                        <?php foreach ($detailNeraca as $item): ?>
                            <?php if (isset($item['kategori']) && in_array($item['kategori'], $kategoriKewajiban)): ?>
                                <tr>
                                    <td><?= $item['kategori']; ?></td>
                                    <td><?= $item['uraian']; ?></td>
                                    <td><?= number_format($saldoBulanLalu[$item['uraian']] ?? 0); ?></td>
                                    <td><?= number_format($item['nilai']); ?></td>
                                </tr>
                                <?php
                                $totalSaldoLalu += $saldoBulanLalu[$item['uraian']] ?? 0;
                                $totalSaldoSekarang += $item['nilai'];
                                ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- Ekuitas -->
                        <tr class="table-primary">
                            <td colspan="4"><strong>EKUITAS</strong></td>
                        </tr>
                        <?php foreach ($detailNeraca as $item): ?>
                            <?php if (isset($item['kategori']) && in_array($item['kategori'], $kategoriEkuitas)): ?>
                                <tr>
                                    <td><?= $item['kategori']; ?></td>
                                    <td><?= $item['uraian']; ?></td>
                                    <td><?= number_format($saldoBulanLalu[$item['uraian']] ?? 0); ?></td>
                                    <td><?= number_format($item['nilai']); ?></td>
                                </tr>
                                <?php
                                $totalSaldoLalu += $saldoBulanLalu[$item['uraian']] ?? 0;
                                $totalSaldoSekarang += $item['nilai'];
                                ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- Total -->
                        <tr class="table-success">
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong><?= number_format($totalSaldoLalu); ?></strong></td>
                            <td><strong><?= number_format($totalSaldoSekarang); ?></strong></td>
                        </tr>

                    </tbody>
                </table>

            </div>

            <!-- Tombol Edit & Hapus -->
            <div class="mt-3">
                <a href="<?= base_url('admin/neraca/edit/' . $neraca['id_neraca_awal']) ?>" class="btn btn-warning">Edit</a>
                <a href="<?= base_url('admin/neraca/delete/' . $neraca['id_neraca_awal']) ?>" class="btn btn-danger"
                    onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Hapus</a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Belum ada data untuk bulan ini.</div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
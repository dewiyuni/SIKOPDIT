<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Transaksi Simpanan</h3>
        <a href="javascript:history.back()" class="btn btn-warning">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informasi Anggota</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Nama Anggota</th>
                            <td>: <?= $anggota->nama ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Nomor BA</th>
                            <td>: <?= $anggota->no_ba ?? '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="bg-light">
                        <th class="text-center" style="width: 5%;">No</th>
                        <th class="text-center" style="width: 15%;">Tanggal</th>
                        <th class="text-center" style="width: 8%;">SW Setor</th>
                        <th class="text-center" style="width: 8%;">SW Tarik</th>
                        <th class="text-center" style="width: 8%;">SWP Setor</th>
                        <th class="text-center" style="width: 8%;">SWP Tarik</th>
                        <th class="text-center" style="width: 8%;">SS Setor</th>
                        <th class="text-center" style="width: 8%;">SS Tarik</th>
                        <th class="text-center" style="width: 8%;">SP Setor</th>
                        <th class="text-center" style="width: 8%;">SP Tarik</th>
                        <th class="text-center" style="width: 16%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>

                    <!-- Riwayat Transaksi -->
                    <?php if (!empty($riwayat_transaksi)): ?>
                        <?php foreach ($riwayat_transaksi as $transaksi): ?>
                            <tr data-transaction-id="<?= $transaksi->id_transaksi ?>"
                                data-created-at="<?= $transaksi->waktu ?>">
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= date('d M Y H:i:s', strtotime($transaksi->waktu)) ?></td>

                                <td class="text-end editable-cell" data-field="setor_sw"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->setor_sw > 0 ? 'Rp ' . number_format($transaksi->setor_sw, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end editable-cell" data-field="tarik_sw"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->tarik_sw > 0 ? 'Rp ' . number_format($transaksi->tarik_sw, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end editable-cell" data-field="setor_swp"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->setor_swp > 0 ? 'Rp ' . number_format($transaksi->setor_swp, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end editable-cell" data-field="tarik_swp"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->tarik_swp > 0 ? 'Rp ' . number_format($transaksi->tarik_swp, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end editable-cell" data-field="setor_ss"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->setor_ss > 0 ? 'Rp ' . number_format($transaksi->setor_ss, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end editable-cell" data-field="tarik_ss"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->tarik_ss > 0 ? 'Rp ' . number_format($transaksi->tarik_ss, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end editable-cell" data-field="setor_sp"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->setor_sp > 0 ? 'Rp ' . number_format($transaksi->setor_sp, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end editable-cell" data-field="tarik_sp"
                                    data-created="<?= $transaksi->waktu ?>">
                                    <?= $transaksi->tarik_sp > 0 ? 'Rp ' . number_format($transaksi->tarik_sp, 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= site_url('karyawan/transaksi_simpanan/edit/' . $transaksi->id_transaksi . '?created_at=' . urlencode($transaksi->waktu)) ?>"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                            data-id="<?= $transaksi->id_transaksi ?>"
                                            data-created="<?= $transaksi->waktu ?>">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada riwayat transaksi</td>
                        </tr>
                    <?php endif; ?>

                    <!-- Saldo Sekarang di akhir tabel -->
                    <tr class="table-warning fw-bold">
                        <td></td> <!-- Tidak ada nomor -->
                        <td><strong>Total</strong></td>
                        <td class="text-end">Rp <?= number_format($total_sw_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_sw_tarik > 0 ? 'Rp ' . number_format($total_sw_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format($total_swp_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_swp_tarik > 0 ? 'Rp ' . number_format($total_swp_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format($total_ss_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_ss_tarik > 0 ? 'Rp ' . number_format($total_ss_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format($total_sp_setor, 0, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $total_sp_tarik > 0 ? 'Rp ' . number_format($total_sp_tarik, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end">Rp <?= number_format(
                            ($total_sw_setor - $total_sw_tarik) +
                            ($total_swp_setor - $total_swp_tarik) +
                            ($total_ss_setor - $total_ss_tarik) +
                            ($total_sp_setor - $total_sp_tarik),
                            0,
                            ',',
                            '.'
                        ) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addTransactionModalLabel">Tambah Transaksi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('karyawan/transaksi_simpanan/store') ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id_anggota" value="<?= $anggota->id_anggota ?>">

                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Transaksi</label>
                        <input type="datetime-local" class="form-control" id="tanggal" name="tanggal"
                            value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Simpanan Wajib (SW)</h6>
                            <div class="mb-3">
                                <label for="setor_sw" class="form-label">Setor</label>
                                <input type="text" class="form-control money-format" id="setor_sw" name="setor_sw">
                            </div>
                            <div class="mb-3">
                                <label for="tarik_sw" class="form-label">Tarik</label>
                                <input type="text" class="form-control money-format" id="tarik_sw" name="tarik_sw">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Simpanan Wajib Pokok (SWP)</h6>
                            <div class="mb-3">
                                <label for="setor_swp" class="form-label">Setor</label>
                                <input type="text" class="form-control money-format" id="setor_swp" name="setor_swp">
                            </div>
                            <div class="mb-3">
                                <label for="tarik_swp" class="form-label">Tarik</label>
                                <input type="text" class="form-control money-format" id="tarik_swp" name="tarik_swp">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Simpanan Sukarela (SS)</h6>
                            <div class="mb-3">
                                <label for="setor_ss" class="form-label">Setor</label>
                                <input type="text" class="form-control money-format" id="setor_ss" name="setor_ss">
                            </div>
                            <div class="mb-3">
                                <label for="tarik_ss" class="form-label">Tarik</label>
                                <input type="text" class="form-control money-format" id="tarik_ss" name="tarik_ss">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Simpanan Pokok (SP)</h6>
                            <div class="mb-3">
                                <label for="setor_sp" class="form-label">Setor</label>
                                <input type="text" class="form-control money-format" id="setor_sp" name="setor_sp">
                            </div>
                            <div class="mb-3">
                                <label for="tarik_sp" class="form-label">Tarik</label>
                                <input type="text" class="form-control money-format" id="tarik_sp" name="tarik_sp">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>

<script>
    $(document).ready(function () {
        // Inisialisasi format input uang
        $('.money-format').each(function () {
            new Cleave(this, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        });

        // Tampilkan modal tambah transaksi
        $('#btnAddTransaction').click(function () {
            $('#addTransactionModal').modal('show');
        });

        // Buat sel-sel jumlah transaksi menjadi bisa diedit
        $('.editable-cell').click(function () {
            if ($(this).find('input').length === 0) {
                const value = $(this).text().trim() === '-' ? '' :
                    $(this).text().replace('Rp ', '').replace(/\./g, '');
                const fieldName = $(this).data('field');
                const transactionId = $(this).closest('tr').data('transaction-id');
                const createdAt = $(this).data('created');

                $(this).html(`<input type="text" class="form-control form-control-sm amount-input" 
                         data-field="${fieldName}" data-id="${transactionId}" data-created="${createdAt}"
                         value="${value}">`);
                $(this).find('input').focus();

                // Tambahkan format uang ke input
                new Cleave($(this).find('input')[0], {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.'
                });
            }
        });

        // Tangani blur input untuk menyimpan perubahan
        $(document).on('blur', '.amount-input', function () {
            const value = $(this).val();
            const fieldName = $(this).data('field');
            const transactionId = $(this).data('id');
            const createdAt = $(this).data('created');
            const formattedValue = value && value !== '0' ? 'Rp ' + formatNumber(value) : '-';

            $(this).parent().html(formattedValue);

            // Kirim permintaan AJAX untuk memperbarui nilai
            $.ajax({
                url: '<?= site_url('karyawan/transaksi_simpanan/update_field') ?>',
                type: 'POST',
                data: {
                    id: transactionId,
                    field: fieldName,
                    value: value.replace(/\./g, ''),
                    created_at: createdAt
                },
                success: function (response) {
                    if (response.success) {
                        showToast('Data berhasil diperbarui', 'success');

                        // Refresh halaman setelah 1 detik untuk memperbarui total
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Gagal memperbarui data: ' + response.message, 'error');
                    }
                },
                error: function () {
                    showToast('Gagal memperbarui data', 'error');
                }
            });
        });

        // Format angka dengan pemisah ribuan
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Notifikasi toast
        function showToast(message, type) {
            $('body').append(`
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 5">
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        `);
            $('.toast').toast('show');
        }

        // Tampilkan modal konfirmasi hapus
        $('.delete-btn').click(function () {
            const id = $(this).data('id');
            const created = $(this).data('created');
            const deleteUrl = `<?= site_url('karyawan/transaksi_simpanan/delete/') ?>${id}?created_at=${encodeURIComponent(created)}`;

            $('#confirmDeleteBtn').attr('href', deleteUrl);
            $('#deleteConfirmModal').modal('show');
        });
    });
</script>

<?= $this->endSection() ?>
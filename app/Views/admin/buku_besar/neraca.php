<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<?php
// Helper function untuk format angka (0 desimal, negatif dalam kurung)
function formatNumber($value, $decimals = 0)
{
    if ($value === null || $value === '') {
        $num = 0.0;
    } else if (is_string($value) && strpos(trim($value), '{') === 0 && strrpos(trim($value), '}') === strlen(trim($value)) - 1) {
        $num_str = str_replace(['{', '}', '.', ','], ['', '', '', '.'], $value);
        $num = -floatval($num_str);
    } else if (is_string($value)) {
        $sValue = str_replace('.', '', $value);
        $sValue = str_replace(',', '.', $sValue);
        $num = floatval($sValue);
    } else {
        $num = floatval($value);
    }

    $formatted = number_format(abs($num), $decimals, ',', '.');
    return $num < 0 ? '(' . $formatted . ')' : $formatted;
}

// Ambil nama bulan dari controller
// $bulan dan $prevBulan sekarang adalah integer (1-12)
$namaBulanCurrent = ($bulanNames[$bulan] ?? "Bulan_" . $bulan) ?? date('F');
$namaBulanPrev = ($bulanNames[$prevBulan] ?? "Bulan_" . $prevBulan) ?? date('F', strtotime('-1 month'));
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Neraca Komparatif</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Neraca Per
                <?= date('t', mktime(0, 0, 0, (int) ($bulan ?? date('n')), 1, (int) ($tahun ?? date('Y')))) . ' ' . esc($namaBulanCurrent) . ' ' . esc($tahun ?? date('Y')) ?>
                dan
                <?= date('t', mktime(0, 0, 0, (int) ($prevBulan ?? date('n', strtotime('-1 month'))), 1, (int) ($prevTahun ?? date('Y', strtotime('-1 month'))))) . ' ' . esc($namaBulanPrev) . ' ' . esc($prevTahun ?? date('Y', strtotime('-1 month'))) ?>
            </h5>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <form id="filterFormNeraca" action="<?= current_url() ?>" method="get" class="d-flex gap-2">
                    <select name="bulan" class="form-select form-select-sm" aria-label="Pilih Bulan">
                        <?php $selectedBulan = (int) ($bulan ?? date('n')); ?>
                        <?php if (!empty($bulanNames) && is_array($bulanNames)): ?>
                            <?php foreach ($bulanNames as $key_month_int => $value_month_name): ?>
                                <option value="<?= $key_month_int ?>" <?= ($selectedBulan == $key_month_int) ? 'selected' : '' ?>>
                                    <?= esc($value_month_name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php for ($m = 1; $m <= 12; ++$m): ?>
                                <option value="<?= $m; ?>" <?= ($selectedBulan == $m) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </select>
                    <select name="tahun" class="form-select form-select-sm" aria-label="Pilih Tahun">
                        <?php $selectedTahun = (int) ($tahun ?? date('Y')); ?>
                        <?php for ($year_loop = date('Y'); $year_loop >= 2020; $year_loop--): ?>
                            <option value="<?= $year_loop ?>" <?= ($selectedTahun == $year_loop) ? 'selected' : '' ?>>
                                <?= esc($year_loop) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </form>
                <button type="button" id="refreshNeracaBtn" class="btn btn-info btn-sm"
                    title="Refresh Data Sesuai Filter"><i class="fas fa-sync-alt"></i> Refresh</button>
                <a href="<?= base_url('admin/buku_besar') ?>" class="btn btn-secondary btn-sm"
                    title="Kembali ke Buku Besar"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="<?= base_url('admin/buku_besar/export/neraca?bulan=' . ($bulan ?? date('n')) . '&tahun=' . ($tahun ?? date('Y'))) ?>"
                    class="btn btn-success btn-sm" title="Export ke Excel"><i class="fas fa-file-excel"></i> Export</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" style="font-size: 0.9rem;">
                    <thead>
                        <tr class="table-light text-center">
                            <th width="5%">No</th>
                            <th>Uraian Akun</th>
                            <th width="20%" class="text-end">
                                <?= esc($namaBulanCurrent) . ', ' . esc($tahun ?? date('Y')) ?>
                            </th>
                            <th width="20%" class="text-end">
                                <?= esc($namaBulanPrev) . ', ' . esc($prevTahun ?? date('Y', strtotime('-1 month'))) ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($laporan) && is_array($laporan) && !empty($laporan)): ?>
                            <?php $no_induk_aktiva = 0; ?>
                            <?php foreach ($laporan as $group_key => $groupData): ?>
                                <?php if (isset($groupData['urutan']) && $groupData['urutan'] >= 1 && $groupData['urutan'] <= 3 && $group_key !== 'TIDAK_TERPETAKAN'): ?>
                                    <tr>
                                        <td><b><?= esc($groupData['no_induk_prefix'] ?? 'I') . '.' . ($groupData['no_induk_val'] ?? ++$no_induk_aktiva) ?></b>
                                        </td>
                                        <td colspan="3"><b><?= esc($groupData['label'] ?? 'N/A') ?></b></td>
                                    </tr>
                                    <?php $sub_no = 0; ?>
                                    <?php if (!empty($groupData['items']) && is_array($groupData['items'])): ?>
                                        <?php foreach ($groupData['items'] as $kodeAkun => $item): ?>
                                            <tr>
                                                <td></td>
                                                <td><span style="padding-left: 15px;"><?= esc($item['nomor_display_sub'] ?? ++$sub_no) ?>.
                                                        <?= esc($item['nama'] ?? 'N/A') ?></span></td>
                                                <td class="text-end">
                                                    <?php if ($item['is_editable'] ?? false): ?>
                                                        <span class="editable-value" data-id="<?= esc($item['id'] ?? '') ?>"
                                                            data-kode="<?= esc($kodeAkun) ?>"
                                                            style="cursor:pointer; border-bottom: 1px dashed #007bff;"
                                                            title="Klik untuk edit"><?= formatNumber($item['saldo_current'] ?? 0) ?></span>
                                                    <?php else: ?>
                                                        <?= formatNumber($item['saldo_current'] ?? 0) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end"><?= formatNumber($item['saldo_prev'] ?? 0) ?></td>
                                            </tr>
                                            <?php if ($group_key == 'ASET_TETAP' && isset($groupData['akumulasi_lookup'][$kodeAkun]) && is_array($groupData['akumulasi_lookup'][$kodeAkun])):
                                                $akum = $groupData['akumulasi_lookup'][$kodeAkun]; ?>
                                                <tr>
                                                    <td></td>
                                                    <td><span
                                                            style="padding-left: 30px; font-style: italic;"><?= esc($akum['nama'] ?? '(Akumulasi Penyusutan)') ?></span>
                                                    </td>
                                                    <td class="text-end" style="font-style: italic;">
                                                        <?php if ($akum['is_editable'] ?? false): ?>
                                                            <span class="editable-value" data-id="<?= esc($akum['id'] ?? '') ?>"
                                                                data-kode="akum_<?= esc($kodeAkun) ?>"
                                                                style="cursor:pointer; border-bottom: 1px dashed #007bff;"
                                                                title="Klik untuk edit"><?= formatNumber($akum['saldo_current'] ?? 0) ?></span>
                                                        <?php else: ?>
                                                            <?= formatNumber($akum['saldo_current'] ?? 0) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end" style="font-style: italic;">
                                                        <?= formatNumber($akum['saldo_prev'] ?? 0) ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="text-end" style="font-style: italic;">Nilai Buku
                                                        <?= esc($item['nama'] ?? 'N/A') ?>
                                                    </td>
                                                    <td class="text-end" style="border-top: 1px solid #dee2e6;">
                                                        <?= formatNumber(($item['saldo_current'] ?? 0) + ($akum['saldo_current'] ?? 0)) // Akumulasi harus negatif ?>
                                                    </td>
                                                    <td class="text-end" style="border-top: 1px solid #dee2e6;">
                                                        <?= formatNumber(($item['saldo_prev'] ?? 0) + ($akum['saldo_prev'] ?? 0)) // Akumulasi harus negatif ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="line-height: 0.3rem; border: none;"> </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center fst-italic">Tidak ada item untuk
                                                <?= esc($groupData['label'] ?? 'grup ini') ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="table-secondary">
                                        <td></td>
                                        <td class="text-end"><b>SUB TOTAL
                                                <?= esc($groupData['label'] ?? 'N/A') . ($group_key == 'ASET_TETAP' ? ' (NETTO)' : '') ?></b>
                                        </td>
                                        <td class="text-end">
                                            <b><?= formatNumber($group_key == 'ASET_TETAP' ? ($groupData['total_net_current'] ?? 0) : ($groupData['total_current'] ?? 0)) ?></b>
                                        </td>
                                        <td class="text-end">
                                            <b><?= formatNumber($group_key == 'ASET_TETAP' ? ($groupData['total_net_prev'] ?? 0) : ($groupData['total_prev'] ?? 0)) ?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="line-height: 0.5rem; border: none;"> </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <tr class="table-primary">
                                <td colspan="2"><b>JUMLAH ASET</b></td>
                                <td class="text-end"><b><?= formatNumber($grand_total_aset_current ?? 0) ?></b></td>
                                <td class="text-end"><b><?= formatNumber($grand_total_aset_prev ?? 0) ?></b></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="background-color: #e9ecef; height: 15px; border: none;"></td>
                            </tr>

                            <?php $no_induk_pasiva = 0; ?>
                            <?php foreach ($laporan as $group_key => $groupData): ?>
                                <?php if (isset($groupData['urutan']) && $groupData['urutan'] >= 4 && $groupData['urutan'] <= 6 && $group_key !== 'TIDAK_TERPETAKAN'): ?>
                                    <tr>
                                        <td><b><?= esc($groupData['no_induk_prefix'] ?? 'II') . '.' . ($groupData['no_induk_val'] ?? ++$no_induk_pasiva) ?></b>
                                        </td>
                                        <td colspan="3"><b><?= esc($groupData['label'] ?? 'N/A') ?></b></td>
                                    </tr>
                                    <?php $sub_no = 0; ?>
                                    <?php if (!empty($groupData['items']) && is_array($groupData['items'])): ?>
                                        <?php foreach ($groupData['items'] as $kodeAkun => $item): ?>
                                            <tr>
                                                <td></td>
                                                <td><span style="padding-left: 15px;"><?= esc($item['nomor_display_sub'] ?? ++$sub_no) ?>.
                                                        <?= esc($item['nama'] ?? 'N/A') ?></span></td>
                                                <td class="text-end">
                                                    <?php if ($item['is_editable'] ?? false): ?>
                                                        <span class="editable-value" data-id="<?= esc($item['id'] ?? '') ?>"
                                                            data-kode="<?= esc($kodeAkun) ?>"
                                                            style="cursor:pointer; border-bottom: 1px dashed #007bff;"
                                                            title="Klik untuk edit"><?= formatNumber($item['saldo_current'] ?? 0) ?></span>
                                                    <?php else: ?>
                                                        <?= formatNumber($item['saldo_current'] ?? 0) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end"><?= formatNumber($item['saldo_prev'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if ($group_key == 'EKUITAS'): ?>
                                        <tr>
                                            <td></td>
                                            <td><span style="padding-left: 15px;">Laba (Rugi) Berjalan</span></td>
                                            <td class="text-end"><?= formatNumber($laba_rugi_bersih_current ?? 0) ?></td>
                                            <td class="text-end">-</td>
                                        </tr>
                                    <?php endif; ?>

                                    <tr class="table-secondary">
                                        <td></td>
                                        <td class="text-end"><b>SUB TOTAL <?= esc($groupData['label'] ?? 'N/A') ?></b></td>
                                        <?php
                                        $subTotalCurrent = $groupData['total_current'] ?? 0;
                                        $subTotalPrev = $groupData['total_prev'] ?? 0;
                                        if ($group_key == 'EKUITAS') {
                                            $subTotalCurrent += ($laba_rugi_bersih_current ?? 0);
                                        }
                                        ?>
                                        <td class="text-end"><b><?= formatNumber($subTotalCurrent) ?></b></td>
                                        <td class="text-end"><b><?= formatNumber($subTotalPrev) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="line-height: 0.5rem; border: none;"> </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <tr class="table-primary">
                                <td colspan="2"><b>JUMLAH KEWAJIBAN & EKUITAS</b></td>
                                <td class="text-end"><b><?= formatNumber($grand_total_pasiva_modal_current ?? 0) ?></b></td>
                                <td class="text-end"><b><?= formatNumber($grand_total_pasiva_modal_prev ?? 0) ?></b></td>
                            </tr>

                            <?php if (isset($laporan['TIDAK_TERPETAKAN']) && !empty($laporan['TIDAK_TERPETAKAN']['items']) && is_array($laporan['TIDAK_TERPETAKAN']['items'])):
                                $group = $laporan['TIDAK_TERPETAKAN']; ?>
                                <tr>
                                    <td colspan="4" class="bg-warning text-dark pt-3"><b>Akun Berikut Belum Terpetakan:</b></td>
                                </tr>
                                <?php foreach ($group['items'] as $kodeAkun => $item): ?>
                                    <tr>
                                        <td></td>
                                        <td><span style="padding-left: 15px;"><?= esc($kodeAkun) ?> -
                                                <?= esc($item['nama'] ?? 'N/A') ?></span></td>
                                        <td class="text-end"><?= formatNumber($item['saldo_current'] ?? 0) ?></td>
                                        <td class="text-end"><?= formatNumber($item['saldo_prev'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center fst-italic">Data laporan tidak tersedia atau struktur
                                    data tidak sesuai.
                                    Pastikan controller mengirim data `$laporan` yang benar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 row">
                <div class="col-md-6">
                    <?php $selisih_current = ($grand_total_aset_current ?? 0) - ($grand_total_pasiva_modal_current ?? 0); ?>
                    <?php if (abs($selisih_current) < 0.01): ?>
                        <div class="alert alert-success text-center py-1" role="alert">Periode
                            <?= esc($namaBulanCurrent) ?>: <b>BALANCE!</b>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger text-center py-1" role="alert">Periode <?= esc($namaBulanCurrent) ?>:
                            <b>TIDAK BALANCE!</b> (Selisih: <?= formatNumber($selisih_current) ?>)
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php $selisih_prev = ($grand_total_aset_prev ?? 0) - ($grand_total_pasiva_modal_prev ?? 0); ?>
                    <?php if (abs($selisih_prev) < 0.01): ?>
                        <div class="alert alert-success text-center py-1" role="alert">Periode <?= esc($namaBulanPrev) ?>:
                            <b>BALANCE!</b>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger text-center py-1" role="alert">Periode <?= esc($namaBulanPrev) ?>:
                            <b>TIDAK BALANCE!</b> (Selisih: <?= formatNumber($selisih_prev) ?>)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editableFields = document.querySelectorAll('.editable-value');
        const refreshButton = document.getElementById('refreshNeracaBtn');
        const filterForm = document.getElementById('filterFormNeraca');

        if (refreshButton && filterForm) {
            refreshButton.addEventListener('click', function () {
                filterForm.submit();
            });
        }

        editableFields.forEach(field => {
            field.addEventListener('click', function () {
                if (this.querySelector('input.editable-input-field')) {
                    return;
                }
                const oldValue = this.textContent;
                const dataId = this.dataset.id;

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm d-inline-block w-auto editable-input-field';

                let unformattedValue = oldValue.replace(/\./g, '');
                if (unformattedValue.startsWith('(') && unformattedValue.endsWith(')')) {
                    unformattedValue = '-' + unformattedValue.substring(1, unformattedValue.length - 1);
                }
                unformattedValue = unformattedValue.replace(/,/g, '.');
                input.value = unformattedValue;

                this.innerHTML = '';
                this.appendChild(input);
                input.focus();
                input.select();

                const saveAndRevert = async () => {
                    input.removeEventListener('blur', onBlur);
                    input.removeEventListener('keypress', onKeypress);
                    await saveChange(input, dataId, oldValue);
                };

                const onBlur = async () => { await saveAndRevert(); };
                const onKeypress = async (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        await saveAndRevert();
                    } else if (e.key === 'Escape') {
                        input.removeEventListener('blur', onBlur);
                        input.removeEventListener('keypress', onKeypress);
                        this.textContent = oldValue;
                    }
                };
                input.addEventListener('blur', onBlur);
                input.addEventListener('keypress', onKeypress);
            });
        });

        async function saveChange(inputElement, id, originalFormattedValue) {
            const newValueRaw = inputElement.value;
            const parentSpan = inputElement.parentElement; // Ini adalah span.editable-value
            const kodeAkunInternal = parentSpan.dataset.kode; // Ambil kode_akun_internal dari data-kode

            // Ambil periode dari filter
            const filterForm = document.getElementById('filterFormNeraca');
            const periodeBulan = filterForm.elements.bulan.value;
            const periodeTahun = filterForm.elements.tahun.value;

            let valueForDb = newValueRaw.replace(/\./g, '');
            valueForDb = valueForDb.replace(/,/g, '.');

            if (isNaN(parseFloat(valueForDb))) {
                alert('Nilai tidak valid. Harap masukkan angka.');
                if (parentSpan) parentSpan.textContent = originalFormattedValue;
                return;
            }

            try {
                const formData = new FormData();
                if (id) { // Jika ada ID, ini adalah UPDATE
                    formData.append('id', id);
                } else { // Jika tidak ada ID, ini adalah INSERT baru
                    formData.append('is_new', 'true'); // Flag untuk controller
                    formData.append('kode_akun_internal', kodeAkunInternal);
                    formData.append('periode_tahun', periodeTahun);
                    formData.append('periode_bulan', periodeBulan);
                    // Informasi lain seperti 'grup_laporan', 'uraian_akun' dll. 
                    // akan diambil controller dari master structure berdasarkan kode_akun_internal
                }
                formData.append('nilai', valueForDb);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                // Endpoint tetap sama, controller akan membedakan berdasarkan 'id' atau 'is_new'
                const response = await fetch('<?= base_url('admin/buku_besar/updateNeracaItem') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.status === 'success') {
                    let numForFormat = parseFloat(valueForDb);
                    let formattedNewValue;
                    const absNumFormatted = Math.abs(numForFormat).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/,/g, '.');
                    formattedNewValue = numForFormat < 0 ? '(' + absNumFormatted + ')' : absNumFormatted;

                    if (parentSpan) {
                        parentSpan.textContent = formattedNewValue;
                        if (result.new_id && !id) { // Jika ini adalah INSERT baru dan berhasil
                            parentSpan.dataset.id = result.new_id; // Update data-id pada span
                        }
                    }

                    const notif = document.createElement('div');
                    notif.className = 'alert alert-info alert-dismissible fade show fixed-top m-3';
                    notif.setAttribute('role', 'alert');
                    notif.style.zIndex = "1050";
                    notif.innerHTML = `Data berhasil ${(id ? 'diperbarui' : 'disimpan')}! Klik <strong>Refresh</strong> untuk melihat total yang diperbarui.
                                   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                    document.body.appendChild(notif);
                    setTimeout(() => { notif.remove(); }, 5000);

                } else {
                    alert('Gagal menyimpan: ' + (result.message || 'Tidak ada pesan error spesifik dari server.'));
                    if (parentSpan) parentSpan.textContent = originalFormattedValue;
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                alert('Terjadi kesalahan saat mengirim data ke server.');
                if (parentSpan) parentSpan.textContent = originalFormattedValue;
            }
        }
    });

</script>

<?= $this->endSection(); ?>
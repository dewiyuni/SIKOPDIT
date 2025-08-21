<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<?php
function formatNumber($value, $decimals = 0)
{
    if ($value === null || $value === '') {
        $num = 0.0;
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

$namaBulanCurrent = $bulanNames[$bulan] ?? "Bulan_" . $bulan;
$namaBulanPrev = $bulanNames[$prevBulan] ?? "Bulan_" . $prevBulan;
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Neraca Komparatif</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Neraca per
                <?= date('t', strtotime("$tahun-$bulan-01")) . " $namaBulanCurrent $tahun dan " . date('t', strtotime("$prevTahun-$prevBulan-01")) . " $namaBulanPrev $prevTahun" ?>
            </h5>
            <form action="<?= current_url() ?>" method="get" class="d-flex gap-2">
                <select name="bulan" class="form-select form-select-sm">
                    <?php foreach ($bulanNames as $key => $val): ?>
                        <option value="<?= $key ?>" <?= $key == $bulan ? 'selected' : '' ?>><?= esc($val) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="form-select form-select-sm">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- AKTIVA -->
                <div class="col-md-6">
                    <h5 class="text-primary">AKTIVA</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Akun</th>
                                <th><?= esc($namaBulanCurrent) ?> <?= esc($tahun) ?></th>
                                <th><?= esc($namaBulanPrev) ?> <?= esc($prevTahun) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $total_aktiva_now = 0;
                            $total_aktiva_prev = 0;
                            ?>

                            <?php foreach ($aktiva as $kategori => $kelompok): ?>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="4"><?= esc($kategori) ?></td>
                                </tr>

                                <?php
                                $sub_total_now = 0;
                                $sub_total_prev = 0;
                                ?>

                                <?php foreach ($kelompok as $p): ?>
                                    <?php
                                    $isSub = ($p['tipe'] ?? 'normal') === 'sub';

                                    // Untuk ASET TETAP, hanya jumlahkan tipe sub
                                    if ($kategori === 'ASET TETAP') {
                                        if ($isSub) {
                                            $total_aktiva_now += $p['saldo_now'] ?? 0;
                                            $total_aktiva_prev += $p['saldo_prev'] ?? 0;

                                            $sub_total_now += $p['saldo_now'] ?? 0;
                                            $sub_total_prev += $p['saldo_prev'] ?? 0;
                                        }
                                    } else {
                                        // ASET LANCAR dan TAK LANCAR: jumlahkan semua
                                        $total_aktiva_now += $p['saldo_now'] ?? 0;
                                        $total_aktiva_prev += $p['saldo_prev'] ?? 0;

                                        $sub_total_now += $p['saldo_now'] ?? 0;
                                        $sub_total_prev += $p['saldo_prev'] ?? 0;
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= esc($p['nama']) ?></td>
                                        <td class="text-end"><?= formatNumber($p['saldo_now'] ?? 0) ?></td>
                                        <td class="text-end"><?= formatNumber($p['saldo_prev'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Subtotal -->
                                <tr class="fw-bold text-primary">
                                    <td colspan="2" class="text-end">Subtotal <?= esc($kategori) ?></td>
                                    <td class="text-end"><?= formatNumber($sub_total_now) ?></td>
                                    <td class="text-end"><?= formatNumber($sub_total_prev) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <tr class="fw-bold">
                                <td colspan="2" class="text-center">Total AKTIVA</td>
                                <td class="text-end"><?= formatNumber($total_aktiva_now) ?></td>
                                <td class="text-end"><?= formatNumber($total_aktiva_prev) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- PASSIVA -->
                <div class="col-md-6">
                    <h5 class="text-primary">PASIVA</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Akun</th>
                                <th><?= esc($namaBulanCurrent) ?> <?= esc($tahun) ?></th>
                                <th><?= esc($namaBulanPrev) ?> <?= esc($prevTahun) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $total_pasiva_now = 0;
                            $total_pasiva_prev = 0;
                            ?>

                            <?php foreach ($pasiva as $jenis => $kelompok): ?>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="4"><?= esc($jenis) ?></td>
                                </tr>

                                <?php
                                $sub_total_now = 0;
                                $sub_total_prev = 0;
                                ?>

                                <?php foreach ($kelompok as $p): ?>
                                    <?php
                                    $total_pasiva_now += $p['saldo_now'] ?? 0;
                                    $total_pasiva_prev += $p['saldo_prev'] ?? 0;

                                    $sub_total_now += $p['saldo_now'] ?? 0;
                                    $sub_total_prev += $p['saldo_prev'] ?? 0;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= esc($p['nama']) ?></td>
                                        <td class="text-end"><?= formatNumber($p['saldo_now'] ?? 0) ?></td>
                                        <td class="text-end"><?= formatNumber($p['saldo_prev'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- ⬇️ Subtotal per kategori PASIVA -->
                                <tr class="fw-bold text-primary">
                                    <td colspan="2" class="text-end">Subtotal <?= esc($jenis) ?></td>
                                    <td class="text-end"><?= formatNumber($sub_total_now) ?></td>
                                    <td class="text-end"><?= formatNumber($sub_total_prev) ?></td>
                                </tr>

                            <?php endforeach; ?>

                            <tr class="fw-bold">
                                <td colspan="2" class="text-center">Total PASIVA</td>
                                <td class="text-end"><?= formatNumber($total_pasiva_now) ?></td>
                                <td class="text-end"><?= formatNumber($total_pasiva_prev) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Balance Check -->
                <div class="mt-4 row">
                    <div class="col-md-6">
                        <?php $selisih_current = $total_aktiva_now - $total_pasiva_now; ?>
                        <div
                            class="alert <?= abs($selisih_current) < 0.01 ? 'alert-success' : 'alert-danger' ?> text-center py-1">
                            Periode <?= esc($namaBulanCurrent) ?>:
                            <b><?= abs($selisih_current) < 0.01 ? 'BALANCE!' : 'TIDAK BALANCE! (Selisih: ' . formatNumber($selisih_current) . ')' ?></b>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php $selisih_prev = $total_aktiva_prev - $total_pasiva_prev; ?>
                        <div
                            class="alert <?= abs($selisih_prev) < 0.01 ? 'alert-success' : 'alert-danger' ?> text-center py-1">
                            Periode <?= esc($namaBulanPrev) ?>:
                            <b><?= abs($selisih_prev) < 0.01 ? 'BALANCE!' : 'TIDAK BALANCE! (Selisih: ' . formatNumber($selisih_prev) . ')' ?></b>
                        </div>
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
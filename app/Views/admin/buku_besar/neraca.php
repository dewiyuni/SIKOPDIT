<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<?php
// Helper function untuk format angka (0 desimal, negatif dalam kurung)
function formatNumber($value, $decimals = 0) {
    $num = floatval($value);
    $formatted = number_format(abs($num), $decimals, ',', '.');
    return $num < 0 ? '(' . $formatted . ')' : $formatted;
}
// Ambil nama bulan dari controller
$namaBulanCurrent = $bulanNames[$bulan] ?? $bulan;
$namaBulanPrev = $bulanNames[$prevBulan] ?? $prevBulan;
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Neraca Komparatif</h3>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Neraca Per <?= date('t', strtotime("$tahun-$bulan-01")) . ' ' . esc($namaBulanCurrent) . ' ' . esc($tahun) ?> dan <?= date('t', strtotime("$prevTahun-$prevBulan-01")) . ' ' . esc($namaBulanPrev) . ' ' . esc($prevTahun) ?>
            </h5>
            <div class="d-flex gap-2 flex-wrap">
                <!-- Filter Form -->
                <form action="<?= base_url('admin/buku_besar/neraca') ?>" method="get" class="d-flex gap-2">
                    <select name="bulan" class="form-select form-select-sm" aria-label="Pilih Bulan">
                        <?php foreach ($bulanNames as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $bulan == $key ? 'selected' : '' ?>><?= esc($value) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="tahun" class="form-select form-select-sm" aria-label="Pilih Tahun">
                        <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                            <option value="<?= $year ?>" <?= $tahun == $year ? 'selected' : '' ?>><?= esc($year) ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </form>
                <a href="<?= base_url('admin/buku_besar') ?>" class="btn btn-secondary btn-sm" title="Kembali ke Buku Besar"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="<?= base_url('admin/buku_besar/export/neraca?bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="btn btn-success btn-sm" title="Export ke Excel"><i class="fas fa-file-excel"></i> Export</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" style="font-size: 0.9rem;">
                    <thead>
                        <tr class="table-light text-center">
                            <th width="5%">No</th>
                            <th>Uraian Akun</th>
                            <th width="20%" class="text-end"><?= esc($namaBulanCurrent) . ', ' . esc($tahun) ?></th>
                            <th width="20%" class="text-end"><?= esc($namaBulanPrev) . ', ' . esc($prevTahun) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- === AKTIVA === -->
                        <?php $no_induk = 1; ?>
                        <?php foreach($laporan as $group_key => $groupData): ?>
                            <?php if ($groupData['urutan'] >= 1 && $groupData['urutan'] <= 3 && $group_key !== 'TIDAK_TERPETAKAN'): // Hanya proses grup Aset ?>
                                <tr>
                                    <td><b><?= ($groupData['urutan'] == 1 ? 'I' : 'I') . '.' . $no_induk++ ?></b></td>
                                    <td colspan="3"><b><?= esc($groupData['label']) ?></b></td>
                                </tr>
                                <?php $sub_no = 1; ?>
                                <?php if (!empty($groupData['items'])): ?>
                                    <?php foreach($groupData['items'] as $kodeAkun => $item): ?>
                                        <tr>
                                            <td></td>
                                            <td><span style="padding-left: 15px;"><?= $sub_no++ ?>. <?= esc($item['nama']) ?></span></td>
                                            <td class="text-end"><?= formatNumber($item['saldo_current']) ?></td>
                                            <td class="text-end"><?= formatNumber($item['saldo_prev']) ?></td>
                                        </tr>
                                        <?php // Handle Akumulasi untuk Aset Tetap
                                        if ($group_key == 'ASET_TETAP' && isset($groupData['akumulasi_lookup'][$kodeAkun])):
                                            $akum = $groupData['akumulasi_lookup'][$kodeAkun];
                                        ?>
                                            <tr>
                                                <td></td>
                                                <td><span style="padding-left: 30px; font-style: italic;">(Akumulasi Penyusutan)</span></td>
                                                <td class="text-end" style="font-style: italic;"><?= formatNumber($akum['saldo_current']) ?></td>
                                                <td class="text-end" style="font-style: italic;"><?= formatNumber($akum['saldo_prev']) ?></td>
                                            </tr>
                                            <!-- Baris Nilai Buku -->
                                            <tr>
                                                <td></td>
                                                <td class="text-end" style="font-style: italic;">Nilai Buku <?= esc($item['nama']) ?></td>
                                                <td class="text-end" style="border-top: 1px solid #dee2e6;"><?= formatNumber($item['saldo_current'] - $akum['saldo_current']) ?></td>
                                                <td class="text-end" style="border-top: 1px solid #dee2e6;"><?= formatNumber($item['saldo_prev'] - $akum['saldo_prev']) ?></td>
                                            </tr>
                                             <tr><td colspan="4" style="line-height: 0.3rem; border: none;"> </td></tr><!-- Mini Spacer -->
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center fst-italic">Tidak ada data</td></tr>
                                <?php endif; ?>
                                <!-- Sub Total Group -->
                                <tr class="table-secondary">
                                    <td></td>
                                    <td class="text-end"><b>SUB TOTAL <?= esc($groupData['label']) . ($group_key == 'ASET_TETAP' ? ' (NETTO)' : '') ?></b></td>
                                    <td class="text-end"><b><?= formatNumber($group_key == 'ASET_TETAP' ? ($groupData['total_net_current'] ?? 0) : $groupData['total_current']) ?></b></td>
                                    <td class="text-end"><b><?= formatNumber($group_key == 'ASET_TETAP' ? ($groupData['total_net_prev'] ?? 0) : $groupData['total_prev']) ?></b></td>
                                </tr>
                                <tr><td colspan="4" style="line-height: 0.5rem; border: none;"> </td></tr><!-- Spacer -->
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- TOTAL ASET -->
                        <tr class="table-primary">
                            <td colspan="2"><b>JUMLAH ASET</b></td>
                            <td class="text-end"><b><?= formatNumber($grand_total_aset_current ?? 0) ?></b></td>
                            <td class="text-end"><b><?= formatNumber($grand_total_aset_prev ?? 0) ?></b></td>
                        </tr>
                        <tr><td colspan="4" style="background-color: #e9ecef; height: 15px; border: none;"></td></tr> <!-- Bigger Spacer -->


                        <!-- === KEWAJIBAN & EKUITAS === -->
                        <?php $no_induk = 1; // Reset nomor induk ?>
                        <?php foreach($laporan as $group_key => $groupData): ?>
                             <?php if ($groupData['urutan'] >= 4 && $groupData['urutan'] <= 6 && $group_key !== 'TIDAK_TERPETAKAN'): // Hanya proses grup Kewajiban & Ekuitas ?>
                                <tr>
                                    <td><b><?= ($groupData['urutan'] == 4 ? 'II' : 'II') . '.' . $no_induk++ ?></b></td>
                                    <td colspan="3"><b><?= esc($groupData['label']) ?></b></td>
                                </tr>
                                 <?php $sub_no = 1; ?>
                                <?php if (!empty($groupData['items'])): ?>
                                    <?php foreach($groupData['items'] as $kodeAkun => $item): ?>
                                        <tr>
                                            <td></td>
                                            <td><span style="padding-left: 15px;"><?= $sub_no++ ?>. <?= esc($item['nama']) ?></span></td>
                                            <td class="text-end"><?= formatNumber($item['saldo_current']) ?></td>
                                            <td class="text-end"><?= formatNumber($item['saldo_prev']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php // Tambahkan L/R Berjalan HANYA di grup EKUITAS
                                if ($group_key == 'EKUITAS'): ?>
                                    <tr>
                                        <td></td>
                                        <td><span style="padding-left: 15px;">Laba (Rugi) Berjalan</span></td>
                                        <td class="text-end"><?= formatNumber($laba_rugi_bersih_current ?? 0) ?></td>
                                        <td class="text-end">-</td>
                                    </tr>
                                <?php endif; ?>

                                <!-- Sub Total Group -->
                                <tr class="table-secondary">
                                    <td></td>
                                    <td class="text-end"><b>SUB TOTAL <?= esc($groupData['label']) ?></b></td>
                                    <?php
                                        // Hitung total sub grup (tambahkan L/R jika Ekuitas)
                                        $subTotalCurrent = $groupData['total_current'];
                                        $subTotalPrev = $groupData['total_prev'];
                                        if ($group_key == 'EKUITAS') {
                                            $subTotalCurrent += ($laba_rugi_bersih_current ?? 0);
                                            // Periode lalu tidak ditambah L/R periode ini
                                        }
                                    ?>
                                    <td class="text-end"><b><?= formatNumber($subTotalCurrent) ?></b></td>
                                    <td class="text-end"><b><?= formatNumber($subTotalPrev) ?></b></td>
                                </tr>
                                 <tr><td colspan="4" style="line-height: 0.5rem; border: none;"> </td></tr><!-- Spacer -->
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- TOTAL KEWAJIBAN & EKUITAS -->
                        <tr class="table-primary">
                            <td colspan="2"><b>JUMLAH KEWAJIBAN & EKUITAS</b></td>
                            <td class="text-end"><b><?= formatNumber($grand_total_pasiva_modal_current ?? 0) ?></b></td>
                            <td class="text-end"><b><?= formatNumber($grand_total_pasiva_modal_prev ?? 0) ?></b></td>
                        </tr>

                        <!-- Akun Tidak Terpetakan (Untuk Debugging) -->
                         <?php if (isset($laporan['TIDAK_TERPETAKAN']) && !empty($laporan['TIDAK_TERPETAKAN']['items'])): $group = $laporan['TIDAK_TERPETAKAN']; ?>
                             <tr><td colspan="4" class="bg-warning text-dark pt-3"><b>Akun Berikut Belum Terpetakan di Controller (Fungsi getNeracaMappingData):</b></td></tr>
                             <?php foreach($group['items'] as $item): ?>
                                <tr>
                                    <td></td>
                                    <td><span style="padding-left: 15px;"><?= esc($item['kode']) ?> - <?= esc($item['nama']) ?></span></td>
                                    <td class="text-end"><?= formatNumber($item['saldo_current']) ?></td>
                                    <td class="text-end"><?= formatNumber($item['saldo_prev']) ?></td>
                                </tr>
                             <?php endforeach; ?>
                         <?php endif; ?>

                    </tbody>
                </table>
            </div>
             <!-- Pengecekan Balance -->
             <div class="mt-3 row">
                <div class="col-md-6">
                    <?php $selisih_current = floatval($grand_total_aset_current ?? 0) - floatval($grand_total_pasiva_modal_current ?? 0); ?>
                    <?php if (abs($selisih_current) < 0.01): // Toleransi pembulatan ?>
                        <div class="alert alert-success text-center py-1" role="alert">Periode <?= esc($namaBulanCurrent) ?>: <b>BALANCE!</b></div>
                    <?php else: ?>
                         <div class="alert alert-danger text-center py-1" role="alert">Periode <?= esc($namaBulanCurrent) ?>: <b>TIDAK BALANCE!</b> (Selisih: <?= formatNumber($selisih_current) ?>)</div>
                    <?php endif; ?>
                </div>
                 <div class="col-md-6">
                    <?php $selisih_prev = floatval($grand_total_aset_prev ?? 0) - floatval($grand_total_pasiva_modal_prev ?? 0); ?>
                    <?php if (abs($selisih_prev) < 0.01): // Toleransi pembulatan ?>
                        <div class="alert alert-success text-center py-1" role="alert">Periode <?= esc($namaBulanPrev) ?>: <b>BALANCE!</b></div>
                    <?php else: ?>
                         <div class="alert alert-danger text-center py-1" role="alert">Periode <?= esc($namaBulanPrev) ?>: <b>TIDAK BALANCE!</b> (Selisih: <?= formatNumber($selisih_prev) ?>)</div>
                    <?php endif; ?>
                </div>
             </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
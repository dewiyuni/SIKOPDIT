<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NeracaDataTemplateSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tbl_neraca_data');

        // Hapus data template lama jika ada untuk periode ini (opsional, tergantung kebutuhan)
        $sampleTahun = 2025;
        $sampleBulan = 1; // Januari
        // $builder->where('periode_tahun', $sampleTahun)->where('periode_bulan', $sampleBulan)->delete();

        $masterStructure = $this->getMasterNeracaStructureForSeed();
        $dataToSeed = [];
        $urutanGlobal = 100; // Urutan awal untuk item

        foreach ($masterStructure as $groupKey => $groupDetails) {
            $urutanDisplayMainGroup = $groupDetails['urutan'] * 1000; // Basis urutan untuk grup

            // Seed item utama
            if (isset($groupDetails['items_template'])) {
                $subItemCounter = 1;
                foreach ($groupDetails['items_template'] as $itemInternalKode => $itemDetail) {
                    $urutanDisplayItem = $urutanDisplayMainGroup + ($subItemCounter * 10);
                    $dataToSeed[] = [
                        'periode_tahun' => $sampleTahun,
                        'periode_bulan' => $sampleBulan,
                        'kode_akun_internal' => $itemInternalKode,
                        'nomor_display_main' => $groupDetails['no_induk_prefix'] ?? null,
                        'nomor_display_sub' => $itemDetail['nomor_display_sub'] ?? null,
                        'uraian_akun' => $itemDetail['nama'],
                        'grup_laporan' => $itemDetail['grup_laporan'],
                        'nilai' => 0.00, // Default nilai 0
                        'is_header_grup' => false,
                        'is_item_utama' => $itemDetail['is_item_utama'] ?? true,
                        'is_akumulasi' => $itemDetail['is_akumulasi'] ?? false,
                        'parent_kode_akun_internal' => $itemDetail['parent_kode_akun_internal'] ?? null,
                        'is_sub_total_grup' => false,
                        'is_nilai_buku_line' => false,
                        'urutan_display' => $urutanDisplayItem,
                        'is_editable' => $itemDetail['is_editable'] ?? true,
                        // created_at dan updated_at akan diisi otomatis oleh DB
                    ];
                    $subItemCounter++;

                    // Jika ini item Aset Tetap, periksa apakah ada template akumulasinya
                    if ($groupKey === 'ASET_TETAP' && isset($masterStructure['ASET_TETAP']['akumulasi_template'][$itemInternalKode])) {
                        $akumTemplate = $masterStructure['ASET_TETAP']['akumulasi_template'][$itemInternalKode];
                        $dataToSeed[] = [
                            'periode_tahun' => $sampleTahun,
                            'periode_bulan' => $sampleBulan,
                            'kode_akun_internal' => $akumTemplate['kode_akun_internal_akum'],
                            'nomor_display_main' => null, // Biasanya akumulasi tidak punya nomor display utama/sub
                            'nomor_display_sub' => null,
                            'uraian_akun' => $akumTemplate['uraian_akun'],
                            'grup_laporan' => $akumTemplate['grup_laporan'],
                            'nilai' => 0.00, // Default nilai 0
                            'is_header_grup' => false,
                            'is_item_utama' => $akumTemplate['is_item_utama'] ?? false,
                            'is_akumulasi' => $akumTemplate['is_akumulasi'] ?? true,
                            'parent_kode_akun_internal' => $itemInternalKode, // Parent-nya adalah item aset tetap induk
                            'is_sub_total_grup' => false,
                            'is_nilai_buku_line' => false,
                            'urutan_display' => $urutanDisplayItem + 1, // Tampil setelah item induknya
                            'is_editable' => $akumTemplate['is_editable'] ?? true,
                        ];
                    }
                }
            }
            $urutanGlobal += 1000; // Naikkan urutan untuk grup berikutnya
        }

        // Insert data ke database jika ada
        if (!empty($dataToSeed)) {
            $builder->insertBatch($dataToSeed);
            echo "Seeder NeracaDataTemplateSeeder berhasil dijalankan untuk {$sampleBulan}/{$sampleTahun}.\n";
        } else {
            echo "Tidak ada data untuk di-seed dari NeracaDataTemplateSeeder.\n";
        }
    }


    // Fungsi ini duplikat dari controller, idealnya diletakkan di helper atau base class
    // atau Anda memiliki sumber data master akun (misalnya dari tabel lain)
    private function getMasterNeracaStructureForSeed(): array
    {
        // Struktur ini harus SAMA PERSIS dengan yang ada di controller Anda
        // Saya copy dari respons sebelumnya, pastikan ini up-to-date dengan versi controller Anda
        return [
            'ASET_LANCAR' => [
                'label' => 'ASET LANCAR',
                'urutan' => 1,
                'no_induk_prefix' => 'I',
                'no_induk_val' => 1,
                'items_template' => [
                    'KAS' => ['nama' => 'kas', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMPANAN_BANK' => ['nama' => 'Simpanan di Bank', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMPANAN_DEPOSITO' => ['nama' => 'Simpanan deposito', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PIUTANG_BIASA' => ['nama' => 'Piutang Biasa', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PIUTANG_KHUSUS' => ['nama' => 'Piutang Khusus', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PIUTANG_RAGU' => ['nama' => 'Piutang Ragu-ragu', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PENY_PIUTANG_RAGU' => ['nama' => 'Penyusutan Piutang Ragu', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'ASET_TAK_LANCAR' => [
                'label' => 'ASET TAK LANCAR',
                'urutan' => 2,
                'no_induk_prefix' => 'I',
                'no_induk_val' => 2,
                'items_template' => [
                    'SIMPANAN_BKD' => ['nama' => 'Simpanan di BK#D', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'ASET_TAK_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INVESTASI' => ['nama' => 'Investasi', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'ASET_TAK_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SERTA_DATA' => ['nama' => 'Serta Data', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'ASET_TAK_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'ASET_TETAP' => [
                'label' => 'ASET TETAP',
                'urutan' => 3,
                'no_induk_prefix' => 'I',
                'no_induk_val' => 3,
                'items_template' => [
                    'INV_MEBEL' => ['nama' => 'Inventaris Barang Mebeler', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'BEBAN_TERTANGGUH' => ['nama' => 'Beban Tertangguh', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_GEDUNG' => ['nama' => 'Inventaris Gedung/Bangunan', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_PAGAR' => ['nama' => 'Inventaris Pagar', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_TANAH' => ['nama' => 'Inventaris tanah', 'is_editable' => false, 'nomor_display_sub' => '5', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_KOMPUTER' => ['nama' => 'Inventaris Komputer', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_KENDARAAN' => ['nama' => 'Inventaris Kendaraan', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ],
                'akumulasi_template' => [
                    'INV_MEBEL' => ['kode_akun_internal_akum' => 'AKUM_INV_MEBEL', 'uraian_akun' => '(Akumulasi Penyusutan Mebeler)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'BEBAN_TERTANGGUH' => ['kode_akun_internal_akum' => 'AKUM_BEBAN_TERTANGGUH', 'uraian_akun' => '(Akumulasi Penyusutan Beban Tertangguh)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_GEDUNG' => ['kode_akun_internal_akum' => 'AKUM_INV_GEDUNG', 'uraian_akun' => '(Akumulasi Penyusutan Gedung)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_TANAH' => ['kode_akun_internal_akum' => 'AKUM_INV_TANAH', 'uraian_akun' => '(Akumulasi Penyusutan Tanah)', 'is_editable' => false, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_KOMPUTER' => ['kode_akun_internal_akum' => 'AKUM_INV_KOMPUTER', 'uraian_akun' => '(Akumulasi Penyusutan Komputer)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_KENDARAAN' => ['kode_akun_internal_akum' => 'AKUM_INV_KENDARAAN', 'uraian_akun' => '(Akumulasi Penyusutan Kendaraan)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                ]
            ],
            'KEWAJIBAN_JANGKA_PENDEK' => [
                'label' => 'KEWAJIBAN JANGKA PENDEK',
                'urutan' => 4,
                'no_induk_prefix' => 'II',
                'no_induk_val' => 1,
                'items_template' => [
                    'SIMP_NON_SAHAM' => ['nama' => 'simpanan Non Saham', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_JASA_NON_SAHAM' => ['nama' => 'Simpanan Jasa Non Saham', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_SUKARELA' => ['nama' => 'Simpanan Suka Rela', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_DANA' => ['nama' => 'Simpanan Dana', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_PENGURUS' => ['nama' => 'Dana Pengurus', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_PENDIDIKAN' => ['nama' => 'Dana Pendidikan', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_KARYAWAN' => ['nama' => 'Dana Karyawan', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_PDK' => ['nama' => 'Dana PDK', 'is_editable' => true, 'nomor_display_sub' => '8', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_SOSIAL' => ['nama' => 'Dana Sosial', 'is_editable' => true, 'nomor_display_sub' => '9', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_INSENTIF' => ['nama' => 'Dana Insentif', 'is_editable' => true, 'nomor_display_sub' => '10', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_SUPERVISI' => ['nama' => 'Dana Supervisi', 'is_editable' => true, 'nomor_display_sub' => '11', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'Beban_YMHDB' => ['nama' => 'Beban yang Masih harus di Bayar', 'is_editable' => true, 'nomor_display_sub' => '12', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_RAT' => ['nama' => '1. Dana RAT', 'is_editable' => true, 'nomor_display_sub' => '13', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_KESEJAHTERAAN' => ['nama' => '2. Dana Kesejahteraan', 'is_editable' => true, 'nomor_display_sub' => '14', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_SHU_LALU' => ['nama' => '3. Dana SHU tahun lalu', 'is_editable' => true, 'nomor_display_sub' => '15', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_PENGURUS' => ['nama' => '4. Titipan Pemilihan Pengurus', 'is_editable' => true, 'nomor_display_sub' => '15.1', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SHU_TAHUN_INI_KEWAJIBAN' => ['nama' => '4. SHU tahun sekarang', 'is_editable' => true, 'nomor_display_sub' => '16', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'KEWAJIBAN_JANGKA_PANJANG' => [
                'label' => 'KEWAJIBAN JANGKA PANJANG',
                'urutan' => 5,
                'no_induk_prefix' => 'II',
                'no_induk_val' => 2,
                'items_template' => [
                    'DANA_SEHAT_KJP' => ['nama' => 'Dana Sehat', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIP_SPSW' => ['nama' => 'Titip SP/SW', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_DANADANA' => ['nama' => 'Titipan Dana-dana', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_CAP' => ['nama' => 'Titipan (CAP)', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_DANA_RAT_KJP' => ['nama' => 'Titipan Dana RAT', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_BIAYA_PAJAK' => ['nama' => 'Titipan Biaya Pajak', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_DANA_PENDAMPING' => ['nama' => 'Titipan Dana Pendamping', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PEMUPUKAN_MODAL_TETAP' => ['nama' => 'Pemupukan Modal tetap', 'is_editable' => true, 'nomor_display_sub' => '8', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TAB_PESANGON' => ['nama' => 'Tabungan Pesangon Karyawan', 'is_editable' => true, 'nomor_display_sub' => '9', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PINJAMAN_PIHAK2' => ['nama' => 'Pinjaman Pihak Ke-2', 'is_editable' => true, 'nomor_display_sub' => '10', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'EKUITAS' => [
                'label' => 'EKUITAS (MODAL)',
                'urutan' => 6,
                'no_induk_prefix' => 'II',
                'no_induk_val' => 3,
                'items_template' => [
                    'SIMP_POKOK' => ['nama' => 'Simpanan Pokok', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_WAJIB' => ['nama' => 'Simpanan Wajib', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_SWP' => ['nama' => 'Simpanan SWP', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'IURAN_DANA_SEHAT' => ['nama' => 'Iuran Dana Sehat', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'HIBAH' => ['nama' => 'Hibah', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'CAD_LIKUIDITAS' => ['nama' => 'Cadangan Likuiditas', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'CAD_KOPERASI' => ['nama' => 'Cadangan Koperasi', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_RESIKO' => ['nama' => 'Dana Resiko', 'is_editable' => true, 'nomor_display_sub' => '8', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PJKR' => ['nama' => 'PJKR', 'is_editable' => true, 'nomor_display_sub' => '9', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SHU_EKUITAS_TAHUN_INI' => ['nama' => 'SHU', 'is_editable' => true, 'nomor_display_sub' => '10', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'TIDAK_TERPETAKAN' => ['label' => 'AKUN BELUM TERPETAKAN', 'urutan' => 99, 'no_induk_prefix' => '', 'no_induk_val' => 0, 'items_template' => []]
        ];
    }
}
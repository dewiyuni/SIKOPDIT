<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\AkunModel;
use App\Models\JurnalKasModel;
use App\Models\SaldoAkunModel;
use App\Models\PemetaanAkunModel; // *** TAMBAHKAN USE INI ***

class BukuBesarModel extends Model
{
    protected $table = 'buku_besar';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['tanggal', 'id_akun', 'id_jurnal', 'keterangan', 'debit', 'kredit', 'saldo'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Konstanta threshold mungkin tidak relevan lagi jika hanya pakai mapping
    // const SIMILARITY_THRESHOLD = 75;

    /**
     * Membersihkan string untuk perbandingan (jika masih diperlukan di tempat lain).
     */
    private function _cleanString(string $str): string
    {
        $str = strtolower($str);
        $str = str_replace(['.', ',', '(', ')', '%', '/', '-'], ' ', $str);
        $str = trim(preg_replace('/\s+/', ' ', $str));
        return $str;
    }

    /**
     * Memproses Jurnal Kas ke Buku Besar MENGGUNAKAN PEMETAAN EKSPLISIT.
     *
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (YYYY)
     * @param array &$logErrors Array untuk menampung pesan error (by reference)
     * @return bool True jika berhasil, False jika gagal karena ada error pemetaan
     */
    public function prosesJurnalKeBukuBesar_dengan_pemetaan($bulan, $tahun, &$logErrors)
    {
        $db = \Config\Database::connect();
        $jurnalModel = new JurnalKasModel();
        $pemetaanModel = new PemetaanAkunModel(); // *** Gunakan model pemetaan ***

        // Kosongkan array error log
        $logErrors = [];
        $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Ambil semua jurnal untuk periode
        $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
            ->orderBy('tanggal', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($jurnal)) {
            log_message('info', "[prosesJurnalMapping] Tidak ada jurnal kas ditemukan untuk $bulan/$tahun.");
            return true; // Berhasil karena tidak ada yang diproses
        }

        $db->transStart();

        // Hapus entri buku besar lama untuk periode ini
        log_message('debug', "[prosesJurnalMapping] Mencoba menghapus buku besar untuk Bulan: $bulan, Tahun: $tahun");
        $this->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', $tahun)
            ->delete();
        $affected = $this->db->affectedRows();
        log_message('info', "[prosesJurnalMapping] Jumlah baris buku besar yang dihapus: " . $affected);

        $bukuBesarBatch = [];

        foreach ($jurnal as $j) {
            $tanggal = $j['tanggal'];
            $uraian = trim($j['uraian']);
            $kategori = $j['kategori']; // DUM atau DUK
            $jumlah = floatval($j['jumlah'] ?? 0);
            $idJurnal = $j['id'];

            if ($jumlah <= 0) {
                log_message('warning', "[prosesJurnalMapping] Jurnal ID {$idJurnal} ('{$uraian}') dilewati karena jumlah 0 atau negatif.");
                continue;
            }

            // --- Cari Aturan Pemetaan ---
            $aturan = $pemetaanModel->findMatchingRule($uraian, $kategori);

            if ($aturan) {
                // Aturan ditemukan, gunakan ID akun dari pemetaan
                $idAkunDebit = $aturan['id_akun_debit'];
                $idAkunKredit = $aturan['id_akun_kredit'];

                // Validasi sederhana: Pastikan ID akun tidak sama
                if ($idAkunDebit == $idAkunKredit) {
                    log_message('error', "[prosesJurnalMapping] Error Pemetaan: Debit dan Kredit sama (ID: {$idAkunDebit}) untuk Jurnal ID {$idJurnal} ('{$uraian}'). Perbaiki aturan pemetaan. Proses Dihentikan.");
                    $logErrors[] = "Error Pemetaan: D/K sama untuk Uraian: '{$uraian}' (Jurnal ID: {$idJurnal}).";
                    $db->transRollback();
                    return false;
                }

                log_message('debug', "[prosesJurnalMapping] Jurnal '{$uraian}' (ID: {$idJurnal}, Kat: {$kategori}) dipetakan -> Debit Akun ID: {$idAkunDebit}, Kredit Akun ID: {$idAkunKredit} (Pola: '{$aturan['pola_uraian']}')");

                // Tambahkan entri Debit ke batch
                $bukuBesarBatch[] = [
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunDebit, // <- ID dari pemetaan
                    'id_jurnal' => $idJurnal,
                    'keterangan' => $uraian,
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'saldo' => 0 // Akan diupdate nanti
                ];

                // Tambahkan entri Kredit ke batch
                $bukuBesarBatch[] = [
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunKredit, // <- ID dari pemetaan
                    'id_jurnal' => $idJurnal,
                    'keterangan' => $uraian,
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'saldo' => 0 // Akan diupdate nanti
                ];

            } else {
                // --- TIDAK ADA ATURAN PEMETAAN ---
                $errorMsg = "Tidak ditemukan aturan pemetaan untuk Uraian: '{$uraian}' (Jurnal ID: {$idJurnal}, Tgl: {$tanggal}, Kategori: {$kategori}).";
                log_message('error', "[prosesJurnalMapping] " . $errorMsg . " Proses Dihentikan.");
                $logErrors[] = $errorMsg; // Tambahkan ke log error
                $db->transRollback(); // Langsung hentikan proses
                return false;
            }
        } // End foreach jurnal

        // --- Selesai Loop Jurnal ---
        // =======================================

        // ========================
// === KHUSUS AKUN KAS ===
// ========================

        // Cari akun Kas (misalnya berdasarkan kode atau kategori)
        $akunModel = new \App\Models\AkunModel();
        $akunKas = $akunModel->where('kode_akun', 'AUTO086')->first(); // Ganti sesuai kode Kas
        if ($akunKas) {
            $idAkunKas = $akunKas['id'];

            // Hitung total DUM dan DUK
            $totalDUM = 0;
            $totalDUK = 0;

            foreach ($jurnal as $j) {
                $kategori = $j['kategori']; // DUM atau DUK
                $jumlah = floatval($j['jumlah'] ?? 0);

                if ($kategori === 'DUM') {
                    $totalDUM += $jumlah;
                } elseif ($kategori === 'DUK') {
                    $totalDUK += $jumlah;
                }
            }

            // Hapus entri buku besar untuk akun Kas dari batch
            $bukuBesarBatch = array_filter($bukuBesarBatch, function ($item) use ($idAkunKas) {
                return $item['id_akun'] != $idAkunKas;
            });

            // Tambahkan entri pengganti
            $tanggalAkhir = end($jurnal)['tanggal'] ?? date('Y-m-t');
            $bukuBesarBatch[] = [
                'tanggal' => $tanggalAkhir,
                'id_akun' => $idAkunKas,
                'id_jurnal' => null,
                'keterangan' => 'Total DUM bulan ' . $bulan . '/' . $tahun,
                'debit' => $totalDUM,
                'kredit' => 0,
                'saldo' => 0
            ];
            $bukuBesarBatch[] = [
                'tanggal' => $tanggalAkhir,
                'id_akun' => $idAkunKas,
                'id_jurnal' => null,
                'keterangan' => 'Total DUK bulan ' . $bulan . '/' . $tahun,
                'debit' => 0,
                'kredit' => $totalDUK,
                'saldo' => 0
            ];
        }

        // Cek lagi $logErrors (seharusnya kosong jika sampai sini)
        if (!empty($logErrors)) {
            log_message('error', "[prosesJurnalMapping] Proses dihentikan karena ada " . count($logErrors) . " jurnal yang tidak dapat dipetakan.");
            $db->transRollback();
            return false;
        }

        // Lakukan batch insert jika ada data dan tidak ada error
        if (!empty($bukuBesarBatch)) {
            if (!$this->insertBatch($bukuBesarBatch)) {
                log_message('error', "[prosesJurnalMapping] Gagal melakukan insertBatch ke buku_besar: " . json_encode($this->db->error()));
                $logErrors[] = "Gagal menyimpan data buku besar ke database.";
                $db->transRollback();
                return false;
            }
            log_message('info', "[prosesJurnalMapping] " . count($bukuBesarBatch) . " entri buku besar ditambahkan untuk $bulan/$tahun.");
        } else {
            // Ini bisa terjadi jika semua jurnal jumlahnya 0
            log_message('info', "[prosesJurnalMapping] Tidak ada entri buku besar yang valid untuk ditambahkan pada $bulan/$tahun.");
        }

        // Update saldo setelah semua entri dimasukkan
        // Pastikan fungsi updateAllSaldos ada dan dipanggil
        if (method_exists($this, 'updateAllSaldos')) {
            log_message('info', "[prosesJurnalMapping] Memulai update saldo akhir untuk $bulan/$tahun...");
            $updateSaldoSuccess = $this->updateAllSaldos($bulan, $tahun);
            if (!$updateSaldoSuccess) {
                log_message('error', "[prosesJurnalMapping] Gagal mengupdate saldo setelah proses jurnal.");
                $db->transRollback();
                $logErrors[] = "Gagal mengupdate saldo akun setelah memproses jurnal.";
                return false;
            }
            log_message('info', "[prosesJurnalMapping] Update saldo akhir selesai untuk $bulan/$tahun.");
            // --- Simpan saldo akhir ke tabel saldo_akun ---
            $akunModel = new \App\Models\AkunModel();
            $saldoAkunModel = new \App\Models\SaldoAkunModel();

            $daftarAkun = $akunModel->findAll();

            foreach ($daftarAkun as $akun) {
                $idAkun = $akun['id'];
                $saldoAwal = $this->getSaldoAwalAkun($idAkun, $bulan, $tahun);

                $transaksi = $this->getBukuBesarByAkun($idAkun, $bulan, $tahun);

                $totalDebit = 0;
                $totalKredit = 0;

                foreach ($transaksi as $t) {
                    $totalDebit += floatval($t['debit']);
                    $totalKredit += floatval($t['kredit']);
                }

                $saldoAkhir = $saldoAwal + ($totalDebit - $totalKredit);

                // Simpan atau update ke saldo_akun
                $existing = $saldoAkunModel
                    ->where('id_akun', $idAkun)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();

                if ($existing) {
                    $saldoAkunModel->update($existing['id'], ['saldo_akhir' => $saldoAkhir]);
                } else {
                    $saldoAkunModel->insert([
                        'id_akun' => $idAkun,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'saldo_akhir' => $saldoAkhir
                    ]);
                }

                log_message('debug', "[PROSES] Saldo akhir akun {$akun['nama_akun']} ($idAkun) bulan $bulan/$tahun: $saldoAkhir");
            }

        } else {
            log_message('critical', "[prosesJurnalMapping] Fungsi updateAllSaldos tidak ditemukan di BukuBesarModel. Saldo tidak akan terupdate!");
            $logErrors[] = "Fungsi update saldo tidak ditemukan.";
            $db->transRollback();
            return false;
        }

        // Selesaikan transaksi jika semua berhasil
        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', '[prosesJurnalMapping] Transaksi database gagal.');
            $logErrors[] = "Transaksi database gagal.";
            return false;
        }

        log_message('info', "[prosesJurnalMapping] Proses jurnal ke buku besar (mapping) untuk $bulan/$tahun selesai BERHASIL.");
        return true; // Berhasil
    }


    // --- Fungsi-Fungsi Pendukung (Pastikan Ada & Benar) ---

    /**
     * Mengambil detail transaksi buku besar untuk satu akun pada periode tertentu.
     */
    public function getBukuBesarByAkun($idAkun, $bulan = null, $tahun = null)
    {
        $builder = $this->select('buku_besar.*, akun.kode_akun, akun.nama_akun')
            ->join('akun', 'akun.id = buku_besar.id_akun')
            ->where('buku_besar.id_akun', $idAkun);

        if ($bulan !== null && $tahun !== null) {
            // Tampilkan transaksi dari awal tahun hingga akhir bulan yang dipilih
            $tanggalAwal = $tahun . '-01-01';
            // Ambil tanggal terakhir di bulan & tahun tsb
            $tanggalAkhir = date('Y-m-t', strtotime("$tahun-$bulan-01"));

            $builder->where('buku_besar.tanggal >=', $tanggalAwal)
                ->where('buku_besar.tanggal <=', $tanggalAkhir);
        }
        // Urutan penting untuk tampilan dan perhitungan saldo berjalan manual jika diperlukan
        $builder->orderBy('buku_besar.tanggal ASC, buku_besar.id ASC');

        return $builder->findAll();
    }

    /**
     * Mendapatkan saldo awal suatu akun pada awal bulan tertentu.
     */
    public function getSaldoAwalAkun($idAkun, $bulan, $tahun)
    {
        try {
            $saldoAkunModel = new \App\Models\SaldoAkunModel();
            $akunModel = new \App\Models\AkunModel();

            // Hitung bulan & tahun sebelumnya
            if ($bulan == 1) {
                $bulanSebelumnya = 12;
                $tahunSebelumnya = $tahun - 1;
            } else {
                $bulanSebelumnya = $bulan - 1;
                $tahunSebelumnya = $tahun;
            }

            // 1. Cari saldo akhir bulan sebelumnya
            $saldoAkhir = $saldoAkunModel->where([
                'id_akun' => $idAkun,
                'bulan' => $bulanSebelumnya,
                'tahun' => $tahunSebelumnya
            ])->first();

            if ($saldoAkhir && isset($saldoAkhir['saldo_akhir'])) {
                log_message('debug', "[getSaldoAwalAkun] Akun $idAkun ($bulan/$tahun): pakai saldo akhir bulan sebelumnya = {$saldoAkhir['saldo_akhir']}");
                return (float) $saldoAkhir['saldo_akhir'];
            }

            // 2. Jika tidak ada saldo bulan lalu, ambil dari master akun
            $akun = $akunModel->find($idAkun);
            $saldoAwal = $akun ? (float) $akun['saldo_awal'] : 0;

            log_message('debug', "[getSaldoAwalAkun] Akun $idAkun ($bulan/$tahun): pakai saldo_awal dari master akun = $saldoAwal");

            return $saldoAwal;

        } catch (\Throwable $e) {
            log_message('error', "[getSaldoAwalAkun] Error akun $idAkun ($bulan/$tahun): " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Mengupdate ringkasan saldo (awal, D/K, akhir) untuk suatu akun
     * pada bulan dan tahun tertentu di tabel saldo_akun.
     */
    public function updateSaldoAkun($idAkun, $bulan, $tahun)
    {
        try {
            $db = \Config\Database::connect();
            $saldoAkunModel = new SaldoAkunModel();
            $akunModel = new AkunModel();

            // 1. Dapatkan Saldo Awal
            $saldoAwal = $this->getSaldoAwalAkun($idAkun, $bulan, $tahun);

            // 2. Hitung Total Debit dan Kredit dari buku_besar untuk bulan ini
            $query = $db->table('buku_besar')
                ->select('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(kredit), 0) as total_kredit', false)
                ->where('id_akun', $idAkun)
                ->where('MONTH(tanggal)', $bulan)
                ->where('YEAR(tanggal)', $tahun)
                ->get();

            $result = $query->getRow();
            $totalDebit = $result ? floatval($result->total_debit) : 0;
            $totalKredit = $result ? floatval($result->total_kredit) : 0;

            // 3. Ambil informasi jenis akun
            $akun = $akunModel->find($idAkun);
            if (!$akun) {
                log_message('error', "[BukuBesarModel::updateSaldoAkun] Akun ID $idAkun tidak ditemukan.");
                return false;
            }
            $jenisAkun = $akun['jenis'];

            // 4. Hitung Saldo Akhir berdasarkan jenis akun
            $saldoAkhir = $saldoAwal; // Mulai dari saldo awal
            if ($jenisAkun == 'Debit') {
                $saldoAkhir += $totalDebit - $totalKredit;
            } elseif ($jenisAkun == 'Kredit') {
                $saldoAkhir += $totalKredit - $totalDebit; // Saldo kredit bertambah jika kredit > debit
            } else {
                log_message('warning', "[BukuBesarModel::updateSaldoAkun] Jenis akun tidak valid ('$jenisAkun') untuk Akun ID $idAkun. Perhitungan saldo mungkin salah.");
                // Default ke logika Debit jika tidak pasti
                $saldoAkhir += $totalDebit - $totalKredit;
            }

            log_message('debug', "[updateSaldoAkun] Update Saldo Akun $idAkun ($bulan/$tahun): Awal=$saldoAwal, D=$totalDebit, K=$totalKredit, Akhir=$saldoAkhir, Jenis=$jenisAkun");

            // 5. Update atau Insert ke tabel saldo_akun
            $existingSaldo = $saldoAkunModel->where('id_akun', $idAkun)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

            $dataSaldo = [
                'id_akun' => $idAkun,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'saldo_awal' => $saldoAwal,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir' => $saldoAkhir,
            ];

            if ($existingSaldo) {
                if (!$saldoAkunModel->update($existingSaldo['id'], $dataSaldo)) {
                    log_message('error', "[updateSaldoAkun] Gagal update saldo_akun ID {$existingSaldo['id']}: " . json_encode($saldoAkunModel->errors()));
                    return false;
                }
                log_message('debug', "[updateSaldoAkun] Saldo Akun $idAkun ($bulan/$tahun) Updated.");
            } else {
                if (!$saldoAkunModel->insert($dataSaldo)) {
                    log_message('error', "[updateSaldoAkun] Gagal insert saldo_akun untuk Akun ID {$idAkun}: " . json_encode($saldoAkunModel->errors()));
                    return false;
                }
                log_message('debug', "[updateSaldoAkun] Saldo Akun $idAkun ($bulan/$tahun) Inserted.");
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', "[BukuBesarModel::updateSaldoAkun] Error for Akun $idAkun ($bulan/$tahun): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Mengupdate saldo berjalan di tabel buku_besar dan memanggil updateSaldoAkun
     * untuk SEMUA akun pada bulan dan tahun tertentu.
     */
    private function updateAllSaldos($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $akunModel = new AkunModel();

        try {
            log_message('debug', "[updateAllSaldos] Memulai update saldo untuk $bulan/$tahun...");
            $akuns = $akunModel->findAll();

            if (empty($akuns)) {
                log_message('warning', "[updateAllSaldos] Tidak ada akun ditemukan untuk update saldo.");
                return true;
            }

            foreach ($akuns as $akun) {
                $idAkun = $akun['id'];
                $jenisAkun = $akun['jenis'];

                // 1. Update saldo berjalan di buku_besar (PENTING!)
                $saldoAwalBulan = $this->getSaldoAwalAkun($idAkun, $bulan, $tahun);

                $query = $db->table('buku_besar')
                    ->select('id, debit, kredit')
                    ->where('id_akun', $idAkun)
                    ->where('MONTH(tanggal)', $bulan)
                    ->where('YEAR(tanggal)', $tahun)
                    ->orderBy('tanggal', 'ASC')
                    ->orderBy('id', 'ASC') // Urutan sangat penting
                    ->get();
                $transaksis = $query->getResultArray();

                $currentSaldo = $saldoAwalBulan;
                $updates = []; // Batch update saldo berjalan

                if (!empty($transaksis)) {
                    foreach ($transaksis as $transaksi) {
                        if ($jenisAkun == 'Debit') {
                            $currentSaldo += floatval($transaksi['debit']) - floatval($transaksi['kredit']);
                        } elseif ($jenisAkun == 'Kredit') {
                            $currentSaldo += floatval($transaksi['kredit']) - floatval($transaksi['debit']);
                        } else {
                            continue;
                        } // Abaikan jika jenis akun aneh

                        $updates[] = ['id' => $transaksi['id'], 'saldo' => $currentSaldo];
                    }

                    if (!empty($updates)) {
                        if (!$this->updateBatch($updates, 'id')) {
                            log_message('error', "[updateAllSaldos] Gagal updateBatch saldo berjalan untuk Akun ID {$idAkun}: " . json_encode($this->db->error()));
                            // Mungkin perlu rollback transaksi luar jika ada
                        } else {
                            log_message('debug', "[updateAllSaldos] Akun {$idAkun}: " . count($updates) . " transaksi saldo berjalan diupdate.");
                        }
                    }
                } else {
                    log_message('debug', "[updateAllSaldos] Akun {$idAkun}: Tidak ada transaksi di bulan {$bulan}/{$tahun} untuk update saldo berjalan.");
                }

                // 2. Update ringkasan di saldo_akun
                if (!$this->updateSaldoAkun($idAkun, $bulan, $tahun)) {
                    log_message('error', "[updateAllSaldos] Gagal mengupdate ringkasan saldo_akun untuk akun {$idAkun}.");
                    // Pertimbangkan return false jika ini kritis
                }
            } // End foreach akun

            log_message('debug', "[updateAllSaldos] Selesai update saldo untuk {$bulan}/{$tahun}.");
            return true;

        } catch (\Exception $e) {
            log_message('error', "[BukuBesarModel::updateAllSaldos] Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

} // End Class BukuBesarModel
<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\AkunModel;       // Pastikan use statement ini ada
use App\Models\JurnalKasModel;  // Pastikan use statement ini ada
use App\Models\SaldoAkunModel;  // Pastikan use statement ini ada

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

    // Konstanta untuk threshold kemiripan (misal: 75%) - Sesuaikan sesuai kebutuhan
    // Naikkan jika terlalu banyak salah cocok, turunkan jika terlalu banyak yg tidak cocok
    const SIMILARITY_THRESHOLD = 70;

    /**
     * Membersihkan string untuk perbandingan fuzzy.
     * Mengubah ke lowercase, menghapus tanda baca umum, merapikan spasi.
     *
     * @param string $str String input
     * @return string String yang sudah dibersihkan
     */
    private function _cleanString(string $str): string
    {
        $str = strtolower($str); // Konversi ke huruf kecil
        // Hapus tanda baca umum (titik, koma, kurung, persen, garis miring, hubung) dan ganti dengan spasi
        $str = str_replace(['.', ',', '(', ')', '%', '/', '-'], ' ', $str);
        // Ganti multiple spasi jadi satu, lalu trim
        $str = trim(preg_replace('/\s+/', ' ', $str));
        return $str;
    }

    /**
     * Mengambil detail transaksi buku besar untuk satu akun pada periode tertentu.
     * Digunakan untuk halaman detail buku besar.
     *
     * @param int $idAkun ID Akun
     * @param int|null $bulan Bulan (1-12)
     * @param int|null $tahun Tahun (YYYY)
     * @return array Daftar transaksi
     */
    public function getBukuBesarByAkun($idAkun, $bulan = null, $tahun = null)
    {
        $builder = $this->select('buku_besar.*, akun.kode_akun, akun.nama_akun')
            ->join('akun', 'akun.id = buku_besar.id_akun')
            ->where('buku_besar.id_akun', $idAkun);

        if ($bulan !== null && $tahun !== null) {
            // Untuk detail, tampilkan transaksi dari awal tahun hingga akhir bulan yang dipilih
            // agar saldo berjalan terlihat benar
            $tanggalAwal = $tahun . '-01-01';
            $tanggalAkhir = date('Y-m-t', strtotime("$tahun-$bulan-01")); // Tanggal terakhir di bulan tsb

            $builder->where('buku_besar.tanggal >=', $tanggalAwal)
                ->where('buku_besar.tanggal <=', $tanggalAkhir);
        }
        $builder->orderBy('buku_besar.tanggal ASC, buku_besar.id ASC'); // Urutkan berdasarkan tanggal dan ID

        return $builder->findAll();
    }

    /**
     * Mendapatkan saldo awal suatu akun pada awal bulan tertentu.
     * Mengambil dari saldo akhir bulan sebelumnya di tabel saldo_akun,
     * atau dari saldo awal master jika tidak ada riwayat.
     *
     * @param int $idAkun ID Akun
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (YYYY)
     * @return float Saldo awal
     */
    public function getSaldoAwalAkun($idAkun, $bulan, $tahun)
    {
        try {
            $saldoAkunModel = new SaldoAkunModel(); // Gunakan model SaldoAkun

            // Tentukan tanggal awal periode yang diminta
            $tanggalAwalPeriode = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";

            // Cari saldo akhir terakhir SEBELUM tanggal awal periode ini
            $saldoAkhirSebelumnya = $saldoAkunModel
                ->where('id_akun', $idAkun)
                // Filter berdasarkan kombinasi tahun dan bulan
                ->where("STR_TO_DATE(CONCAT(tahun, '-', LPAD(bulan, 2, '0'), '-01'), '%Y-%m-%d') <", $tanggalAwalPeriode)
                ->orderBy('tahun', 'DESC')
                ->orderBy('bulan', 'DESC')
                ->first();

            if ($saldoAkhirSebelumnya) {
                log_message('debug', "[getSaldoAwalAkun] Saldo awal (akhir bln lalu) Akun $idAkun ($bulan/$tahun): " . $saldoAkhirSebelumnya['saldo_akhir']);
                return floatval($saldoAkhirSebelumnya['saldo_akhir']);
            } else {
                // Jika tidak ada saldo bulan sebelumnya, ambil saldo awal dari tabel akun (master)
                $akunModel = new AkunModel();
                $akun = $akunModel->find($idAkun);
                $saldoAwalMaster = $akun ? floatval($akun['saldo_awal']) : 0;
                log_message('debug', "[getSaldoAwalAkun] Saldo awal (master) Akun $idAkun ($bulan/$tahun): " . $saldoAwalMaster);
                return $saldoAwalMaster;
            }
        } catch (\Exception $e) {
            log_message('error', "[BukuBesarModel::getSaldoAwalAkun] Error for Akun $idAkun ($bulan/$tahun): " . $e->getMessage());
            return 0; // Kembalikan 0 jika terjadi error
        }
    }

    /**
     * Mengupdate ringkasan saldo (awal, D/K, akhir) untuk suatu akun
     * pada bulan dan tahun tertentu di tabel saldo_akun.
     *
     * @param int $idAkun ID Akun
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (YYYY)
     * @return bool True jika berhasil, False jika gagal
     */
    public function updateSaldoAkun($idAkun, $bulan, $tahun)
    {
        try {
            $db = \Config\Database::connect();
            $saldoAkunModel = new SaldoAkunModel(); // Model untuk tabel saldo_akun
            $akunModel = new AkunModel();

            // 1. Dapatkan Saldo Awal untuk bulan ini
            $saldoAwal = $this->getSaldoAwalAkun($idAkun, $bulan, $tahun);

            // 2. Hitung Total Debit dan Kredit dari buku_besar untuk bulan ini
            $query = $db->query("
                SELECT
                    COALESCE(SUM(debit), 0) as total_debit,
                    COALESCE(SUM(kredit), 0) as total_kredit
                FROM buku_besar
                WHERE id_akun = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
            ", [$idAkun, $bulan, $tahun]);

            $result = $query->getRow();
            $totalDebit = $result ? floatval($result->total_debit) : 0;
            $totalKredit = $result ? floatval($result->total_kredit) : 0;

            // 3. Ambil informasi jenis akun
            $akun = $akunModel->find($idAkun);
            if (!$akun) {
                log_message('error', "[BukuBesarModel::updateSaldoAkun] Akun dengan ID $idAkun tidak ditemukan.");
                return false;
            }
            $jenisAkun = $akun['jenis'];

            // 4. Hitung Saldo Akhir
            if ($jenisAkun == 'Debit') {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } elseif ($jenisAkun == 'Kredit') {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            } else {
                log_message('error', "[BukuBesarModel::updateSaldoAkun] Jenis akun tidak valid ('$jenisAkun') untuk Akun ID $idAkun.");
                return false;
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
                // Update jika sudah ada
                $saldoAkunModel->update($existingSaldo['id'], $dataSaldo);
                log_message('debug', "[updateSaldoAkun] Saldo Akun $idAkun ($bulan/$tahun) Updated.");
            } else {
                // Insert jika belum ada
                $saldoAkunModel->insert($dataSaldo);
                log_message('debug', "[updateSaldoAkun] Saldo Akun $idAkun ($bulan/$tahun) Inserted.");
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', "[BukuBesarModel::updateSaldoAkun] Error for Akun $idAkun ($bulan/$tahun): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Memproses Jurnal Kas ke Buku Besar menggunakan Fuzzy Matching.
     * Mencoba menemukan akun yang paling mirip berdasarkan uraian.
     *
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (YYYY)
     * @param int $idAkunKas ID Akun untuk Kas/Bank
     * @param array &$logErrors Array untuk menampung pesan error (by reference)
     * @return bool True jika berhasil, False jika gagal karena ada error pencocokan
     */
    public function prosesJurnalKeBukuBesar_tanpaPemetaan($bulan, $tahun, $idAkunKas, &$logErrors)
    {
        $db = \Config\Database::connect();
        $jurnalModel = new JurnalKasModel();
        $akunModel = new AkunModel();

        // Kosongkan array error log setiap kali proses dimulai
        $logErrors = [];

        // Format bulan untuk query
        $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Ambil semua jurnal untuk bulan dan tahun yang dipilih
        $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
            ->orderBy('tanggal', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($jurnal)) {
            log_message('info', "[prosesJurnal] Tidak ada jurnal kas ditemukan untuk $bulan/$tahun.");
            return true; // Tidak ada yang diproses, dianggap berhasil
        }

        // Ambil semua akun dan siapkan data untuk matching
        $semuaAkun = $akunModel->findAll();
        $akunDataForMatching = [];
        foreach ($semuaAkun as $ak) {
            // Jangan ikutkan akun kas/bank dalam target matching uraian
            if ($ak['id'] != $idAkunKas) {
                $akunDataForMatching[] = [
                    'id' => $ak['id'],
                    'nama_akun' => $ak['nama_akun'],
                    'nama_akun_cleaned' => $this->_cleanString($ak['nama_akun']) // Bersihkan nama akun sekali saja
                ];
            }
        }
        if (empty($akunDataForMatching)) {
            log_message('error', "[prosesJurnal] Tidak ada akun (selain kas) ditemukan untuk matching.");
            $logErrors[] = "Tidak ada akun (selain kas) ditemukan untuk pencocokan.";
            return false; // Tidak bisa matching jika tidak ada target
        }


        $db->transStart();

        // Hapus entri buku besar yang sudah ada untuk bulan ini
        $this->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', $tahun)
            ->delete();
        log_message('info', "[prosesJurnal] Buku besar existing untuk $bulan/$tahun dihapus.");

        $bukuBesarBatch = []; // Untuk batch insert

        foreach ($jurnal as $j) {
            $tanggal = $j['tanggal'];
            $uraian = trim($j['uraian']);
            $kategori = $j['kategori'];
            $jumlah = floatval($j['jumlah'] ?? 0);
            $idJurnal = $j['id'];

            if ($jumlah <= 0) {
                log_message('warning', "[prosesJurnal] Jurnal ID {$idJurnal} dilewati karena jumlah 0 atau negatif.");
                continue; // Lewati jika jumlah tidak valid
            }

            // --- Fuzzy Matching Logic ---
            $uraianCleaned = $this->_cleanString($uraian);
            $bestMatchAkun = null;
            $maxSimilarity = -1; // Inisialisasi dengan -1

            foreach ($akunDataForMatching as $akunTarget) {
                // Hitung persentase kemiripan
                similar_text($uraianCleaned, $akunTarget['nama_akun_cleaned'], $percent);

                // Jika kemiripan saat ini lebih tinggi dari maksimum sebelumnya
                if ($percent > $maxSimilarity) {
                    $maxSimilarity = $percent;
                    $bestMatchAkun = $akunTarget; // Simpan data akun yang paling mirip sejauh ini
                }
            }
            // --- End Fuzzy Matching Logic ---

            // Cek apakah ditemukan akun yang cukup mirip (di atas threshold)
            if ($bestMatchAkun && $maxSimilarity >= self::SIMILARITY_THRESHOLD) {
                $idAkunNonKas = $bestMatchAkun['id'];
                $namaAkunCocok = $bestMatchAkun['nama_akun']; // Nama akun asli yg cocok
                log_message('debug', "[prosesJurnal] Jurnal '{$uraian}' (ID: {$idJurnal}) cocok (Similarity: " . round($maxSimilarity, 2) . "%) dengan Akun '{$namaAkunCocok}' (ID: {$idAkunNonKas})");

                // Tentukan ID Akun Debit dan Kredit
                $idAkunDebit = null;
                $idAkunKredit = null;

                if ($kategori == 'DUM') { // Debet Uang Masuk (Kas Bertambah +)
                    $idAkunDebit = $idAkunKas;       // Kas di Debit
                    $idAkunKredit = $idAkunNonKas;  // Akun Non-Kas (Pendapatan/Setoran/dll) di Kredit
                } elseif ($kategori == 'DUK') { // Debet Uang Keluar (Kas Berkurang -)
                    $idAkunDebit = $idAkunNonKas;  // Akun Non-Kas (Biaya/Penarikan/dll) di Debit
                    $idAkunKredit = $idAkunKas;       // Kas di Kredit
                } else {
                    log_message('warning', "[prosesJurnal] Kategori jurnal tidak valid ('{$kategori}') untuk Jurnal ID {$idJurnal}.");
                    continue; // Lewati jika kategori tidak dikenal
                }

                // Tambahkan data Debit ke batch
                if ($idAkunDebit) {
                    $bukuBesarBatch[] = [
                        'tanggal' => $tanggal,
                        'id_akun' => $idAkunDebit,
                        'id_jurnal' => $idJurnal,
                        'keterangan' => $uraian, // Tetap pakai uraian asli
                        'debit' => $jumlah,
                        'kredit' => 0,
                        'saldo' => 0 // Akan diupdate nanti
                    ];
                }
                // Tambahkan data Kredit ke batch
                if ($idAkunKredit) {
                    $bukuBesarBatch[] = [
                        'tanggal' => $tanggal,
                        'id_akun' => $idAkunKredit,
                        'id_jurnal' => $idJurnal,
                        'keterangan' => $uraian, // Tetap pakai uraian asli
                        'debit' => 0,
                        'kredit' => $jumlah,
                        'saldo' => 0 // Akan diupdate nanti
                    ];
                }

            } else {
                // Jika tidak ada akun yang cukup mirip ditemukan
                $similarityInfo = ($maxSimilarity >= 0) ? "Max Similarity: " . round($maxSimilarity, 2) . "%" : "Tidak ada kemiripan";
                $errorMsg = "Uraian '{$uraian}' (Jurnal ID: {$idJurnal}, Tgl: {$tanggal}) tidak ditemukan Akun yang cukup mirip ($similarityInfo).";
                log_message('error', "[prosesJurnal] " . $errorMsg);
                $logErrors[] = $errorMsg; // Tambahkan ke log error
                continue; // Lanjutkan ke jurnal berikutnya (agar semua error bisa dilaporkan)
            }
        } // End foreach jurnal

        // --- Selesai Loop Jurnal ---

        // Lakukan batch insert jika ada data
        if (!empty($bukuBesarBatch)) {
            // Gunakan insertBatch dari Query Builder untuk performa
            $this->insertBatch($bukuBesarBatch);
            log_message('info', "[prosesJurnal] " . count($bukuBesarBatch) . " entri buku besar ditambahkan untuk $bulan/$tahun.");
        } else {
            // Cek apakah ada error, jika tidak ada error berarti memang tidak ada jurnal valid
            if (empty($logErrors)) {
                log_message('info', "[prosesJurnal] Tidak ada entri buku besar yang valid untuk ditambahkan pada $bulan/$tahun.");
            }
        }

        // Jika ada error pencocokan uraian selama loop, hentikan proses dan rollback
        if (!empty($logErrors)) {
            log_message('error', "[prosesJurnal] Proses dihentikan karena ada " . count($logErrors) . " uraian yang tidak cocok.");
            $db->transRollback();
            return false; // Kembalikan false karena proses tidak lengkap
        }

        // Setelah semua entri dimasukkan (dan tidak ada error), update saldo
        log_message('info', "[prosesJurnal] Memulai update saldo akhir untuk $bulan/$tahun...");
        $updateSaldoSuccess = $this->updateAllSaldos($bulan, $tahun);
        if (!$updateSaldoSuccess) {
            log_message('error', "[prosesJurnal] Gagal mengupdate saldo setelah proses jurnal.");
            $db->transRollback(); // Rollback jika update saldo gagal
            $logErrors[] = "Gagal mengupdate saldo akun setelah memproses jurnal.";
            return false;
        }
        log_message('info', "[prosesJurnal] Update saldo akhir selesai untuk $bulan/$tahun.");


        // Jika semua langkah berhasil dan tidak ada error
        $db->transComplete();

        if ($db->transStatus() === false) {
            // Jika transaksi gagal karena alasan lain (misal: constraint, disk space)
            log_message('error', '[prosesJurnal] Transaksi database gagal saat memproses jurnal ke buku besar.');
            $logErrors[] = "Transaksi database gagal.";
            return false;
        }

        log_message('info', "[prosesJurnal] Proses jurnal ke buku besar untuk $bulan/$tahun selesai BERHASIL.");
        return true; // Berhasil
    }

    /**
     * Mengupdate saldo berjalan di tabel buku_besar dan saldo akhir di saldo_akun
     * untuk SEMUA akun pada bulan dan tahun tertentu.
     * Penting untuk dipanggil SETELAH semua entri buku besar untuk bulan itu dimasukkan.
     *
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (YYYY)
     * @return bool True jika berhasil, False jika gagal
     */
    private function updateAllSaldos($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $akunModel = new AkunModel();

        try {
            log_message('debug', "[updateAllSaldos] Memulai update saldo untuk $bulan/$tahun...");
            // Ambil semua akun yang ada
            $akuns = $akunModel->findAll();

            if (empty($akuns)) {
                log_message('warning', "[updateAllSaldos] Tidak ada akun ditemukan untuk update saldo.");
                return true; // Tidak ada yang diupdate
            }

            // Mulai transaksi (opsional, tapi lebih aman jika banyak update)
            // $db->transStart();

            foreach ($akuns as $akun) {
                $idAkun = $akun['id'];
                $jenisAkun = $akun['jenis'];

                // 1. Dapatkan Saldo Awal untuk bulan ini
                $saldoAwalBulan = $this->getSaldoAwalAkun($idAkun, $bulan, $tahun);
                log_message('debug', "[updateAllSaldos] Akun $idAkun ('{$akun['nama_akun']}', $jenisAkun): Saldo Awal = $saldoAwalBulan");

                // 2. Ambil semua transaksi buku besar untuk akun ini pada bulan yang dipilih
                // Urutkan berdasarkan tanggal dan ID untuk memastikan urutan perhitungan saldo benar
                $query = $db->query("
                    SELECT id, tanggal, debit, kredit
                    FROM buku_besar
                    WHERE id_akun = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                    ORDER BY tanggal ASC, id ASC
                ", [$idAkun, $bulan, $tahun]);

                $transaksis = $query->getResultArray();

                $currentSaldo = $saldoAwalBulan;
                $updates = []; // Untuk menampung data update saldo berjalan

                // 3. Hitung saldo berjalan untuk setiap transaksi
                if (!empty($transaksis)) {
                    foreach ($transaksis as $transaksi) {
                        if ($jenisAkun == 'Debit') {
                            $currentSaldo = $currentSaldo + floatval($transaksi['debit']) - floatval($transaksi['kredit']);
                        } elseif ($jenisAkun == 'Kredit') {
                            $currentSaldo = $currentSaldo - floatval($transaksi['debit']) + floatval($transaksi['kredit']);
                        } else {
                            // Seharusnya tidak terjadi jika validasi data akun bagus
                            log_message('warning', "[updateAllSaldos] Jenis akun tidak dikenal '$jenisAkun' untuk akun ID $idAkun");
                            continue; // Lewati transaksi ini jika jenis akun aneh
                        }

                        // Siapkan data untuk update batch (lebih efisien)
                        $updates[] = [
                            'id' => $transaksi['id'],
                            'saldo' => $currentSaldo
                        ];
                    }

                    // Lakukan batch update saldo berjalan jika ada data
                    if (!empty($updates)) {
                        $this->updateBatch($updates, 'id');
                        log_message('debug', "[updateAllSaldos] Akun $idAkun: " . count($transaksis) . " transaksi saldo berjalan diupdate.");
                    }
                } else {
                    log_message('debug', "[updateAllSaldos] Akun $idAkun: Tidak ada transaksi di bulan $bulan/$tahun.");
                }

                // 4. Update ringkasan saldo akhir di tabel saldo_akun
                // Method ini sudah menghitung ulang total D/K dan saldo akhir berdasarkan $saldoAwalBulan
                $updateRingkasanOk = $this->updateSaldoAkun($idAkun, $bulan, $tahun);
                if (!$updateRingkasanOk) {
                    log_message('error', "[updateAllSaldos] Gagal mengupdate ringkasan saldo_akun untuk akun $idAkun.");
                    // Jika ingin menghentikan semua proses jika satu update gagal:
                    // $db->transRollback();
                    // return false;
                }

            } // End foreach akun

            // $db->transComplete();
            // if ($db->transStatus() === false) {
            //     log_message('error', "[updateAllSaldos] Transaksi gagal saat update saldo.");
            //     return false;
            // }

            log_message('debug', "[updateAllSaldos] Selesai update saldo untuk $bulan/$tahun.");
            return true;

        } catch (\Exception $e) {
            log_message('error', "[BukuBesarModel::updateAllSaldos] Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            // if ($db->transStatus() !== false) { // Rollback jika transaksi masih aktif
            //     $db->transRollback();
            // }
            return false;
        }
    }

} // End Class
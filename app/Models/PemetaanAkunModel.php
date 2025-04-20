<?php

namespace App\Models;

use CodeIgniter\Model;

class PemetaanAkunModel extends Model
{
    protected $table = 'pemetaan_akun'; // Nama tabel
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    // Sesuaikan allowedFields dengan kolom tabel (tanpa id, created_at, updated_at)
    protected $allowedFields = [
        'pola_uraian',
        'kategori_jurnal',
        'id_akun_debit',
        'id_akun_kredit',
        'prioritas',
        'deskripsi'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Mencari aturan pemetaan yang cocok untuk jurnal tertentu.
     * Menggunakan LIKE untuk pola_uraian dan urut berdasarkan prioritas.
     *
     * @param string $uraian Uraian dari jurnal kas
     * @param string $kategori Kategori DUM/DUK dari jurnal kas
     * @return array|null Data aturan pemetaan yang cocok atau null jika tidak ditemukan
     */
    public function findMatchingRule(string $uraian, string $kategori): ?array
    {
        $cleanedUraian = trim($uraian);

        // Cari aturan yang cocok menggunakan LIKE dan urutkan berdasarkan prioritas DESC
        return $this->where('kategori_jurnal', $kategori)
            ->where("'$cleanedUraian' LIKE pola_uraian", null, false) // false agar CI tidak escape 'like'
            ->orderBy('prioritas', 'DESC') // Prioritas tertinggi diutamakan
            ->first();                     // Ambil hanya 1 aturan terbaik
    }
    /**
     * Membuat aturan pemetaan secara otomatis berdasarkan pencocokan langsung
     * antara uraian jurnal kas dan nama akun.
     * Mengabaikan aturan yang sudah ada dan beberapa kasus khusus (penyusutan, transfer).
     *
     * @param int $idAkunKasUtama ID Akun Kas/Bank utama untuk DUM/DUK.
     * @return array Statistik hasil: ['created' => int, 'skipped_exist' => int, 'skipped_special' => int, 'failed_match' => int, 'skipped_same_dk' => int]
     */
    public function generateOtomatisFromJournal(int $idAkunKasUtama): array
    {
        $db = \Config\Database::connect();
        $jurnalModel = new JurnalKasModel();
        $akunModel = new AkunModel();

        $stats = ['created' => 0, 'skipped_exist' => 0, 'skipped_special' => 0, 'failed_match' => 0, 'skipped_same_dk' => 0];

        // Ambil Akun Kas Utama untuk pengecualian
        $akunKasUtama = $akunModel->find($idAkunKasUtama);
        if (!$akunKasUtama) {
            log_message('error', "[generateOtomatis] Akun Kas Utama ID {$idAkunKasUtama} tidak ditemukan.");
            return $stats; // Tidak bisa lanjut tanpa akun kas utama
        }
        $namaAkunKasUtama = trim($akunKasUtama['nama_akun']);

        // 1. Ambil semua akun, siapkan lookup berdasarkan nama
        $semuaAkun = $akunModel->findAll();
        $akunLookupByName = [];
        foreach ($semuaAkun as $akun) {
            // Gunakan nama akun asli sebagai key (case sensitive mungkin lebih aman untuk direct match)
            $akunLookupByName[trim($akun['nama_akun'])] = $akun;
        }

        // 2. Ambil kombinasi unik uraian dan kategori dari jurnal kas
        $uniqueJournalEntries = $db->table('jurnal_kas')
            ->select('uraian, kategori')
            ->distinct()
            ->get()
            ->getResultArray();

        $dataToInsert = [];

        // 3. Loop melalui entri jurnal unik
        foreach ($uniqueJournalEntries as $entry) {
            $uraian = trim($entry['uraian']);
            $kategori = $entry['kategori'];

            // 4. Abaikan kasus khusus
            if (stripos($uraian, 'Penyusutan') !== false || stripos($uraian, 'Akumulasi') !== false) {
                log_message('info', "[generateOtomatis] Skipping special case (Penyusutan/Akumulasi): '{$uraian}'");
                $stats['skipped_special']++;
                continue;
            }
            if (strcasecmp($uraian, $namaAkunKasUtama) == 0) { // Case-insensitive compare
                log_message('info', "[generateOtomatis] Skipping special case (Uraian = Akun Kas Utama): '{$uraian}'");
                $stats['skipped_special']++;
                continue;
            }

            // 5. Cari akun yang cocok berdasarkan nama persis
            if (isset($akunLookupByName[$uraian])) {
                $akunCocok = $akunLookupByName[$uraian];
                $idAkunCocok = $akunCocok['id'];

                // 6. Tentukan Akun Debit dan Kredit
                $idAkunDebit = null;
                $idAkunKredit = null;

                if ($kategori == 'DUM') {
                    $idAkunDebit = $idAkunKasUtama;
                    $idAkunKredit = $idAkunCocok;
                } elseif ($kategori == 'DUK') {
                    $idAkunDebit = $idAkunCocok;
                    $idAkunKredit = $idAkunKasUtama;
                } else {
                    // Seharusnya tidak terjadi jika data jurnal valid
                    log_message('warning', "[generateOtomatis] Kategori tidak dikenal '{$kategori}' untuk uraian '{$uraian}'. Skipping.");
                    $stats['failed_match']++; // Atau kategori lain?
                    continue;
                }

                // Jangan buat aturan jika D/K sama
                if ($idAkunDebit == $idAkunKredit) {
                    log_message('warning', "[generateOtomatis] Skipping same D/K account for uraian '{$uraian}'. Check logic or data.");
                    $stats['skipped_same_dk']++;
                    continue;
                }

                // 7. Buat pola uraian (tambahkan % di akhir)
                // Escape karakter khusus SQL LIKE (% dan _) dalam uraian sebelum menambahkan wildcard %
                $escapedUraian = str_replace(['%', '_'], ['\%', '\_'], $uraian);
                $polaUraian = $escapedUraian . '%';


                // 8. Cek apakah aturan sudah ada
                $existingRule = $this->where('pola_uraian', $polaUraian)
                    ->where('kategori_jurnal', $kategori)
                    ->first();

                if (!$existingRule) {
                    // 9. Jika belum ada, siapkan data untuk insert
                    $dataToInsert[] = [
                        'pola_uraian' => $polaUraian,
                        'kategori_jurnal' => $kategori,
                        'id_akun_debit' => $idAkunDebit,
                        'id_akun_kredit' => $idAkunKredit,
                        'prioritas' => 0, // Default prioritas
                        'deskripsi' => 'Dibuat otomatis pada ' . date('Y-m-d H:i:s')
                    ];
                    log_message('debug', "[generateOtomatis] Preparing rule for '{$uraian}' (Kat:{$kategori}): D={$idAkunDebit}, K={$idAkunKredit}, Pola='{$polaUraian}'");

                } else {
                    log_message('info', "[generateOtomatis] Skipping existing rule for Pola='{$polaUraian}', Kat='{$kategori}'");
                    $stats['skipped_exist']++;
                }

            } else {
                // Uraian tidak cocok dengan nama akun manapun
                log_message('info', "[generateOtomatis] No direct account name match found for uraian: '{$uraian}'. Manual mapping needed.");
                $stats['failed_match']++;
            }
        } // End loop unique journal entries

        // 10. Insert batch jika ada data baru
        if (!empty($dataToInsert)) {
            if ($this->insertBatch($dataToInsert)) {
                $stats['created'] = count($dataToInsert);
                log_message('info', "[generateOtomatis] Successfully inserted {$stats['created']} new mapping rules.");
            } else {
                log_message('error', "[generateOtomatis] Failed to insertBatch mapping rules: " . json_encode($this->errors()));
                // Statistik created tetap 0
            }
        }

        return $stats;
    }
}
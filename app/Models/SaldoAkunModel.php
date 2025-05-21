<?php

namespace App\Models;

use CodeIgniter\Model;

class SaldoAkunModel extends Model
{
    protected $table = 'saldo_akun';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['id_akun', 'bulan', 'tahun', 'saldo_awal', 'total_debit', 'total_kredit', 'saldo_akhir'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Mengambil saldo ringkasan per akun untuk bulan dan tahun tertentu.
     */
    public function getSaldoByBulanTahun($bulan, $tahun)
    {
        return $this->select('saldo_akun.*, akun.kode_akun, akun.nama_akun, akun.kategori, akun.jenis')
            ->join('akun', 'akun.id = saldo_akun.id_akun')
            ->where('saldo_akun.bulan', $bulan)
            ->where('saldo_akun.tahun', $tahun)
            ->orderBy('akun.kode_akun', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil saldo ringkasan untuk satu akun pada bulan dan tahun tertentu.
     */
    public function getSaldoByAkunBulanTahun($idAkun, $bulan, $tahun)
    {
        return $this->where('id_akun', $idAkun)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();
    }

    /**
     * Mengambil data untuk Neraca Saldo standar (Debit/Kredit).
     */
    public function getNeracaSaldo($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT
                a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo_akhir_bulan,
                CASE
                    WHEN a.jenis = 'Debit' THEN GREATEST(COALESCE(sa.saldo_akhir, a.saldo_awal), 0)
                    ELSE CASE WHEN COALESCE(sa.saldo_akhir, a.saldo_awal) < 0 THEN ABS(COALESCE(sa.saldo_akhir, a.saldo_awal)) ELSE 0 END
                END as debit,
                CASE
                    WHEN a.jenis = 'Kredit' THEN GREATEST(COALESCE(sa.saldo_akhir, a.saldo_awal), 0)
                    ELSE CASE WHEN COALESCE(sa.saldo_akhir, a.saldo_awal) < 0 THEN ABS(COALESCE(sa.saldo_akhir, a.saldo_awal)) ELSE 0 END
                END as kredit
            FROM akun a
            LEFT JOIN saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            ORDER BY a.kode_akun ASC
        ", [$bulan, $tahun]);
        return $query->getResultArray();
    }

    /**
     * Mengambil data akun yang relevan untuk Laporan Laba Rugi.
     * Menggunakan kategori aktual dari COA Anda.
     */
    public function getLaporanLabaRugi($bulan, $tahun)
    {
        $db = \Config\Database::connect();

        // Kategori disesuaikan dengan yang ada di tabel 'akun' Anda
        // dan dengan asumsi Anda sudah melakukan penyesuaian di tabel 'akun'
        // untuk akun-akun biaya (yang tadinya 'LAIN-LAIN' menjadi 'BEBAN')
        // dan Anda sudah punya akun BEBAN PENYUSUTAN.
        $kategoriPendapatan = ['PENDAPATAN']; // Sesuai tabel 'akun' Anda
        $kategoriBeban = [
            'BEBAN',             // Untuk beban operasional umum (termasuk yang diubah dari 'LAIN-LAIN')
            'BEBAN PENYUSUTAN',  // Jika Anda membuat kategori spesifik ini untuk akun beban penyusutan
            // 'BEBAN PAJAK',    // Jika Anda punya akun beban pajak dengan kategori ini
        ];
        // Jika akun beban penyusutan Anda juga menggunakan kategori 'BEBAN',
        // maka cukup 'BEBAN' saja di atas. Jika berbeda, tambahkan.

        $kategoriLabaRugi = array_merge($kategoriPendapatan, $kategoriBeban);

        if (empty($kategoriLabaRugi)) {
            log_message('warning', '[SaldoAkunModel::getLaporanLabaRugi] Tidak ada kategori L/R yang didefinisikan.');
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($kategoriLabaRugi), '?'));
        $bindings = array_merge([$bulan, $tahun], $kategoriLabaRugi);

        $sql = "SELECT
                    a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                    COALESCE(sa.total_debit, 0) as total_debit_periode,
                    COALESCE(sa.total_kredit, 0) as total_kredit_periode
                FROM akun a
                LEFT JOIN saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
                WHERE a.kategori IN ($placeholders)
                ORDER BY CASE a.kategori ";

        $caseOrder = "";
        $orderIndex = 1;
        // Urutkan pendapatan dulu, baru beban
        $tempKategoriPendapatan = array_intersect($kategoriLabaRugi, $kategoriPendapatan);
        $tempKategoriBeban = array_intersect($kategoriLabaRugi, $kategoriBeban);

        foreach ($tempKategoriPendapatan as $kat) {
            $caseOrder .= " WHEN " . $db->escape($kat) . " THEN " . $orderIndex++;
        }
        foreach ($tempKategoriBeban as $kat) {
            $caseOrder .= " WHEN " . $db->escape($kat) . " THEN " . $orderIndex++;
        }
        // Jika ada kategori lain yang tidak masuk $kategoriPendapatan atau $kategoriBeban tapi ada di $kategoriLabaRugi
        $remainingCategories = array_diff($kategoriLabaRugi, $tempKategoriPendapatan, $tempKategoriBeban);
        foreach ($remainingCategories as $kat) {
            $caseOrder .= " WHEN " . $db->escape($kat) . " THEN " . $orderIndex++;
        }

        $sql .= $caseOrder . " ELSE 99 END, a.kode_akun ASC";

        log_message('debug', '[SaldoAkunModel::getLaporanLabaRugi] SQL Query: ' . $sql);
        log_message('debug', '[SaldoAkunModel::getLaporanLabaRugi] Bindings: ' . json_encode($bindings));

        $query = $db->query($sql, $bindings);
        $results = $query->getResultArray();

        if (empty($results)) {
            log_message('info', '[SaldoAkunModel::getLaporanLabaRugi] Tidak ada data akun L/R ditemukan untuk periode ' . $bulan . '/' . $tahun . ' dengan kategori: ' . implode(', ', $kategoriLabaRugi));
        } else {
            log_message('info', '[SaldoAkunModel::getLaporanLabaRugi] Ditemukan ' . count($results) . ' akun L/R untuk periode ' . $bulan . '/' . $tahun . '. Data mentah: ' . json_encode($results));
        }

        $processedResults = [];
        foreach ($results as $row) {
            $saldoPeriode = 0;
            if (strtoupper($row['jenis']) == 'KREDIT') { // Akun Pendapatan
                $saldoPeriode = floatval($row['total_kredit_periode']) - floatval($row['total_debit_periode']);
            } elseif (strtoupper($row['jenis']) == 'DEBIT') { // Akun Beban
                $saldoPeriode = floatval($row['total_debit_periode']) - floatval($row['total_kredit_periode']);
            } else {
                log_message('warning', "[SaldoAkunModel::getLaporanLabaRugi] Akun ID {$row['id']} ('{$row['nama_akun']}') kategori '{$row['kategori']}' memiliki jenis akun '{$row['jenis']}' yang tidak standar untuk L/R. Saldo mungkin tidak akurat.");
            }
            $row['saldo'] = $saldoPeriode; // Ini adalah saldo mutasi periode
            $processedResults[] = $row;
        }
        return $processedResults;
    }

    /**
     * Mengambil data saldo komparatif untuk akun Neraca.
     *
     * @param array $listKodeAkunNeraca Daftar kode akun yang relevan.
     * @param int $bulan Bulan periode saat ini.
     * @param int $tahun Tahun periode saat ini.
     * @param int $prevBulan Bulan periode sebelumnya.
     * @param int $prevTahun Tahun periode sebelumnya.
     * @return array Hasil query [id, kode_akun, nama_akun, jenis, saldo_current, saldo_prev]
     */
    public function getNeracaComparativeData(array $listKodeAkunNeraca, int $bulan, int $tahun, int $prevBulan, int $prevTahun): array
    {
        if (empty($listKodeAkunNeraca)) {
            return [];
        }

        $db = \Config\Database::connect();
        // Buat string ('kode1', 'kode2', ...) untuk klausa IN
        $placeholdersKode = "'" . implode("','", array_map([$db, 'escapeString'], $listKodeAkunNeraca)) . "'";

        // Bindings untuk bulan dan tahun
        $bindings = [$bulan, $tahun, $prevBulan, $prevTahun];

        $sql = "
            SELECT
                a.id, a.kode_akun, a.nama_akun, a.jenis, a.kategori, -- Tambah kategori untuk debug
                COALESCE(sa_current.saldo_akhir, a.saldo_awal) as saldo_current,
                COALESCE(sa_prev.saldo_akhir, a.saldo_awal) as saldo_prev
            FROM akun a
            LEFT JOIN saldo_akun sa_current ON a.id = sa_current.id_akun AND sa_current.bulan = ? AND sa_current.tahun = ?
            LEFT JOIN saldo_akun sa_prev ON a.id = sa_prev.id_akun AND sa_prev.bulan = ? AND sa_prev.tahun = ?
            WHERE a.kode_akun IN ($placeholdersKode)
        "; // Menggunakan placeholdersKode langsung karena sudah di-escape

        log_message('debug', '[SaldoAkunModel::getNeracaComparativeData] SQL: ' . $sql);
        log_message('debug', '[SaldoAkunModel::getNeracaComparativeData] Bindings (bulan/tahun): ' . json_encode($bindings));
        log_message('debug', '[SaldoAkunModel::getNeracaComparativeData] Kode Akun IN Clause: ' . $placeholdersKode);


        $query = $db->query($sql, $bindings);
        $result = $query->getResultArray();
        log_message('debug', '[SaldoAkunModel::getNeracaComparativeData] Result Count: ' . count($result));
        return $result;
    }

} // End Class
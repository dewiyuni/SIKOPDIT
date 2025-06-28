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
        // --- PERBAIKAN UTAMA DI SINI ---
        // Definisikan semua kategori yang relevan untuk Laba Rugi berdasarkan tabel 'akun' Anda.
        $kategoriLabaRugi = [
            'PENDAPATAN',
            'BEBAN',
            'BEBAN PENYUSUTAN', // Tetap sertakan untuk jaga-jaga jika ada
            'LAIN-LAIN'         // Ini yang paling penting untuk disertakan
        ];

        // Buat placeholder '?' sejumlah kategori untuk query IN (...)
        $placeholders = implode(',', array_fill(0, count($kategoriLabaRugi), '?'));

        // Gabungkan parameter untuk query
        $bindings = array_merge([$bulan, $tahun], $kategoriLabaRugi);

        // Query yang lebih sederhana dan aman
        $sql = "SELECT
                    a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                    COALESCE(sa.total_debit, 0) as total_debit_periode,
                    COALESCE(sa.total_kredit, 0) as total_kredit_periode
                FROM akun a
                LEFT JOIN saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
                WHERE a.kategori IN ($placeholders)
                ORDER BY 
                    CASE 
                        WHEN a.kategori = 'PENDAPATAN' THEN 1
                        WHEN a.kategori = 'BEBAN' THEN 2
                        WHEN a.kategori = 'LAIN-LAIN' THEN 3 -- Urutkan setelah BEBAN
                        WHEN a.kategori = 'BEBAN PENYUSUTAN' THEN 4
                        ELSE 99 
                    END, 
                    a.kode_akun ASC";

        $db = \Config\Database::connect();
        $query = $db->query($sql, $bindings);
        $results = $query->getResultArray();

        // Jika tidak ada hasil, kembalikan array kosong
        if (empty($results)) {
            return [];
        }

        // --- Proses hasil untuk menghitung 'saldo' ---
        // Logika ini sudah ada dan sudah benar, kita pertahankan.
        $processedResults = [];
        foreach ($results as $row) {
            $saldoPeriode = 0;
            if (strtoupper($row['jenis']) == 'KREDIT') { // Akun Pendapatan
                $saldoPeriode = floatval($row['total_kredit_periode']) - floatval($row['total_debit_periode']);
            } elseif (strtoupper($row['jenis']) == 'DEBIT') { // Akun Beban
                $saldoPeriode = floatval($row['total_debit_periode']) - floatval($row['total_kredit_periode']);
            }

            // Tambahkan key 'saldo' ke setiap baris data
            $row['saldo'] = $saldoPeriode;
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
    public function getSaldoByAkunAndPeriode($idAkun, $startDate, $endDate)
    {
        return $this->db->table('buku_besar')
            ->select('SUM(debit) AS debit, SUM(kredit) AS kredit')
            ->where('id_akun', $idAkun)
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->get()
            ->getRow(); // Hasil berupa objek: ->debit, ->kredit
    }
    public function getSaldoAkhir($idAkun, $endDate, $jenis = 'AKTIVA')
    {
        $tahun = date('Y', strtotime($endDate));
        $bulan = date('n', strtotime($endDate));
        $startOfMonth = date('Y-m-01', strtotime($endDate));

        $saldoAwal = $this->db->table('saldo_akun')
            ->select('saldo_awal')
            ->where('id_akun', $idAkun)
            ->where('tahun', $tahun)
            ->get()
            ->getRow();

        if (!$saldoAwal) {
            $saldoAwal = $this->db->table('saldo_akun')
                ->select('saldo_awal')
                ->where('id_akun', $idAkun)
                ->orderBy('tahun', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();
        }

        // Tentukan rumus sesuai jenis akun
        $mutasiSelect = ($jenis === 'AKTIVA') ? 'SUM(debit - kredit) as saldo' : 'SUM(kredit - debit) as saldo';

        // Kalau bulan Januari
        if ((int) $bulan === 1) {
            $mutasi = $this->db->table('buku_besar')
                ->select($mutasiSelect)
                ->where('id_akun', $idAkun)
                ->where('tanggal >=', $startOfMonth)
                ->where('tanggal <=', $endDate)
                ->get()
                ->getRow();

            return (object) [
                'saldo' => ($saldoAwal->saldo_awal ?? 0) + ($mutasi->saldo ?? 0)
            ];
        }

        // Selain Januari: hitung dari saldo bulan lalu
        $prevEndDate = date('Y-m-t', strtotime("-1 month", strtotime($startOfMonth)));
        $saldoBulanLalu = $this->getSaldoAkhir($idAkun, $prevEndDate, $jenis);

        $mutasi = $this->db->table('buku_besar')
            ->select($mutasiSelect)
            ->where('id_akun', $idAkun)
            ->where('tanggal >=', $startOfMonth)
            ->where('tanggal <=', $endDate)
            ->get()
            ->getRow();

        return (object) [
            'saldo' => ($saldoBulanLalu->saldo ?? 0) + ($mutasi->saldo ?? 0)
        ];
    }


    public function getSaldoDenganSaldoAwal($idAkun, $startDate, $endDate)
    {
        // Ambil saldo awal dari tabel khusus
        $saldoAwal = $this->db->table('saldo_akun')
            ->select('saldo_awal')
            ->where('id_akun', $idAkun)
            ->get()
            ->getRow();

        // Transaksi dari buku besar
        $transaksi = $this->db->table('buku_besar')
            ->select('SUM(debit - kredit) as saldo')
            ->where('id_akun', $idAkun)
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->get()
            ->getRow();
        // Hitung saldo akhir

        $totalSaldo = ($saldoAwal->saldo ?? 0) + ($transaksi->saldo ?? 0);
        return (object) ['saldo' => $totalSaldo];
    }

} // End Class
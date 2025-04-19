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
        $kategoriPendapatan = ['PEMASUKAN']; // Sesuaikan jika ada kategori pendapatan lain
        $kategoriBeban = [
            'BIAYA BIAYA',
            'BIAYA PAJAK',
            'PENYISIHAN BEBAN DANA',
            'PENYUSUTAN PENYUSUTAN'
        ];
        $kategoriLabaRugi = array_merge($kategoriPendapatan, $kategoriBeban);

        if (empty($kategoriLabaRugi))
            return [];

        $placeholders = implode(',', array_fill(0, count($kategoriLabaRugi), '?'));
        $bindings = array_merge([$bulan, $tahun], $kategoriLabaRugi);

        $sql = "SELECT a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                       COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo
                FROM akun a
                LEFT JOIN saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
                WHERE a.kategori IN ($placeholders)
                ORDER BY CASE a.kategori ";
        $caseOrder = "";
        $orderIndex = 1;
        foreach ($kategoriPendapatan as $kat) {
            $caseOrder .= " WHEN " . $db->escape($kat) . " THEN $orderIndex";
            $orderIndex++;
        }
        foreach ($kategoriBeban as $kat) {
            $caseOrder .= " WHEN " . $db->escape($kat) . " THEN $orderIndex";
            $orderIndex++;
        }
        $sql .= $caseOrder . " ELSE 99 END, a.kode_akun ASC";

        $query = $db->query($sql, $bindings);
        return $query->getResultArray();
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
            return []; // Tidak ada akun untuk dicari
        }

        $db = \Config\Database::connect();
        $placeholders = implode(',', array_fill(0, count($listKodeAkunNeraca), '?'));
        $bindings = array_merge([$bulan, $tahun, $prevBulan, $prevTahun], $listKodeAkunNeraca);

        $sql = "
            SELECT
                a.id, a.kode_akun, a.nama_akun, a.jenis,
                COALESCE(sa_current.saldo_akhir, a.saldo_awal) as saldo_current,
                COALESCE(sa_prev.saldo_akhir, a.saldo_awal) as saldo_prev
            FROM akun a
            LEFT JOIN saldo_akun sa_current ON a.id = sa_current.id_akun AND sa_current.bulan = ? AND sa_current.tahun = ?
            LEFT JOIN saldo_akun sa_prev ON a.id = sa_prev.id_akun AND sa_prev.bulan = ? AND sa_prev.tahun = ?
            WHERE a.kode_akun IN ($placeholders)
        ";

        $query = $db->query($sql, $bindings);
        return $query->getResultArray();
    }

} // End Class
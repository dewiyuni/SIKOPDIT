<?php

namespace App\Models;

use CodeIgniter\Model;

class JurnalKasModel extends Model
{
    protected $table = 'jurnal_kas_harian';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;
    protected $allowedFields = ['tanggal', 'uraian', 'kategori', 'jumlah'];

    public function getAllKas()
    {
        return $this->db->table('jurnal_kas_harian')->get()->getResultArray();
    }
    public function getRekapBulanan()
    {
        $db = \Config\Database::connect();

        $query = $db->query(
            "SELECT kategori, uraian,
                SUM(CASE WHEN MONTH(tanggal) = 1 THEN jumlah ELSE 0 END) AS Januari,
                SUM(CASE WHEN MONTH(tanggal) = 2 THEN jumlah ELSE 0 END) AS Februari,
                SUM(CASE WHEN MONTH(tanggal) = 3 THEN jumlah ELSE 0 END) AS Maret,
                SUM(CASE WHEN MONTH(tanggal) = 4 THEN jumlah ELSE 0 END) AS April,
                SUM(CASE WHEN MONTH(tanggal) = 5 THEN jumlah ELSE 0 END) AS Mei,
                SUM(CASE WHEN MONTH(tanggal) = 6 THEN jumlah ELSE 0 END) AS Juni,
                SUM(CASE WHEN MONTH(tanggal) = 7 THEN jumlah ELSE 0 END) AS Juli,
                SUM(CASE WHEN MONTH(tanggal) = 8 THEN jumlah ELSE 0 END) AS Agustus,
                SUM(CASE WHEN MONTH(tanggal) = 9 THEN jumlah ELSE 0 END) AS September,
                SUM(CASE WHEN MONTH(tanggal) = 10 THEN jumlah ELSE 0 END) AS Oktober,
                SUM(CASE WHEN MONTH(tanggal) = 11 THEN jumlah ELSE 0 END) AS November,
                SUM(CASE WHEN MONTH(tanggal) = 12 THEN jumlah ELSE 0 END) AS Desember
            FROM jurnal_kas_harian
            GROUP BY kategori, uraian
            ORDER BY kategori, uraian"
        );

        return $query->getResultArray();
    }
    public function updateTotalHarian()
    {
        // Query untuk menghitung total DUM dan DUK per tanggal
        $sql = "UPDATE jurnal_kas jk
                JOIN (
                    SELECT tanggal, 
                           SUM(CASE WHEN kategori = 'DUM' THEN jumlah ELSE 0 END) AS total_dum,
                           SUM(CASE WHEN kategori = 'DUK' THEN jumlah ELSE 0 END) AS total_duk
                    FROM jurnal_kas
                    GROUP BY tanggal
                ) AS subquery
                ON jk.tanggal = subquery.tanggal
                SET jk.total_dum = subquery.total_dum,
                    jk.total_duk = subquery.total_duk";

        return $this->db->query($sql);
    }
    public function getSaldoAwal($bulan, $tahun)
    {
        // Ambil saldo akhir bulan sebelumnya sebagai saldo awal
        $query = $this->db->table('neraca')
            ->select('saldo_akhir')
            ->where('bulan', $bulan - 1)
            ->where('tahun', ($bulan == 1) ? $tahun - 1 : $tahun)
            ->get();

        return $query->getRow() ? $query->getRow()->saldo_akhir : 0;
    }

    public function getTotalKategori($bulan, $tahun, $kategori)
    {
        $query = $this->db->table('jurnal_kas')
            ->selectSum('jumlah')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('kategori', $kategori)
            ->get();

        return $query->getRow() ? $query->getRow()->jumlah : 0;
    }
}

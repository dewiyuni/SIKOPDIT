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

    public function getSaldoByBulanTahun($bulan, $tahun)
    {
        return $this->select('saldo_akun.*, akun.kode_akun, akun.nama_akun, akun.kategori, akun.jenis')
            ->join('akun', 'akun.id = saldo_akun.id_akun')
            ->where('saldo_akun.bulan', $bulan)
            ->where('saldo_akun.tahun', $tahun)
            ->orderBy('akun.kode_akun', 'ASC')
            ->findAll();
    }

    public function getSaldoByAkunBulanTahun($idAkun, $bulan, $tahun)
    {
        return $this->where('id_akun', $idAkun)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();
    }

    public function getNeracaSaldo($bulan, $tahun)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo,
                CASE 
                    WHEN a.jenis = 'Debit' THEN 
                        CASE 
                            WHEN COALESCE(sa.saldo_akhir, a.saldo_awal) >= 0 THEN COALESCE(sa.saldo_akhir, a.saldo_awal)
                            ELSE 0
                        END
                    ELSE 
                        CASE 
                            WHEN COALESCE(sa.saldo_akhir, a.saldo_awal) < 0 THEN ABS(COALESCE(sa.saldo_akhir, a.saldo_awal))
                            ELSE 0
                        END
                END as debit,
                CASE 
                    WHEN a.jenis = 'Kredit' THEN 
                        CASE 
                            WHEN COALESCE(sa.saldo_akhir, a.saldo_awal) >= 0 THEN COALESCE(sa.saldo_akhir, a.saldo_awal)
                            ELSE 0
                        END
                    ELSE 
                        CASE 
                            WHEN COALESCE(sa.saldo_akhir, a.saldo_awal) < 0 THEN ABS(COALESCE(sa.saldo_akhir, a.saldo_awal))
                            ELSE 0
                        END
                END as kredit
            FROM 
                akun a
            LEFT JOIN 
                saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            ORDER BY 
                a.kode_akun ASC
        ", [$bulan, $tahun]);

        return $query->getResultArray();
    }

    public function getLaporanLabaRugi($bulan, $tahun)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo
            FROM 
                akun a
            LEFT JOIN 
                saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            WHERE 
                a.kategori IN ('Pendapatan', 'Beban')
            ORDER BY 
                a.kategori, a.kode_akun ASC
        ", [$bulan, $tahun]);

        return $query->getResultArray();
    }

    public function getNeraca($bulan, $tahun)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis,
                COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo
            FROM 
                akun a
            LEFT JOIN 
                saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            WHERE 
                a.kategori IN ('Aktiva', 'Pasiva', 'Modal')
            ORDER BY 
                a.kategori, a.kode_akun ASC
        ", [$bulan, $tahun]);

        return $query->getResultArray();
    }
}

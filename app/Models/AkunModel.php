<?php

namespace App\Models;

use CodeIgniter\Model;

class AkunModel extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['kode_akun', 'nama_akun', 'kategori', 'jenis', 'saldo_awal'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAkunByKode($kode)
    {
        return $this->where('kode_akun', $kode)->first();
    }

    public function getAkunByKategori($kategori)
    {
        return $this->where('kategori', $kategori)->orderBy('kode_akun', 'ASC')->findAll();
    }

    public function getAkunWithSaldo($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT 
                a.*, 
                COALESCE(sa.saldo_awal, a.saldo_awal) as saldo_bulan_ini,
                COALESCE(sa.total_debit, 0) as total_debit,
                COALESCE(sa.total_kredit, 0) as total_kredit,
                COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo_akhir
            FROM 
                akun a
            LEFT JOIN 
                saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            ORDER BY 
                a.kode_akun ASC
        ", [$bulan, $tahun]);

        return $query->getResultArray();
    }
    private function getLastSaldo($idAkun, $tanggal)
    {
        $db = \Config\Database::connect();

        // Cari saldo terakhir sebelum tanggal ini
        $query = $db->query("
        SELECT saldo 
        FROM buku_besar 
        WHERE id_akun = ? AND tanggal <= ? 
        ORDER BY tanggal DESC, id DESC 
        LIMIT 1
    ", [$idAkun, $tanggal]);

        $result = $query->getRow();

        if ($result) {
            log_message('debug', "Saldo terakhir ditemukan untuk akun {$idAkun}: {$result->saldo}");
            return $result->saldo;
        } else {
            // Jika tidak ada, ambil saldo awal
            $akunModel = new \App\Models\AkunModel();
            $akun = $akunModel->find($idAkun);
            $saldoAwal = $akun ? $akun['saldo_awal'] : 0;
            log_message('debug', "Menggunakan saldo awal untuk akun {$idAkun}: {$saldoAwal}");
            return $saldoAwal;
        }
    }

}
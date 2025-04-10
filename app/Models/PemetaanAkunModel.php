<?php

namespace App\Models;

use CodeIgniter\Model;

class PemetaanAkunModel extends Model
{
    protected $table = 'pemetaan_akun';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['kategori_jurnal', 'uraian_jurnal', 'id_akun_debit', 'id_akun_kredit'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getPemetaanWithAkun()
    {
        return $this->select('pemetaan_akun.*, akun_debit.kode_akun as kode_akun_debit, akun_debit.nama_akun as nama_akun_debit, akun_kredit.kode_akun as kode_akun_kredit, akun_kredit.nama_akun as nama_akun_kredit')
            ->join('akun as akun_debit', 'akun_debit.id = pemetaan_akun.id_akun_debit', 'left')
            ->join('akun as akun_kredit', 'akun_kredit.id = pemetaan_akun.id_akun_kredit', 'left')
            ->findAll();
    }

    public function getPemetaanByKategoriUraian($kategori, $uraian)
    {
        return $this->where('kategori_jurnal', $kategori)
            ->where('uraian_jurnal', $uraian)
            ->first();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class MappingAkunNeracaModel extends Model
{
    protected $table = 'mapping_akun_neraca';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_laporan', 'id_akun_utama', 'id_akun_pengurang', 'jenis', 'urutan'];

    public function getMappingWithKategori()
    {
        return $this->select('mapping_akun_neraca.*, akun.nama_akun, akun.kode_akun, akun.kategori')
            ->join('akun', 'akun.id = mapping_akun_neraca.id_akun_utama')
            ->orderBy('akun.kategori', 'ASC')
            ->orderBy('akun.kode_akun', 'ASC')
            ->findAll();
    }
}

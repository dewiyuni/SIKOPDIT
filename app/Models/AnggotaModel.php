<?php

namespace App\Models;

use CodeIgniter\Model;

class AnggotaModel extends Model
{
    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';

    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $allowedFields = ['no_ba', 'nama', 'nik', 'dusun', 'alamat', 'pekerjaan', 'tgl_lahir', 'nama_pasangan', 'status', 'created_at', 'updated_at'];

    public function getAnggotaWithTransaksi()
    {
        return $this->select('anggota.*, 
        COALESCE(SUM(transaksi_simpanan.setor_sw + transaksi_simpanan.setor_swp + transaksi_simpanan.setor_ss + transaksi_simpanan.setor_sp), 0) 
        - COALESCE(SUM(transaksi_simpanan.tarik_sw + transaksi_simpanan.tarik_swp + transaksi_simpanan.tarik_ss + transaksi_simpanan.tarik_sp), 0) 
        as saldo_total')
            ->join('transaksi_simpanan', 'transaksi_simpanan.id_anggota = anggota.id_anggota', 'left')
            ->groupBy('anggota.id_anggota')
            ->findAll();
    }

}

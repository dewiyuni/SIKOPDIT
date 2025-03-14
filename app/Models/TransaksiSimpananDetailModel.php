<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiSimpananDetailModel extends Model
{
    protected $table = 'transaksi_simpanan_detail';
    protected $primaryKey = 'id_detail';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['id_transaksi_simpanan', 'id_jenis_simpanan', 'setor', 'tarik', 'saldo_akhir', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getDetailWithJenisSimpanan()
    {
        return $this->select('transaksi_simpanan_detail.*, jenis_simpanan.nama_simpanan')
            ->join('jenis_simpanan', 'jenis_simpanan.id_jenis_simpanan = transaksi_simpanan_detail.id_jenis_simpanan')
            ->findAll();
    }
    public function getDetailTransaksiByAnggota($id_anggota)
    {
        return $this->db->table('transaksi_simpanan_detail td')
            ->select('td.*, ts.tanggal, ts.id_anggota, 
                      a.nama, a.no_ba, 
                      js.nama_simpanan')
            ->join('transaksi_simpanan ts', 'ts.id_transaksi_simpanan = td.id_transaksi_simpanan', 'left')
            ->join('anggota a', 'a.id_anggota = ts.id_anggota', 'left')
            ->join('jenis_simpanan js', 'js.id_jenis_simpanan = td.id_jenis_simpanan', 'left')
            ->where('ts.id_anggota', $id_anggota)
            ->orderBy('ts.tanggal', 'DESC')
            ->get()->getResult();
    }

}

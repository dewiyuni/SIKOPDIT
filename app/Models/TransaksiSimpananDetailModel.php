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
    public function getSaldoDariDetail()
    {
        return $this->select("
        anggota.id_anggota,
        anggota.nama AS nama_anggota,
        anggota.no_ba,
        COALESCE(SUM(CASE WHEN jenis_simpanan.nama_simpanan = 'SW' THEN transaksi_simpanan_detail.setor - transaksi_simpanan_detail.tarik ELSE 0 END), 0) AS saldo_sw,
        COALESCE(SUM(CASE WHEN jenis_simpanan.nama_simpanan = 'SWP' THEN transaksi_simpanan_detail.setor - transaksi_simpanan_detail.tarik ELSE 0 END), 0) AS saldo_swp,
        COALESCE(SUM(CASE WHEN jenis_simpanan.nama_simpanan = 'SS' THEN transaksi_simpanan_detail.setor - transaksi_simpanan_detail.tarik ELSE 0 END), 0) AS saldo_ss,
        COALESCE(SUM(CASE WHEN jenis_simpanan.nama_simpanan = 'SP' THEN transaksi_simpanan_detail.setor - transaksi_simpanan_detail.tarik ELSE 0 END), 0) AS saldo_sp,
        COALESCE(SUM(transaksi_simpanan_detail.setor - transaksi_simpanan_detail.tarik), 0) AS saldo_total,
        MAX(transaksi_simpanan.tanggal) AS tanggal_terakhir
    ")
            ->join('transaksi_simpanan', 'transaksi_simpanan.id_transaksi_simpanan = transaksi_simpanan_detail.id_transaksi_simpanan', 'left')
            ->join('anggota', 'anggota.id_anggota = transaksi_simpanan.id_anggota', 'left')
            ->join('jenis_simpanan', 'jenis_simpanan.id_jenis_simpanan = transaksi_simpanan_detail.id_jenis_simpanan', 'left')
            ->groupBy('anggota.id_anggota')
            ->findAll();
    }
    public function getAngsuranByPinjaman($id)
    {
        return $this->db->table('angsuran')
            ->where('id_pinjaman', $id)
            ->orderBy('tanggal_angsuran', 'ASC') // Urutkan berdasarkan tanggal angsuran
            ->get()
            ->getResult();
    }

}

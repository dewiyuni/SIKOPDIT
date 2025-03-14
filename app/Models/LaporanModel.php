<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getLaporanTransaksi()
    {
        $query = "
            SELECT 
                'Simpanan' AS jenis_transaksi, 
                ts.id_transaksi_simpanan AS id_transaksi, 
                a.nama AS nama_anggota, 
                ts.tanggal AS tanggal_transaksi, 
                ts.saldo_total AS jumlah, 
                ts.keterangan
            FROM transaksi_simpanan ts
            JOIN anggota a ON ts.id_anggota = a.id_anggota
            
            UNION 
            
            SELECT 
                'Pinjaman' AS jenis_transaksi, 
                tp.id_pinjaman AS id_transaksi, 
                a.nama AS nama_anggota, 
                tp.tanggal_pinjaman AS tanggal_transaksi, 
                tp.jumlah_pinjaman AS jumlah, 
                tp.status AS keterangan
            FROM transaksi_pinjaman tp
            JOIN anggota a ON tp.id_anggota = a.id_anggota
            
            ORDER BY tanggal_transaksi DESC
        ";

        return $this->db->query($query)->getResult();
    }
}

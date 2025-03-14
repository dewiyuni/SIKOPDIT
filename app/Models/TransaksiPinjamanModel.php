<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiPinjamanModel extends Model
{
    protected $table = 'transaksi_pinjaman';
    protected $primaryKey = 'id_pinjaman';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['id_anggota', 'tanggal_pinjaman', 'jumlah_pinjaman', 'jangka_waktu', 'bunga', 'jaminan', 'status', 'created_at', 'updated_at'];

    public function getDataPinjaman()
    {
        $query = $this->db->query("
        SELECT 
            p.id_pinjaman, 
            a.nama, 
            a.no_ba, 
            p.tanggal_pinjaman, 
            p.jangka_waktu, 
            p.jumlah_pinjaman, 
            p.jaminan,
            COALESCE(ang.saldo_terakhir, p.jumlah_pinjaman) AS saldo_terakhir,
            COALESCE(ang.angsuran_terakhir, 0) AS angsuran_terakhir
        FROM transaksi_pinjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        LEFT JOIN (
            SELECT ang1.id_pinjaman, 
                   ang1.sisa_pinjaman AS saldo_terakhir, 
                   ang1.jumlah_angsuran AS angsuran_terakhir
            FROM angsuran ang1
            JOIN (
                SELECT id_pinjaman, MAX(tanggal_angsuran) AS max_tanggal
                FROM angsuran
                GROUP BY id_pinjaman
            ) ang2 
            ON ang1.id_pinjaman = ang2.id_pinjaman 
            AND ang1.tanggal_angsuran = ang2.max_tanggal
        ) ang 
        ON p.id_pinjaman = ang.id_pinjaman
        ORDER BY p.tanggal_pinjaman DESC
    ");
        return $query->getResult();
    }
    public function getDataById($id)
    {
        return $this->db->table('transaksi_pinjaman p')
            ->select('p.*, a.nama, a.no_ba, 
            COALESCE((SELECT sisa_pinjaman FROM angsuran WHERE id_pinjaman = p.id_pinjaman ORDER BY tanggal_angsuran DESC LIMIT 1), p.jumlah_pinjaman) AS saldo_terakhir,
            (SELECT jumlah_angsuran FROM angsuran WHERE id_pinjaman = p.id_pinjaman ORDER BY tanggal_angsuran DESC LIMIT 1) AS angsuran_terakhir')
            ->join('anggota a', 'p.id_anggota = a.id_anggota')
            ->where('p.id_pinjaman', $id)
            ->get()
            ->getRow();
    }

    public function getAngsuranByPinjaman($id)
    {
        return $this->db->table('angsuran')
            ->select("id_angsuran, id_pinjaman, tanggal_angsuran, jumlah_angsuran, sisa_pinjaman, status, DATE_FORMAT(tanggal_angsuran, '%d %M %Y') AS tanggal_format")
            ->where('id_pinjaman', $id)
            ->orderBy('tanggal_angsuran', 'DESC')
            ->get()
            ->getResult();
    }

    public function getAllPinjaman()
    {
        return $this->db->table('transaksi_pinjaman p')
            ->select('p.id_pinjaman, a.nama, a.no_ba, p.tanggal_pinjaman, p.jangka_waktu, p.jumlah_pinjaman, p.jaminan, 
              COALESCE((SELECT sisa_pinjaman FROM angsuran WHERE id_pinjaman = p.id_pinjaman ORDER BY tanggal_angsuran DESC LIMIT 1), p.jumlah_pinjaman) AS saldo_terakhir')
            ->join('anggota a', 'p.id_anggota = a.id_anggota')
            ->orderBy('p.tanggal_pinjaman', 'DESC')
            ->get()
            ->getResult();
    }
    public function getTotalPinjaman()
    {
        return $this->selectSum('jumlah_pinjaman')->get()->getRow()->jumlah_pinjaman ?? 0;
    }

    public function validatePinjaman($idPinjaman)
    {
        return $this->where('id_pinjaman', $idPinjaman)->countAllResults() > 0;
    }

}

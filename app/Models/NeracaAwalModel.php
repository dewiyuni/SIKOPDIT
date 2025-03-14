<?php

namespace App\Models;

use CodeIgniter\Model;

class NeracaAwalModel extends Model
{
    protected $table = 'neraca_awal';
    protected $primaryKey = 'id_neraca_awal';
    protected $allowedFields = ['bulan', 'tahun', 'created_at', 'updated_at'];
    public function getSaldoBulanLalu($bulan, $tahun)
    {
        if ($bulan == 1) {
            $bulan = 12;
            $tahun -= 1;
        } else {
            $bulan -= 1;
        }

        $query = $this->db->table('detail_neraca')
            ->select('detail_neraca.id_kategori, detail_neraca.uraian, detail_neraca.nilai')
            ->join('neraca_awal', 'neraca_awal.id_neraca_awal = detail_neraca.id_neraca_awal')
            ->where('neraca_awal.bulan', $bulan)
            ->where('neraca_awal.tahun', $tahun)
            ->get()
            ->getResultArray();

        $saldoBulanLalu = [];
        foreach ($query as $row) {
            $saldoBulanLalu[$row['id_kategori']][$row['uraian']] = $row['nilai']; // Menyimpan berdasarkan kategori
        }

        return $saldoBulanLalu;
    }

}

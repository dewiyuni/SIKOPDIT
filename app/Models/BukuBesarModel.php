<?php

namespace App\Models;

use CodeIgniter\Model;

class BukuBesarModel extends Model
{
    protected $table = 'buku_besar';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal', 'akun', 'debit', 'kredit', 'saldo', 'created_at'];

    public function getBukuBesar($tahun, $bulan)
    {
        $builder = $this->where("YEAR(tanggal)", $tahun);
        if ($bulan) {
            $builder->where("MONTH(tanggal)", $bulan);
        }
        return $builder->orderBy('tanggal', 'ASC')->findAll();
    }


}

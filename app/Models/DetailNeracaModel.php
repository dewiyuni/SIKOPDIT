<?php

namespace App\Models;

use CodeIgniter\Model;
class DetailNeracaModel extends Model
{
    protected $table = 'detail_neraca';
    protected $primaryKey = 'id_detail';
    protected $allowedFields = [
        'id_neraca_awal',
        'id_kategori',
        'uraian',
        'nilai',
        'created_at',
        'updated_at'
    ];
    public function getDetailByNeraca($id_neraca)
    {
        return $this->where('id_neraca_awal', $id_neraca)->findAll();
    }
}
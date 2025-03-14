<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriNeracaModel extends Model
{
    protected $table = 'kategori_neraca';
    protected $primaryKey = 'id_kategori';
    protected $allowedFields = ['nama_kategori', 'created_at', 'updated_at'];
}

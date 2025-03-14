<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisSimpananModel extends Model
{
    protected $table = 'jenis_simpanan';
    protected $primaryKey = 'id_jenis_simpanan';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['nama_simpanan', 'created_at', 'updated_at'];
    protected $useTimestamps = true;


}

<?php

namespace App\Models;

use CodeIgniter\Model;

class AkunKeuanganModel extends Model
{
    protected $table = 'akun_keuangan';
    protected $primaryKey = 'id_akun';
    protected $allowedFields = ['kode_akun', 'nama_akun', 'jenis', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class KeuanganModel extends Model
{
    protected $table = 'keuangan_koperasi';
    protected $primaryKey = 'id_keuangan';
    protected $returnType = 'object';

    protected $allowedFields = ['id_anggota', 'keterangan', 'jumlah', 'jenis', 'tanggal'];
}

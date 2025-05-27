<?php

namespace App\Models;

use CodeIgniter\Model;

class NeracaDataModel extends Model
{
    protected $table = 'tbl_neraca_data'; // Your table name
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array'; // Can be 'object' or your custom Entity class
    protected $useSoftDeletes = false; // Set to true if you add a 'deleted_at' column for soft deletes

    // Fields that are allowed to be saved by create() or update()
    protected $allowedFields = [
        'periode_tahun',
        'periode_bulan',
        'kode_akun_internal',
        'nomor_display_main',
        'nomor_display_sub',
        'uraian_akun',
        'kategori_utama',
        'grup_laporan', // Renamed from grup_akun in my previous suggestion for clarity
        'sub_grup_akun',
        'is_header_grup',
        'is_sub_grup_header',
        'is_item_utama', // Changed from is_item_detail
        'is_akumulasi',
        'is_nilai_buku_line',
        'is_sub_total_grup', // Renamed from is_sub_total_line
        'is_grand_total_line',
        'nilai',
        'urutan_display',
        'is_editable',
        'parent_kode_akun_internal'
        // 'created_at' and 'updated_at' will be handled by the database or useTimestamps
    ];

    // Dates
    protected $useTimestamps = true; // Set to true if you want CI Model to handle created_at and updated_at
    protected $dateFormat = 'datetime'; // Or 'int' or 'date'
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // For soft deletes

    // Validation (optional, but highly recommended)
    protected $validationRules = [
        // 'periode_tahun' => 'required|integer|exact_length[4]',
        // 'periode_bulan' => 'required|integer|less_than_equal_to[12]|greater_than_equal_to[1]',
        // 'kode_akun_internal' => 'required|alpha_numeric_punct|max_length[50]',
        // 'uraian_akun' => 'required|string|max_length[255]',
        // 'grup_laporan' => 'required|string|max_length[100]',
        // 'nilai' => 'permit_empty|decimal', // Or 'numeric' if you handle formatting before saving
        // 'urutan_display' => 'required|integer',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks (optional)
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
}
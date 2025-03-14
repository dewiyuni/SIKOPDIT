<?php
namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['nama', 'email', 'password', 'role', 'status', 'deleted_at', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id_user' => 'required|integer',  // Tambahkan aturan validasi untuk id_user
        'nama' => 'required|min_length[3]',
        'email' => 'required|valid_email|is_unique[users.email,id_user,{id_user}]',
        'role' => 'required|in_list[admin,karyawan]',
        'status' => 'required|in_list[aktif,nonaktif]',
    ];


    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }
}

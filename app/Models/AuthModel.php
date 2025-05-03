<?php
namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $useAutoIncrement = true;
    protected $returnType = 'object'; // Mengembalikan hasil sebagai objek
    protected $useSoftDeletes = true; // Menggunakan soft delete
    protected $protectFields = true;
    // Tambahkan 'status' jika belum ada di allowedFields
    protected $allowedFields = ['nama', 'email', 'password', 'role', 'status'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at'; // Field untuk soft delete

    // Aturan validasi untuk insert/update data
    // ID_user dihapus dari sini karena bukan field yang di-post
    // Password validation akan dihandle di controller untuk update
    protected $validationRules = [
        'nama' => 'required|min_length[3]',
        // is_unique[tabel.kolom,kolom_id,nilai_id] digunakan di controller saat validasi update
        'email' => 'required|valid_email',
        'role' => 'required|in_list[admin,karyawan]',
        'status' => 'required|in_list[aktif,nonaktif]',
    ];

    // Anda bisa tambahkan pesan validasi kustom di sini jika perlu
    // protected $validationMessages = [...];

    // Method untuk mencari user berdasarkan email
    public function getUserByEmail($email)
    {
        // Menggunakan first() karena email seharusnya unique
        // Termasuk yang soft-deleted jika perlu (tambahkan withDeleted())
        return $this->where('email', $email)->first();
    }

    // Jika butuh method get total simpanan/pinjaman di model masing-masing:
    // Di TransaksiSimpananModel.php:
    // public function getTotalSimpanan() {
    //     return $this->selectSum('saldo_total')->first()->saldo_total ?? 0;
    // }
    // Di TransaksiPinjamanModel.php:
    // public function getTotalPinjaman() {
    //     return $this->selectSum('jumlah_pinjaman')->first()->jumlah_pinjaman ?? 0;
    // }
}
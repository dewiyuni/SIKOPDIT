<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama' => 'Admin Koperasi',
                'email' => 'admin@koperasi.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Karyawan Koperasi',
                'email' => 'karyawan@koperasi.com',
                'password' => password_hash('karyawan123', PASSWORD_DEFAULT),
                'role' => 'karyawan',
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data ke database
        $this->db->table('users')->insertBatch($data);
    }
}

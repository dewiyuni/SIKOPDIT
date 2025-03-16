<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JenisSimpananSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama_simpanan' => 'Simpanan Wajib',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_simpanan' => 'Simpanan Wajib Penyertaan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_simpanan' => 'Simpanan Sukarela',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_simpanan' => 'Simpanan Pokok',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        // Insert ke database
        $this->db->table('jenis_simpanan')->insertBatch($data);
    }
}

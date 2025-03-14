<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpdatedAtToTransaksiSimpananDetail extends Migration
{
    public function up()
    {
        // Menambahkan kolom updated_at
        $this->forge->addColumn('transaksi_simpanan_detail', [
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Menghapus kolom updated_at jika rollback migration
        $this->forge->dropColumn('transaksi_simpanan_detail', 'updated_at');
    }
}

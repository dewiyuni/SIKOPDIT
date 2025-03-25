<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AkunKeuangan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_akun' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'kode_akun' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true,
            ],
            'nama_akun' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'jenis' => [
                'type' => 'ENUM',
                'constraint' => ['aktiva', 'pasiva', 'pendapatan', 'biaya'],
                'default' => 'aktiva',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id_akun', true);
        $this->forge->createTable('akun_keuangan');
    }

    public function down()
    {
        $this->forge->dropTable('akun_keuangan');
    }
}

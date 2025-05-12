<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class JenisSimpanan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_jenis_simpanan' => [
                'type' => 'INT',
                'constraint' => 4,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nama_simpanan' => [
                'type' => 'VARCHAR',
                'constraint' => 30
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

        $this->forge->addKey('id_jenis_simpanan', true);
        $this->forge->createTable('jenis_simpanan');
    }

    public function down()
    {
        $this->forge->dropTable('jenis_simpanan');
    }
}

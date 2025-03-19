<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class JurnalKas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'uraian' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'kategori' => [
                'type' => 'ENUM',
                'constraint' => ['DUM', 'DUK'],
                'null' => false,
            ],
            'jumlah' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('jurnal_kas');
    }

    public function down()
    {
        $this->forge->dropTable('jurnal_kas');
    }
}

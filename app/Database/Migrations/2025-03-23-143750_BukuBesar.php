<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BukuBesar extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false
            ],
            'akun' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ],
            'debit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ],
            'kredit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ],
            'saldo' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('buku_besar');
    }

    public function down()
    {
        $this->forge->dropTable('buku_besar');
    }
}


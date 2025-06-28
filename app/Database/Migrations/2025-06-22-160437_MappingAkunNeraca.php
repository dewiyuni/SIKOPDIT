<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MappingAkunNeraca extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
                'unsigned' => true
            ],
            'nama_laporan' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'id_akun_utama' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'id_akun_pengurang' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'jenis' => [
                'type' => 'ENUM',
                'constraint' => ['AKTIVA', 'KEWAJIBAN JANGKA PENDEK', 'KEWAJIBAN JANGKA PANJANG', 'EKUITAS']
            ],
            'urutan' => [
                'type' => 'INT',
                'default' => 0
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

        $this->forge->addKey('id', true);
        $this->forge->createTable('mapping_akun_neraca');
    }

    public function down()
    {
        $this->forge->dropTable('mapping_akun_neraca');
    }
}

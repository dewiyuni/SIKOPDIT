<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PemetaanAkun extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'kategori_jurnal' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'uraian_jurnal' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'id_akun_debit' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'id_akun_kredit' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['kategori_jurnal', 'uraian_jurnal']);
        $this->forge->addForeignKey('id_akun_debit', 'akun', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_akun_kredit', 'akun', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pemetaan_akun');
    }


    public function down()
    {
        $this->forge->dropTable('pemetaan_akun');
    }
}

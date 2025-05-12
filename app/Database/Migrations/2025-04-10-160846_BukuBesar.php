<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BukuBesar extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'tanggal' => ['type' => 'DATE'],
            'id_akun' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'id_jurnal' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'debit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'kredit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'saldo' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true], // akan kita ubah manual setelahnya
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_akun', 'akun', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_jurnal', 'jurnal_kas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('buku_besar');
    }

    public function down()
    {
        $this->forge->dropTable('buku_besar');
    }
}

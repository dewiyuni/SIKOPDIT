<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Angsuran extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_angsuran' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_pinjaman' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true
            ],
            'tanggal_angsuran' => [
                'type' => 'DATE'
            ],
            'bunga' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 2,
                'null' => false,
            ],
            'denda' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 1,
                'null' => false,
            ],
            'jumlah_angsuran' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'sisa_pinjaman' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['belum lunas', 'lunas'],
                'default' => 'belum lunas'
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

        $this->forge->addKey('id_angsuran', true);
        $this->forge->addForeignKey('id_pinjaman', 'transaksi_pinjaman', 'id_pinjaman', 'CASCADE', 'CASCADE');
        $this->forge->createTable('angsuran');
    }

    public function down()
    {
        $this->forge->dropTable('angsuran');
    }
}

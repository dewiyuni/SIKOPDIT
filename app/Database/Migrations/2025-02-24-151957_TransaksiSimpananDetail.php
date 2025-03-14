<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransaksiSimpananDetail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_detail' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_transaksi_simpanan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'id_jenis_simpanan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'setor' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'tarik' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'saldo_akhir' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id_detail', true);
        $this->forge->addForeignKey('id_transaksi_simpanan', 'transaksi_simpanan', 'id_transaksi_simpanan', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_jenis_simpanan', 'jenis_simpanan', 'id_jenis_simpanan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transaksi_simpanan_detail');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi_simpanan_detail');
    }
}

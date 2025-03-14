<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransaksiSimpanan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transaksi_simpanan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_anggota' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'tanggal' => [
                'type' => 'DATE'
            ],
            'saldo_sw' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'saldo_swp' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'saldo_ss' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'saldo_sp' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 10000, // Simpanan Pokok awal 10K
            ],
            'saldo_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true
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

        $this->forge->addKey('id_transaksi_simpanan', true);
        $this->forge->addForeignKey('id_anggota', 'anggota', 'id_anggota', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transaksi_simpanan');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi_simpanan');
    }
}

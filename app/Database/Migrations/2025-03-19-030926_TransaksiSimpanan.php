<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransaksiSimpanan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_simpanan' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_anggota' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true
            ],
            'id_pinjaman' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true
            ],
            'tanggal' => [
                'type' => 'DATE'
            ],
            'setor_sw' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'tarik_sw' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'setor_swp' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'tarik_swp' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'setor_ss' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'tarik_ss' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'setor_sp' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'tarik_sp' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
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

        $this->forge->addKey('id_simpanan', true);
        $this->forge->addForeignKey('id_anggota', 'anggota', 'id_anggota', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_pinjaman', 'transaksi_pinjaman', 'id_pinjaman', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transaksi_simpanan');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi_simpanan');
    }
}

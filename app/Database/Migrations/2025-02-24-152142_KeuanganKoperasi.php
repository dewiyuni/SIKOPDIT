<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KeuanganKoperasi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_keuangan' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'id_anggota' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'keterangan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'jumlah' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'jenis' => [
                'type' => 'ENUM',
                'constraint' => ['penerimaan', 'pengeluaran'],
                'default' => 'penerimaan',
            ],
            'tanggal' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id_keuangan', true);

        // Foreign Key ke tabel anggota
        $this->forge->addForeignKey('id_anggota', 'anggota', 'id_anggota', 'CASCADE', 'CASCADE');

        // Create table
        $this->forge->createTable('keuangan_koperasi');
    }

    public function down()
    {
        $this->forge->dropTable('keuangan_koperasi');
    }
}

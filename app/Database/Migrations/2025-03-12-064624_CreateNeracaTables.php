<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNeracaTables extends Migration
{
    public function up()
    {
        // Tabel neraca_awal
        $this->forge->addField([
            'id_neraca_awal' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'bulan' => [
                'type' => 'TINYINT',
                'constraint' => 2,
            ],
            'tahun' => [
                'type' => 'YEAR',
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
        $this->forge->addKey('id_neraca_awal', true);
        $this->forge->createTable('neraca_awal');

        // Tabel kategori_neraca
        $this->forge->addField([
            'id_kategori' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_kategori' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->addKey('id_kategori', true);
        $this->forge->createTable('kategori_neraca');

        // Tabel detail_neraca
        $this->forge->addField([
            'id_detail' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_neraca_awal' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_kategori' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'uraian' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nilai' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
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
        $this->forge->addKey('id_detail', true);
        $this->forge->addForeignKey('id_neraca_awal', 'neraca_awal', 'id_neraca_awal', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kategori', 'kategori_neraca', 'id_kategori', 'CASCADE', 'CASCADE');
        $this->forge->createTable('detail_neraca');
    }

    public function down()
    {
        $this->forge->dropTable('detail_neraca');
        $this->forge->dropTable('kategori_neraca');
        $this->forge->dropTable('neraca_awal');
    }
}

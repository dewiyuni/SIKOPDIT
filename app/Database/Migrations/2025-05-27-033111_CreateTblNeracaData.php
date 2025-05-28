<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblNeracaData extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'periode_tahun' => [
                'type' => 'INT',
                'constraint' => 10, // Cukup 4 digit untuk tahun
            ],
            'periode_bulan' => [
                'type' => 'INT',
                'constraint' => 10, // Cukup 2 digit untuk bulan
            ],
            'kode_akun_internal' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'nomor_display_main' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => true,
            ],
            'nomor_display_sub' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => true,
            ],
            'uraian_akun' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'grup_laporan' => [ // Sesuai dengan struktur Anda, ini penting
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'nilai' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2', // 20 digit total, 2 digit di belakang koma
                'null' => true,
            ],
            'is_header_grup' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],
            'is_item_utama' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 1,
            ],
            'is_akumulasi' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],
            'parent_kode_akun_internal' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'is_sub_total_grup' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],
            'is_nilai_buku_line' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],
            'urutan_display' => [
                'type' => 'INT',
                'constraint' => 10,
            ],
            'is_editable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true); // Primary Key
        // Tambahkan unique key jika diperlukan untuk kombinasi periode & kode akun
        $this->forge->addUniqueKey(['periode_tahun', 'periode_bulan', 'kode_akun_internal'], 'unique_neraca_item_per_periode');
        $this->forge->createTable('tbl_neraca_data');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_neraca_data');
    }
}
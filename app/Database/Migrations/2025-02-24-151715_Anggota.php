<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Anggota extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_anggota' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'no_ba' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'unique' => true
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => '100'
            ],
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => '16',
                'unique' => true
            ],
            'dusun' => [
                'type' => 'ENUM',
                'constraint' => ['Sapon', 'Jekeling', 'Gerjen', 'Tubin', 'Senden', 'Karang', 'Kwarakan', 'Diran', 'Geden', 'Bekelan', 'Sedan', 'Jurug', 'Ledok', 'Gentan'],
                'null' => false,
            ],
            'alamat' => [
                'type' => 'TEXT'
            ],
            'pekerjaan' => [
                'type' => 'VARCHAR',
                'constraint' => '100'
            ],
            'tgl_lahir' => [
                'type' => 'DATE'
            ],
            'nama_pasangan' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default' => 'aktif'
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

        $this->forge->addKey('id_anggota', true);
        $this->forge->createTable('anggota');
    }

    public function down()
    {
        $this->forge->dropTable('anggota');
    }
}

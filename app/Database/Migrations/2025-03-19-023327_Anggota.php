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
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'no_ba' => [
                'type' => 'VARCHAR',
                'constraint' => '25',
                'unique' => true
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => '50'
            ],
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => '16',
                'unique' => true
            ],
            'dusun' => [
                'type' => 'ENUM',
                'constraint' => [
                    'Sapon',
                    'Jekeling',
                    'Gerjen',
                    'Tubin',
                    'Senden',
                    'Karang',
                    'Kwarakan',
                    'Diran',
                    'Geden',
                    'Bekelan',
                    'Sedan',
                    'Jurug',
                    'Ledok',
                    'Gentan',
                    'Pleret',
                    'Tuksono',
                    'Kelompok',
                    'Luar'
                ],
                'null' => false,
            ],
            'alamat' => [
                'type' => 'TEXT'
            ],
            'pekerjaan' => [
                'type' => 'VARCHAR',
                'constraint' => '50'
            ],
            'tgl_lahir' => [
                'type' => 'DATE'
            ],
            'nama_pasangan' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
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

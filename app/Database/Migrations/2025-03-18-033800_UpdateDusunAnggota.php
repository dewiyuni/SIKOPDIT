<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateDusunAnggota extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('anggota', [
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
                    'Luar' // Dusun baru
                ],
                'null' => false,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('anggota', [
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
                    'Gentan'
                ],
                'null' => false,
            ]
        ]);
    }
}

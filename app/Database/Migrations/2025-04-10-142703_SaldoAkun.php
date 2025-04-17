<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SaldoAkun extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_akun' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'bulan' => ['type' => 'INT', 'constraint' => 2],
            'tahun' => ['type' => 'INT', 'constraint' => 4],
            'saldo_awal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'total_debit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'total_kredit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'saldo_akhir' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true], // akan kita ubah manual setelahnya
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_akun', 'bulan', 'tahun']);
        $this->forge->addForeignKey('id_akun', 'akun', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('saldo_akun');
    }

    public function down()
    {
        $this->forge->dropTable('saldo_akun');
    }
}

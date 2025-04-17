<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class Akun extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kode_akun' => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_akun' => ['type' => 'VARCHAR', 'constraint' => 100],
            'kategori' => ['type' => 'ENUM', 'constraint' => ['Aktiva', 'Pasiva', 'Modal', 'Pendapatan', 'Beban']],
            'jenis' => ['type' => 'ENUM', 'constraint' => ['Debit', 'Kredit']],
            'saldo_awal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true], // akan kita ubah manual setelahnya
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_akun');
        $this->forge->createTable('akun');

        // Tambahkan "ON UPDATE CURRENT_TIMESTAMP" ke updated_at
        $this->db->query("ALTER TABLE akun MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('akun');
    }
}

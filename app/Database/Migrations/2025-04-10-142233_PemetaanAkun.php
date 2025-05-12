<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PemetaanAkun extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10, // Sesuai SQL int(11)
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pola_uraian' => [ // Mengganti uraian_jurnal
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false, // NOT NULL
                'comment' => 'Gunakan % sebagai wildcard LIKE',
            ],
            'kategori_jurnal' => [ // Mengganti tipe menjadi ENUM
                'type' => 'ENUM',
                'constraint' => ['DUM', 'DUK'], // Nilai ENUM
                'null' => false,           // NOT NULL
                'comment' => 'Harus cocok dgn Jurnal Kas',
            ],
            'id_akun_debit' => [
                'type' => 'INT',
                'constraint' => 10, // Sesuai SQL int(11)
                'unsigned' => true,
                'null' => false, // NOT NULL (berubah dari null=true)
                'comment' => 'FK ke tabel akun',
            ],
            'id_akun_kredit' => [
                'type' => 'INT',
                'constraint' => 10, // Sesuai SQL int(11)
                'unsigned' => true,
                'null' => false, // NOT NULL (berubah dari null=true)
                'comment' => 'FK ke tabel akun',
            ],
            'prioritas' => [ // Kolom baru
                'type' => 'INT',
                'constraint' => 3,
                'null' => false, // NOT NULL
                'default' => 0,     // DEFAULT 0
                'comment' => 'Angka lebih tinggi diproses dulu jika pola sama',
            ],
            'deskripsi' => [ // Kolom baru
                'type' => 'TEXT',
                'null' => true, // DEFAULT NULL (default CI4 jika null tidak diset false)
                'comment' => 'Penjelasan aturan (opsional)',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true, // DEFAULT NULL
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true, // DEFAULT NULL
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id', true); // true menandakan PRIMARY KEY

        // Foreign Keys (Menambahkan nama constraint agar sesuai SQL)
        $this->forge->addForeignKey('id_akun_debit', 'akun', 'id', 'CASCADE', 'CASCADE', 'fk_pemetaan_debit_akun');
        $this->forge->addForeignKey('id_akun_kredit', 'akun', 'id', 'CASCADE', 'CASCADE', 'fk_pemetaan_kredit_akun');

        // Index (Menambahkan index baru)
        // Parameter kedua false = bukan primary, ketiga false = bukan unique
        $this->forge->addKey(['pola_uraian', 'kategori_jurnal'], false, false, 'idx_pola_kategori');

        // Hapus unique key yang lama jika ada di migrasi sebelumnya (tidak diperlukan di sini karena ini file baru/modifikasi)
        // $this->forge->dropKey('pemetaan_akun', 'kategori_jurnal_uraian_jurnal'); // Contoh jika key lama bernama ini

        // Membuat tabel
        // Anda bisa menambahkan atribut ENGINE dan CHARSET jika perlu, meskipun biasanya diatur di konfigurasi database
        $attributes = ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci'];
        $this->forge->createTable('pemetaan_akun', true, $attributes); // true = IF NOT EXISTS
    }

    public function down()
    {
        // Drop foreign keys sebelum drop tabel (best practice)
        // Nama constraint harus sama dengan yang didefinisikan di 'up'
        $this->forge->dropForeignKey('pemetaan_akun', 'fk_pemetaan_debit_akun');
        $this->forge->dropForeignKey('pemetaan_akun', 'fk_pemetaan_kredit_akun');

        // Drop tabel
        $this->forge->dropTable('pemetaan_akun', true); // true = IF EXISTS
    }
}
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnggotaSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builderAnggota = $db->table('anggota');
        $builderTransaksi = $db->table('transaksi_simpanan');

        $dataAnggota = [
            [
                'no_ba' => '001',
                'nama' => 'Andi Saputra',
                'nik' => '1234567890123456',
                'dusun' => 'Sapon',
                'alamat' => 'Jl. Merdeka No. 1',
                'pekerjaan' => 'Petani',
                'tgl_lahir' => '1990-05-10',
                'nama_pasangan' => 'Siti Aisyah',
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'no_ba' => '002',
                'nama' => 'Budi Santoso',
                'nik' => '2345678901234567',
                'dusun' => 'Jekeling',
                'alamat' => 'Jl. Pahlawan No. 2',
                'pekerjaan' => 'Wiraswasta',
                'tgl_lahir' => '1985-08-15',
                'nama_pasangan' => 'Rina Kusuma',
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'no_ba' => '12',
                'nama' => 'Hasan wisnu',
                'nik' => '3847264759685100',
                'dusun' => 'Sapon',
                'alamat' => 'sapon sidorejo, lendah, kulon progo',
                'pekerjaan' => 'Petani',
                'tgl_lahir' => '1990-05-10',
                'nama_pasangan' => 'Aisyah Tiara',
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'no_ba' => '8',
                'nama' => 'Cahyo wirawan',
                'nik' => '9867543265403120',
                'dusun' => 'Sedan',
                'alamat' => 'sedan sidorejo, lendah, kulon progo',
                'pekerjaan' => 'pengusaha',
                'tgl_lahir' => '1990-05-10',
                'nama_pasangan' => 'Cahyaninggsih',
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        foreach ($dataAnggota as $anggota) {
            // Cek apakah anggota sudah ada
            $existingAnggota = $builderAnggota->where('nik', $anggota['nik'])->get()->getRow();
            if (!$existingAnggota) {
                $builderAnggota->insert($anggota);
                $idAnggota = $db->insertID();
            } else {
                $idAnggota = $existingAnggota->id_anggota;
            }

            // Cek apakah transaksi simpanan sudah ada
            $existingTransaksi = $builderTransaksi->where('id_anggota', $idAnggota)->get()->getRow();
            if (!$existingTransaksi) {
                $builderTransaksi->insert([
                    'id_anggota' => $idAnggota,
                    'id_pinjaman' => null, // Sesuaikan jika ada pinjaman
                    'tanggal' => date('Y-m-d'),
                    'setor_sw' => 75000,
                    'tarik_sw' => 0,
                    'setor_swp' => 0,
                    'tarik_swp' => 0,
                    'setor_ss' => 5000,
                    'tarik_ss' => 0,
                    'setor_sp' => 10000,
                    'tarik_sp' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}

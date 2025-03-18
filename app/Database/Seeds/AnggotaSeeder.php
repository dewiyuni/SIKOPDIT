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
        $builderDetail = $db->table('transaksi_simpanan_detail');

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
                'no_ba' => '003',
                'nama' => 'Citra Dewi',
                'nik' => '3456789012345678',
                'dusun' => 'Gerjen',
                'alamat' => 'Jl. Diponegoro No. 3',
                'pekerjaan' => 'Guru',
                'tgl_lahir' => '1992-11-20',
                'nama_pasangan' => null,
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($dataAnggota as $anggota) {
            // Insert data anggota
            $builderAnggota->insert($anggota);
            $idAnggota = $db->insertID(); // Ambil ID anggota yang baru dibuat

            // Insert transaksi simpanan (mendapatkan id_transaksi_simpanan)
            $builderTransaksi->insert([
                'id_anggota' => $idAnggota,
                'tanggal' => date('Y-m-d'),
                'saldo_sw' => 75000,
                'saldo_swp' => 0,
                'saldo_ss' => 5000,
                'saldo_sp' => 10000,
                'saldo_total' => 90000,
                'keterangan' => 'Saldo Awal Pendaftaran',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $idTransaksiSimpanan = $db->insertID(); // Ambil ID transaksi_simpanan

            // ID Jenis Simpanan (sesuaikan dengan tabel `jenis_simpanan`)
            $idJenisSimpanan = [
                'SW' => 1,  // Simpanan Wajib
                'SWP' => 2,  // Simpanan Wajib Pokok
                'SS' => 3,  // Simpanan Sukarela
                'SP' => 4,  // Simpanan Pokok
            ];

            // Insert ke transaksi_simpanan_detail
            $simpananAwal = [
                ['id_jenis_simpanan' => $idJenisSimpanan['SW'], 'setor' => 75000, 'tarik' => 0, 'saldo_akhir' => 75000],
                ['id_jenis_simpanan' => $idJenisSimpanan['SWP'], 'setor' => 0, 'tarik' => 0, 'saldo_akhir' => 0],
                ['id_jenis_simpanan' => $idJenisSimpanan['SS'], 'setor' => 5000, 'tarik' => 0, 'saldo_akhir' => 5000],
                ['id_jenis_simpanan' => $idJenisSimpanan['SP'], 'setor' => 10000, 'tarik' => 0, 'saldo_akhir' => 10000],
            ];

            foreach ($simpananAwal as $simpanan) {
                $simpanan['id_transaksi_simpanan'] = $idTransaksiSimpanan;
                $simpanan['created_at'] = date('Y-m-d H:i:s');
                $simpanan['updated_at'] = date('Y-m-d H:i:s');
                $builderDetail->insert($simpanan);
            }
        }
    }
}

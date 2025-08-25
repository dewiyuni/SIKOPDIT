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

        $dusunList = [
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
        ];

        $firstNames = ["Andi", "Budi", "Cahyo", "Dewi", "Eka", "Fajar", "Gita", "Hasan", "Indra", "Joko", "Kiki", "Lina"];
        $lastNames = ["Saputra", "Santoso", "Wijaya", "Rahmawati", "Herlambang", "Putri", "Nugroho", "Sari", "Utami", "Firmansyah"];
        $jobs = ["Petani", "Wiraswasta", "Karyawan", "Guru", "Pedagang"];

        for ($i = 1; $i <= 50; $i++) {
            $no_ba = str_pad($i, 3, '0', STR_PAD_LEFT);
            $nama = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
            $nik = strval(rand(1000000000000000, 9999999999999999));
            $dusun = $dusunList[array_rand($dusunList)];
            $alamat = strtolower($dusun) . ' Sidorejo, Lendah, Kulon Progo';
            $pekerjaan = $jobs[array_rand($jobs)];
            $tahun = rand(1975, 1999);
            $bulan = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
            $tanggal = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
            $tgl_lahir = "$tahun-$bulan-$tanggal";
            $nama_pasangan = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
            $now = date('Y-m-d H:i:s');

            // Email & Password
            $email = strtolower(str_replace(' ', '.', $nama));
            $email = preg_replace('/[^a-z0-9.]/', '', $email); // filter biar aman
            $email .= rand(100, 999) . '@gmail.com'; // kasih angka biar unique

            $password = password_hash("password{$no_ba}", PASSWORD_DEFAULT);
            $no_hp = '08' . rand(1000000000, 9999999999); // random nomor hp
            $anggota = [
                'no_ba' => $no_ba,
                'nama' => $nama,
                'nik' => $nik,
                'email' => $email,
                'password' => $password,
                'dusun' => $dusun,
                'alamat' => $alamat,
                'pekerjaan' => $pekerjaan,
                'tgl_lahir' => $tgl_lahir,
                'nama_pasangan' => $nama_pasangan,
                'no_hp' => $no_hp,
                'status' => 'aktif',
                'role' => 'anggota',
                'created_at' => $now,
                'updated_at' => $now
            ];

            // Insert anggota jika belum ada
            $existingAnggota = $builderAnggota->where('nik', $nik)->get()->getRow();
            if (!$existingAnggota) {
                $builderAnggota->insert($anggota);
                $idAnggota = $db->insertID(); // ✅ ambil ID terakhir dari $db
            } else {
                $idAnggota = $existingAnggota->id_anggota;
            }


            // Insert transaksi simpanan default jika belum ada
            $existingTransaksi = $builderTransaksi->where('id_anggota', $idAnggota)->get()->getRow();
            if (!$existingTransaksi) {
                $builderTransaksi->insert([
                    'id_anggota' => $idAnggota,
                    'id_pinjaman' => null,
                    'tanggal' => date('Y-m-d'),
                    'setor_sw' => 75000,
                    'tarik_sw' => 0,
                    'setor_swp' => 0,
                    'tarik_swp' => 0,
                    'setor_ss' => 5000,
                    'tarik_ss' => 0,
                    'setor_sp' => 10000,
                    'tarik_sp' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
    }
}

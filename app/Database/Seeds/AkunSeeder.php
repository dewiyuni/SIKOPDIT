<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // =============== aktiva ============================
            ['kode_akun' => '101', 'nama_akun' => 'Simpanan di bank', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '102', 'nama_akun' => 'Simpanan deposito', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '103', 'nama_akun' => 'Tarik simpanan', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '104', 'nama_akun' => 'Tarik sp (Simpanan Pokok)', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '105', 'nama_akun' => 'Tarik sw (Simpanan Wajib)', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '106', 'nama_akun' => 'Tarik swo (Simpanan SWP)', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '107', 'nama_akun' => 'Tarik ss (Simpanan SS)', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '108', 'nama_akun' => 'Peminjaman anggota', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Investasi & Penyertaan Modal
            ['kode_akun' => '109', 'nama_akun' => 'Penyertaan modal tetap', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '110', 'nama_akun' => 'Investaris gedung/bangunan', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '111', 'nama_akun' => 'Pembelian inventaris mebeler', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '112', 'nama_akun' => 'Pembelian proyektor', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '113', 'nama_akun' => 'Pembelian inventaris komputer', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Aset Tetap (yang mengalami penyusutan)
            ['kode_akun' => '114', 'nama_akun' => 'Akumulasi Penyusutan Barang Invest Mebeler', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '115', 'nama_akun' => 'Akumulasi Penyusutan Inventaris Gedung', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '116', 'nama_akun' => 'Akumulasi Penyusutan Investasi Sepeda Motor', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '117', 'nama_akun' => 'Akumulasi Penyusutan Investasi Komputer', 'jenis' => 'Aktiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // =============== Pasiva ============================
            // Modal & Simpanan Anggota
            ['kode_akun' => '201', 'nama_akun' => 'Uang Pangkal', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '202', 'nama_akun' => 'Simpanan Pokok', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '203', 'nama_akun' => 'Simpanan Wajib', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '204', 'nama_akun' => 'Simpanan SWP', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '205', 'nama_akun' => 'Simpanan SS', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '206', 'nama_akun' => 'Simpanan Non Saham', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '207', 'nama_akun' => 'Simpanan Jasa Non Saham', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Dana Titipan & Penyisihan
            ['kode_akun' => '208', 'nama_akun' => 'Penyisihan Tab Hari Tua Karyawan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '209', 'nama_akun' => 'Penyisihan Dana Kesejahteraan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '210', 'nama_akun' => 'Penyisihan Dana Pemilihan Pengurus', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '211', 'nama_akun' => 'Penyisihan Pendampingan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '212', 'nama_akun' => 'Penyisihan Tunjangan Pensiun Karyawan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '213', 'nama_akun' => 'Titipan Dana Kesejahteraan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '214', 'nama_akun' => 'Titipan Dana RAT', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '215', 'nama_akun' => 'Titipan Dana Pendampingan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '216', 'nama_akun' => 'Titipan Penyisihan Pajak SHU', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '217', 'nama_akun' => 'Titipan Tunjangan Pesangon Karyawan', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Hutang & Kewajiban
            ['kode_akun' => '218', 'nama_akun' => 'Pinjaman dari BPD', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '219', 'nama_akun' => 'Angsuran Pinjaman ke BPD', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '220', 'nama_akun' => 'Bunga Hutang Bank', 'jenis' => 'pasiva', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // =============== Pendapatan ============================
            ['kode_akun' => '301', 'nama_akun' => 'Jasa Pinjaman', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '302', 'nama_akun' => 'Jasa dari Bank', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '303', 'nama_akun' => 'Jasa Deposito', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '304', 'nama_akun' => 'Pendapatan dari Tari TTP SHU', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '305', 'nama_akun' => 'Fee (%)', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '306', 'nama_akun' => 'Fee (Anggota Keluar)', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '307', 'nama_akun' => 'Fee (BPKB)', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '308', 'nama_akun' => 'Denda', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '309', 'nama_akun' => 'Hibah', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '310', 'nama_akun' => 'Pendapatan Lain-lain', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Pendapatan dari Simpanan & Pinjaman
            ['kode_akun' => '311', 'nama_akun' => 'Jasa Piutang', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '312', 'nama_akun' => 'Fee % (Profisi) 1%', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '313', 'nama_akun' => 'Jasa Deposito', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '314', 'nama_akun' => 'Fe Jasa Simpanan dari Bank', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '315', 'nama_akun' => 'Titip Pj', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '316', 'nama_akun' => 'Ajak (JS Non Saham)', 'jenis' => 'Pendapatan', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],


            // =============== Biaya ============================
            // Kas & Bank
            ['kode_akun' => '401', 'nama_akun' => 'Simpanan di bank', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '402', 'nama_akun' => 'Simpanan deposito', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '403', 'nama_akun' => 'Tarik simpanan', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Investasi & Penyertaan Modal
            ['kode_akun' => '404', 'nama_akun' => 'Penyertaan modal tetap', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '405', 'nama_akun' => 'Investasi gedung/bangunan', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Aset Tetap
            ['kode_akun' => '406', 'nama_akun' => 'Akumulasi Penyusutan Barang Invest Mebeler', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Pasiva (Kewajiban & Ekuitas)
            ['kode_akun' => '407', 'nama_akun' => 'Uang pangkal', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Pendapatan
            ['kode_akun' => '408', 'nama_akun' => 'Jasa Pinjaman', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            // Biaya (Beban)
            ['kode_akun' => '409', 'nama_akun' => 'Biaya administrasi', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '410', 'nama_akun' => 'Biaya organisasi', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['kode_akun' => '411', 'nama_akun' => 'Biaya pajak listrik', 'jenis' => 'biaya', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

        ];

        // Insert batch data ke tabel akun_keuangan
        $this->db->table('akun_keuangan')->insertBatch($data);
    }
}

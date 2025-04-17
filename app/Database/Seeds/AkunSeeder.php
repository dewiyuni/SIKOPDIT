<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            // aktiva
            // Aset Lancar
            ['kode_akun' => '1-1000', 'nama_akun' => 'Kas', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1100', 'nama_akun' => 'Simpanan di Bank', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1200', 'nama_akun' => 'Simpanan Deposito', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1300', 'nama_akun' => 'Piutang Biasa', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1400', 'nama_akun' => 'Piutang Khusus', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1500', 'nama_akun' => 'Piutang Ragu-ragu', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1600', 'nama_akun' => 'Penyusutan Piutang Ragu-ragu', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            // Aset Tidak Lancar
            ['kode_akun' => '1-1700', 'nama_akun' => 'Simpanan di BK3D', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1800', 'nama_akun' => 'Investasi', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-1900', 'nama_akun' => 'Serta Data', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            // Aset Tetap (Inventaris)
            ['kode_akun' => '1-2000', 'nama_akun' => 'Inventaris Mebel', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2100', 'nama_akun' => 'Akumulasi Penyusutan Mebel', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2200', 'nama_akun' => 'Beban Tertangguh', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2300', 'nama_akun' => 'Inventaris Gedung', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2400', 'nama_akun' => 'Akumulasi Penyusutan Gedung', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2500', 'nama_akun' => 'Inventaris Pagar', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2600', 'nama_akun' => 'Akumulasi Penyusutan Pagar', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2700', 'nama_akun' => 'Inventaris Tanah', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2800', 'nama_akun' => 'Akumulasi Penyusutan Tanah', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-2900', 'nama_akun' => 'Inventaris Komputer', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-3000', 'nama_akun' => 'Akumulasi Penyusutan Komputer', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-3100', 'nama_akun' => 'Inventaris Kendaraan', 'kategori' => 'Aktiva', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '1-3200', 'nama_akun' => 'Akumulasi Penyusutan Kendaraan', 'kategori' => 'Aktiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            // ==================================================
            // pasiva
            // Kewajiban jangka pendek
            ['kode_akun' => '2-1000', 'nama_akun' => 'Cadangan Aktiva Produktif', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1100', 'nama_akun' => 'Dana Cadangan RAT', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1200', 'nama_akun' => 'Dana Cadangan Pemilihan Pengurus', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1300', 'nama_akun' => 'Utang Bank (BPD)', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1400', 'nama_akun' => 'Cadangan Risiko', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1500', 'nama_akun' => 'Titipan Dana Kesejahteraan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1600', 'nama_akun' => 'Titipan Dana Tunjangan Pesangon', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1700', 'nama_akun' => 'Uang Pangkal', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1800', 'nama_akun' => 'Titipan Dana RAT', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-1900', 'nama_akun' => 'Titipan Dana Pendampingan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2000', 'nama_akun' => 'Titipan Penyisihan Pajak SHU', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2100', 'nama_akun' => 'Titipan Pajak Jasa Non Saham', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2200', 'nama_akun' => 'Utang Bank (BPD)', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2300', 'nama_akun' => 'Dana Titipan Simpanan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2400', 'nama_akun' => 'Dana Pengurus', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2500', 'nama_akun' => 'Dana Karyawan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2600', 'nama_akun' => 'Dana Pendidikan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2700', 'nama_akun' => 'Dana Sosial', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2800', 'nama_akun' => 'Dana PDK', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-2900', 'nama_akun' => 'Titipan Dana RAT', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3000', 'nama_akun' => 'Dana Risiko', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3100', 'nama_akun' => 'Titipan Pajak', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3200', 'nama_akun' => 'Dana Kesejahteraan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3300', 'nama_akun' => 'Dana Pendampingan', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3400', 'nama_akun' => 'Dana Pemupukan Modal Tetap', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3500', 'nama_akun' => 'Dana CAP', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3600', 'nama_akun' => 'Dana-dana', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3700', 'nama_akun' => 'Beban yang harus dibayar', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3800', 'nama_akun' => 'Dana Sehat', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-3900', 'nama_akun' => 'Titipan dana CAP', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-4000', 'nama_akun' => 'Hutang Pihak ke 2', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-4100', 'nama_akun' => 'Iuran Dana Sehat', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-4200', 'nama_akun' => 'Iuran Dana Sehat', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-4300', 'nama_akun' => 'Cadangan Likuiditas', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '2-4400', 'nama_akun' => 'PJKR', 'kategori' => 'Pasiva', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],

            // modal
            ['kode_akun' => '3-1000', 'nama_akun' => 'Simpanan Non Saham', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-1100', 'nama_akun' => 'Jasa Simpanan Non Saham', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-1200', 'nama_akun' => 'Simpanan Sukarela', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-1300', 'nama_akun' => 'Modal Tetap', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '7-1600', 'nama_akun' => 'Dana Kesejahteraan', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '7-1700', 'nama_akun' => 'Penyertaan Modal Tetap', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '7-1800', 'nama_akun' => 'Dana Pendampingan', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '7-1900', 'nama_akun' => 'Dana Pensiun', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-2000', 'nama_akun' => 'SHU Anggota', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-2100', 'nama_akun' => 'Tabungan Hari Tua', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-2200', 'nama_akun' => 'Simpanan Pokok', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-2300', 'nama_akun' => 'Simpanan Wajib', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-2400', 'nama_akun' => 'Simpanan Wajib Penyertaan', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '3-2500', 'nama_akun' => 'Cadangan Koperasi', 'kategori' => 'Modal', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],

            // pendapatan
            ['kode_akun' => '4-1000', 'nama_akun' => 'Pendapatan Denda', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1100', 'nama_akun' => 'Fee Anggota Keluar', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1200', 'nama_akun' => 'Pendapatan Profisi 1%', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1300', 'nama_akun' => 'Pendapatan Jasa Pinjaman', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1400', 'nama_akun' => 'Pendapatan Jasa Simpanan Non Saham', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1500', 'nama_akun' => 'Pendapatan Lain-lain', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1600', 'nama_akun' => 'Hibah ', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1700', 'nama_akun' => 'Pendapatan Jasa Simpanan Bank', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '4-1800', 'nama_akun' => 'Pendapatan Jasa Deposito', 'kategori' => 'Pendapatan', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now],

            // beban
            ['kode_akun' => '5-1000', 'nama_akun' => 'Beban Lain-lain', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1100', 'nama_akun' => 'Biaya Administrasi', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1200', 'nama_akun' => 'Biaya Organisasi', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1300', 'nama_akun' => 'Gaji Karyawan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1400', 'nama_akun' => 'Tunjangan Istri', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1500', 'nama_akun' => 'Tunjangan Anak', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1600', 'nama_akun' => 'THR Karyawan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1700', 'nama_akun' => 'Insentif Pengurus/Pengawas', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1800', 'nama_akun' => 'Biaya Koordinator', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-1900', 'nama_akun' => 'Biaya Minum', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2000', 'nama_akun' => 'Gaji Penjaga/Kebersihan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2100', 'nama_akun' => 'THR Penjaga', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2200', 'nama_akun' => 'Biaya Harkopnas', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2300', 'nama_akun' => 'Biaya Transportasi', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2400', 'nama_akun' => 'Biaya Transportasi Rapat', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2500', 'nama_akun' => 'Biaya Bensin', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2600', 'nama_akun' => 'Biaya Lembur', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2700', 'nama_akun' => 'Biaya Non Saham', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2800', 'nama_akun' => 'Perawatan Inventaris', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-2900', 'nama_akun' => 'Biaya Promosi', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3000', 'nama_akun' => 'Biaya Pendidikan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3100', 'nama_akun' => 'Perawatan Gedung', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3200', 'nama_akun' => 'Biaya Sosial', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3300', 'nama_akun' => 'Bonus Target', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3400', 'nama_akun' => 'Biaya Kesejahteraan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3500', 'nama_akun' => 'Biaya Audit', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3600', 'nama_akun' => 'Biaya Supervisi', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3700', 'nama_akun' => 'Insentif Koordinator', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3800', 'nama_akun' => 'Biaya Syawalan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-3900', 'nama_akun' => 'Biaya Bingkisan Lebaran', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4000', 'nama_akun' => 'Biaya Seragam', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4100', 'nama_akun' => 'Biaya Bunga Bank', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4200', 'nama_akun' => 'Administrasi/Pajak Bank', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4300', 'nama_akun' => 'Pesangon Karyawan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4400', 'nama_akun' => 'Denda Pajak', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4500', 'nama_akun' => 'Biaya Pemilihan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4600', 'nama_akun' => 'BPJS', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4700', 'nama_akun' => 'Penyisihan Pesangon', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4800', 'nama_akun' => 'Inventaris Mebel', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-4900', 'nama_akun' => 'Inventaris Proyektor', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5000', 'nama_akun' => 'Inventaris Komputer', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5100', 'nama_akun' => 'Gedung', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5200', 'nama_akun' => 'Beban Penyusutan Tertagih', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5300', 'nama_akun' => 'Beban Penyusutan Mebel', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5400', 'nama_akun' => 'Beban Penyusutan Gedung', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5500', 'nama_akun' => 'Beban Penyusutan Motor', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5600', 'nama_akun' => 'Beban Penyusutan Komputer', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5700', 'nama_akun' => 'Pajak Listrik', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5800', 'nama_akun' => 'Pajak PPh', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-5900', 'nama_akun' => 'Pajak PBB', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-6000', 'nama_akun' => 'Pajak SHU', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-6100', 'nama_akun' => 'Pajak Wifi', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-6200', 'nama_akun' => 'Pajak Jasa Simpanan Bank', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],
            ['kode_akun' => '5-6300', 'nama_akun' => 'Pajak Kendaraan', 'kategori' => 'Beban', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now],


        ];

        // Insert batch data ke tabel akun
        $this->db->table('akun')->insertBatch($data);
    }
}

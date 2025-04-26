<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run()
    {
        // 1. Nonaktifkan Foreign Key Checks
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Hapus data lama menggunakan truncate
        $this->db->table('akun')->truncate();

        $now = date('Y-m-d H:i:s');

        // Daftar Akun Baru berdasarkan "Uraian"
        // Catatan: Kode akun, kategori, jenis, saldo_awal dipertahankan dari data lama
        //          untuk menjaga konsistensi, hanya nama_akun yang diubah.
        //          Akun yang tidak ada di daftar "Uraian" baru tidak dimasukkan.
        $data = [
            ['kode_akun' => 'AST001', 'nama_akun' => 'Akumulasi Penyusutan Inventaris Komputer', 'kategori' => 'ASSET', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AST002', 'nama_akun' => 'Akumulasi Penyusutan Inventaris Mebel', 'kategori' => 'ASSET', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AST003', 'nama_akun' => 'Akumulasi Penyusutan Inventaris Gedung', 'kategori' => 'ASSET', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AST004', 'nama_akun' => 'Akumulasi Penyusutan Inventaris Kendaraan', 'kategori' => 'ASSET', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PIN001', 'nama_akun' => 'Angsuran Pinjaman', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'CAP001', 'nama_akun' => 'Cadangan Aktiva Produktif (CAP)', 'kategori' => 'MODAL', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND001', 'nama_akun' => 'Denda', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'FEE001', 'nama_akun' => 'Fee (Anggota Keluar)', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND002', 'nama_akun' => 'Fee Persentase (Profisi) 1%', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND003', 'nama_akun' => 'Pendapatan Jasa Pinjaman', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND008', 'nama_akun' => 'Jasa Piutang', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'DAC001', 'nama_akun' => 'Dana Cadangan RAT', 'kategori' => 'MODAL', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'DAC002', 'nama_akun' => 'Penyisihan Dana Pemilihan Pengurus', 'kategori' => 'MODAL', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PIN002', 'nama_akun' => 'Pinjaman dari Bank Pembangunan Daerah (BPD)', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'RIS001', 'nama_akun' => 'Risiko (Re) 0,5%', 'kategori' => 'BEBAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'SIM001', 'nama_akun' => 'Simpanan Non-Saham', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'SIM006', 'nama_akun' => 'Jasa Simpanan Non-Saham', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'SIM002', 'nama_akun' => 'Simpanan Pokok (SP)', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'SIM003', 'nama_akun' => 'Simpanan Sukarela (SS)', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'SIM004', 'nama_akun' => 'Simpanan Wajib (SW)', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'SIM005', 'nama_akun' => 'Simpanan Wajib Penyertaan (SWP)', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TAR001', 'nama_akun' => 'Tarik Dana dari Bank', 'kategori' => 'ASSET', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TDP001', 'nama_akun' => 'Titipan Dana Kesejahteraan', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TDP002', 'nama_akun' => 'Titipan Tunjangan Pesangon Karyawan', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'UAN001', 'nama_akun' => 'Uang Pangkal', 'kategori' => 'ASSET', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TDP003', 'nama_akun' => 'Titipan Dana RAT', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TDP004', 'nama_akun' => 'Titipan Dana Pendampingan', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TDP005', 'nama_akun' => 'Titipan Penyisihan Pajak SHU', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'MOD001', 'nama_akun' => 'Modal Tetap', 'kategori' => 'ASSET', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND004', 'nama_akun' => 'Pendapatan Lain-lain', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND005', 'nama_akun' => 'Pendapatan Hibah', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND006', 'nama_akun' => 'Pendapatan Jasa Simpanan Bank', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PND007', 'nama_akun' => 'Pendapatan Jasa Deposito', 'kategori' => 'PENDAPATAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'TDP006', 'nama_akun' => 'Titipan Pajak Jasa Non Saham', 'kategori' => 'KEWAJIBAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

            ['kode_akun' => 'AUTO001', 'nama_akun' => 'Pinjaman Anggota', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO002', 'nama_akun' => 'Simpanan di Bank', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO003', 'nama_akun' => 'Simpanan Deposito', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO004', 'nama_akun' => 'Angsuran pinjaman ke BPD', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO005', 'nama_akun' => 'Tarik Simpanan Pokok (SP)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO006', 'nama_akun' => 'Tarik Simpanan Wajib (SW)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO007', 'nama_akun' => 'Tarik Simpanan Wajib Penyertaan (SWP)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO008', 'nama_akun' => 'Tarik Simpanan Sukarela (SS)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO009', 'nama_akun' => 'Tarik Simpanan Non-Saham', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO010', 'nama_akun' => 'Tarik Jasa Simpanan Non-Saham', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO011', 'nama_akun' => 'Tarik titip (SP, SW, SWP, SS)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO012', 'nama_akun' => 'Tarik Dana Pengurus', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO013', 'nama_akun' => 'Tarik Dana Karyawan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO014', 'nama_akun' => 'Tarik Dana Pendidikan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO015', 'nama_akun' => 'Tarik Dana Sosial', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO016', 'nama_akun' => 'Tarik Dana PDK', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO017', 'nama_akun' => 'Tarik Titipan Dana RAT', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO018', 'nama_akun' => 'Tarik Dana Risiko', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO019', 'nama_akun' => 'Tarik Dana Pemupukan Modal Tetap', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO020', 'nama_akun' => 'Tarik Dana CAP', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO021', 'nama_akun' => 'Tarik Sisa Hasil Usaha (SHU)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO022', 'nama_akun' => 'Tarik THT', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO023', 'nama_akun' => 'Tarik Titipan Pajak', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO024', 'nama_akun' => 'Tarik Dana Kesejahteraan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

            ['kode_akun' => 'AUTO025', 'nama_akun' => 'Tarik Dana Pendampingan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO026', 'nama_akun' => 'Biaya Administrasi', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO027', 'nama_akun' => 'Biaya Organisasi', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO028', 'nama_akun' => 'Biaya Gaji Karyawan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO029', 'nama_akun' => 'Biaya Tunjangan Istri', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO030', 'nama_akun' => 'Biaya Tunjangan Anak', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO031', 'nama_akun' => 'THR (karyawan)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO032', 'nama_akun' => 'Biaya insentif pengurus & pengawas', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO070', 'nama_akun' => 'Biaya IPTW (Insentif Pekerja Tidak Tetap Wajib)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO033', 'nama_akun' => 'Biaya Koordinator', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO034', 'nama_akun' => 'Biaya Minum', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO035', 'nama_akun' => 'Biaya penjaga/kebersihan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO036', 'nama_akun' => 'THR Penjaga', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO037', 'nama_akun' => 'Biaya Harkop/Besar Nasional', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO038', 'nama_akun' => 'Biaya Transportasi', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO039', 'nama_akun' => 'Biaya Transportasi rapat', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO040', 'nama_akun' => 'Biaya Bensin', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO041', 'nama_akun' => 'Biaya Lembur', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO042', 'nama_akun' => 'Biaya Non-Saham', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO043', 'nama_akun' => 'Biaya Perawatan Inventaris', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO044', 'nama_akun' => 'Biaya kalender/promosi', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO045', 'nama_akun' => 'Biaya pendidikan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO046', 'nama_akun' => 'Perawatan gedung', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO047', 'nama_akun' => 'Biaya Dana Sosial', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO048', 'nama_akun' => 'Bonus pencapaian target', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO049', 'nama_akun' => 'Biaya kesejahteraan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO050', 'nama_akun' => 'Biaya audit pengawas', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO051', 'nama_akun' => 'Biaya supervisi pengurus', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO052', 'nama_akun' => 'Insentif koordinator', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO053', 'nama_akun' => 'Biaya syawalan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO054', 'nama_akun' => 'Bingkisan lebaran', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO055', 'nama_akun' => 'Seragam', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO056', 'nama_akun' => 'Biaya Bunga Hutang Bank', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO057', 'nama_akun' => 'Biaya Administrasi/Pajak Bank', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO058', 'nama_akun' => 'Pesangon purna karyawan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO059', 'nama_akun' => 'Modal Tetap', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO060', 'nama_akun' => 'Denda pajak', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO061', 'nama_akun' => 'Biaya Pemilihan Pengawas/Pengurus', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO062', 'nama_akun' => 'Biaya BPJS', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO063', 'nama_akun' => 'Penyisihan Tunjangan Pensiun Karyawan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO064', 'nama_akun' => 'CAP (Cadangan Aktiva Produktif)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO065', 'nama_akun' => 'Penyisihan Dana Rapat Tahunan (RAT)', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO066', 'nama_akun' => 'Penyisihan Dana Kesejahteraan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO067', 'nama_akun' => 'Penyertaan Modal Tetap', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO069', 'nama_akun' => 'Proyektor', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO072', 'nama_akun' => 'Penyisihan pendampingan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO073', 'nama_akun' => 'Penyisihan tunjangan pensiun', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO074', 'nama_akun' => 'Penyusutan tertagih', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO075', 'nama_akun' => 'Biaya Penyusutan Inventaris Mebel', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO076', 'nama_akun' => 'Biaya Penyusutan Inventaris Gedung', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO077', 'nama_akun' => 'Biaya Penyusutan Inventaris Kendaraan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO078', 'nama_akun' => 'Biaya Penyusutan Inventaris Komputer', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO079', 'nama_akun' => 'Biaya Pajak Listrik', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO080', 'nama_akun' => 'Pajak PPh', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO081', 'nama_akun' => 'Pajak PBB', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO082', 'nama_akun' => 'Pajak SHU', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO083', 'nama_akun' => 'Biaya Pajak Wi-Fi', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO084', 'nama_akun' => 'Pajak jasa simpanan bank', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO085', 'nama_akun' => 'Pajak kendaraan', 'kategori' => 'LAIN-LAIN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

            // Akun kas dan simpanan bank bisa dibuat berbeda jenis dan kategori
            ['kode_akun' => 'AUTO086', 'nama_akun' => 'Kas', 'kategori' => 'AKTIVA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'AUTO087', 'nama_akun' => 'Simpanan di bank', 'kategori' => 'AKTIVA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now]

        ];

        // Menggunakan Query Builder untuk insert batch
        $this->db->table('akun')->insertBatch($data);

        // 3. Aktifkan kembali Foreign Key Checks
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // Optional: Tampilkan pesan di CLI saat seeder berjalan
        // echo "AkunSeeder executed successfully with updated names.\n";
    }
}
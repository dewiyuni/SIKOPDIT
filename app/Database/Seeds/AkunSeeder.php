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
            // --- Dari Kategori PEMASUKAN (Lama) ---
            ['kode_akun' => 'PEM001', 'nama_akun' => 'Uang Pangkal', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PEM002', 'nama_akun' => 'Simpanan Pokok (SP)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Ekuitas
            ['kode_akun' => 'PEM003', 'nama_akun' => 'Simpanan Wajib (SW)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Ekuitas
            ['kode_akun' => 'PEM004', 'nama_akun' => 'Simpanan Wajib Penyertaan (SWP)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Ekuitas/Liabilitas?
            ['kode_akun' => 'PEM005', 'nama_akun' => 'Simpanan Sukarela (SS)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Liabilitas
            ['kode_akun' => 'PEM006', 'nama_akun' => 'Simpanan Non-Saham', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Liabilitas
            ['kode_akun' => 'PEM007', 'nama_akun' => 'Simpanan Jasa Non-Saham', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Liabilitas
            ['kode_akun' => 'PEM008', 'nama_akun' => 'Angsuran Pinjaman', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Arus Kas Masuk
            ['kode_akun' => 'PEM009', 'nama_akun' => 'Jasa Piutang', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM010', 'nama_akun' => 'Fee Persentase (Profisi) 1%', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM013', 'nama_akun' => 'Pinjaman dari Bank Pembangunan Daerah (BPD)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas
            ['kode_akun' => 'PEM015', 'nama_akun' => 'Resiko (Re) 0,5%', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Ekuitas (Cadangan Resiko)
            ['kode_akun' => 'PEM016', 'nama_akun' => 'Tarik Dana dari Bank', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Arus Kas Masuk
            ['kode_akun' => 'PEM017', 'nama_akun' => 'Denda', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM019', 'nama_akun' => 'Fee (Anggota Keluar)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan

            // --- Dari Kategori: AKUMULASI PENYUSUTAN (Lama) ---
            ['kode_akun' => 'AKM001', 'nama_akun' => 'Akumulasi Penyusutan Barang Investasi Mebel', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset
            ['kode_akun' => 'AKM002', 'nama_akun' => 'Akumulasi Penyusutan Inventaris Gedung', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset
            ['kode_akun' => 'AKM004', 'nama_akun' => 'Akumulasi Penyusutan Investasi Komputer', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset

            // --- Dari Kategori: PENYISIHAN PENYISIHAN (Lama) ---
            ['kode_akun' => 'PPN002', 'nama_akun' => 'Penyisihan Dana Rapat Tahunan (RAT)', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek
            ['kode_akun' => 'PPN003', 'nama_akun' => 'Cadangan Aktiva Produktif (CAP)', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset / Liabilitas
            ['kode_akun' => 'PPN005', 'nama_akun' => 'Titipan Dana Kesejahteraan', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek
            ['kode_akun' => 'PPN009', 'nama_akun' => 'Titipan Tunjangan Pesangon Karyawan', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Panjang
            ['kode_akun' => 'PPN010', 'nama_akun' => 'Penyisihan Pemilihan Pengurus', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek

            // --- Dari Kategori: PENGELUARAN (Lama) --- (Sebagian Aset/Aktivitas)
            ['kode_akun' => 'PNG001', 'nama_akun' => 'Pinjaman Anggota', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset (Piutang)
            ['kode_akun' => 'PNG002', 'nama_akun' => 'Simpanan di Bank', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset (Kas/Bank)
            ['kode_akun' => 'PNG004', 'nama_akun' => 'Angsuran Pinjaman ke BPD', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Utang
            ['kode_akun' => 'PNG006', 'nama_akun' => 'Tarik Simpanan Pokok (SP)', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Ekuitas
            ['kode_akun' => 'PNG007', 'nama_akun' => 'Tarik Simpanan Wajib (SW)', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Ekuitas
            ['kode_akun' => 'PNG008', 'nama_akun' => 'Tarik Simpanan Wajib Penyertaan (SWP)', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Ekuitas?
            ['kode_akun' => 'PNG009', 'nama_akun' => 'Tarik Simpanan Sukarela (SS)', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG010', 'nama_akun' => 'Tarik Simpanan Non-Saham', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG011', 'nama_akun' => 'Tarik Jasa Simpanan Non-Saham', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG014', 'nama_akun' => 'Tarik Dana Pengurus', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG015', 'nama_akun' => 'Tarik Dana Karyawan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG017', 'nama_akun' => 'Tarik Dana Sosial', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG019', 'nama_akun' => 'Tarik Titipan Dana RAT', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG023', 'nama_akun' => 'Tarik Sisa Hasil Usaha (SHU)', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Ekuitas
            ['kode_akun' => 'PNG026', 'nama_akun' => 'Tarik Dana Kesejahteraan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas

            // --- Dari Kategori: BIAYA BIAYA (Lama) ---
            ['kode_akun' => 'BIA001', 'nama_akun' => 'Biaya Administrasi', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA002', 'nama_akun' => 'Biaya Organisasi', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA003', 'nama_akun' => 'Biaya Gaji Karyawan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA004', 'nama_akun' => 'Biaya Tunjangan Istri', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA005', 'nama_akun' => 'Biaya Tunjangan Anak', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA007', 'nama_akun' => 'Biaya IPTW (Insentif Pekerja Tidak Tetap Wajib)', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA008', 'nama_akun' => 'Biaya Insentif Pengurus dan Pengawas', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA009', 'nama_akun' => 'Biaya Koordinator', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Cocokkan dengan BIA009, bukan BIA028
            ['kode_akun' => 'BIA010', 'nama_akun' => 'Biaya Minum', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA011', 'nama_akun' => 'Biaya Penjaga/Kebersihan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA016', 'nama_akun' => 'Biaya Bensin', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA017', 'nama_akun' => 'Biaya Lembur', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA018', 'nama_akun' => 'Biaya Non-Saham', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA032', 'nama_akun' => 'Biaya Bunga Hutang Bank', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA033', 'nama_akun' => 'Biaya Administrasi/Pajak Bank', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA038', 'nama_akun' => 'Biaya Pemilihan Pengawas/Pengurus', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA040', 'nama_akun' => 'Biaya BPJS', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

            // --- Dari Kategori: PENYISIHAN BEBAN DANA (Lama) ---
            ['kode_akun' => 'PBD002', 'nama_akun' => 'Cadangan Aktiva Produktif (CAP)', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban (Ini duplikat nama dengan PPN003, tapi beda kode & kategori lama)
            ['kode_akun' => 'PBD003', 'nama_akun' => 'Biaya Penyisihan Dana RAT', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban
            ['kode_akun' => 'PBD004', 'nama_akun' => 'Penyisihan Dana Kesejahteraan', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban (Nama sama dengan PPN005, tapi beda kode & kategori lama)
            ['kode_akun' => 'PBD007', 'nama_akun' => 'Penyisihan Tunjangan Pensiun Karyawan', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban

            // --- Dari Kategori: PENYUSUTAN PENYUSUTAN (Lama) --- (Beban Penyusutan)
            ['kode_akun' => 'PNY002', 'nama_akun' => 'Biaya Penyusutan Inventaris Mebel', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan
            ['kode_akun' => 'PNY003', 'nama_akun' => 'Biaya Penyusutan Bangunan', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan
            ['kode_akun' => 'PNY005', 'nama_akun' => 'Biaya Penyusutan Komputer', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan

            // --- Dari Kategori: BIAYA PAJAK (Lama) ---
            ['kode_akun' => 'BJK001', 'nama_akun' => 'Biaya Pajak Listrik', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK005', 'nama_akun' => 'Biaya Pajak Wi-Fi', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

        ];

        // Menggunakan Query Builder untuk insert batch
        $this->db->table('akun')->insertBatch($data);

        // 3. Aktifkan kembali Foreign Key Checks
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // Optional: Tampilkan pesan di CLI saat seeder berjalan
        // echo "AkunSeeder executed successfully with updated names.\n";
    }
}
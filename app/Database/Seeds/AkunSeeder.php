<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run()
    {
        // Hapus data lama jika seeder dijalankan ulang (opsional tapi disarankan)
        $this->db->table('akun')->truncate();

        $now = date('Y-m-d H:i:s');

        $data = [
            // --- Kategori: PEMASUKAN ---
            ['kode_akun' => 'PEM001', 'nama_akun' => 'Uang Pangkal', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'PEM002', 'nama_akun' => 'S.P (Simpanan Pokok)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Ekuitas
            ['kode_akun' => 'PEM003', 'nama_akun' => 'S.W (Simpanan Wajib)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Ekuitas
            ['kode_akun' => 'PEM004', 'nama_akun' => 'S.W.P (Simpanan Wajib Pinjaman?)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Ekuitas/Liabilitas?
            ['kode_akun' => 'PEM005', 'nama_akun' => 'S.S (Simpanan Sukarela)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Liabilitas
            ['kode_akun' => 'PEM006', 'nama_akun' => 'S.Non Saham', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Liabilitas
            ['kode_akun' => 'PEM007', 'nama_akun' => 'S.Jasa Non Saham', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Seharusnya Liabilitas
            ['kode_akun' => 'PEM008', 'nama_akun' => 'Angsuran Pinjaman', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Arus Kas Masuk, bukan akun Neraca/L-R
            ['kode_akun' => 'PEM009', 'nama_akun' => 'Jasa Piutang', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM010', 'nama_akun' => 'Fe % (Profisi) 1%', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM011', 'nama_akun' => 'Jasa Deposito', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM012', 'nama_akun' => 'Titip Pajak (Js Non Shm)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas
            ['kode_akun' => 'PEM013', 'nama_akun' => 'Pinjaman dari BPD', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas
            ['kode_akun' => 'PEM014', 'nama_akun' => 'Fe Jasa Simpanan dr Bank', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM015', 'nama_akun' => 'Re (Resiko) 0,5%', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Ekuitas (Cadangan Resiko)
            ['kode_akun' => 'PEM016', 'nama_akun' => 'tTarik dr Bank', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Arus Kas Masuk, bukan akun Neraca/L-R
            ['kode_akun' => 'PEM017', 'nama_akun' => 'denda', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM018', 'nama_akun' => 'Fe (Perpanjangan BPKB)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM019', 'nama_akun' => 'Fe (Anggota Keluar)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM020', 'nama_akun' => 'Penyisihan Ttp Pjk Non Shm', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas/Ekuitas?
            ['kode_akun' => 'PEM021', 'nama_akun' => 'Modal Tetap', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Ekuitas (Pemupukan Modal?)
            ['kode_akun' => 'PEM022', 'nama_akun' => 'Pendapatan Lain-lain', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan
            ['kode_akun' => 'PEM023', 'nama_akun' => 'Hibah', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Ekuitas
            ['kode_akun' => 'PEM024', 'nama_akun' => 'Lain-lain (Pemasukan)', 'kategori' => 'PEMASUKAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Pendapatan

            // --- Kategori: AKUMULASI PENYUSUTAN ---
            ['kode_akun' => 'AKM001', 'nama_akun' => 'Akum. Penyusutan Brg Invet Mebeler', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset
            ['kode_akun' => 'AKM002', 'nama_akun' => 'Akum. Penyusutan Inventaris gedung', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset
            ['kode_akun' => 'AKM003', 'nama_akun' => 'Akum. Penyusutan Inventaris Spd Mtr', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset
            ['kode_akun' => 'AKM004', 'nama_akun' => 'Akum. Penyusutan Inventaris Komputer', 'kategori' => 'AKUMULASI PENYUSUTAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset

            // --- Kategori: PENYISIHAN PENYISIHAN ---
            ['kode_akun' => 'PPN001', 'nama_akun' => 'Penyisihan Tab Hari Tua Karyawan', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Panjang
            ['kode_akun' => 'PPN002', 'nama_akun' => 'Penyisihan Dana RAT', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek
            ['kode_akun' => 'PPN003', 'nama_akun' => 'CAP (Cad. Aktiva Produktif)', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Kontra Aset (Piutang) / Liabilitas
            ['kode_akun' => 'PPN004', 'nama_akun' => 'PJKR (Penyisihan Jasa Kredit Resiko?)', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Ekuitas (Cadangan) ?
            ['kode_akun' => 'PPN005', 'nama_akun' => 'Titip Dana Kesejahteraan', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek
            ['kode_akun' => 'PPN006', 'nama_akun' => 'Titip Dana RAT', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek/Panjang?
            ['kode_akun' => 'PPN007', 'nama_akun' => 'Titip Dana Pendampingan', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Panjang?
            ['kode_akun' => 'PPN008', 'nama_akun' => 'Titipan Penyisihan Pjk SHU', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek
            ['kode_akun' => 'PPN009', 'nama_akun' => 'Titipan Tunj. Pesangon Karyawan', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Panjang
            ['kode_akun' => 'PPN010', 'nama_akun' => 'Penyisihan Pemilihan Pengurus', 'kategori' => 'PENYISIHAN PENYISIHAN', 'jenis' => 'Kredit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Liabilitas Jk Pendek

            // --- Kategori: PENGELUARAN --- (Sebagian besar Aset atau Aktivitas)
            ['kode_akun' => 'PNG001', 'nama_akun' => 'Pinjaman Anggota', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset (Piutang)
            ['kode_akun' => 'PNG002', 'nama_akun' => 'Simpanan di Bank', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset (Kas/Bank)
            ['kode_akun' => 'PNG003', 'nama_akun' => 'Simpanan DEPOSITO', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset
            ['kode_akun' => 'PNG004', 'nama_akun' => 'Angsuran Pinjaman Ke BPD', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Utang
            ['kode_akun' => 'PNG005', 'nama_akun' => 'Tarik Simpanan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG006', 'nama_akun' => 'Tarik S.P', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Ekuitas
            ['kode_akun' => 'PNG007', 'nama_akun' => 'Tarik S.W', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Ekuitas
            ['kode_akun' => 'PNG008', 'nama_akun' => 'Tarik S.W.P', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Ekuitas?
            ['kode_akun' => 'PNG009', 'nama_akun' => 'Tarik S.S', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG010', 'nama_akun' => 'Tarik simp Non Shm', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG011', 'nama_akun' => 'Tarik Js Simp Non Shm', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Kas/Liabilitas
            ['kode_akun' => 'PNG012', 'nama_akun' => 'Tarik Titip (SP.SW.SWP.SS)', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG013', 'nama_akun' => 'Tarik DANA DANA', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG014', 'nama_akun' => 'Tarik Dana Pengurus', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG015', 'nama_akun' => 'Tarik Dana Karyawan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG016', 'nama_akun' => 'Tarik Dana Pendidikan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG017', 'nama_akun' => 'Tarik Dana Sosial', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG018', 'nama_akun' => 'Tarik Dana PDK', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG019', 'nama_akun' => 'Tarik Titipan Dana RAT', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG020', 'nama_akun' => 'Tarik Dana Resiko', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG021', 'nama_akun' => 'Tarik Dana Pemupukan Modal Tetap', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG022', 'nama_akun' => 'Tarik dana CAP', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG023', 'nama_akun' => 'Tarik SHU', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Ekuitas
            ['kode_akun' => 'PNG024', 'nama_akun' => 'Tarik THT', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG025', 'nama_akun' => 'Tarik Titipan Pajak', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas Pengurang Liabilitas
            ['kode_akun' => 'PNG026', 'nama_akun' => 'Tarik Dana Kesejahteraan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG027', 'nama_akun' => 'Tarik Dana Pendampingan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aktivitas
            ['kode_akun' => 'PNG028', 'nama_akun' => 'Pembelian Inventaris Mebeler', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset
            ['kode_akun' => 'PNG029', 'nama_akun' => 'Pembelian Proyektor', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset
            ['kode_akun' => 'PNG030', 'nama_akun' => 'Pembelian Inventaris Komputer', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset
            ['kode_akun' => 'PNG031', 'nama_akun' => 'Inventaris Gedung/Bangunan', 'kategori' => 'PENGELUARAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Aset

            // --- Kategori: BIAYA BIAYA ---
            ['kode_akun' => 'BIA001', 'nama_akun' => 'By. Administrasi', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA002', 'nama_akun' => 'By. Organisasi', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA003', 'nama_akun' => 'By. Gaji Karyawan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA004', 'nama_akun' => 'By. Tunjangan Istri', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA005', 'nama_akun' => 'By. Tunjangan Anak', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA006', 'nama_akun' => 'THR (Karyawan)', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA007', 'nama_akun' => 'By. IPTW', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA008', 'nama_akun' => 'By. Insentip Pengurus & Pengawas', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA009', 'nama_akun' => 'By. Koordinator', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA010', 'nama_akun' => 'By. Minum', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA011', 'nama_akun' => 'By. Penjaga/Kebersihan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA012', 'nama_akun' => 'By. THR (Penjaga)', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA013', 'nama_akun' => 'By. Har Kop / Besar Nasional', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA014', 'nama_akun' => 'By. Transpot', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA015', 'nama_akun' => 'By. Transpot Rapat', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA016', 'nama_akun' => 'By. Bensin', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA017', 'nama_akun' => 'By. Lembur', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA018', 'nama_akun' => 'By. Non Saham', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA019', 'nama_akun' => 'By. Perawatan Inventaris', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA020', 'nama_akun' => 'By. Kalender (Promosi)', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA021', 'nama_akun' => 'By. Pendidikan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA022', 'nama_akun' => 'By. Perawatan Inventaris Gedung', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA023', 'nama_akun' => 'By. Dana Sosial', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA024', 'nama_akun' => 'By. Bonus Pencapaian Target', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA025', 'nama_akun' => 'By. Kesejahteraan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA026', 'nama_akun' => 'By. Audit Pengawas', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA027', 'nama_akun' => 'By. Supervisi Pengurus', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA028', 'nama_akun' => 'By. Insentip Koordinator', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA029', 'nama_akun' => 'By. Syawalan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA030', 'nama_akun' => 'By. Bingkisan lebaran', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA031', 'nama_akun' => 'By. Seragam', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA032', 'nama_akun' => 'By. Bunga Hutang Bank', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA033', 'nama_akun' => 'By. Administrasi/pajak Bank', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA034', 'nama_akun' => 'By. Pesangon Purna Karyawan', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA036', 'nama_akun' => 'By. Modal Tetap', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Apakah ini biaya?
            ['kode_akun' => 'BIA037', 'nama_akun' => 'By. Denda Pajak', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA038', 'nama_akun' => 'By. Pemilihan Pengawas/Pengurus', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BIA040', 'nama_akun' => 'By. BPJS', 'kategori' => 'BIAYA BIAYA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

            // --- Kategori: PENYISIHAN BEBAN DANA ---
            ['kode_akun' => 'PBD001', 'nama_akun' => 'By. Penyisihan Tab. Pesangon Karyawan', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban
            ['kode_akun' => 'PBD002', 'nama_akun' => 'CAP (Cad. Aktiva Produktif) - Beban', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban
            ['kode_akun' => 'PBD003', 'nama_akun' => 'By. Penyisihan Dana RAT', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban
            ['kode_akun' => 'PBD004', 'nama_akun' => 'Penyisihan Dana Kesejahteraan - Beban', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban
            ['kode_akun' => 'PBD005', 'nama_akun' => 'Peyertaan Modal Tetap - Beban', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban?
            ['kode_akun' => 'PBD006', 'nama_akun' => 'Penyisihan Pendampingan - Beban', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban
            ['kode_akun' => 'PBD007', 'nama_akun' => 'Penyisihan Tunjangan Pensiun Karyawan - Beban', 'kategori' => 'PENYISIHAN BEBAN DANA', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban

            // --- Kategori: PENYUSUTAN PENYUSUTAN ---
            ['kode_akun' => 'PNY001', 'nama_akun' => 'By. Penyusutan Tertagih (Beban Pghtgn Piutang?)', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Pghpsn Piutang
            ['kode_akun' => 'PNY002', 'nama_akun' => 'By. Penyusutan Inventaris mebeler', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan
            ['kode_akun' => 'PNY003', 'nama_akun' => 'By. Penyusutan Bangunan', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan
            ['kode_akun' => 'PNY004', 'nama_akun' => 'By. Penyusutan Inventaris Motor', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan
            ['kode_akun' => 'PNY005', 'nama_akun' => 'By. Penyusutan Komputer', 'kategori' => 'PENYUSUTAN PENYUSUTAN', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now], // Beban Penyusutan

            // --- Kategori: BIAYA PAJAK ---
            ['kode_akun' => 'BJK001', 'nama_akun' => 'By. Pajak Listrik', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK002', 'nama_akun' => 'By. Pajak PPH', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK003', 'nama_akun' => 'By. Pajak PBB', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK004', 'nama_akun' => 'By. Pajak SHU', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK005', 'nama_akun' => 'By. Pajak Wifi', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK006', 'nama_akun' => 'By. Pajak Jasa dr Simpanan di Bank', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],
            ['kode_akun' => 'BJK007', 'nama_akun' => 'By. Pajak Kendaraan', 'kategori' => 'BIAYA PAJAK', 'jenis' => 'Debit', 'saldo_awal' => 0.00, 'created_at' => $now, 'updated_at' => $now],

        ];

        // Menggunakan Query Builder untuk insert batch
        $this->db->table('akun')->insertBatch($data);

        // Optional: Tampilkan pesan di CLI saat seeder berjalan
        // echo "AkunSeeder executed successfully.\n";
    }
}
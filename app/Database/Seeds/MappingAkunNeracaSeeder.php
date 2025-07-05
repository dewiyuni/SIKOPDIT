<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MappingAkunNeracaSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            // ASET LANCAR
            ['nama_laporan' => 'Kas', 'id_akun_utama' => 1, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Simpanan di Bank', 'id_akun_utama' => 2, 'id_akun_pengurang' => 9, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Simpanan Deposito', 'id_akun_utama' => 3, 'id_akun_pengurang' => 50, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Piutang Biasa', 'id_akun_utama' => 62, 'id_akun_pengurang' => 24, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Piutang Khusus', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Piutang Ragu-ragu', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Penyusutan Piutang Ragu', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET LANCAR', 'urutan' => 7, 'created_at' => $now, 'updated_at' => $now],

            // ASET TAK LANCAR
            ['nama_laporan' => 'Simpanan di BK3D', 'id_akun_utama' => 21, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TAK LANCAR', 'urutan' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Investasi', 'id_akun_utama' => 22, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TAK LANCAR', 'urutan' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Serta Data', 'id_akun_utama' => 23, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TAK LANCAR', 'urutan' => 10, 'created_at' => $now, 'updated_at' => $now],

            // ASET TETAP: Mebel + Akumulasi
            ['nama_laporan' => 'Inventaris Barang Mebel', 'id_akun_utama' => 13, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Akumulasi Penyusutan Inventaris Mebel', 'id_akun_utama' => 5, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Sub Mebel', 'id_akun_utama' => 13, 'id_akun_pengurang' => 5, 'tipe' => 'sub', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 13, 'created_at' => $now, 'updated_at' => $now],

            // Beban tertangguh + akumulasi
            ['nama_laporan' => 'Beban Tertangguh', 'id_akun_utama' => 149, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 14, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Akumulasi Penyusutan Tertangguh', 'id_akun_utama' => 8, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 15, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Sub Tertangguh', 'id_akun_utama' => 149, 'id_akun_pengurang' => 8, 'tipe' => 'sub', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 16, 'created_at' => $now, 'updated_at' => $now],

            // Gedung + akumulasi
            ['nama_laporan' => 'Inventaris Gedung/Bangunan', 'id_akun_utama' => 14, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 17, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Akumulasi Penyusutan Inventaris Gedung', 'id_akun_utama' => 6, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 18, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Inventaris Pagar', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 19, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Sub Gedung', 'id_akun_utama' => 14, 'id_akun_pengurang' => 6, 'tipe' => 'sub', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 20, 'created_at' => $now, 'updated_at' => $now],

            // Tanah + akumulasi
            ['nama_laporan' => 'Inventaris Tanah', 'id_akun_utama' => 188, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 23, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Akumulasi Penyusutan Inventaris Tanah', 'id_akun_utama' => 189, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 24, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Sub Tanah', 'id_akun_utama' => 188, 'id_akun_pengurang' => 189, 'tipe' => 'sub', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 25, 'created_at' => $now, 'updated_at' => $now],


            // Komputer + akumulasi
            ['nama_laporan' => 'Inventaris Komputer', 'id_akun_utama' => 15, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 26, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Akumulasi Penyusutan Inventaris Komputer', 'id_akun_utama' => 4, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 27, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Sub Komputer', 'id_akun_utama' => 15, 'id_akun_pengurang' => 4, 'tipe' => 'sub', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 28, 'created_at' => $now, 'updated_at' => $now],

            // Kendaraan + akumulasi
            ['nama_laporan' => 'Inventaris Kendaraan', 'id_akun_utama' => 16, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 29, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Akumulasi Penyusutan Inventaris Kendaraan', 'id_akun_utama' => 7, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Sub Kendaraan', 'id_akun_utama' => 16, 'id_akun_pengurang' => 7, 'tipe' => 'sub', 'jenis' => 'AKTIVA', 'kategori_jenis' => 'ASET TETAP', 'urutan' => 31, 'created_at' => $now, 'updated_at' => $now],

            // KEWAJIBAN JANGKA PENDEK
            ['nama_laporan' => 'Simpanan Non Saham', 'id_akun_utama' => 34, 'id_akun_pengurang' => 68, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Simpanan Jasa Non Saham', 'id_akun_utama' => 35, 'id_akun_pengurang' => 69, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Simpanan Sukarela', 'id_akun_utama' => 37, 'id_akun_pengurang' => 67, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Dana', 'id_akun_utama' => 162, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Pengurus', 'id_akun_utama' => 163, 'id_akun_pengurang' => 71, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Pendidikan', 'id_akun_utama' => 164, 'id_akun_pengurang' => 74, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Karyawan', 'id_akun_utama' => 165, 'id_akun_pengurang' => 73, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana PDK', 'id_akun_utama' => 56, 'id_akun_pengurang' => 76, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Sosial', 'id_akun_utama' => 57, 'id_akun_pengurang' => 75, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Insentif', 'id_akun_utama' => 166, 'id_akun_pengurang' => 150, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Supervisi', 'id_akun_utama' => 167, 'id_akun_pengurang' => 143, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Beban yang Masih Harus Dibayar', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana RAT', 'id_akun_utama' => 125, 'id_akun_pengurang' => 77, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 13, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Kesejahteraan', 'id_akun_utama' => 40, 'id_akun_pengurang' => 84, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 14, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana SHU Tahun Lalu', 'id_akun_utama' => 171, 'id_akun_pengurang' => 81, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 15, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan Pemilihan Pengurus', 'id_akun_utama' => 31, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 16, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'SHU Tahun Sekarang', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 17, 'created_at' => $now, 'updated_at' => $now],

            // KEWAJIBAN JANGKA PANJANG
            ['nama_laporan' => 'Dana Sehat', 'id_akun_utama' => 174, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 18, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan Simpanan Pokok/Simpanan Wajib', 'id_akun_utama' => 175, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 19, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan Dana-Dana', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan CAP', 'id_akun_utama' => 25, 'id_akun_pengurang' => 80, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 21, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan Dana RAT', 'id_akun_utama' => 42, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 22, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan Biaya Pajak', 'id_akun_utama' => 178, 'id_akun_pengurang' => 83, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 23, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Titipan Dana Pendamping', 'id_akun_utama' => 43, 'id_akun_pengurang' => 86, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 24, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Pemupukan Modal Tetap', 'id_akun_utama' => 179, 'id_akun_pengurang' => 79, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 25, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Tabungan Pesangon Karyawan', 'id_akun_utama' => 41, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 26, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Pinjaman Pihak Ke 2', 'id_akun_utama' => 181, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 27, 'created_at' => $now, 'updated_at' => $now],

            // EKUITAS / MODAL
            ['nama_laporan' => 'Simpanan Pokok', 'id_akun_utama' => 36, 'id_akun_pengurang' => 64, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 28, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Simpanan Wajib', 'id_akun_utama' => 38, 'id_akun_pengurang' => 65, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 29, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Simpanan SWP', 'id_akun_utama' => 39, 'id_akun_pengurang' => 66, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Iuran Dana Sehat', 'id_akun_utama' => 182, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 31, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Hibah', 'id_akun_utama' => 46, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 33, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Cadangan Likuiditas', 'id_akun_utama' => 183, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 33, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Cadangan Koperasi', 'id_akun_utama' => 184, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 34, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'Dana Risiko', 'id_akun_utama' => 33, 'id_akun_pengurang' => 78, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 35, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'PJKR', 'id_akun_utama' => 186, 'id_akun_pengurang' => 145, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 36, 'created_at' => $now, 'updated_at' => $now],
            ['nama_laporan' => 'SHU', 'id_akun_utama' => 187, 'id_akun_pengurang' => 0, 'tipe' => 'normal', 'jenis' => 'PASIVA', 'kategori_jenis' => 'EKUITAS', 'urutan' => 37, 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('mapping_akun_neraca')->insertBatch($data);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MappingAkunNeracaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ASET LANCAR
            ['nama_laporan' => 'Kas', 'id_akun_utama' => 1, 'id_akun_pengurang' => 2, 'jenis' => 'AKTIVA', 'urutan' => 1],
            ['nama_laporan' => 'Simpanan di Bank', 'id_akun_utama' => 2, 'id_akun_pengurang' => 9, 'jenis' => 'AKTIVA', 'urutan' => 2],
            ['nama_laporan' => 'Simpanan Deposito', 'id_akun_utama' => 3, 'id_akun_pengurang' => 50, 'jenis' => 'AKTIVA', 'urutan' => 3],
            ['nama_laporan' => 'Piutang Biasa', 'id_akun_utama' => 62, 'id_akun_pengurang' => 24, 'jenis' => 'AKTIVA', 'urutan' => 4],
            ['nama_laporan' => 'Piutang Khusus', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 5],
            ['nama_laporan' => 'Piutang Ragu-ragu', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 6],
            ['nama_laporan' => 'Penyusutan Piutang Ragu', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 7],

            // ASET TAK LANCAR
            ['nama_laporan' => 'Simpanan di BK3D', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 8],
            ['nama_laporan' => 'Investasi', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 9],
            ['nama_laporan' => 'Serta Data', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 10],

            // ASET TETAP: Mebel + Akumulasi
            ['nama_laporan' => 'Inventaris Barang Mebeler', 'id_akun_utama' => 13, 'id_akun_pengurang' => 5, 'jenis' => 'AKTIVA', 'urutan' => 11],
            // Beban tertangguh + akumulasi
            ['nama_laporan' => 'Beban Tertangguh', 'id_akun_utama' => 149, 'id_akun_pengurang' => 8, 'jenis' => 'AKTIVA', 'urutan' => 12],
            // Gedung + akumulasi
            ['nama_laporan' => 'Inventaris Gedung/Bangunan', 'id_akun_utama' => 14, 'id_akun_pengurang' => 6, 'jenis' => 'AKTIVA', 'urutan' => 13],
            // Pagar
            ['nama_laporan' => 'Inventaris Pagar', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 14],
            // Tanah + akumulasi
            ['nama_laporan' => 'Inventaris Tanah', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'AKTIVA', 'urutan' => 15],
            // Komputer + akumulasi
            ['nama_laporan' => 'Inventaris Komputer', 'id_akun_utama' => 15, 'id_akun_pengurang' => 4, 'jenis' => 'AKTIVA', 'urutan' => 16],
            // Kendaraan + akumulasi
            ['nama_laporan' => 'Inventaris Kendaraan', 'id_akun_utama' => 16, 'id_akun_pengurang' => 7, 'jenis' => 'AKTIVA', 'urutan' => 17],

            // KEWAJIBAN JANGKA PENDEK
            ['nama_laporan' => 'Simpanan Non Saham', 'id_akun_utama' => 34, 'id_akun_pengurang' => 68, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 1],
            ['nama_laporan' => 'Simpanan Jasa Non Saham', 'id_akun_utama' => 35, 'id_akun_pengurang' => 69, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 2],
            ['nama_laporan' => 'Simpanan Sukarela', 'id_akun_utama' => 37, 'id_akun_pengurang' => 67, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 3],
            ['nama_laporan' => 'Dana Dana', 'id_akun_utama' => 162, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 4],
            ['nama_laporan' => 'Dana Pengurus', 'id_akun_utama' => 163, 'id_akun_pengurang' => 71, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 5],
            ['nama_laporan' => 'Dana Pendidikan', 'id_akun_utama' => 164, 'id_akun_pengurang' => 74, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 6],
            ['nama_laporan' => 'Dana Karyawan', 'id_akun_utama' => 165, 'id_akun_pengurang' => 73, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 7],
            ['nama_laporan' => 'Dana PDK', 'id_akun_utama' => 56, 'id_akun_pengurang' => 76, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 8],
            ['nama_laporan' => 'Dana Sosial', 'id_akun_utama' => 57, 'id_akun_pengurang' => 75, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 9],
            ['nama_laporan' => 'Dana Insentif', 'id_akun_utama' => 166, 'id_akun_pengurang' => 150, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 10],
            ['nama_laporan' => 'Dana Supervisi', 'id_akun_utama' => 167, 'id_akun_pengurang' => 143, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 11],
            ['nama_laporan' => 'Beban yang Masih Harus Dibayar', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 12],
            ['nama_laporan' => 'Dana RAT', 'id_akun_utama' => 169, 'id_akun_pengurang' => 144, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 13],
            ['nama_laporan' => 'Dana Kesejahteraan', 'id_akun_utama' => 40, 'id_akun_pengurang' => 84, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 14],
            ['nama_laporan' => 'Dana SHU Tahun Lalu', 'id_akun_utama' => 171, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 15],
            ['nama_laporan' => 'Titipan Pemilihan Pengurus', 'id_akun_utama' => 31, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 16],
            ['nama_laporan' => 'SHU Tahun Sekarang', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 17],

            // KEWAJIBAN JANGKA PANJANG
            ['nama_laporan' => 'Dana Sehat', 'id_akun_utama' => 174, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 18],
            ['nama_laporan' => 'Titipan Simpanan Pokok/Simpanan Wajib', 'id_akun_utama' => 175, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 19],
            ['nama_laporan' => 'Titipan Dana-Dana', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 20],
            ['nama_laporan' => 'Titipan CAP', 'id_akun_utama' => 25, 'id_akun_pengurang' => 80, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 21],
            ['nama_laporan' => 'Titipan Dana RAT', 'id_akun_utama' => 42, 'id_akun_pengurang' => 77, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 22],
            ['nama_laporan' => 'Titipan Biaya Pajak', 'id_akun_utama' => 178, 'id_akun_pengurang' => 83, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 23],
            ['nama_laporan' => 'Titipan Dana Pendamping', 'id_akun_utama' => 43, 'id_akun_pengurang' => 86, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 24],
            ['nama_laporan' => 'Pemupukan Modal Tetap', 'id_akun_utama' => 11, 'id_akun_pengurang' => 79, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 25],
            ['nama_laporan' => 'Tabungan Pesangon Karyawan', 'id_akun_utama' => 41, 'id_akun_pengurang' => 123, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 26],
            ['nama_laporan' => 'Pinjaman Pihak Ke 2', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 27],

            // EKUITAS / MODAL
            ['nama_laporan' => 'Simpanan Pokok', 'id_akun_utama' => 36, 'id_akun_pengurang' => 64, 'jenis' => 'EKUITAS', 'urutan' => 28],
            ['nama_laporan' => 'Simpanan Wajib', 'id_akun_utama' => 38, 'id_akun_pengurang' => 65, 'jenis' => 'EKUITAS', 'urutan' => 29],
            ['nama_laporan' => 'Simpanan SWP', 'id_akun_utama' => 39, 'id_akun_pengurang' => 66, 'jenis' => 'EKUITAS', 'urutan' => 30],
            ['nama_laporan' => 'Iuran Dana Sehat', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'EKUITAS', 'urutan' => 31],
            ['nama_laporan' => 'Hibah', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'EKUITAS', 'urutan' => 33],
            ['nama_laporan' => 'Cadangan Likuiditas', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'EKUITAS', 'urutan' => 33],
            ['nama_laporan' => 'Cadangan Koperasi', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'EKUITAS', 'urutan' => 34],
            ['nama_laporan' => 'Dana Risiko', 'id_akun_utama' => 33, 'id_akun_pengurang' => 78, 'jenis' => 'EKUITAS', 'urutan' => 35],
            ['nama_laporan' => 'PJKR', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'EKUITAS', 'urutan' => 36],
            ['nama_laporan' => 'SHU', 'id_akun_utama' => 0, 'id_akun_pengurang' => 0, 'jenis' => 'EKUITAS', 'urutan' => 37],
        ];

        $this->db->table('mapping_akun_neraca')->insertBatch($data);
    }
}

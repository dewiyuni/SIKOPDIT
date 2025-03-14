<?php

namespace App\Models;

use CodeIgniter\Model;

class AnggotaModel extends Model
{
    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';

    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $allowedFields = ['no_ba', 'nama', 'nik', 'dusun', 'alamat', 'pekerjaan', 'tgl_lahir', 'nama_pasangan', 'status', 'created_at', 'updated_at'];

    public function insertAnggota($data)
    {
        $db = \Config\Database::connect();
        $db->transStart(); // Mulai transaksi

        // Simpan data anggota
        if (!$this->insert($data)) {
            $db->transRollback(); // Batalkan jika gagal
            return false;
        }

        $id_anggota = $this->insertID(); // Ambil ID terakhir yang baru dibuat

        if ($id_anggota) {
            // Data transaksi awal pendaftaran
            $simpananData = [
                'id_anggota' => $id_anggota,
                'tanggal' => date('Y-m-d'),
                'setor_pangkal' => 10000,   // Uang pangkal
                'setor_penyertaan' => 75000, // Uang penyertaan
                'setor_pokok' => 10000,     // Simpanan pokok
                'setor_wajib' => 5000,      // Simpanan wajib (bulan pertama)
                'saldo_pangkal' => 10000,
                'saldo_penyertaan' => 75000,
                'saldo_pokok' => 10000,
                'saldo_wajib' => 5000,
                'saldo_total' => 100000,    // Total awal = 100.000
                'keterangan' => 'Saldo awal pendaftaran'
            ];

            if (!$db->table('transaksi_simpanan')->insert($simpananData)) {
                $db->transRollback(); // Jika gagal, batalkan semua
                return false;
            }

            // Buat transaksi bulanan pertama (5.000 simpanan wajib)
            $bulan_ini = date('Y-m-01'); // Set tanggal ke awal bulan
            $simpananBulanan = [
                'id_anggota' => $id_anggota,
                'tanggal' => $bulan_ini,
                'setor_wajib' => 5000,
                'saldo_wajib' => 10000, // Simpanan wajib awal (5k dari pendaftaran + 5k bulan ini)
                'saldo_total' => 105000, // Saldo bertambah jadi 105.000
                'keterangan' => 'Simpanan wajib bulan pertama'
            ];

            if (!$db->table('transaksi_simpanan')->insert($simpananBulanan)) {
                $db->transRollback();
                return false;
            }
        }

        $db->transComplete(); // Selesaikan transaksi
        return $db->transStatus() ? $id_anggota : false;
    }



    public function getAnggotaWithTransaksi()
    {
        return $this->select('anggota.*, COALESCE(SUM(transaksi_simpanan.saldo_total), 0) as total_transaksi')
            ->join('transaksi_simpanan', 'transaksi_simpanan.id_anggota = anggota.id_anggota', 'left')
            ->groupBy('anggota.id_anggota')
            ->findAll();
    }
}

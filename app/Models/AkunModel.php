<?php

namespace App\Models;

use CodeIgniter\Model;

class AkunModel extends Model
{
    protected $table = 'akun'; // Pastikan nama tabel benar 'akun' bukan 'coa_akun'
    protected $primaryKey = 'id'; // Pastikan primary key benar 'id' bukan 'Utama'
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    // Sesuaikan allowedFields dengan kolom di tabel 'akun' Anda
    protected $allowedFields = ['kode_akun', 'nama_akun', 'kategori', 'jenis', 'saldo_awal'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Removed duplicate method declaration to avoid redeclaration error.

    // Method ini mungkin tidak diperlukan lagi jika Anda selalu filter by kategori di view utama
    // public function getAkunByKategori($kategori)
    // {
    //     return $this->where('kategori', $kategori)->orderBy('kode_akun', 'ASC')->findAll();
    // }

    /**
     * Mengambil daftar kategori unik dari tabel akun.
     *
     * @return array Daftar kategori unik.
     */
    // public function getDistinctKategori()
    // {
    //     return $this->distinct()->select('kategori')->orderBy('kategori', 'ASC')->findAll();
    // }

    public function getAkunByKode($kode)
    {
        return $this->where('kode_akun', $kode)->first();
    }

    public function getDistinctKategori()
    {
        // Pastikan kolom 'kategori' tidak null atau kosong jika memungkinkan
        return $this->distinct()
            ->select('kategori')
            ->where('kategori IS NOT NULL')
            ->where('kategori !=', '')
            ->orderBy('kategori', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil akun beserta saldo untuk kategori, bulan, dan tahun tertentu.
     * Saldo akhir dihitung ulang berdasarkan jenis akun.
     *
     * @param string $kategori Nama kategori akun.
     * @param int    $bulan    Bulan (1-12).
     * @param int    $tahun    Tahun (YYYY).
     * @return array Daftar akun dalam kategori beserta saldo.
     */
    public function getAkunWithSaldoByKategori($kategori, $bulan, $tahun)
    {
        $db = \Config\Database::connect();

        // Bangun query SQL dengan perhitungan saldo akhir dinamis
        $sql = "
            SELECT
                a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis, a.saldo_awal,
                COALESCE(sa.saldo_awal, a.saldo_awal) as saldo_bulan_ini,
                COALESCE(sa.total_debit, 0) as total_debit,
                COALESCE(sa.total_kredit, 0) as total_kredit,
                -- Hitung ulang saldo akhir berdasarkan jenis akun
                CASE
                    WHEN LOWER(a.jenis) = 'debit' THEN
                        (COALESCE(sa.saldo_awal, a.saldo_awal) + COALESCE(sa.total_debit, 0) - COALESCE(sa.total_kredit, 0))
                    WHEN LOWER(a.jenis) = 'kredit' THEN
                        (COALESCE(sa.saldo_awal, a.saldo_awal) - COALESCE(sa.total_debit, 0) + COALESCE(sa.total_kredit, 0))
                    ELSE -- Default jika jenis tidak 'Debit' atau 'Kredit' (sebaiknya dihindari)
                        (COALESCE(sa.saldo_awal, a.saldo_awal) + COALESCE(sa.total_debit, 0) - COALESCE(sa.total_kredit, 0))
                END AS saldo_akhir
            FROM
                akun a
            LEFT JOIN
                saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            WHERE
                a.kategori = ?
            ORDER BY
                a.kode_akun ASC
        ";

        $query = $db->query($sql, [$bulan, $tahun, $kategori]);

        return $query->getResultArray();
    }

    // Fungsi getAkunWithSaldo bisa diupdate serupa jika digunakan di tempat lain
    public function getAkunWithSaldo($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $sql = "
            SELECT
                a.id, a.kode_akun, a.nama_akun, a.kategori, a.jenis, a.saldo_awal,
                COALESCE(sa.saldo_awal, a.saldo_awal) as saldo_bulan_ini,
                COALESCE(sa.total_debit, 0) as total_debit,
                COALESCE(sa.total_kredit, 0) as total_kredit,
                 -- Hitung ulang saldo akhir berdasarkan jenis akun
                CASE
                    WHEN LOWER(a.jenis) = 'debit' THEN
                        (COALESCE(sa.saldo_awal, a.saldo_awal) + COALESCE(sa.total_debit, 0) - COALESCE(sa.total_kredit, 0))
                    WHEN LOWER(a.jenis) = 'kredit' THEN
                        (COALESCE(sa.saldo_awal, a.saldo_awal) - COALESCE(sa.total_debit, 0) + COALESCE(sa.total_kredit, 0))
                    ELSE
                        (COALESCE(sa.saldo_awal, a.saldo_awal) + COALESCE(sa.total_debit, 0) - COALESCE(sa.total_kredit, 0))
                END AS saldo_akhir
            FROM
                akun a
            LEFT JOIN
                saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
            ORDER BY
                a.kode_akun ASC
        ";
        $query = $db->query($sql, [$bulan, $tahun]);
        return $query->getResultArray();
    }
    // Fungsi getAkunWithSaldo yang lama mungkin tidak terpakai di view index utama,
    // tapi bisa berguna untuk laporan lain. Saya biarkan saja.
    // public function getAkunWithSaldo($bulan, $tahun)
    // {
    //     $db = \Config\Database::connect();
    //     $query = $db->query("
    //         SELECT
    //             a.*,
    //             COALESCE(sa.saldo_awal, a.saldo_awal) as saldo_bulan_ini,
    //             COALESCE(sa.total_debit, 0) as total_debit,
    //             COALESCE(sa.total_kredit, 0) as total_kredit,
    //             COALESCE(sa.saldo_akhir, a.saldo_awal) as saldo_akhir
    //         FROM
    //             akun a
    //         LEFT JOIN
    //             saldo_akun sa ON a.id = sa.id_akun AND sa.bulan = ? AND sa.tahun = ?
    //         ORDER BY
    //             a.kode_akun ASC
    //     ", [$bulan, $tahun]);

    //     return $query->getResultArray();
    // }

    // Fungsi getLastSaldo tetap sama
    private function getLastSaldo($idAkun, $tanggal)
    {
        // ... (kode getLastSaldo tetap sama) ...
        $db = \Config\Database::connect();

        // Cari saldo terakhir sebelum tanggal ini
        $query = $db->query("
        SELECT saldo
        FROM buku_besar
        WHERE id_akun = ? AND tanggal <= ?
        ORDER BY tanggal DESC, id DESC
        LIMIT 1
        ", [$idAkun, $tanggal]);

        $result = $query->getRow();

        if ($result) {
            log_message('debug', "Saldo terakhir ditemukan untuk akun {$idAkun}: {$result->saldo}");
            return $result->saldo;
        } else {
            // Jika tidak ada, ambil saldo awal
            // $akunModel = new \App\Models\AkunModel(); // Tidak perlu instance baru jika dipanggil dari dalam model
            $akun = $this->find($idAkun);
            $saldoAwal = $akun ? $akun['saldo_awal'] : 0;
            log_message('debug', "Menggunakan saldo awal untuk akun {$idAkun}: {$saldoAwal}");
            return $saldoAwal;
        }
    }
}
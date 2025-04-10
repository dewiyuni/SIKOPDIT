<?php

namespace App\Models;

use CodeIgniter\Model;

class BukuBesarModel extends Model
{
    protected $table = 'buku_besar';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['tanggal', 'id_akun', 'id_jurnal', 'keterangan', 'debit', 'kredit', 'saldo'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getBukuBesarByAkun($idAkun, $bulan = null, $tahun = null)
    {
        $builder = $this->select('buku_besar.*, akun.kode_akun, akun.nama_akun')
            ->join('akun', 'akun.id = buku_besar.id_akun')
            ->where('buku_besar.id_akun', $idAkun)
            ->orderBy('buku_besar.tanggal', 'ASC');

        if ($bulan !== null && $tahun !== null) {
            $builder->where('MONTH(buku_besar.tanggal)', $bulan)
                ->where('YEAR(buku_besar.tanggal)', $tahun);
        }

        return $builder->findAll();
    }

    public function getSaldoAwalAkun($idAkun, $bulan, $tahun)
    {
        try {
            $db = \Config\Database::connect();

            // Jika bulan Januari, ambil saldo awal dari tabel akun
            if ($bulan == 1) {
                $query = $db->query("
                    SELECT saldo_awal FROM akun WHERE id = ?
                ", [$idAkun]);

                $result = $query->getRow();
                return $result ? floatval($result->saldo_awal) : 0;
            }

            // Jika bukan Januari, ambil saldo akhir bulan sebelumnya
            $prevMonth = $bulan - 1;
            $prevYear = $tahun;

            $query = $db->query("
                SELECT saldo_akhir 
                FROM saldo_akun 
                WHERE id_akun = ? AND bulan = ? AND tahun = ?
            ", [$idAkun, $prevMonth, $prevYear]);

            $result = $query->getRow();

            if ($result) {
                return floatval($result->saldo_akhir);
            } else {
                // Jika tidak ada saldo bulan sebelumnya, cari saldo terakhir yang ada
                $query = $db->query("
                    SELECT saldo_akhir 
                    FROM saldo_akun 
                    WHERE id_akun = ? AND (tahun < ? OR (tahun = ? AND bulan < ?))
                    ORDER BY tahun DESC, bulan DESC
                    LIMIT 1
                ", [$idAkun, $tahun, $tahun, $bulan]);

                $result = $query->getRow();

                if ($result) {
                    return floatval($result->saldo_akhir);
                } else {
                    // Jika tidak ada sama sekali, ambil saldo awal dari tabel akun
                    $query = $db->query("
                        SELECT saldo_awal FROM akun WHERE id = ?
                    ", [$idAkun]);

                    $result = $query->getRow();
                    return $result ? floatval($result->saldo_awal) : 0;
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error pada getSaldoAwalAkun: " . $e->getMessage());
            return 0;
        }
    }


    public function updateSaldoAkun($idAkun, $bulan, $tahun)
    {
        try {
            $db = \Config\Database::connect();

            // Ambil saldo awal
            $saldoAwal = $this->getSaldoAwalAkun($idAkun, $bulan, $tahun);

            // Hitung total debit dan kredit untuk bulan ini
            $query = $db->query("
                SELECT 
                    SUM(debit) as total_debit, 
                    SUM(kredit) as total_kredit 
                FROM buku_besar 
                WHERE id_akun = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
            ", [$idAkun, $bulan, $tahun]);

            $result = $query->getRow();
            $totalDebit = $result ? floatval($result->total_debit) : 0;
            $totalKredit = $result ? floatval($result->total_kredit) : 0;

            // Ambil informasi jenis akun
            $akunModel = new \App\Models\AkunModel();
            $akun = $akunModel->find($idAkun);

            if (!$akun) {
                log_message('error', "Akun dengan ID $idAkun tidak ditemukan");
                return false;
            }

            $jenisAkun = $akun['jenis'];

            // Hitung saldo akhir berdasarkan jenis akun
            if ($jenisAkun == 'Debit') {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }

            log_message('debug', "Akun $idAkun: Saldo Awal=$saldoAwal, Debit=$totalDebit, Kredit=$totalKredit, Saldo Akhir=$saldoAkhir");

            // Update atau insert ke tabel saldo_akun
            $checkQuery = $db->query("
                SELECT id FROM saldo_akun WHERE id_akun = ? AND bulan = ? AND tahun = ?
            ", [$idAkun, $bulan, $tahun]);

            $checkResult = $checkQuery->getRow();

            if ($checkResult) {
                // Update
                $db->query("
                    UPDATE saldo_akun 
                    SET saldo_awal = ?, total_debit = ?, total_kredit = ?, saldo_akhir = ?, updated_at = NOW()
                    WHERE id = ?
                ", [$saldoAwal, $totalDebit, $totalKredit, $saldoAkhir, $checkResult->id]);
            } else {
                // Insert
                $db->query("
                    INSERT INTO saldo_akun (id_akun, bulan, tahun, saldo_awal, total_debit, total_kredit, saldo_akhir)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$idAkun, $bulan, $tahun, $saldoAwal, $totalDebit, $totalKredit, $saldoAkhir]);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', "Error pada updateSaldoAkun: " . $e->getMessage());
            return false;
        }
    }


    public function prosesJurnalKeBukuBesar($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $jurnalModel = new \App\Models\JurnalKasModel();
        $pemetaanModel = new \App\Models\PemetaanAkunModel();

        // Format bulan untuk query
        $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Ambil semua jurnal untuk bulan dan tahun yang dipilih
        $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        // Mulai transaksi database
        $db->transStart();

        // Hapus entri buku besar yang sudah ada untuk bulan ini (opsional)
        $db->query("
            DELETE FROM buku_besar 
            WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
        ", [$bulan, $tahun]);

        foreach ($jurnal as $j) {
            // Cari pemetaan akun berdasarkan kategori dan uraian
            $pemetaan = $pemetaanModel->where('kategori_jurnal', $j['kategori'])
                ->where('uraian_jurnal', $j['uraian'])
                ->first();

            if (!$pemetaan) {
                // Jika tidak ada pemetaan spesifik, cari pemetaan default untuk kategori
                $pemetaan = $pemetaanModel->where('kategori_jurnal', $j['kategori'])
                    ->where('uraian_jurnal', 'default')
                    ->first();

                if (!$pemetaan) {
                    // Jika masih tidak ada, gunakan akun default
                    if ($j['kategori'] == 'DUM') {
                        $idAkunDebit = 1; // Kas
                        $idAkunKredit = 30; // Pendapatan Lain-lain
                    } else {
                        $idAkunDebit = 40; // Beban Operasional Lainnya
                        $idAkunKredit = 1; // Kas
                    }
                } else {
                    $idAkunDebit = $pemetaan['id_akun_debit'];
                    $idAkunKredit = $pemetaan['id_akun_kredit'];
                }
            } else {
                $idAkunDebit = $pemetaan['id_akun_debit'];
                $idAkunKredit = $pemetaan['id_akun_kredit'];
            }

            $tanggal = $j['tanggal'];
            $keterangan = $j['uraian'];
            $jumlah = $j['jumlah'];

            // Buat entri untuk akun debit
            if ($idAkunDebit) {
                // Ambil saldo terakhir
                $lastSaldo = $this->getLastSaldo($idAkunDebit, $tanggal);
                $saldoBaru = $lastSaldo + $jumlah;

                $this->insert([
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunDebit,
                    'id_jurnal' => $j['id'],
                    'keterangan' => $keterangan,
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'saldo' => $saldoBaru
                ]);
            }

            // Buat entri untuk akun kredit
            if ($idAkunKredit) {
                // Ambil saldo terakhir
                $lastSaldo = $this->getLastSaldo($idAkunKredit, $tanggal);
                $saldoBaru = $lastSaldo - $jumlah;

                $this->insert([
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunKredit,
                    'id_jurnal' => $j['id'],
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'saldo' => $saldoBaru
                ]);
            }
        }

        // Update saldo semua akun
        $akunModel = new \App\Models\AkunModel();
        $allAkun = $akunModel->findAll();

        foreach ($allAkun as $akun) {
            $this->updateSaldoAkun($akun['id'], $bulan, $tahun);
        }

        $db->transComplete();

        return $db->transStatus();
    }

    private function getLastSaldo($idAkun, $tanggal)
    {
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
            $akunModel = new \App\Models\AkunModel();
            $akun = $akunModel->find($idAkun);
            $saldoAwal = $akun ? $akun['saldo_awal'] : 0;
            log_message('debug', "Menggunakan saldo awal untuk akun {$idAkun}: {$saldoAwal}");
            return $saldoAwal;
        }
    }

    public function buatPemetaanOtomatis()
    {
        $jurnalModel = new \App\Models\JurnalKasModel();
        $pemetaanModel = new \App\Models\PemetaanAkunModel();

        // Ambil semua uraian unik dari jurnal
        $dumUraian = $jurnalModel->select('uraian')->where('kategori', 'DUM')->groupBy('uraian')->findAll();
        $dukUraian = $jurnalModel->select('uraian')->where('kategori', 'DUK')->groupBy('uraian')->findAll();

        $db = \Config\Database::connect();
        $db->transStart();

        // Buat pemetaan default untuk DUM
        $pemetaanModel->insert([
            'kategori_jurnal' => 'DUM',
            'uraian_jurnal' => 'default',
            'id_akun_debit' => 1, // Kas
            'id_akun_kredit' => 30 // Pendapatan Lain-lain
        ]);

        // Buat pemetaan default untuk DUK
        $pemetaanModel->insert([
            'kategori_jurnal' => 'DUK',
            'uraian_jurnal' => 'default',
            'id_akun_debit' => 40, // Beban Operasional Lainnya
            'id_akun_kredit' => 1 // Kas
        ]);

        // Buat pemetaan spesifik untuk DUM
        foreach ($dumUraian as $uraian) {
            $existing = $pemetaanModel->where('kategori_jurnal', 'DUM')
                ->where('uraian_jurnal', $uraian['uraian'])
                ->first();

            if (!$existing) {
                // Tentukan akun berdasarkan uraian
                $idAkunDebit = 1; // Default: Kas
                $idAkunKredit = 30; // Default: Pendapatan Lain-lain

                // Logika untuk menentukan akun berdasarkan uraian
                if (strpos($uraian['uraian'], 'pinjaman') !== false) {
                    $idAkunDebit = 1; // Kas
                    $idAkunKredit = 3; // Piutang Anggota
                } elseif (strpos($uraian['uraian'], 'bank') !== false) {
                    $idAkunDebit = 1; // Kas
                    $idAkunKredit = 2; // Bank
                }

                $pemetaanModel->insert([
                    'kategori_jurnal' => 'DUM',
                    'uraian_jurnal' => $uraian['uraian'],
                    'id_akun_debit' => $idAkunDebit,
                    'id_akun_kredit' => $idAkunKredit
                ]);
            }
        }

        // Buat pemetaan spesifik untuk DUK
        foreach ($dukUraian as $uraian) {
            $existing = $pemetaanModel->where('kategori_jurnal', 'DUK')
                ->where('uraian_jurnal', $uraian['uraian'])
                ->first();

            if (!$existing) {
                // Tentukan akun berdasarkan uraian
                $idAkunDebit = 40; // Default: Beban Operasional Lainnya
                $idAkunKredit = 1; // Default: Kas

                // Logika untuk menentukan akun berdasarkan uraian
                if (strpos($uraian['uraian'], 'bank') !== false) {
                    $idAkunDebit = 2; // Bank
                    $idAkunKredit = 1; // Kas
                } elseif (strpos($uraian['uraian'], 'pinjaman') !== false) {
                    $idAkunDebit = 3; // Piutang Anggota
                    $idAkunKredit = 1; // Kas
                }

                $pemetaanModel->insert([
                    'kategori_jurnal' => 'DUK',
                    'uraian_jurnal' => $uraian['uraian'],
                    'id_akun_debit' => $idAkunDebit,
                    'id_akun_kredit' => $idAkunKredit
                ]);
            }
        }

        $db->transComplete();

        return $db->transStatus();
    }

}
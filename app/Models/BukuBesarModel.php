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
        try {
            $jurnalModel = new \App\Models\JurnalKasModel();
            $pemetaanModel = new \App\Models\PemetaanAkunModel();
            $akunModel = new \App\Models\AkunModel();

            // Cek apakah akun-akun yang diperlukan sudah ada
            $akun = $akunModel->findAll();
            if (empty($akun)) {
                log_message('error', 'Tidak ada akun yang tersedia untuk pemetaan otomatis');
                return false;
            }

            // Ambil semua uraian unik dari jurnal
            $dumUraian = $jurnalModel->select('uraian')->where('kategori', 'DUM')->groupBy('uraian')->findAll();
            $dukUraian = $jurnalModel->select('uraian')->where('kategori', 'DUK')->groupBy('uraian')->findAll();

            $db = \Config\Database::connect();
            $db->transStart();

            // Buat pemetaan default untuk DUM jika belum ada
            $existingDUMDefault = $pemetaanModel->where('kategori_jurnal', 'DUM')
                ->where('uraian_jurnal', 'default')
                ->first();

            if (!$existingDUMDefault) {
                $pemetaanModel->insert([
                    'kategori_jurnal' => 'DUM',
                    'uraian_jurnal' => 'default',
                    'id_akun_debit' => 1, // Kas
                    'id_akun_kredit' => 30 // Pendapatan Lain-lain
                ]);
                log_message('info', 'Pemetaan default untuk DUM berhasil dibuat');
            }

            // Buat pemetaan default untuk DUK jika belum ada
            $existingDUKDefault = $pemetaanModel->where('kategori_jurnal', 'DUK')
                ->where('uraian_jurnal', 'default')
                ->first();

            if (!$existingDUKDefault) {
                $pemetaanModel->insert([
                    'kategori_jurnal' => 'DUK',
                    'uraian_jurnal' => 'default',
                    'id_akun_debit' => 40, // Beban Operasional Lainnya
                    'id_akun_kredit' => 1 // Kas
                ]);
                log_message('info', 'Pemetaan default untuk DUK berhasil dibuat');
            }

            // Definisikan pemetaan kata kunci ke akun
            $akunKeywords = [
                // Akun untuk DUM (Kredit)
                'pinjaman' => ['debit' => 1, 'kredit' => 3], // Kas -> Piutang Anggota
                'angsuran' => ['debit' => 1, 'kredit' => 3], // Kas -> Piutang Anggota
                'bank' => ['debit' => 1, 'kredit' => 2], // Kas -> Bank
                'tarik dari bank' => ['debit' => 1, 'kredit' => 2], // Kas -> Bank
                'simpanan' => ['debit' => 1, 'kredit' => 14], // Kas -> Simpanan Sukarela
                'sp' => ['debit' => 1, 'kredit' => 13], // Kas -> Simpanan Pokok
                'sw' => ['debit' => 1, 'kredit' => 14], // Kas -> Simpanan Wajib
                'ss' => ['debit' => 1, 'kredit' => 14], // Kas -> Simpanan Sukarela
                'jasa' => ['debit' => 1, 'kredit' => 27], // Kas -> Pendapatan Jasa Pinjaman
                'denda' => ['debit' => 1, 'kredit' => 29], // Kas -> Pendapatan Denda
                'fee' => ['debit' => 1, 'kredit' => 28], // Kas -> Pendapatan Provisi
                'administrasi' => ['debit' => 1, 'kredit' => 28], // Kas -> Pendapatan Administrasi
                'uang pangkal' => ['debit' => 1, 'kredit' => 13], // Kas -> Simpanan Pokok
                'penyusutan' => ['debit' => 1, 'kredit' => 9], // Kas -> Akumulasi Penyusutan
                'penyisihan' => ['debit' => 1, 'kredit' => 15], // Kas -> Dana Cadangan
                'titip' => ['debit' => 1, 'kredit' => 19], // Kas -> Dana Kesejahteraan

                // Akun untuk DUK (Debit)
                'pinjaman anggota' => ['debit' => 3, 'kredit' => 1], // Piutang Anggota -> Kas
                'simpanan di bank' => ['debit' => 2, 'kredit' => 1], // Bank -> Kas
                'tarik dana' => ['debit' => 19, 'kredit' => 1], // Dana Kesejahteraan -> Kas
                'tarik sp' => ['debit' => 13, 'kredit' => 1], // Simpanan Pokok -> Kas
                'tarik sw' => ['debit' => 14, 'kredit' => 1], // Simpanan Wajib -> Kas
                'tarik ss' => ['debit' => 14, 'kredit' => 1], // Simpanan Sukarela -> Kas
                'gaji' => ['debit' => 31, 'kredit' => 1], // Beban Gaji -> Kas
                'listrik' => ['debit' => 33, 'kredit' => 1], // Beban Listrik -> Kas
                'wifi' => ['debit' => 33, 'kredit' => 1], // Beban Internet -> Kas
                'insentip' => ['debit' => 32, 'kredit' => 1], // Beban Insentif -> Kas
                'penyusutan' => ['debit' => 36, 'kredit' => 1], // Beban Penyusutan -> Kas
                'bunga' => ['debit' => 38, 'kredit' => 1], // Beban Bunga -> Kas
                'administrasi' => ['debit' => 34, 'kredit' => 1], // Beban Administrasi -> Kas
                'by' => ['debit' => 40, 'kredit' => 1], // Beban Operasional -> Kas
            ];

            // Buat pemetaan spesifik untuk DUM
            $countDUM = 0;
            foreach ($dumUraian as $item) {
                $uraian = $item['uraian'];
                $existing = $pemetaanModel->where('kategori_jurnal', 'DUM')
                    ->where('uraian_jurnal', $uraian)
                    ->first();

                if (!$existing) {
                    // Tentukan akun berdasarkan uraian
                    $idAkunDebit = 1; // Default: Kas
                    $idAkunKredit = 30; // Default: Pendapatan Lain-lain

                    // Cari kata kunci yang cocok dengan uraian
                    foreach ($akunKeywords as $keyword => $akuns) {
                        if (stripos($uraian, $keyword) !== false) {
                            $idAkunDebit = $akuns['debit'];
                            $idAkunKredit = $akuns['kredit'];
                            break;
                        }
                    }

                    // Cek apakah akun debit dan kredit ada
                    $akunDebit = $akunModel->find($idAkunDebit);
                    $akunKredit = $akunModel->find($idAkunKredit);

                    if ($akunDebit && $akunKredit) {
                        $pemetaanModel->insert([
                            'kategori_jurnal' => 'DUM',
                            'uraian_jurnal' => $uraian,
                            'id_akun_debit' => $idAkunDebit,
                            'id_akun_kredit' => $idAkunKredit
                        ]);
                        $countDUM++;
                    } else {
                        log_message('warning', "Akun tidak ditemukan untuk pemetaan DUM: $uraian");
                    }
                }
            }

            // Buat pemetaan spesifik untuk DUK
            $countDUK = 0;
            foreach ($dukUraian as $item) {
                $uraian = $item['uraian'];
                $existing = $pemetaanModel->where('kategori_jurnal', 'DUK')
                    ->where('uraian_jurnal', $uraian)
                    ->first();

                if (!$existing) {
                    // Tentukan akun berdasarkan uraian
                    $idAkunDebit = 40; // Default: Beban Operasional Lainnya
                    $idAkunKredit = 1; // Default: Kas

                    // Cari kata kunci yang cocok dengan uraian
                    foreach ($akunKeywords as $keyword => $akuns) {
                        if (stripos($uraian, $keyword) !== false) {
                            $idAkunDebit = $akuns['debit'];
                            $idAkunKredit = $akuns['kredit'];
                            break;
                        }
                    }

                    // Cek apakah akun debit dan kredit ada
                    $akunDebit = $akunModel->find($idAkunDebit);
                    $akunKredit = $akunModel->find($idAkunKredit);

                    if ($akunDebit && $akunKredit) {
                        $pemetaanModel->insert([
                            'kategori_jurnal' => 'DUK',
                            'uraian_jurnal' => $uraian,
                            'id_akun_debit' => $idAkunDebit,
                            'id_akun_kredit' => $idAkunKredit
                        ]);
                        $countDUK++;
                    } else {
                        log_message('warning', "Akun tidak ditemukan untuk pemetaan DUK: $uraian");
                    }
                }
            }

            $db->transComplete();
            $success = $db->transStatus();

            if ($success) {
                log_message('info', "Pemetaan otomatis berhasil: $countDUM DUM, $countDUK DUK");
            } else {
                log_message('error', "Pemetaan otomatis gagal");
            }

            return [
                'success' => $success,
                'count_dum' => $countDUM,
                'count_duk' => $countDUK
            ];
        } catch (\Exception $e) {
            log_message('error', "Error pada buatPemetaanOtomatis: " . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            return false;
        }
    }


}
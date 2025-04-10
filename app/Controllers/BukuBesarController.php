<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\BukuBesarModel;
use App\Models\PemetaanAkunModel;
use App\Models\SaldoAkunModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BukuBesarController extends BaseController
{
    protected $akunModel;
    protected $bukuBesarModel;
    protected $pemetaanModel;
    protected $saldoAkunModel;

    public function __construct()
    {
        $this->akunModel = new AkunModel();
        $this->bukuBesarModel = new BukuBesarModel();
        $this->pemetaanModel = new PemetaanAkunModel();
        $this->saldoAkunModel = new SaldoAkunModel();
    }

    public function index()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Buku Besar',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'akun' => $this->akunModel->getAkunWithSaldo($bulan, $tahun)
        ];

        return view('admin/buku_besar/index', $data);
    }

    public function detail($idAkun)
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $akun = $this->akunModel->find($idAkun);

        if (!$akun) {
            return redirect()->to(base_url('admin/buku_besar'))->with('error', 'Akun tidak ditemukan');
        }

        $saldoAwal = $this->bukuBesarModel->getSaldoAwalAkun($idAkun, $bulan, $tahun);
        $transaksi = $this->bukuBesarModel->getBukuBesarByAkun($idAkun, $bulan, $tahun);

        $data = [
            'title' => 'Detail Buku Besar - ' . $akun['nama_akun'],
            'bulan' => $bulan,
            'tahun' => $tahun,
            'akun' => $akun,
            'saldo_awal' => $saldoAwal,
            'transaksi' => $transaksi
        ];

        return view('admin/buku_besar/detail', $data);
    }

    // Tambahkan kode berikut ke BukuBesarController::proses() untuk debugging
    public function proses()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        try {
            // Cek apakah ada jurnal untuk bulan dan tahun yang dipilih
            $jurnalModel = new \App\Models\JurnalKasModel();
            $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
                ->findAll();

            if (empty($jurnal)) {
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                    ->with('error', 'Tidak ada jurnal untuk bulan ' . $bulan . ' tahun ' . $tahun);
            }

            // Cek apakah ada akun yang tersedia
            $akunModel = new \App\Models\AkunModel();
            $akun = $akunModel->findAll();

            if (empty($akun)) {
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                    ->with('error', 'Tidak ada akun yang tersedia. Silakan tambahkan akun terlebih dahulu.');
            }

            // Proses jurnal ke buku besar
            $result = $this->bukuBesarModel->prosesJurnalKeBukuBesar($bulan, $tahun);

            if ($result) {
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                    ->with('success', 'Jurnal berhasil diproses ke Buku Besar');
            } else {
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                    ->with('error', 'Terjadi kesalahan saat memproses jurnal ke Buku Besar. Silakan periksa log untuk detail error.');
            }
        } catch (\Exception $e) {
            log_message('error', "Error pada proses: " . $e->getMessage());
            return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function prosesJurnalKeBukuBesar($bulan, $tahun)
    {
        try {
            $db = \Config\Database::connect();
            $jurnalModel = new \App\Models\JurnalKasModel();

            // Format bulan untuk query
            $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);

            // Ambil semua jurnal untuk bulan dan tahun yang dipilih
            $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
                ->orderBy('tanggal', 'ASC')
                ->findAll();

            // Log jumlah jurnal yang ditemukan
            log_message('debug', "Jumlah jurnal ditemukan: " . count($jurnal));

            if (empty($jurnal)) {
                log_message('error', "Tidak ada jurnal untuk bulan $bulan tahun $tahun");
                return false;
            }

            // Mulai transaksi database
            $db->transStart();

            // Hapus entri buku besar yang sudah ada untuk bulan ini
            $db->query("
                DELETE FROM buku_besar 
                WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
            ", [$bulan, $tahun]);

            // Proses semua jurnal
            foreach ($jurnal as $j) {
                // Tentukan akun berdasarkan kategori
                if ($j['kategori'] == 'DUM') {
                    // DUM: Debit Kas, Kredit sesuai uraian
                    $idAkunDebit = 1; // Kas
                    $idAkunKredit = $this->getKreditAkunForDUM($j['uraian']);
                } else {
                    // DUK: Debit sesuai uraian, Kredit Kas
                    $idAkunDebit = $this->getDebitAkunForDUK($j['uraian']);
                    $idAkunKredit = 1; // Kas
                }

                $tanggal = $j['tanggal'];
                $keterangan = $j['uraian'];
                $jumlah = $j['jumlah'];

                // Insert entri debit
                $this->insert([
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunDebit,
                    'id_jurnal' => $j['id'],
                    'keterangan' => $keterangan,
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'saldo' => 0 // Saldo akan diupdate nanti
                ]);

                // Insert entri kredit
                $this->insert([
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunKredit,
                    'id_jurnal' => $j['id'],
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'saldo' => 0 // Saldo akan diupdate nanti
                ]);
            }

            // Update saldo untuk semua akun
            $this->updateAllSaldos($bulan, $tahun);

            $db->transComplete();

            return $db->transStatus();
        } catch (\Exception $e) {
            log_message('error', "Error pada prosesJurnalKeBukuBesar: " . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            return false;
        }
    }

    // Fungsi untuk mendapatkan akun kredit untuk DUM berdasarkan uraian
    private function getKreditAkunForDUM($uraian)
    {
        // Default akun kredit untuk DUM adalah Pendapatan Lain-lain
        $defaultAkunId = 30; // Pendapatan Lain-lain

        // Pemetaan uraian ke akun
        $mapping = [
            'pinjaman' => 3, // Piutang Anggota
            'bank' => 2, // Bank
            'simpanan' => 14, // Simpanan Sukarela
            'sp' => 13, // Simpanan Pokok
            'sw' => 14, // Simpanan Wajib
            'ss' => 14, // Simpanan Sukarela
            'jasa' => 27, // Pendapatan Jasa Pinjaman
            'denda' => 29, // Pendapatan Denda
            'fee' => 28, // Pendapatan Provisi
            'administrasi' => 28, // Pendapatan Administrasi
        ];

        // Cari kata kunci dalam uraian
        foreach ($mapping as $keyword => $akunId) {
            if (stripos($uraian, $keyword) !== false) {
                return $akunId;
            }
        }

        return $defaultAkunId;
    }

    // Fungsi untuk mendapatkan akun debit untuk DUK berdasarkan uraian
    private function getDebitAkunForDUK($uraian)
    {
        // Default akun debit untuk DUK adalah Beban Operasional Lainnya
        $defaultAkunId = 40; // Beban Operasional Lainnya

        // Pemetaan uraian ke akun
        $mapping = [
            'pinjaman' => 3, // Piutang Anggota
            'bank' => 2, // Bank
            'simpanan' => 14, // Simpanan Sukarela
            'gaji' => 31, // Beban Gaji Karyawan
            'listrik' => 33, // Beban Listrik dan Air
            'internet' => 33, // Beban Telepon dan Internet
            'insentif' => 32, // Beban Insentif Pengurus
            'penyusutan' => 36, // Beban Penyusutan Inventaris
            'bunga' => 38, // Beban Bunga Bank
            'administrasi' => 34, // Beban Administrasi
        ];

        // Cari kata kunci dalam uraian
        foreach ($mapping as $keyword => $akunId) {
            if (stripos($uraian, $keyword) !== false) {
                return $akunId;
            }
        }

        return $defaultAkunId;
    }

    // Fungsi untuk mengupdate saldo semua akun
    private function updateAllSaldos($bulan, $tahun)
    {
        $db = \Config\Database::connect();
        $akunModel = new \App\Models\AkunModel();

        // Ambil semua akun
        $akuns = $akunModel->findAll();

        foreach ($akuns as $akun) {
            // Ambil saldo awal bulan
            $saldoAwal = $this->getSaldoAwalAkun($akun['id'], $bulan, $tahun);

            // Ambil semua transaksi untuk akun ini pada bulan yang dipilih
            $query = $db->query("
                SELECT id, tanggal, debit, kredit
                FROM buku_besar
                WHERE id_akun = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                ORDER BY tanggal ASC, id ASC
            ", [$akun['id'], $bulan, $tahun]);

            $transaksis = $query->getResultArray();

            // Jika tidak ada transaksi, lanjutkan ke akun berikutnya
            if (empty($transaksis)) {
                continue;
            }

            $currentSaldo = $saldoAwal;

            // Update saldo untuk setiap transaksi
            foreach ($transaksis as $transaksi) {
                // Hitung saldo berdasarkan jenis akun
                if ($akun['jenis'] == 'Debit') {
                    $currentSaldo = $currentSaldo + $transaksi['debit'] - $transaksi['kredit'];
                } else {
                    $currentSaldo = $currentSaldo - $transaksi['debit'] + $transaksi['kredit'];
                }

                // Update saldo transaksi
                $db->query("
                    UPDATE buku_besar
                    SET saldo = ?
                    WHERE id = ?
                ", [$currentSaldo, $transaksi['id']]);
            }

            // Update saldo akhir di tabel saldo_akun
            $this->updateSaldoAkun($akun['id'], $bulan, $tahun);
        }
    }

    public function debug()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $jurnalModel = new \App\Models\JurnalKasModel();
        $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Cek jumlah jurnal
        $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        // Cek akun yang ada
        $akunModel = new \App\Models\AkunModel();
        $akun = $akunModel->findAll();

        // Cek buku besar yang sudah ada
        $bukuBesar = $this->bukuBesarModel->where("MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun")
            ->findAll();

        $data = [
            'jumlah_jurnal' => count($jurnal),
            'jurnal_sample' => array_slice($jurnal, 0, 5), // Ambil 5 jurnal pertama
            'jumlah_akun' => count($akun),
            'akun_sample' => array_slice($akun, 0, 5), // Ambil 5 akun pertama
            'jumlah_buku_besar' => count($bukuBesar),
            'buku_besar_sample' => array_slice($bukuBesar, 0, 5) // Ambil 5 buku besar pertama
        ];

        return $this->response->setJSON($data);
    }

    public function akun()
    {
        $data = [
            'title' => 'Daftar Akun',
            'akun' => $this->akunModel->orderBy('kode_akun', 'ASC')->findAll()
        ];

        return view('admin/buku_besar/akun', $data);
    }

    public function createAkun()
    {
        $data = [
            'title' => 'Tambah Akun Baru'
        ];

        return view('admin/buku_besar/create_akun', $data);
    }

    public function storeAkun()
    {
        $rules = [
            'kode_akun' => 'required|is_unique[akun.kode_akun]',
            'nama_akun' => 'required',
            'kategori' => 'required',
            'jenis' => 'required',
            'saldo_awal' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_akun' => $this->request->getPost('kode_akun'),
            'nama_akun' => $this->request->getPost('nama_akun'),
            'kategori' => $this->request->getPost('kategori'),
            'jenis' => $this->request->getPost('jenis'),
            'saldo_awal' => $this->request->getPost('saldo_awal')
        ];

        $this->akunModel->insert($data);

        return redirect()->to(base_url('admin/buku_besar/akun'))
            ->with('success', 'Akun berhasil ditambahkan');
    }

    public function editAkun($id)
    {
        $akun = $this->akunModel->find($id);

        if (!$akun) {
            return redirect()->to(base_url('admin/buku_besar/akun'))->with('error', 'Akun tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Akun',
            'akun' => $akun
        ];

        return view('admin/buku_besar/edit_akun', $data);
    }

    public function updateAkun($id)
    {
        $akun = $this->akunModel->find($id);

        if (!$akun) {
            return redirect()->to(base_url('admin/buku_besar/akun'))->with('error', 'Akun tidak ditemukan');
        }

        $rules = [
            'kode_akun' => 'required|is_unique[akun.kode_akun,id,' . $id . ']',
            'nama_akun' => 'required',
            'kategori' => 'required',
            'jenis' => 'required',
            'saldo_awal' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_akun' => $this->request->getPost('kode_akun'),
            'nama_akun' => $this->request->getPost('nama_akun'),
            'kategori' => $this->request->getPost('kategori'),
            'jenis' => $this->request->getPost('jenis'),
            'saldo_awal' => $this->request->getPost('saldo_awal')
        ];

        $this->akunModel->update($id, $data);

        return redirect()->to(base_url('admin/buku_besar/akun'))
            ->with('success', 'Akun berhasil diperbarui');
    }

    public function deleteAkun($id)
    {
        $akun = $this->akunModel->find($id);

        if (!$akun) {
            return redirect()->to(base_url('admin/buku_besar/akun'))->with('error', 'Akun tidak ditemukan');
        }

        // Periksa apakah akun sudah digunakan dalam buku besar
        $bukuBesar = $this->bukuBesarModel->where('id_akun', $id)->first();

        if ($bukuBesar) {
            return redirect()->to(base_url('admin/buku_besar/akun'))
                ->with('error', 'Akun tidak dapat dihapus karena sudah digunakan dalam transaksi');
        }

        $this->akunModel->delete($id);

        return redirect()->to(base_url('admin/buku_besar/akun'))
            ->with('success', 'Akun berhasil dihapus');
    }
    public function pemetaanOtomatis()
    {
        $result = $this->bukuBesarModel->buatPemetaanOtomatis();

        if ($result) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('success', 'Pemetaan akun otomatis berhasil dibuat');
        } else {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('error', 'Terjadi kesalahan saat membuat pemetaan akun otomatis');
        }
    }

    public function pemetaan()
    {
        $data = [
            'title' => 'Pemetaan Akun',
            'pemetaan' => $this->pemetaanModel->getPemetaanWithAkun(),
            'akun' => $this->akunModel->orderBy('kode_akun', 'ASC')->findAll()
        ];

        return view('admin/buku_besar/pemetaan', $data);
    }

    public function storePemetaan()
    {
        $rules = [
            'kategori_jurnal' => 'required',
            'uraian_jurnal' => 'required',
            'id_akun_debit' => 'required',
            'id_akun_kredit' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kategori_jurnal' => $this->request->getPost('kategori_jurnal'),
            'uraian_jurnal' => $this->request->getPost('uraian_jurnal'),
            'id_akun_debit' => $this->request->getPost('id_akun_debit'),
            'id_akun_kredit' => $this->request->getPost('id_akun_kredit')
        ];

        // Cek apakah pemetaan sudah ada
        $existing = $this->pemetaanModel->where('kategori_jurnal', $data['kategori_jurnal'])
            ->where('uraian_jurnal', $data['uraian_jurnal'])
            ->first();

        if ($existing) {
            $this->pemetaanModel->update($existing['id'], $data);
            $message = 'Pemetaan berhasil diperbarui';
        } else {
            $this->pemetaanModel->insert($data);
            $message = 'Pemetaan berhasil ditambahkan';
        }

        return redirect()->to(base_url('admin/buku_besar/pemetaan'))
            ->with('success', $message);
    }

    public function deletePemetaan($id)
    {
        $pemetaan = $this->pemetaanModel->find($id);

        if (!$pemetaan) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))->with('error', 'Pemetaan tidak ditemukan');
        }

        $this->pemetaanModel->delete($id);

        return redirect()->to(base_url('admin/buku_besar/pemetaan'))
            ->with('success', 'Pemetaan berhasil dihapus');
    }

    public function neracaSaldo()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $neracaSaldo = $this->saldoAkunModel->getNeracaSaldo($bulan, $tahun);

        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($neracaSaldo as $neraca) {
            $totalDebit += $neraca['debit'];
            $totalKredit += $neraca['kredit'];
        }

        $data = [
            'title' => 'Neraca Saldo',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'neraca_saldo' => $neracaSaldo,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit
        ];

        return view('admin/buku_besar/neraca_saldo', $data);
    }

    public function labaRugi()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $labaRugi = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        $totalPendapatan = 0;
        $totalBeban = 0;

        foreach ($labaRugi as $item) {
            if ($item['kategori'] == 'Pendapatan') {
                $totalPendapatan += $item['saldo'];
            } else if ($item['kategori'] == 'Beban') {
                $totalBeban += $item['saldo'];
            }
        }

        $labaRugiBersih = $totalPendapatan - $totalBeban;

        $data = [
            'title' => 'Laporan Laba Rugi',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'laba_rugi' => $labaRugi,
            'total_pendapatan' => $totalPendapatan,
            'total_beban' => $totalBeban,
            'laba_rugi_bersih' => $labaRugiBersih
        ];

        return view('admin/buku_besar/laba_rugi', $data);
    }

    public function neraca()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $neraca = $this->saldoAkunModel->getNeraca($bulan, $tahun);

        $totalAktiva = 0;
        $totalPasiva = 0;
        $totalModal = 0;

        foreach ($neraca as $item) {
            if ($item['kategori'] == 'Aktiva') {
                $totalAktiva += $item['saldo'];
            } else if ($item['kategori'] == 'Pasiva') {
                $totalPasiva += $item['saldo'];
            } else if ($item['kategori'] == 'Modal') {
                $totalModal += $item['saldo'];
            }
        }

        // Hitung laba rugi berjalan
        $labaRugi = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        $totalPendapatan = 0;
        $totalBeban = 0;

        foreach ($labaRugi as $item) {
            if ($item['kategori'] == 'Pendapatan') {
                $totalPendapatan += $item['saldo'];
            } else if ($item['kategori'] == 'Beban') {
                $totalBeban += $item['saldo'];
            }
        }

        $labaRugiBersih = $totalPendapatan - $totalBeban;

        $data = [
            'title' => 'Neraca',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'neraca' => $neraca,
            'total_aktiva' => $totalAktiva,
            'total_pasiva' => $totalPasiva,
            'total_modal' => $totalModal,
            'laba_rugi_bersih' => $labaRugiBersih
        ];

        return view('admin/buku_besar/neraca', $data);
    }

    public function exportBukuBesar($idAkun)
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $akun = $this->akunModel->find($idAkun);

        if (!$akun) {
            return redirect()->to(base_url('admin/buku_besar'))->with('error', 'Akun tidak ditemukan');
        }

        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $namaBulan = $bulanNames[$bulan];

        $saldoAwal = $this->bukuBesarModel->getSaldoAwalAkun($idAkun, $bulan, $tahun);
        $transaksi = $this->bukuBesarModel->getBukuBesarByAkun($idAkun, $bulan, $tahun);

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar');

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Buku Besar Koperasi')
            ->setLastModifiedBy('Sistem Buku Besar Koperasi')
            ->setTitle("Buku Besar - " . $akun['nama_akun'])
            ->setSubject("Buku Besar " . $akun['nama_akun'] . " - " . $namaBulan . " " . $tahun)
            ->setDescription("Buku Besar untuk akun " . $akun['nama_akun'] . " periode " . $namaBulan . " " . $tahun);

        // Add title
        $sheet->setCellValue('A1', "BUKU BESAR");
        $sheet->setCellValue('A2', "Periode: " . $namaBulan . " " . $tahun);
        $sheet->setCellValue('A3', "Akun: " . $akun['kode_akun'] . " - " . $akun['nama_akun']);

        // Merge cells for title
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        // Style the title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($titleStyle);

        $subtitleStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:F3')->applyFromArray($subtitleStyle);

        // Add headers
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Tanggal');
        $sheet->setCellValue('C5', 'Keterangan');
        $sheet->setCellValue('D5', 'Debit');
        $sheet->setCellValue('E5', 'Kredit');
        $sheet->setCellValue('F5', 'Saldo');

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D9E1F2',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A5:F5')->applyFromArray($headerStyle);

        // Add saldo awal row
        $sheet->setCellValue('A6', '');
        $sheet->setCellValue('B6', date('01-m-Y', strtotime($tahun . '-' . $bulan . '-01')));
        $sheet->setCellValue('C6', 'Saldo Awal');
        $sheet->setCellValue('D6', '');
        $sheet->setCellValue('E6', '');
        $sheet->setCellValue('F6', $saldoAwal);

        // Add data
        $row = 7;
        $no = 1;
        $currentSaldo = $saldoAwal;

        foreach ($transaksi as $t) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($t['tanggal'])));
            $sheet->setCellValue('C' . $row, $t['keterangan']);
            $sheet->setCellValue('D' . $row, $t['debit']);
            $sheet->setCellValue('E' . $row, $t['kredit']);
            $sheet->setCellValue('F' . $row, $t['saldo']);

            $currentSaldo = $t['saldo'];
            $row++;
        }

        // Add saldo akhir row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, date('t-m-Y', strtotime($tahun . '-' . $bulan . '-01')));
        $sheet->setCellValue('C' . $row, 'Saldo Akhir');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, $currentSaldo);

        // Style saldo awal dan akhir
        $saldoStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
        ];
        $sheet->getStyle('A6:F6')->applyFromArray($saldoStyle);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($saldoStyle);

        // Apply number format to amount columns
        $sheet->getStyle('D6:F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply borders to all data
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A5:F' . $row)->applyFromArray($borderStyle);

        // Add footer with date
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Dicetak pada: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A' . $row . ':F' . $row);
        // Create writer
        $writer = new Xlsx($spreadsheet);
        $filename = "Buku_Besar_" . str_replace(' ', '_', $akun['nama_akun']) . "_" . $namaBulan . "_" . $tahun . ".xlsx";
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportNeracaSaldo()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $namaBulan = $bulanNames[$bulan];

        $neracaSaldo = $this->saldoAkunModel->getNeracaSaldo($bulan, $tahun);

        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($neracaSaldo as $neraca) {
            $totalDebit += $neraca['debit'];
            $totalKredit += $neraca['kredit'];
        }

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Saldo');

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Buku Besar Koperasi')
            ->setLastModifiedBy('Sistem Buku Besar Koperasi')
            ->setTitle("Neraca Saldo")
            ->setSubject("Neraca Saldo - " . $namaBulan . " " . $tahun)
            ->setDescription("Neraca Saldo periode " . $namaBulan . " " . $tahun);

        // Add title
        $sheet->setCellValue('A1', "NERACA SALDO");
        $sheet->setCellValue('A2', "Periode: " . $namaBulan . " " . $tahun);

        // Merge cells for title
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');

        // Style the title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($titleStyle);

        $subtitleStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:D2')->applyFromArray($subtitleStyle);

        // Add headers
        $sheet->setCellValue('A4', 'Kode Akun');
        $sheet->setCellValue('B4', 'Nama Akun');
        $sheet->setCellValue('C4', 'Debit');
        $sheet->setCellValue('D4', 'Kredit');

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D9E1F2',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A4:D4')->applyFromArray($headerStyle);

        // Add data
        $row = 5;

        foreach ($neracaSaldo as $neraca) {
            $sheet->setCellValue('A' . $row, $neraca['kode_akun']);
            $sheet->setCellValue('B' . $row, $neraca['nama_akun']);
            $sheet->setCellValue('C' . $row, $neraca['debit']);
            $sheet->setCellValue('D' . $row, $neraca['kredit']);
            $row++;
        }

        // Add total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('C' . $row, $totalDebit);
        $sheet->setCellValue('D' . $row, $totalKredit);

        // Style total row
        $totalStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
        ];
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($totalStyle);

        // Apply number format to amount columns
        $sheet->getStyle('C5:D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply borders to all data
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:D' . $row)->applyFromArray($borderStyle);

        // Add footer with date
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Dicetak pada: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A' . $row . ':D' . $row);

        // Create writer
        $writer = new Xlsx($spreadsheet);
        $filename = "Neraca_Saldo_" . $namaBulan . "_" . $tahun . ".xlsx";
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportLabaRugi()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $namaBulan = $bulanNames[$bulan];

        $labaRugi = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        $totalPendapatan = 0;
        $totalBeban = 0;

        foreach ($labaRugi as $item) {
            if ($item['kategori'] == 'Pendapatan') {
                $totalPendapatan += $item['saldo'];
            } else if ($item['kategori'] == 'Beban') {
                $totalBeban += $item['saldo'];
            }
        }

        $labaRugiBersih = $totalPendapatan - $totalBeban;

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Laba Rugi');

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Buku Besar Koperasi')
            ->setLastModifiedBy('Sistem Buku Besar Koperasi')
            ->setTitle("Laporan Laba Rugi")
            ->setSubject("Laporan Laba Rugi - " . $namaBulan . " " . $tahun)
            ->setDescription("Laporan Laba Rugi periode " . $namaBulan . " " . $tahun);

        // Add title
        $sheet->setCellValue('A1', "LAPORAN LABA RUGI");
        $sheet->setCellValue('A2', "Periode: " . $namaBulan . " " . $tahun);

        // Merge cells for title
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        // Style the title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($titleStyle);

        $subtitleStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:C2')->applyFromArray($subtitleStyle);

        // Add Pendapatan section
        $row = 4;
        $sheet->setCellValue('A' . $row, 'PENDAPATAN');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        foreach ($labaRugi as $item) {
            if ($item['kategori'] == 'Pendapatan') {
                $sheet->setCellValue('A' . $row, $item['kode_akun']);
                $sheet->setCellValue('B' . $row, $item['nama_akun']);
                $sheet->setCellValue('C' . $row, $item['saldo']);
                $row++;
            }
        }

        // Add Total Pendapatan
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Total Pendapatan');
        $sheet->setCellValue('C' . $row, $totalPendapatan);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA');

        // Add Beban section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'BEBAN');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        foreach ($labaRugi as $item) {
            if ($item['kategori'] == 'Beban') {
                $sheet->setCellValue('A' . $row, $item['kode_akun']);
                $sheet->setCellValue('B' . $row, $item['nama_akun']);
                $sheet->setCellValue('C' . $row, $item['saldo']);
                $row++;
            }
        }

        // Add Total Beban
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Total Beban');
        $sheet->setCellValue('C' . $row, $totalBeban);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA');

        // Add Laba Rugi Bersih
        $row += 2;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'LABA (RUGI) BERSIH');
        $sheet->setCellValue('C' . $row, $labaRugiBersih);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFEB9C');

        // Apply number format to amount columns
        $sheet->getStyle('C5:C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply borders to all data
        $borderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:C' . $row)->applyFromArray($borderStyle);

        // Add footer with date
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Dicetak pada: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A' . $row . ':C' . $row);

        // Create writer
        $writer = new Xlsx($spreadsheet);
        $filename = "Laporan_Laba_Rugi_" . $namaBulan . "_" . $tahun . ".xlsx";
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportNeraca()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $namaBulan = $bulanNames[$bulan];

        $neraca = $this->saldoAkunModel->getNeraca($bulan, $tahun);

        $totalAktiva = 0;
        $totalPasiva = 0;
        $totalModal = 0;

        foreach ($neraca as $item) {
            if ($item['kategori'] == 'Aktiva') {
                $totalAktiva += $item['saldo'];
            } else if ($item['kategori'] == 'Pasiva') {
                $totalPasiva += $item['saldo'];
            } else if ($item['kategori'] == 'Modal') {
                $totalModal += $item['saldo'];
            }
        }

        // Hitung laba rugi berjalan
        $labaRugi = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        $totalPendapatan = 0;
        $totalBeban = 0;

        foreach ($labaRugi as $item) {
            if ($item['kategori'] == 'Pendapatan') {
                $totalPendapatan += $item['saldo'];
            } else if ($item['kategori'] == 'Beban') {
                $totalBeban += $item['saldo'];
            }
        }

        $labaRugiBersih = $totalPendapatan - $totalBeban;

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca');

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Buku Besar Koperasi')
            ->setLastModifiedBy('Sistem Buku Besar Koperasi')
            ->setTitle("Neraca")
            ->setSubject("Neraca - " . $namaBulan . " " . $tahun)
            ->setDescription("Neraca periode " . $namaBulan . " " . $tahun);

        // Add title
        $sheet->setCellValue('A1', "NERACA");
        $sheet->setCellValue('A2', "Periode: " . $namaBulan . " " . $tahun);

        // Merge cells for title
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        // Style the title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($titleStyle);

        $subtitleStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:C2')->applyFromArray($subtitleStyle);

        // Add Aktiva section
        $row = 4;
        $sheet->setCellValue('A' . $row, 'AKTIVA');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        foreach ($neraca as $item) {
            if ($item['kategori'] == 'Aktiva') {
                $sheet->setCellValue('A' . $row, $item['kode_akun']);
                $sheet->setCellValue('B' . $row, $item['nama_akun']);
                $sheet->setCellValue('C' . $row, $item['saldo']);
                $row++;
            }
        }

        // Add Total Aktiva
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Total Aktiva');
        $sheet->setCellValue('C' . $row, $totalAktiva);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA');

        // Add Pasiva section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'PASIVA');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        foreach ($neraca as $item) {
            if ($item['kategori'] == 'Pasiva') {
                $sheet->setCellValue('A' . $row, $item['kode_akun']);
                $sheet->setCellValue('B' . $row, $item['nama_akun']);
                $sheet->setCellValue('C' . $row, $item['saldo']);
                $row++;
            }
        }

        // Add Total Pasiva
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Total Pasiva');
        $sheet->setCellValue('C' . $row, $totalPasiva);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA');

        // Add Modal section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'MODAL');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        foreach ($neraca as $item) {
            if ($item['kategori'] == 'Modal') {
                $sheet->setCellValue('A' . $row, $item['kode_akun']);
                $sheet->setCellValue('B' . $row, $item['nama_akun']);
                $sheet->setCellValue('C' . $row, $item['saldo']);
                $row++;
            }
        }

        // Add Laba Rugi Berjalan
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Laba (Rugi) Berjalan');
        $sheet->setCellValue('C' . $row, $labaRugiBersih);
        $row++;

        // Add Total Modal
        $totalModalDenganLabaRugi = $totalModal + $labaRugiBersih;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Total Modal');
        $sheet->setCellValue('C' . $row, $totalModalDenganLabaRugi);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA');

        // Add Total Pasiva dan Modal
        $row += 2;
        $totalPasivaModal = $totalPasiva + $totalModalDenganLabaRugi;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'TOTAL PASIVA DAN MODAL');
        $sheet->setCellValue('C' . $row, $totalPasivaModal);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFEB9C');

        // Apply number format to amount columns
        $sheet->getStyle('C5:C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply borders to all data
        $borderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:C' . $row)->applyFromArray($borderStyle);

        // Add footer with date
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Dicetak pada: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A' . $row . ':C' . $row);

        // Create writer
        $writer = new Xlsx($spreadsheet);
        $filename = "Neraca_" . $namaBulan . "_" . $tahun . ".xlsx";
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }
}

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

        // Tambahkan log untuk melihat apa yang sedang diproses
        log_message('debug', "Memproses jurnal ke buku besar untuk bulan $bulan tahun $tahun");

        // Ambil semua jurnal untuk bulan dan tahun yang dipilih
        $jurnalModel = new \App\Models\JurnalKasModel();
        $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $jurnal = $jurnalModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        log_message('debug', "Jumlah jurnal ditemukan: " . count($jurnal));

        // Cek pemetaan akun
        $pemetaanModel = new \App\Models\PemetaanAkunModel();
        $pemetaan = $pemetaanModel->findAll();
        log_message('debug', "Jumlah pemetaan akun: " . count($pemetaan));

        // Lanjutkan dengan proses yang ada
        $result = $this->bukuBesarModel->prosesJurnalKeBukuBesar($bulan, $tahun);

        if ($result) {
            return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                ->with('success', 'Jurnal berhasil diproses ke Buku Besar');
        } else {
            return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                ->with('error', 'Terjadi kesalahan saat memproses jurnal ke Buku Besar');
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

        // Log jumlah jurnal yang ditemukan
        log_message('debug', "Jumlah jurnal ditemukan: " . count($jurnal));

        // Mulai transaksi database
        $db->transStart();

        // Hapus entri buku besar yang sudah ada untuk bulan ini (opsional)
        $db->query("
            DELETE FROM buku_besar 
            WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
        ", [$bulan, $tahun]);

        // Buat array untuk melacak akun yang sudah diproses
        $processedAccounts = [];

        foreach ($jurnal as $j) {
            // Log data jurnal yang sedang diproses
            log_message('debug', "Memproses jurnal: " . json_encode($j));

            // Cari pemetaan akun berdasarkan kategori dan uraian
            $pemetaan = $pemetaanModel->where('kategori_jurnal', $j['kategori'])
                ->where('uraian_jurnal', $j['uraian'])
                ->first();

            // Log hasil pencarian pemetaan
            log_message('debug', "Pemetaan ditemukan: " . ($pemetaan ? 'Ya' : 'Tidak'));

            if (!$pemetaan) {
                // Jika tidak ada pemetaan spesifik, cari pemetaan default untuk kategori
                $pemetaan = $pemetaanModel->where('kategori_jurnal', $j['kategori'])
                    ->where('uraian_jurnal', 'default')
                    ->first();

                log_message('debug', "Pemetaan default ditemukan: " . ($pemetaan ? 'Ya' : 'Tidak'));

                if (!$pemetaan) {
                    // Jika masih tidak ada, gunakan akun default
                    if ($j['kategori'] == 'DUM') {
                        $idAkunDebit = 1; // Kas
                        $idAkunKredit = 30; // Pendapatan Lain-lain
                        log_message('debug', "Menggunakan akun default untuk DUM: Debit=1, Kredit=30");
                    } else {
                        $idAkunDebit = 40; // Beban Operasional Lainnya
                        $idAkunKredit = 1; // Kas
                        log_message('debug', "Menggunakan akun default untuk DUK: Debit=40, Kredit=1");
                    }
                } else {
                    $idAkunDebit = $pemetaan['id_akun_debit'];
                    $idAkunKredit = $pemetaan['id_akun_kredit'];
                    log_message('debug', "Menggunakan pemetaan default: Debit={$idAkunDebit}, Kredit={$idAkunKredit}");
                }
            } else {
                $idAkunDebit = $pemetaan['id_akun_debit'];
                $idAkunKredit = $pemetaan['id_akun_kredit'];
                log_message('debug', "Menggunakan pemetaan spesifik: Debit={$idAkunDebit}, Kredit={$idAkunKredit}");
            }

            $tanggal = $j['tanggal'];
            $keterangan = $j['uraian'];
            $jumlah = $j['jumlah'];

            // Tambahkan akun ke daftar yang perlu diperbarui saldonya
            $processedAccounts[$idAkunDebit] = true;
            $processedAccounts[$idAkunKredit] = true;

            // Buat entri untuk akun debit
            if ($idAkunDebit) {
                // Ambil saldo terakhir
                $lastSaldo = $this->getLastSaldo($idAkunDebit, $tanggal);

                // Hitung saldo baru berdasarkan jenis akun
                $akunModel = new \App\Models\AkunModel();
                $akun = $akunModel->find($idAkunDebit);

                if ($akun['jenis'] == 'Debit') {
                    $saldoBaru = $lastSaldo + $jumlah;
                } else {
                    $saldoBaru = $lastSaldo - $jumlah;
                }

                $this->insert([
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunDebit,
                    'id_jurnal' => $j['id'],
                    'keterangan' => $keterangan,
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'saldo' => $saldoBaru
                ]);

                log_message('debug', "Entri debit dibuat: Akun={$idAkunDebit}, Jumlah={$jumlah}, Saldo={$saldoBaru}");
            }

            // Buat entri untuk akun kredit
            if ($idAkunKredit) {
                // Ambil saldo terakhir
                $lastSaldo = $this->getLastSaldo($idAkunKredit, $tanggal);

                // Hitung saldo baru berdasarkan jenis akun
                $akunModel = new \App\Models\AkunModel();
                $akun = $akunModel->find($idAkunKredit);

                if ($akun['jenis'] == 'Kredit') {
                    $saldoBaru = $lastSaldo + $jumlah;
                } else {
                    $saldoBaru = $lastSaldo - $jumlah;
                }

                $this->insert([
                    'tanggal' => $tanggal,
                    'id_akun' => $idAkunKredit,
                    'id_jurnal' => $j['id'],
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'saldo' => $saldoBaru
                ]);

                log_message('debug', "Entri kredit dibuat: Akun={$idAkunKredit}, Jumlah={$jumlah}, Saldo={$saldoBaru}");
            }
        }

        // Update saldo semua akun yang telah diproses
        foreach (array_keys($processedAccounts) as $idAkun) {
            $this->updateSaldoAkun($idAkun, $bulan, $tahun);
            log_message('debug', "Saldo akun {$idAkun} diperbarui untuk bulan {$bulan} tahun {$tahun}");
        }

        $db->transComplete();
        $status = $db->transStatus();

        log_message('debug', "Transaksi database: " . ($status ? 'Berhasil' : 'Gagal'));

        return $status;
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

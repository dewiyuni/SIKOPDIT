<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\BukuBesarModel;
use App\Models\JurnalKasModel;
use App\Models\SaldoAkunModel;
use App\Models\NeracaDataModel;
use App\Models\PemetaanAkunModel;
use App\Controllers\BaseController;
use App\Models\MappingAkunNeracaModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Alignment as PhpSpreadsheetAlignment;
use PhpOffice\PhpSpreadsheet\Style\Border as ExcelBorder; // <--- TAMBAHKAN/PASTIKAN INI ADA
use PhpOffice\PhpSpreadsheet\Style\Fill as ExcelFill;     // <--- TAMBAHKAN/PASTIKAN INI ADA


class BukuBesarController extends BaseController
{
    protected $akunModel;
    protected $bukuBesarModel;
    protected $pemetaanModel;
    protected $saldoAkunModel;
    protected $jurnalKasModel;
    protected $neracaModel;
    protected $MappingModel;
    protected $bulanNames;


    public function __construct()
    {
        $this->akunModel = new AkunModel();
        $this->bukuBesarModel = new BukuBesarModel();
        $this->pemetaanModel = new PemetaanAkunModel();
        $this->saldoAkunModel = new SaldoAkunModel();
        $this->jurnalKasModel = new JurnalKasModel();
        $this->MappingModel = new MappingAkunNeracaModel();
        $this->neracaModel = new NeracaDataModel();
        helper('number');
        $this->bulanNames = [
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
    }

    public function index()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        // 1. Ambil daftar kategori unik
        $kategoriList = $this->akunModel->getDistinctKategori();

        // 2. Siapkan array untuk menampung data akun per kategori
        $akunPerKategori = [];

        // 3. Loop setiap kategori dan ambil data akunnya
        foreach ($kategoriList as $item) {
            $namaKategori = $item['kategori'];
            $akunPerKategori[$namaKategori] = $this->akunModel->getAkunWithSaldoByKategori($namaKategori, $bulan, $tahun);
        }

        $data = [
            'title' => 'Buku Besar per Kategori',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategoriList' => $kategoriList, // Kirim daftar kategori ke view
            'akunPerKategori' => $akunPerKategori // Kirim data akun yang sudah dikelompokkan
        ];

        // Gunakan view baru atau modifikasi view lama
        return view('admin/buku_besar/index', $data);
        // atau jika memodifikasi view lama: return view('admin/buku_besar/index', $data);
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
        $logErrors = []; // Untuk menampung error pemetaan

        try {
            $bulanNames = $this->bulanNames;

            // Cek apakah ada jurnal
            $bulanFormat = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $jurnal = $this->jurnalKasModel->where("DATE_FORMAT(tanggal, '%Y-%m') = '$tahun-$bulanFormat'")->findAll();
            if (empty($jurnal)) {
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                    ->with('error', 'Tidak ada data Jurnal Kas untuk diproses pada periode ' . ($bulanNames[$bulan] ?? $bulan) . ' ' . $tahun . '.');
            }

            // --- PANGGIL FUNGSI PROSES DENGAN PEMETAAN ---
            $result = $this->bukuBesarModel->prosesJurnalKeBukuBesar_dengan_pemetaan($bulan, $tahun, $logErrors);

            $session = session();
            if (!empty($logErrors)) {
                $errorMessage = 'Gagal memproses jurnal ke Buku Besar. Jurnal berikut tidak memiliki aturan pemetaan di tabel `pemetaan_akun`: <ul>';
                foreach ($logErrors as $err) {
                    $errorMessage .= "<li>" . esc($err) . "</li>";
                }
                $errorMessage .= "</ul> Silakan tambahkan aturan pemetaan yang sesuai melalui menu Pengaturan > Kelola Pemetaan Jurnal.";
                $session->setFlashdata('error', $errorMessage);
            }

            if ($result) {
                $bulanBerikutnya = $bulan == 12 ? 1 : $bulan + 1;
                $tahunBerikutnya = $bulan == 12 ? $tahun + 1 : $tahun;

                $dataAkun = $this->akunModel->getAkunWithSaldo($bulan, $tahun); // Ambil saldo akhir

                $db = \Config\Database::connect();
                foreach ($dataAkun as $akun) {
                    $idAkun = $akun['id'];
                    $saldoAkhir = $akun['saldo_akhir'];

                    // Cek apakah sudah ada entri untuk bulan berikutnya
                    $cek = $db->table('saldo_akun')
                        ->where([
                            'id_akun' => $idAkun,
                            'bulan' => $bulanBerikutnya,
                            'tahun' => $tahunBerikutnya
                        ])->get()->getRow();

                    if (!$cek) {
                        // Masukkan saldo akhir ke saldo_awal bulan berikutnya
                        $db->table('saldo_akun')->insert([
                            'id_akun' => $idAkun,
                            'bulan' => $bulanBerikutnya,
                            'tahun' => $tahunBerikutnya,
                            'saldo_awal' => $saldoAkhir,
                            'total_debit' => 0,
                            'total_kredit' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
                $session->setFlashdata('success', 'Jurnal berhasil diproses ke Buku Besar menggunakan pemetaan.');
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun));
            } else {
                // Error spesifik sudah di set di atas jika ada logErrors
                if (empty($logErrors)) {
                    $session->setFlashdata('error', 'Terjadi kesalahan umum saat memproses jurnal ke Buku Besar dengan pemetaan. Silakan periksa log sistem.');
                }
                return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun));
            }

        } catch (\Exception $e) {
            log_message('error', "[BukuBesarController::proses] Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->to(base_url('admin/buku_besar?bulan=' . $bulan . '&tahun=' . $tahun))
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // === FUNGSI CRUD PEMETAAN AKUN (BARU) ===

    public function pemetaan()
    {
        // Ambil data pemetaan join dengan nama akun untuk tampilan
        $pemetaanData = $this->pemetaanModel
            ->select('pemetaan_akun.*, ad.nama_akun as nama_akun_debit, ak.nama_akun as nama_akun_kredit, ad.kode_akun as kode_akun_debit, ak.kode_akun as kode_akun_kredit')
            ->join('akun ad', 'ad.id = pemetaan_akun.id_akun_debit', 'left')
            ->join('akun ak', 'ak.id = pemetaan_akun.id_akun_kredit', 'left')
            ->orderBy('pemetaan_akun.prioritas', 'DESC')
            ->orderBy('pemetaan_akun.kategori_jurnal', 'ASC')
            ->orderBy('pemetaan_akun.pola_uraian', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Pemetaan Jurnal ke Akun',
            'pemetaan' => $pemetaanData
        ];
        // Buat view ini: app/Views/admin/buku_besar/pemetaan_index.php
        return view('admin/buku_besar/pemetaan_index', $data);
    }

    public function createPemetaan()
    {
        $data = [
            'title' => 'Tambah Aturan Pemetaan',
            // Ambil daftar akun untuk dropdown
            'akun_list' => $this->akunModel->orderBy('kode_akun', 'ASC')->findAll() // Urutkan berdasarkan kode
        ];
        // Buat view ini: app/Views/admin/buku_besar/pemetaan_create.php
        return view('admin/buku_besar/pemetaan_create', $data);
    }

    public function storePemetaan()
    {
        $rules = [
            'pola_uraian' => 'required|max_length[255]',
            'kategori_jurnal' => 'required|in_list[DUM,DUK]',
            'id_akun_debit' => 'required|integer|is_not_unique[akun.id]', // Pastikan ID akun valid
            'id_akun_kredit' => 'required|integer|is_not_unique[akun.id]',
            'prioritas' => 'permit_empty|integer',
            'deskripsi' => 'permit_empty|string',
        ];

        $messages = [
            'id_akun_debit' => ['is_not_unique' => 'Akun Debit yang dipilih tidak valid.'],
            'id_akun_kredit' => ['is_not_unique' => 'Akun Kredit yang dipilih tidak valid.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->validator->getValidated();

        // Periksa debit != kredit
        if ($data['id_akun_debit'] == $data['id_akun_kredit']) {
            return redirect()->back()->withInput()->with('error', 'Akun Debit dan Kredit tidak boleh sama.');
        }

        $data['prioritas'] = empty($data['prioritas']) ? 0 : $data['prioritas']; // Default prioritas 0 jika kosong


        if ($this->pemetaanModel->insert($data)) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('success', 'Aturan pemetaan berhasil ditambahkan.');
        } else {
            log_message('error', 'Gagal insert pemetaan: ' . json_encode($this->pemetaanModel->errors()));
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan aturan pemetaan. Periksa log.');
        }
    }

    public function editPemetaan($id)
    {
        $pemetaan = $this->pemetaanModel->find($id);
        if (!$pemetaan) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))->with('error', 'Aturan pemetaan tidak ditemukan.');
        }
        $data = [
            'title' => 'Edit Aturan Pemetaan',
            'pemetaan' => $pemetaan,
            'akun_list' => $this->akunModel->orderBy('kode_akun', 'ASC')->findAll()
        ];
        // Buat view ini: app/Views/admin/buku_besar/pemetaan_edit.php
        return view('admin/buku_besar/pemetaan_edit', $data);
    }

    public function updatePemetaan($id)
    {
        $pemetaan = $this->pemetaanModel->find($id);
        if (!$pemetaan) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))->with('error', 'Aturan pemetaan tidak ditemukan.');
        }

        // Rules sama seperti create
        $rules = [
            'pola_uraian' => 'required|max_length[255]',
            'kategori_jurnal' => 'required|in_list[DUM,DUK]',
            'id_akun_debit' => 'required|integer|is_not_unique[akun.id]',
            'id_akun_kredit' => 'required|integer|is_not_unique[akun.id]',
            'prioritas' => 'permit_empty|integer',
            'deskripsi' => 'permit_empty|string',
        ];
        $messages = [
            'id_akun_debit' => ['is_not_unique' => 'Akun Debit yang dipilih tidak valid.'],
            'id_akun_kredit' => ['is_not_unique' => 'Akun Kredit yang dipilih tidak valid.'],
        ];


        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->validator->getValidated();

        if ($data['id_akun_debit'] == $data['id_akun_kredit']) {
            return redirect()->back()->withInput()->with('error', 'Akun Debit dan Kredit tidak boleh sama.');
        }
        $data['prioritas'] = empty($data['prioritas']) ? 0 : $data['prioritas'];

        if ($this->pemetaanModel->update($id, $data)) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('success', 'Aturan pemetaan berhasil diperbarui.');
        } else {
            log_message('error', 'Gagal update pemetaan ID ' . $id . ': ' . json_encode($this->pemetaanModel->errors()));
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui aturan pemetaan. Periksa log.');
        }
    }

    public function deletePemetaan($id)
    {
        // Optional: Tambahkan konfirmasi form method POST untuk keamanan
        $pemetaan = $this->pemetaanModel->find($id);
        if (!$pemetaan) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('error', 'Aturan pemetaan tidak ditemukan.');
        }

        if ($this->pemetaanModel->delete($id)) {
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('success', 'Aturan pemetaan berhasil dihapus.');
        } else {
            log_message('error', 'Gagal delete pemetaan ID ' . $id . ': ' . json_encode($this->pemetaanModel->errors()));
            return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                ->with('error', 'Gagal menghapus aturan pemetaan. Periksa log.');
        }
    }
    /**
     * Menjalankan proses pembuatan pemetaan otomatis.
     */
    public function generateAutoMapping()
    {
        try {
            // Identifikasi Akun Kas Utama (WAJIB SAMA dengan yang dipakai di proses())
            $akunKasUtama = $this->akunModel->where('nama_akun', 'Simpanan di Bank')->first();
            if (!$akunKasUtama) {
                $akunKasUtama = $this->akunModel->where('nama_akun', 'Kas')->first();
                if (!$akunKasUtama) {
                    return redirect()->to(base_url('admin/buku_besar/pemetaan'))
                        ->with('error', 'Akun Kas/Bank Utama ("Simpanan di Bank" atau "Kas") tidak ditemukan untuk proses otomatis.');
                }
            }
            $idAkunKasUtama = $akunKasUtama['id'];
            log_message('info', "[generateAutoMapping] Starting automatic mapping process using Main Cash Account ID: {$idAkunKasUtama} ('{$akunKasUtama['nama_akun']}')");


            // Panggil method di model
            $stats = $this->pemetaanModel->generateOtomatisFromJournal($idAkunKasUtama);

            // Siapkan pesan feedback
            $message = "Proses pemetaan otomatis selesai. <br>";
            $message .= "Aturan baru dibuat: " . $stats['created'] . "<br>";
            if ($stats['skipped_exist'] > 0)
                $message .= "Dilewati (sudah ada): " . $stats['skipped_exist'] . "<br>";
            if ($stats['skipped_special'] > 0)
                $message .= "Dilewati (kasus khusus: penyusutan/transfer): " . $stats['skipped_special'] . "<br>";
            if ($stats['failed_match'] > 0)
                $message .= "Gagal Cocok (uraian != nama akun): " . $stats['failed_match'] . " (Perlu pemetaan manual)<br>";
            if ($stats['skipped_same_dk'] > 0)
                $message .= "Dilewati (akun D/K sama): " . $stats['skipped_same_dk'] . "<br>";


            if ($stats['created'] > 0) {
                session()->setFlashdata('success', $message);
            } else {
                session()->setFlashdata('info', $message); // Gunakan info jika tidak ada yg baru
            }

        } catch (\Exception $e) {
            log_message('error', "[generateAutoMapping] Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            session()->setFlashdata('error', 'Terjadi kesalahan sistem saat membuat pemetaan otomatis: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/buku_besar/pemetaan'));
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

    /**
     * Menampilkan Laporan Laba Rugi.
     * Memperbaiki logika pemisahan berdasarkan kategori aktual.
     */
    public function labaRugi()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $laporanData = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        // DEBUGGING: Cek apa yang dikembalikan model dan bagaimana controller memprosesnya
        // echo "<pre>Laporan Data dari Model untuk {$bulan}-{$tahun}:\n";
        // print_r($laporanData);
        // echo "</pre>";
        // // die; // Aktifkan untuk berhenti di sini saat debugging

        $pendapatanItems = [];
        $bebanItems = [];
        $totalPendapatan = 0;
        $totalBeban = 0;

        // Kategori disesuaikan dengan yang ada di Model (dan di tabel 'akun')
        $kategoriPendapatanActual = ['PENDAPATAN'];
        $kategoriBebanActual = [
            'BEBAN',
            'BEBAN PENYUSUTAN', // Harus sama dengan di Model
            // 'BEBAN PAJAK',   // Harus sama dengan di Model
        ];

        if (!empty($laporanData)) {
            foreach ($laporanData as $item) {
                // 'saldo' dari model adalah saldo mutasi periode
                $saldo = floatval($item['saldo'] ?? 0);

                if (isset($item['kategori'])) {
                    if (in_array($item['kategori'], $kategoriPendapatanActual)) {
                        $totalPendapatan += $saldo;
                        $pendapatanItems[] = $item;
                    } elseif (in_array($item['kategori'], $kategoriBebanActual)) {
                        $totalBeban += $saldo;
                        $bebanItems[] = $item;
                    } else {
                        log_message('debug', "[Controller::labaRugi] Akun '{$item['nama_akun']}' (Kategori: '{$item['kategori']}') tidak termasuk dalam definisi L/R saat ini.");
                    }
                }
            }
        }
        // echo "<pre>Pendapatan Items:\n"; print_r($pendapatanItems); echo "</pre>";
        // echo "<pre>Beban Items:\n"; print_r($bebanItems); echo "</pre>";
        // echo "<pre>Total Pendapatan: $totalPendapatan, Total Beban: $totalBeban</pre>";
        // // die; // Aktifkan untuk berhenti di sini saat debugging


        $labaRugiBersih = $totalPendapatan - $totalBeban;

        $data = [
            'title' => 'Laporan Laba Rugi',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'pendapatan_items' => $pendapatanItems,
            'beban_items' => $bebanItems,
            'total_pendapatan' => $totalPendapatan,
            'total_beban' => $totalBeban,
            'laba_rugi_bersih' => $labaRugiBersih,
            'bulanNames' => $this->bulanNames,
        ];

        return view('admin/buku_besar/laba_rugi', $data); // Sesuaikan path view jika perlu
    }

    /**
     * Mengembalikan array mapping Neraca Komparatif.
     * !! VERIFIKASI DAN LENGKAPI MAPPING INI SESUAI COA ANDA !!
     */
    private function getNeracaMappingData(): array
    {
        // Helper untuk mendapatkan kode akun berdasarkan ID, untuk kejelasan
        $getKode = function ($idAkun) {
            // Cache sederhana untuk lookup ID ke Kode Akun dalam satu request
            static $akunCache = [];
            if (!isset($akunCache[$idAkun])) {
                $akun = $this->akunModel->select('kode_akun')->find($idAkun);
                $akunCache[$idAkun] = $akun ? $akun['kode_akun'] : 'KODE_NOT_FOUND_FOR_ID_' . $idAkun;
            }
            return $akunCache[$idAkun];
        };

        // Kode Akun untuk ASET TETAP AKTUAL.
        // Ini harus ID dari akun ASET di tabel 'akun', BUKAN akun beban pembelian.
        // Ambil dari pemetaan DUK untuk "Pembelian Inventaris..." jika ID tersebut benar-benar akun ASET.
        // Jika tidak, Anda harus tahu ID akun asetnya secara manual.
        // Contoh: ID 148 adalah 'Pembelian Inventaris Komputer' (DUK), asumsikan ini akun asetnya.
        $kodeInvKomputer = $getKode(15); // Ganti 148 dengan ID akun ASET 'Inventaris Komputer'
        $kodeInvMebel = $getKode(13);    // Ganti 146 dengan ID akun ASET 'Inventaris Mebel'
        $kodeInvGedung = $getKode(14);   // Ganti 147 dengan ID akun ASET 'Inventaris Gedung/Bangunan' (atau 138)
        $kodeInvKendaraan = $getKode(16); // Ganti 149 dengan ID akun ASET 'Inventaris Kendaraan'

        return [
            // --- ASET LANCAR (Urutan 1) ---
            $getKode(1) => ['ASET_LANCAR', 1, false, null], // Kas
            $getKode(2) => ['ASET_LANCAR', 1, false, null], // Simpanan di Bank
            $getKode(3) => ['ASET_LANCAR', 1, false, null], // Simpanan Deposito (Aset)
            $getKode(17) => ['ASET_LANCAR', 1, false, null], // Piutang Biasa
            $getKode(18) => ['ASET_LANCAR', 1, false, null], // Piutang Khusus
            $getKode(19) => ['ASET_LANCAR', 1, false, null], // Piutang Ragu-ragu
            $getKode(20) => ['ASET_LANCAR', 1, false, null], // Penyusutan Piutang Ragu-ragu

            // --- ASET TAK LANCAR (Urutan 2) ---
            $getKode(21) => ['ASET_TAK_LANCAR', 2, false, null], // SImpanan di BK3D
            $getKode(22) => ['ASET_TAK_LANCAR', 2, false, null], // Investasi
            $getKode(23) => ['ASET_TAK_LANCAR', 2, false, null], // Serta Data

            // --- ASET TETAP (Urutan 3) ---

            $kodeInvMebel => ['ASET_TETAP', 3, false, null],      // Inventaris Mebel
            $getKode(5) => ['ASET_TETAP', 3, true, $kodeInvMebel],      // Akum. Peny. Mebel

            $kodeInvGedung => ['ASET_TETAP', 3, false, null],      // Inventaris Gedung
            $getKode(6) => ['ASET_TETAP', 3, true, $kodeInvGedung],     // Akum. Peny. Gedung

            // $getKode(ID_ASET_TERTANGGUH) => ['ASET_TETAP', 3, false, null], // Jika ada Aset Tertangguh
            // $getKode(40)      => ['ASET_TETAP', 3, true,  $getKode(ID_ASET_TERTANGGUH)], // Akum. Peny. Tertangguh

            $kodeInvKomputer => ['ASET_TETAP', 3, false, null],      // Inventaris Komputer
            $getKode(4) => ['ASET_TETAP', 3, true, $kodeInvKomputer],   // Akum. Peny. Komputer          

            $kodeInvKendaraan => ['ASET_TETAP', 3, false, null],      // Inventaris Kendaraan
            $getKode(7) => ['ASET_TETAP', 3, true, $kodeInvKendaraan],  // Akum. Peny. Kendaraan

            // --- KEWAJIBAN JANGKA PENDEK (Urutan 4) ---
            $getKode(34) => ['KEWAJIBAN_PENDEK', 4, false, null], // Simpanan Non-Saham
            $getKode(35) => ['KEWAJIBAN_PENDEK', 4, false, null], // Jasa Simpanan Non-Saham
            $getKode(37) => ['KEWAJIBAN_PENDEK', 4, false, null], // Simpanan Sukarela (SS)
            $getKode(162) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana dana
            $getKode(163) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Pengurus
            $getKode(164) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Pendidikan
            $getKode(165) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Karyawan
            $getKode(56) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana PDK
            $getKode(57) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Sosial
            $getKode(166) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Insentif
            $getKode(167) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Supervisi
            $getKode(168) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Beban yang Harus Di bayar
            $getKode(169) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana RAT
            $getKode(171) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Kesejahteraan
            $getKode(171) => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana SHU tahun lalu
            $getKode(172) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Pemilihan Pengurus
            $getKode(173) => ['KEWAJIBAN_PENDEK', 4, false, null], // SHU tahun Sekarang

            // --- KEWAJIBAN JANGKA PANJANG (Urutan 5) ---
            $getKode(174) => ['KEWAJIBAN_PANJANG', 5, false, null], // Dana Sehat
            $getKode(175) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan sp/sw
            $getKode(176) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Dana Dana
            $getKode(177) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan CAP
            $getKode(42) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Dana RAT
            $getKode(178) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Biaya Pajak
            $getKode(43) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Dana Pendampingan
            $getKode(179) => ['KEWAJIBAN_PANJANG', 5, false, null], // Pemupukan Modal Tetap
            $getKode(180) => ['KEWAJIBAN_PANJANG', 5, false, null], // Tabungan Pesangon Karyawan
            $getKode(181) => ['KEWAJIBAN_PANJANG', 5, false, null], // Pinjaman pihak ke 2

            // --- EKUITAS (MODAL) (Urutan 6) ---
            $getKode(36) => ['EKUITAS', 6, false, null], // Simpanan Pokok (SP)
            $getKode(38) => ['EKUITAS', 6, false, null], // Simpanan Wajib (SW)
            $getKode(39) => ['EKUITAS', 6, false, null], // Simpanan Wajib Penyertaan (SWP)
            $getKode(182) => ['EKUITAS', 6, false, null], // Iuran Dana Sehat
            $getKode(46) => ['EKUITAS', 6, false, null], // Pendapatan Hibah
            $getKode(183) => ['EKUITAS', 6, false, null], // Cadangan Likuiditas
            $getKode(184) => ['EKUITAS', 6, false, null], // Cadangan Koperasi
            $getKode(185) => ['EKUITAS', 6, false, null], // Dana Risiko
            $getKode(186) => ['EKUITAS', 6, false, null], // PJKR
            $getKode(187) => ['EKUITAS', 6, false, null], // SHU

        ];
    }
    private function getMasterNeracaStructure(): array
    {
        return [
            'ASET_LANCAR' => [
                'label' => 'ASET LANCAR',
                'urutan' => 1,
                'no_induk_prefix' => 'I',
                'no_induk_val' => 1,
                'items_template' => [
                    'KAS' => ['nama' => 'kas', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMPANAN_BANK' => ['nama' => 'Simpanan di Bank', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMPANAN_DEPOSITO' => ['nama' => 'Simpanan deposito', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PIUTANG_BIASA' => ['nama' => 'Piutang Biasa', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PIUTANG_KHUSUS' => ['nama' => 'Piutang Khusus', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PIUTANG_RAGU' => ['nama' => 'Piutang Ragu-ragu', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PENY_PIUTANG_RAGU' => ['nama' => 'Penyusutan Piutang Ragu', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'ASET_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true], // Sebenarnya ini kontra-aset
                ]
            ],
            'ASET_TAK_LANCAR' => [
                'label' => 'ASET TAK LANCAR',
                'urutan' => 2,
                'no_induk_prefix' => 'I',
                'no_induk_val' => 2,
                'items_template' => [
                    'SIMPANAN_BKD' => ['nama' => 'Simpanan di BK#D', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'ASET_TAK_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INVESTASI' => ['nama' => 'Investasi', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'ASET_TAK_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SERTA_DATA' => ['nama' => 'Serta Data', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'ASET_TAK_LANCAR', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'ASET_TETAP' => [
                'label' => 'ASET TETAP',
                'urutan' => 3,
                'no_induk_prefix' => 'I',
                'no_induk_val' => 3,
                'items_template' => [
                    'INV_MEBEL' => ['nama' => 'Inventaris Barang Mebeler', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'BEBAN_TERTANGGUH' => ['nama' => 'Beban Tertangguh', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_GEDUNG' => ['nama' => 'Inventaris Gedung/Bangunan', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_PAGAR' => ['nama' => 'Inventaris Pagar', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_TANAH' => ['nama' => 'Inventaris tanah', 'is_editable' => false, 'nomor_display_sub' => '5', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_KOMPUTER' => ['nama' => 'Inventaris Komputer', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'INV_KENDARAAN' => ['nama' => 'Inventaris Kendaraan', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ],
                'akumulasi_template' => [ // Kunci di sini adalah kode_akun_internal dari ASET INDUKNYA
                    'INV_MEBEL' => ['kode_akun_internal_akum' => 'AKUM_INV_MEBEL', 'uraian_akun' => '(Akumulasi Penyusutan Mebeler)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'BEBAN_TERTANGGUH' => ['kode_akun_internal_akum' => 'AKUM_BEBAN_TERTANGGUH', 'uraian_akun' => '(Akumulasi Penyusutan Beban Tertangguh)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_GEDUNG' => ['kode_akun_internal_akum' => 'AKUM_INV_GEDUNG', 'uraian_akun' => '(Akumulasi Penyusutan Gedung)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_TANAH' => ['kode_akun_internal_akum' => 'AKUM_INV_TANAH', 'uraian_akun' => '(Akumulasi Penyusutan Tanah)', 'is_editable' => false, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_KOMPUTER' => ['kode_akun_internal_akum' => 'AKUM_INV_KOMPUTER', 'uraian_akun' => '(Akumulasi Penyusutan Komputer)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                    'INV_KENDARAAN' => ['kode_akun_internal_akum' => 'AKUM_INV_KENDARAAN', 'uraian_akun' => '(Akumulasi Penyusutan Kendaraan)', 'is_editable' => true, 'grup_laporan' => 'ASET_TETAP', 'is_akumulasi' => true, 'is_item_utama' => false],
                ]
            ],
            'KEWAJIBAN_JANGKA_PENDEK' => [
                'label' => 'KEWAJIBAN JANGKA PENDEK',
                'urutan' => 4,
                'no_induk_prefix' => 'II',
                'no_induk_val' => 1,
                'items_template' => [
                    'SIMP_NON_SAHAM' => ['nama' => 'simpanan Non Saham', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_JASA_NON_SAHAM' => ['nama' => 'Simpanan Jasa Non Saham', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_SUKARELA' => ['nama' => 'Simpanan Suka Rela', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_DANA' => ['nama' => 'Simpanan Dana', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_PENGURUS' => ['nama' => 'Dana Pengurus', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_PENDIDIKAN' => ['nama' => 'Dana Pendidikan', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_KARYAWAN' => ['nama' => 'Dana Karyawan', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_PDK' => ['nama' => 'Dana PDK', 'is_editable' => true, 'nomor_display_sub' => '8', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_SOSIAL' => ['nama' => 'Dana Sosial', 'is_editable' => true, 'nomor_display_sub' => '9', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_INSENTIF' => ['nama' => 'Dana Insentif', 'is_editable' => true, 'nomor_display_sub' => '10', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_SUPERVISI' => ['nama' => 'Dana Supervisi', 'is_editable' => true, 'nomor_display_sub' => '11', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'Beban_YMHDB' => ['nama' => 'Beban yang Masih harus di Bayar', 'is_editable' => true, 'nomor_display_sub' => '12', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_RAT' => ['nama' => '1. Dana RAT', 'is_editable' => true, 'nomor_display_sub' => '13', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_KESEJAHTERAAN' => ['nama' => '2. Dana Kesejahteraan', 'is_editable' => true, 'nomor_display_sub' => '14', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_SHU_LALU' => ['nama' => '3. Dana SHU tahun lalu', 'is_editable' => true, 'nomor_display_sub' => '15', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_PENGURUS' => ['nama' => '4. Titipan Pemilihan Pengurus', 'is_editable' => true, 'nomor_display_sub' => '15.1', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SHU_TAHUN_INI_KEWAJIBAN' => ['nama' => '4. SHU tahun sekarang', 'is_editable' => true, 'nomor_display_sub' => '16', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PENDEK', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'KEWAJIBAN_JANGKA_PANJANG' => [
                'label' => 'KEWAJIBAN JANGKA PANJANG',
                'urutan' => 5,
                'no_induk_prefix' => 'II',
                'no_induk_val' => 2,
                'items_template' => [
                    'DANA_SEHAT_KJP' => ['nama' => 'Dana Sehat', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIP_SPSW' => ['nama' => 'Titip SP/SW', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_DANADANA' => ['nama' => 'Titipan Dana-dana', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_CAP' => ['nama' => 'Titipan (CAP)', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_DANA_RAT_KJP' => ['nama' => 'Titipan Dana RAT', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_BIAYA_PAJAK' => ['nama' => 'Titipan Biaya Pajak', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TITIPAN_DANA_PENDAMPING' => ['nama' => 'Titipan Dana Pendamping', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PEMUPUKAN_MODAL_TETAP' => ['nama' => 'Pemupukan Modal tetap', 'is_editable' => true, 'nomor_display_sub' => '8', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'TAB_PESANGON' => ['nama' => 'Tabungan Pesangon Karyawan', 'is_editable' => true, 'nomor_display_sub' => '9', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PINJAMAN_PIHAK2' => ['nama' => 'Pinjaman Pihak Ke-2', 'is_editable' => true, 'nomor_display_sub' => '10', 'grup_laporan' => 'KEWAJIBAN_JANGKA_PANJANG', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'EKUITAS' => [
                'label' => 'EKUITAS (MODAL)',
                'urutan' => 6,
                'no_induk_prefix' => 'II',
                'no_induk_val' => 3,
                'items_template' => [
                    'SIMP_POKOK' => ['nama' => 'Simpanan Pokok', 'is_editable' => true, 'nomor_display_sub' => '1', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_WAJIB' => ['nama' => 'Simpanan Wajib', 'is_editable' => true, 'nomor_display_sub' => '2', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SIMP_SWP' => ['nama' => 'Simpanan SWP', 'is_editable' => true, 'nomor_display_sub' => '3', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'IURAN_DANA_SEHAT' => ['nama' => 'Iuran Dana Sehat', 'is_editable' => true, 'nomor_display_sub' => '4', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'HIBAH' => ['nama' => 'Hibah', 'is_editable' => true, 'nomor_display_sub' => '5', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'CAD_LIKUIDITAS' => ['nama' => 'Cadangan Likuiditas', 'is_editable' => true, 'nomor_display_sub' => '6', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'CAD_KOPERASI' => ['nama' => 'Cadangan Koperasi', 'is_editable' => true, 'nomor_display_sub' => '7', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'DANA_RESIKO' => ['nama' => 'Dana Resiko', 'is_editable' => true, 'nomor_display_sub' => '8', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'PJKR' => ['nama' => 'PJKR', 'is_editable' => true, 'nomor_display_sub' => '9', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                    'SHU_EKUITAS_TAHUN_INI' => ['nama' => 'SHU', 'is_editable' => true, 'nomor_display_sub' => '10', 'grup_laporan' => 'EKUITAS', 'is_akumulasi' => false, 'parent_kode_akun_internal' => null, 'is_item_utama' => true],
                ]
            ],
            'TIDAK_TERPETAKAN' => ['label' => 'AKUN BELUM TERPETAKAN', 'urutan' => 99, 'no_induk_prefix' => '', 'no_induk_val' => 0, 'items_template' => []]
        ];
    }

    /**
     * Method helper untuk menyiapkan data Neraca yang akan digunakan oleh view dan export.
     */
    private function _prepareNeracaViewData(int $tahun, int $bulan): array
    {
        if (!$this->neracaModel) {
            // Kembalikan struktur kosong jika model tidak ada, agar tidak error total
            return [
                'laporan' => $this->getMasterNeracaStructure(), // Kirim struktur dasar
                'grand_total_aset_current' => 0,
                'grand_total_aset_prev' => 0,
                'grand_total_pasiva_modal_current' => 0,
                'grand_total_pasiva_modal_prev' => 0,
                'laba_rugi_bersih_current' => 0,
                'prevBulan' => (int) date('n', strtotime("$tahun-$bulan-01 -1 month")),
                'prevTahun' => (int) date('Y', strtotime("$tahun-$bulan-01 -1 month")),
            ];
        }

        $currentDate = new \DateTime();
        try {
            $currentDate->setDate($tahun, $bulan, 1);
        } catch (\Exception $e) {
            // Default ke tanggal saat ini jika tanggal tidak valid
            $currentDate = new \DateTime();
            $tahun = (int) $currentDate->format('Y');
            $bulan = (int) $currentDate->format('n');
            log_message('error', 'Tanggal tidak valid di _prepareNeracaViewData: ' . $e->getMessage());
        }


        $prevDate = (clone $currentDate)->modify('-1 month');
        $prevBulan = (int) $prevDate->format('n');
        $prevTahun = (int) $prevDate->format('Y');

        $masterStructure = $this->getMasterNeracaStructure();
        $laporan = [];

        foreach ($masterStructure as $groupKey => $groupDetails) {
            $laporan[$groupKey] = [
                'label' => $groupDetails['label'],
                'urutan' => $groupDetails['urutan'],
                'no_induk_prefix' => $groupDetails['no_induk_prefix'],
                'no_induk_val' => $groupDetails['no_induk_val'],
                'items' => [],
                'akumulasi_lookup' => [],
                'total_current' => 0,
                'total_prev' => 0,
                'total_net_current' => 0,
                'total_net_prev' => 0,
            ];
            if (isset($groupDetails['items_template'])) {
                foreach ($groupDetails['items_template'] as $itemKey => $itemDetails) {
                    $laporan[$groupKey]['items'][$itemKey] = [
                        'id' => null,
                        'nama' => $itemDetails['nama'],
                        'nomor_display_sub' => $itemDetails['nomor_display_sub'],
                        'is_editable' => $itemDetails['is_editable'],
                        'saldo_current' => 0,
                        'saldo_prev' => 0
                    ];
                }
            }
            if ($groupKey === 'ASET_TETAP' && isset($groupDetails['akumulasi_template'])) {
                foreach ($groupDetails['akumulasi_template'] as $itemKeyParent => $akumDetails) {
                    $laporan[$groupKey]['akumulasi_lookup'][$itemKeyParent] = [
                        'id' => null,
                        'nama' => $akumDetails['uraian_akun'],
                        'is_editable' => $akumDetails['is_editable'],
                        'saldo_current' => 0,
                        'saldo_prev' => 0
                    ];
                }
            }
        }

        $itemsCurrentPeriod = $this->neracaModel->where('periode_tahun', $tahun)->where('periode_bulan', $bulan)->findAll();
        $itemsPrevPeriod = $this->neracaModel->where('periode_tahun', $prevTahun)->where('periode_bulan', $prevBulan)->findAll();

        $dbCurrentMap = [];
        foreach ($itemsCurrentPeriod as $dbItem) {
            $dbCurrentMap[$dbItem['kode_akun_internal']] = $dbItem;
        }
        $dbPrevMap = [];
        foreach ($itemsPrevPeriod as $dbItem) {
            $dbPrevMap[$dbItem['kode_akun_internal']] = $dbItem;
        }

        foreach ($laporan as $groupKey => &$groupData) {
            if (isset($groupData['items'])) {
                foreach ($groupData['items'] as $itemKey => &$itemData) {
                    if (isset($dbCurrentMap[$itemKey]) && !$dbCurrentMap[$itemKey]['is_akumulasi']) {
                        $itemData['id'] = $dbCurrentMap[$itemKey]['id'];
                        $itemData['saldo_current'] = (float) $dbCurrentMap[$itemKey]['nilai'];
                    }
                    if (isset($dbPrevMap[$itemKey]) && !$dbPrevMap[$itemKey]['is_akumulasi']) {
                        $itemData['saldo_prev'] = (float) $dbPrevMap[$itemKey]['nilai'];
                    }
                }
                unset($itemData);
            }
            if ($groupKey === 'ASET_TETAP' && isset($groupData['akumulasi_lookup'])) {
                foreach ($groupData['akumulasi_lookup'] as $itemKeyParent => &$akumData) {
                    $akumKodeInternal = 'AKUM_' . $itemKeyParent;
                    if (isset($dbCurrentMap[$akumKodeInternal]) && $dbCurrentMap[$akumKodeInternal]['is_akumulasi'] && $dbCurrentMap[$akumKodeInternal]['parent_kode_akun_internal'] == $itemKeyParent) {
                        $akumData['id'] = $dbCurrentMap[$akumKodeInternal]['id'];
                        $akumData['saldo_current'] = (float) $dbCurrentMap[$akumKodeInternal]['nilai'];
                    }
                    if (isset($dbPrevMap[$akumKodeInternal]) && $dbPrevMap[$akumKodeInternal]['is_akumulasi'] && $dbPrevMap[$akumKodeInternal]['parent_kode_akun_internal'] == $itemKeyParent) {
                        $akumData['saldo_prev'] = (float) $dbPrevMap[$akumKodeInternal]['nilai'];
                    }
                }
                unset($akumData);
            }
        }
        unset($groupData);

        $laba_rugi_bersih_current = 0;
        if (isset($laporan['EKUITAS']['items']['SHU_EKUITAS_TAHUN_INI']['saldo_current'])) {
            $laba_rugi_bersih_current = (float) $laporan['EKUITAS']['items']['SHU_EKUITAS_TAHUN_INI']['saldo_current'];
        }
        // Atau, jika SHU tahun ini di Kewajiban Jangka Pendek juga relevan (jarang untuk L/R Berjalan di neraca)
        // if(isset($laporan['KEWAJIBAN_JANGKA_PENDEK']['items']['SHU_TAHUN_INI_KEWAJIBAN']['saldo_current'])){
        //    // $laba_rugi_bersih_current = (float) $laporan['KEWAJIBAN_JANGKA_PENDEK']['items']['SHU_TAHUN_INI_KEWAJIBAN']['saldo_current'];
        // }


        $grand_total_aset_current = 0;
        $grand_total_aset_prev = 0;
        $grand_total_pasiva_modal_current = 0;
        $grand_total_pasiva_modal_prev = 0;

        foreach ($laporan as $groupKey => &$groupData) {
            $current_group_total = 0;
            $prev_group_total = 0;
            if (isset($groupData['items'])) {
                foreach ($groupData['items'] as $itemKey => $itemData) {
                    // Untuk total grup, jangan masukkan SHU_EKUITAS_TAHUN_INI jika L/R berjalan akan ditambahkan terpisah ke subtotal Ekuitas
                    if (!($groupKey === 'EKUITAS' && $itemKey === 'SHU_EKUITAS_TAHUN_INI' && ($laba_rugi_bersih_current == $itemData['saldo_current']))) {
                        $current_group_total += $itemData['saldo_current'];
                    }
                    $prev_group_total += $itemData['saldo_prev'];
                }
            }
            $groupData['total_current'] = $current_group_total;
            $groupData['total_prev'] = $prev_group_total;

            if ($groupKey === 'ASET_TETAP') {
                $net_current_total_aset_tetap = 0;
                $net_prev_total_aset_tetap = 0;
                if (isset($groupData['items'])) {
                    foreach ($groupData['items'] as $itemKey => $itemData) {
                        $akum_current = $groupData['akumulasi_lookup'][$itemKey]['saldo_current'] ?? 0;
                        $akum_prev = $groupData['akumulasi_lookup'][$itemKey]['saldo_prev'] ?? 0;
                        $net_current_total_aset_tetap += ($itemData['saldo_current'] + $akum_current);
                        $net_prev_total_aset_tetap += ($itemData['saldo_prev'] + $akum_prev);
                    }
                }
                $groupData['total_net_current'] = $net_current_total_aset_tetap;
                $groupData['total_net_prev'] = $net_prev_total_aset_tetap;
            }

            if (in_array($groupKey, ['ASET_LANCAR', 'ASET_TAK_LANCAR'])) {
                $grand_total_aset_current += $groupData['total_current'];
                $grand_total_aset_prev += $groupData['total_prev'];
            } elseif ($groupKey == 'ASET_TETAP') {
                $grand_total_aset_current += $groupData['total_net_current'];
                $grand_total_aset_prev += $groupData['total_net_prev'];
            } elseif (in_array($groupKey, ['KEWAJIBAN_JANGKA_PENDEK', 'KEWAJIBAN_JANGKA_PANJANG'])) {
                $grand_total_pasiva_modal_current += $groupData['total_current'];
                $grand_total_pasiva_modal_prev += $groupData['total_prev'];
            } elseif ($groupKey == 'EKUITAS') {
                $grand_total_pasiva_modal_current += $groupData['total_current'];
                $grand_total_pasiva_modal_current += $laba_rugi_bersih_current; // L/R berjalan ditambahkan ke total Pasiva & Modal
                $grand_total_pasiva_modal_prev += $groupData['total_prev'];
            }
        }
        unset($groupData);

        return [
            'laporan' => $laporan,
            'grand_total_aset_current' => $grand_total_aset_current,
            'grand_total_aset_prev' => $grand_total_aset_prev,
            'grand_total_pasiva_modal_current' => $grand_total_pasiva_modal_current,
            'grand_total_pasiva_modal_prev' => $grand_total_pasiva_modal_prev,
            'laba_rugi_bersih_current' => $laba_rugi_bersih_current,
            'prevBulan' => $prevBulan,
            'prevTahun' => $prevTahun,
        ];
    }
    public function neraca()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $prevBulan = $bulan - 1;
        $prevTahun = $tahun;
        if ($bulan == 1) {
            $prevBulan = 12;
            $prevTahun = $tahun - 1;
        }

        $startDate = "$tahun-$bulan-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $isBulanPertama = (int) $bulan === 1;

        $mapping = $this->MappingModel->orderBy('urutan', 'ASC')->findAll();
        $laporan = [];

        foreach ($mapping as $row) {
            $jenis = $row['jenis']; // ambil jenis dari mapping

            // SALDO BULAN INI
            $utama = $this->saldoAkunModel->getSaldoAkhir($row['id_akun_utama'], $endDate, $jenis);
            $pengurang = $this->saldoAkunModel->getSaldoAkhir($row['id_akun_pengurang'], $endDate, $jenis);

            $saldoPengurangNow = (!empty($row['id_akun_pengurang']) && $pengurang) ? $pengurang->saldo : 0;
            $saldoAkhir = ($utama->saldo ?? 0) - abs($saldoPengurangNow);

            // SALDO BULAN SEBELUMNYA
            $prevStart = "$prevTahun-$prevBulan-01";
            $prevEnd = date("Y-m-t", strtotime($prevStart));

            $prevUtama = $this->saldoAkunModel->getSaldoAkhir($row['id_akun_utama'], $prevEnd, $jenis);
            $prevPengurang = $this->saldoAkunModel->getSaldoAkhir($row['id_akun_pengurang'], $prevEnd, $jenis);

            $saldoPengurangPrev = (!empty($row['id_akun_pengurang']) && $prevPengurang) ? $prevPengurang->saldo : 0;
            $saldoAkhirPrev = ($prevUtama->saldo ?? 0) - $saldoPengurangPrev;

            $laporan[] = [
                'nama' => $row['nama_laporan'],
                'jenis' => $jenis,
                'urutan' => $row['urutan'],
                'saldo_now' => $saldoAkhir,
                'saldo_prev' => $saldoAkhirPrev
            ];

        }

        // Filter & urutkan
        // Kelompokkan ke dalam 2 array utama: aktiva & pasiva
        $aktiva = array_filter($laporan, fn($item) => $item['jenis'] === 'AKTIVA');

        // PASIVA berisi beberapa jenis
        $jenisPasiva = ['KEWAJIBAN JANGKA PENDEK', 'KEWAJIBAN JANGKA PANJANG', 'EKUITAS'];
        $pasiva = [];

        foreach ($jenisPasiva as $jenis) {
            $group = array_filter($laporan, fn($item) => trim($item['jenis']) === $jenis);
            usort($group, fn($a, $b) => $a['urutan'] <=> $b['urutan']);
            $pasiva[$jenis] = $group;
        }

        usort($aktiva, fn($a, $b) => $a['urutan'] <=> $b['urutan']);

        return view('admin/buku_besar/neraca', [
            'aktiva' => $aktiva,
            'pasiva' => $pasiva,
            'namaBulanCurrent' => $this->bulanNames[(int) $bulan] ?? 'Bulan Tidak Diketahui',
            'tahun' => $tahun,
            'bulan' => $bulan,
            'prevBulan' => $prevBulan,
            'prevTahun' => $prevTahun,
            'bulanNames' => $this->bulanNames,
        ]);

    }

    public function updateNeracaItem()
    {
        if (!$this->neracaModel) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Kesalahan internal server: Model tidak termuat.']);
        }

        if ($this->request->isAJAX() && $this->request->getMethod(true) === 'POST') {
            $id = $this->request->getPost('id');
            $is_new_flag = $this->request->getPost('is_new'); // Ambil flag is_new
            $nilai_raw = $this->request->getPost('nilai');

            $nilai_db_str = str_replace(',', '.', (string) $nilai_raw);
            if (!is_numeric($nilai_db_str)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Nilai yang dimasukkan harus berupa angka.']);
            }
            $nilai_db = floatval($nilai_db_str);

            // Logika untuk INSERT jika 'id' kosong DAN 'is_new' adalah 'true'
            if (empty($id) && $is_new_flag === 'true') {
                $kode_akun_internal_js = $this->request->getPost('kode_akun_internal');
                $periode_tahun = (int) $this->request->getPost('periode_tahun');
                $periode_bulan = (int) $this->request->getPost('periode_bulan');

                if (empty($kode_akun_internal_js) || empty($periode_tahun) || empty($periode_bulan)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Data untuk membuat item baru tidak lengkap (kode akun, tahun, atau bulan).']);
                }

                $masterStructure = $this->getMasterNeracaStructure();
                $templateForInsert = null; // Ini akan berisi detail akun dari master
                $final_kode_akun_internal_db = $kode_akun_internal_js; // Default, bisa di-override untuk akumulasi
                $parent_for_akum_insert = null;

                if (strpos($kode_akun_internal_js, 'akum_') === 0) { // Handle Akumulasi
                    $parent_kode_internal_insert = substr($kode_akun_internal_js, 5);
                    if (isset($masterStructure['ASET_TETAP']['akumulasi_template'][$parent_kode_internal_insert])) {
                        $templateForInsert = $masterStructure['ASET_TETAP']['akumulasi_template'][$parent_kode_internal_insert];
                        $final_kode_akun_internal_db = $templateForInsert['kode_akun_internal_akum']; // Gunakan kode internal akumulasi yg benar
                        $parent_for_akum_insert = $parent_kode_internal_insert; // Simpan parent untuk DB
                    }
                } else { // Handle Item Utama
                    foreach ($masterStructure as $groupDetails) {
                        if (isset($groupDetails['items_template'][$kode_akun_internal_js])) {
                            $templateForInsert = $groupDetails['items_template'][$kode_akun_internal_js];
                            break;
                        }
                    }
                }

                if (!$templateForInsert) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Definisi master akun tidak ditemukan untuk kode: ' . esc($kode_akun_internal_js)]);
                }

                // Pastikan semua field yang dibutuhkan ada di template
                $required_keys = ['grup_laporan', 'is_editable', 'is_akumulasi', 'is_item_utama'];
                if (strpos($kode_akun_internal_js, 'akum_') === 0) {
                    $required_keys[] = 'uraian_akun'; // uraian_akun ada di template akumulasi
                } else {
                    $required_keys[] = 'nama'; // 'nama' ada di template item
                    $required_keys[] = 'nomor_display_sub';
                }

                foreach ($required_keys as $req_key) {
                    if (!isset($templateForInsert[$req_key])) {
                        return $this->response->setJSON(['status' => 'error', 'message' => "Template master akun untuk '" . esc($kode_akun_internal_js) . "' tidak lengkap, field '" . esc($req_key) . "' tidak ada."]);
                    }
                }


                // Tentukan urutan_display (ini adalah bagian yang paling tricky untuk item baru)
                // Anda mungkin perlu logika yang lebih baik di sini, atau kolom urutan di master COA DB.
                $urutan_display_val = 9900; // Default tinggi jika tidak ditemukan
                $found_group_for_order = $templateForInsert['grup_laporan'];
                if (isset($masterStructure[$found_group_for_order])) {
                    $urutan_display_val = $masterStructure[$found_group_for_order]['urutan'] * 100; // Basis urutan grup
                    if ($is_new_flag === 'true' && strpos($kode_akun_internal_js, 'akum_') === 0) {
                        // Akumulasi biasanya setelah item induknya
                        $urutan_display_val += (int) preg_replace('/[^0-9]/', '', $masterStructure[$found_group_for_order]['items_template'][$parent_for_akum_insert]['nomor_display_sub'] ?? '0') + 0.5; // Tambah 0.5 agar setelah induk
                    } else {
                        $urutan_display_val += (int) preg_replace('/[^0-9]/', '', $templateForInsert['nomor_display_sub'] ?? '99');
                    }
                }


                $dataToInsert = [
                    'periode_tahun' => $periode_tahun,
                    'periode_bulan' => $periode_bulan,
                    'kode_akun_internal' => $final_kode_akun_internal_db,
                    'uraian_akun' => $templateForInsert['uraian_akun'] ?? $templateForInsert['nama'], // Ambil uraian yang sesuai
                    'grup_laporan' => $templateForInsert['grup_laporan'],
                    'nomor_display_main' => $masterStructure[$templateForInsert['grup_laporan']]['no_induk_prefix'] ?? null,
                    'nomor_display_sub' => $templateForInsert['nomor_display_sub'] ?? null,
                    'nilai' => $nilai_db,
                    'is_item_utama' => (bool) $templateForInsert['is_item_utama'],
                    'is_akumulasi' => (bool) $templateForInsert['is_akumulasi'],
                    'parent_kode_akun_internal' => $parent_for_akum_insert, // Ini penting untuk akumulasi
                    'is_editable' => (bool) $templateForInsert['is_editable'],
                    'urutan_display' => (int) $urutan_display_val,
                    // Default lain dari skema DB akan berlaku (is_header_grup=0, dll.)
                ];

                try {
                    $newId = $this->neracaModel->insert($dataToInsert, true);
                    if ($newId) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'message' => 'Data baru berhasil disimpan.',
                            'new_id' => $newId // Kirim ID baru ke client
                        ]);
                    } else {
                        $errors = $this->neracaModel->errors();
                        log_message('error', 'Gagal insert neraca item: ' . json_encode($dataToInsert) . ' Errors: ' . json_encode($errors));
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data baru ke database.', 'errors' => $errors]);
                    }
                } catch (\Exception $e) {
                    log_message('error', '[ERROR] Exception in insertNeracaItem: ' . $e->getMessage() . ' Data: ' . json_encode($dataToInsert));
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan server saat menyimpan: ' . $e->getMessage()]);
                }

            } elseif (!empty($id) && is_numeric($id)) { // Proses UPDATE
                try {
                    if ($this->neracaModel->update($id, ['nilai' => $nilai_db])) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'message' => 'Data berhasil diperbarui.'
                        ]);
                    } else {
                        $errors = $this->neracaModel->errors();
                        log_message('error', 'Gagal update neraca item ID ' . $id . ': ' . json_encode($errors));
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data di database.', 'errors' => $errors]);
                    }
                } catch (\Exception $e) {
                    log_message('error', '[ERROR] Exception in updateNeracaItem (update part): ' . $e->getMessage() . ' - ID: ' . $id . ' Nilai: ' . $nilai_raw);
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()]);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Permintaan tidak valid (ID atau flag tidak sesuai untuk operasi).']);
            }
        }
        return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
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
            01 => 'Januari',
            02 => 'Februari',
            03 => 'Maret',
            04 => 'April',
            05 => 'Mei',
            06 => 'Juni',
            07 => 'Juli',
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

    /**
     * Export Laporan Laba Rugi ke Excel
     */
    public function exportLabaRugi()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        // Gunakan nama bulan dari property class
        $namaBulan = $this->bulanNames[$bulan] ?? $bulan;

        // 1. Ambil data dari Model (sudah menggunakan kategori aktual)
        $laporanData = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        // 2. Proses data menggunakan KATEGORI AKTUAL (sama seperti di fungsi labaRugi view)
        $pendapatanItems = [];
        $bebanItems = [];
        $totalPendapatan = 0;
        $totalBeban = 0;
        $kategoriPendapatanActual = ['PEMASUKAN']; // Sesuaikan jika perlu
        $kategoriBebanActual = [
            'BIAYA BIAYA',
            'BIAYA PAJAK',
            'PENYISIHAN BEBAN DANA',
            'PENYUSUTAN PENYUSUTAN'
        ]; // Sesuaikan jika perlu

        if (!empty($laporanData)) {
            foreach ($laporanData as $item) {
                $saldo = floatval($item['saldo'] ?? 0);
                if (isset($item['kategori'])) {
                    if (in_array($item['kategori'], $kategoriPendapatanActual)) {
                        $totalPendapatan += $saldo;
                        $pendapatanItems[] = $item;
                    } elseif (in_array($item['kategori'], $kategoriBebanActual)) {
                        $totalBeban += $saldo;
                        $bebanItems[] = $item;
                    }
                }
            }
        }
        $labaRugiBersih = $totalPendapatan - $totalBeban;

        // --- Pembuatan Excel ---
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laba Rugi');

        // Set document properties
        $spreadsheet->getProperties()->setCreator('Sistem Akuntansi')->setTitle("Laporan Laba Rugi");

        // Judul
        $sheet->mergeCells('A1:C1')->setCellValue('A1', "LAPORAN LABA RUGI");
        $sheet->mergeCells('A2:C2')->setCellValue('A2', "Periode: " . $namaBulan . " " . $tahun);
        $sheet->getStyle('A1:C1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('A2:C2')->applyFromArray(['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        // Style umum
        $numberFormat = '#,##0_);(#,##0)'; // Format akuntansi
        $boldFont = ['font' => ['bold' => true]];
        $totalFill = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]]; // Hijau muda untuk total
        $grandTotalFill = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB9C']]]; // Kuning untuk grand total
        $thinBorderOutline = ['borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        // Mulai tulis data
        $row = 4;

        // --- PENDAPATAN ---
        $sheet->mergeCells('A' . $row . ':C' . $row)->setCellValue('A' . $row, 'PENDAPATAN');
        $sheet->getStyle('A' . $row)->applyFromArray($boldFont);
        $row++;
        $startRowPendapatan = $row; // Tandai awal data pendapatan
        // Tulis header kolom pendapatan
        $sheet->fromArray(['Kode', 'Nama Akun', 'Jumlah'], NULL, 'A' . $row);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($boldFont);
        $row++;
        if (!empty($pendapatanItems)) {
            // Tulis item pendapatan
            foreach ($pendapatanItems as $item) {
                $sheet->fromArray([
                    $item['kode_akun'] ?? '-',
                    $item['nama_akun'] ?? 'N/A',
                    floatval($item['saldo'] ?? 0)
                ], NULL, 'A' . $row);
                $row++;
            }
        } else {
            $sheet->mergeCells('A' . $row . ':C' . $row)->setCellValue('A' . $row, 'Tidak ada data pendapatan');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }
        // Total Pendapatan
        $sheet->setCellValue('B' . $row, 'Total Pendapatan');
        $sheet->setCellValue('C' . $row, $totalPendapatan);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($boldFont);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($totalFill);
        $endRowPendapatan = $row; // Tandai akhir data pendapatan
        $row++; // Spacer

        // --- BEBAN ---
        $row++;
        $sheet->mergeCells('A' . $row . ':C' . $row)->setCellValue('A' . $row, 'BEBAN');
        $sheet->getStyle('A' . $row)->applyFromArray($boldFont);
        $row++;
        $startRowBeban = $row; // Tandai awal data beban
        // Tulis header kolom beban
        $sheet->fromArray(['Kode', 'Nama Akun', 'Jumlah'], NULL, 'A' . $row);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($boldFont);
        $row++;
        if (!empty($bebanItems)) {
            // Tulis item beban
            foreach ($bebanItems as $item) {
                $sheet->fromArray([
                    $item['kode_akun'] ?? '-',
                    $item['nama_akun'] ?? 'N/A',
                    floatval($item['saldo'] ?? 0)
                ], NULL, 'A' . $row);
                $row++;
            }
        } else {
            $sheet->mergeCells('A' . $row . ':C' . $row)->setCellValue('A' . $row, 'Tidak ada data beban');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }
        // Total Beban
        $sheet->setCellValue('B' . $row, 'Total Beban');
        $sheet->setCellValue('C' . $row, $totalBeban);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($boldFont);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($totalFill);
        $endRowBeban = $row; // Tandai akhir data beban
        $row++; // Spacer

        // --- LABA RUGI BERSIH ---
        $row++;
        $sheet->setCellValue('B' . $row, 'LABA (RUGI) BERSIH');
        $sheet->setCellValue('C' . $row, $labaRugiBersih);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($boldFont);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($grandTotalFill);
        $endRowLabaRugi = $row;

        // --- FORMATTING AKHIR ---
        // Format Angka untuk semua kolom Jumlah
        $sheet->getStyle('C' . $startRowPendapatan . ':C' . $endRowLabaRugi)->getNumberFormat()->setFormatCode($numberFormat);

        // Apply Borders
        $sheet->getStyle('A4:C' . $endRowPendapatan)->applyFromArray($thinBorderOutline); // Border Pendapatan
        $sheet->getStyle('A' . ($endRowPendapatan + 2) . ':C' . $endRowBeban)->applyFromArray($thinBorderOutline); // Border Beban
        $sheet->getStyle('A' . ($endRowLabaRugi) . ':C' . $endRowLabaRugi)->applyFromArray($thinBorderOutline); // Border L/R Bersih

        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setWidth(45); // Beri lebar lebih untuk nama akun

        // Footer
        $row += 2;
        $sheet->mergeCells('A' . $row . ':C' . $row)->setCellValue('A' . $row, 'Dicetak pada: ' . date('d-m-Y H:i:s'));

        // --- SAVE & DOWNLOAD ---
        $writer = new Xlsx($spreadsheet);
        $filename = "Laporan_Laba_Rugi_" . $namaBulan . "_" . $tahun . ".xlsx";
        $filePath = WRITEPATH . 'uploads/' . $filename;
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        try {
            $writer->save($filePath);
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            log_message('error', 'Error saving Excel file: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan file Excel.');
        }
        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportNeraca()
    {
        $bulan_req = $this->request->getGet('bulan');
        $tahun_req = $this->request->getGet('tahun');

        $bulan = !empty($bulan_req) ? (int) $bulan_req : (int) date('n');
        $tahun = !empty($tahun_req) ? (int) $tahun_req : (int) date('Y');

        $neracaData = $this->_prepareNeracaViewData($tahun, $bulan);

        $laporan = $neracaData['laporan'];
        $grandTotalAset_current = $neracaData['grand_total_aset_current'];
        $grandTotalAset_prev = $neracaData['grand_total_aset_prev'];
        $grandTotalPasivaModal_current = $neracaData['grand_total_pasiva_modal_current'];
        $grandTotalPasivaModal_prev = $neracaData['grand_total_pasiva_modal_prev'];
        // $labaRugiBersihPeriode tidak diperlukan lagi jika SHU sudah dalam item Ekuitas
        $prevBulan = $neracaData['prevBulan'];
        $prevTahun = $neracaData['prevTahun'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Komparatif');

        $namaBulanCurrent = $this->bulanNames[(int) $bulan] ?? (string) $bulan;
        $namaBulanPrev = $this->bulanNames[(int) $prevBulan] ?? (string) $prevBulan;

        $spreadsheet->getProperties()->setCreator('Sistem Akuntansi Anda')->setTitle("Neraca Komparatif");

        $sheet->mergeCells('A1:D1')->setCellValue('A1', "NERACA KOMPARATIF");
        $sheet->mergeCells('A2:D2')->setCellValue('A2', "Per " . date('t', mktime(0, 0, 0, $bulan, 1, $tahun)) . " " . $namaBulanCurrent . " " . $tahun . " dan " . date('t', mktime(0, 0, 0, $prevBulan, 1, $prevTahun)) . " " . $namaBulanPrev . " " . $prevTahun);
        $sheet->getStyle('A1:D1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('A2:D2')->applyFromArray(['font' => ['size' => 11], 'alignment' => ['horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER]]);

        $row = 4;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Uraian Akun');
        $sheet->setCellValue('C' . $row, $namaBulanCurrent . ', ' . $tahun);
        $sheet->setCellValue('D' . $row, $namaBulanPrev . ', ' . $prevTahun);

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => ExcelFill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => ExcelBorder::BORDER_THIN]],
            'alignment' => ['horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER, 'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER]
        ];
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);

        $numberFormat = '#,##0;(#,##0);0';
        $row++;

        $no_induk_aktiva_excel = 0;
        $no_induk_pasiva_excel = 0;

        foreach ($laporan as $group_key => $groupData) {
            if ($group_key === 'TIDAK_TERPETAKAN' && (empty($groupData['items']) || !is_array($groupData['items']))) { // Tambah pengecekan is_array
                continue;
            }

            $isAsetGroup = (isset($groupData['urutan']) && $groupData['urutan'] >= 1 && $groupData['urutan'] <= 3);
            $isPasivaGroup = (isset($groupData['urutan']) && $groupData['urutan'] >= 4 && $groupData['urutan'] <= 6);
            $currentNoInduk = '';

            if ($isAsetGroup) {
                $currentNoInduk = ($groupData['no_induk_prefix'] ?? 'I') . '.' . ($groupData['no_induk_val'] ?? ++$no_induk_aktiva_excel);
            } else if ($isPasivaGroup) {
                $currentNoInduk = ($groupData['no_induk_prefix'] ?? 'II') . '.' . ($groupData['no_induk_val'] ?? ++$no_induk_pasiva_excel);
            }

            if ($group_key !== 'TIDAK_TERPETAKAN') {
                $sheet->setCellValue('A' . $row, $currentNoInduk);
                $sheet->setCellValue('B' . $row, $groupData['label']);
                $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                $row++;
            } else {
                $sheet->mergeCells('A' . $row . ':D' . $row)->setCellValue('A' . $row, $groupData['label']);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;
            }

            $subNo = 0;
            if (!empty($groupData['items']) && is_array($groupData['items'])) {
                foreach ($groupData['items'] as $kodeAkun => $item) {
                    $sheet->setCellValue('A' . $row, '');
                    $sheet->setCellValue('B' . $row, str_repeat(' ', 4) . ($item['nomor_display_sub'] ?? ++$subNo) . '. ' . ($item['nama'] ?? 'N/A'));
                    $sheet->setCellValueExplicit('C' . $row, $item['saldo_current'] ?? 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit('D' . $row, $item['saldo_prev'] ?? 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
                    $row++;

                    if ($group_key == 'ASET_TETAP' && isset($groupData['akumulasi_lookup'][$kodeAkun]) && is_array($groupData['akumulasi_lookup'][$kodeAkun])) {
                        $akum = $groupData['akumulasi_lookup'][$kodeAkun];
                        $sheet->setCellValue('B' . $row, str_repeat(' ', 8) . ($akum['nama'] ?? '(Akumulasi Penyusutan)'));
                        $sheet->setCellValueExplicit('C' . $row, (float) ($akum['saldo_current'] ?? 0), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit('D' . $row, (float) ($akum['saldo_prev'] ?? 0), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setItalic(true);
                        $row++;

                        $sheet->setCellValue('B' . $row, str_repeat(' ', 8) . 'Nilai Buku ' . ($item['nama'] ?? 'N/A'));
                        $sheet->setCellValueExplicit('C' . $row, ($item['saldo_current'] ?? 0) + ($akum['saldo_current'] ?? 0), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit('D' . $row, ($item['saldo_prev'] ?? 0) + ($akum['saldo_prev'] ?? 0), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle('B' . $row . ':D' . $row)->applyFromArray(['font' => ['italic' => true], 'borders' => ['top' => ['borderStyle' => ExcelBorder::BORDER_THIN]]]);
                        $row++;
                        $sheet->getRowDimension($row)->setRowHeight(2);
                        $row++;
                    }
                }
            }

            // Tidak ada lagi baris L/R Berjalan terpisah untuk Ekuitas di Excel
            // karena sudah termasuk dalam total item Ekuitas jika SHU_EKUITAS_TAHUN_INI adalah item.

            if ($group_key !== 'TIDAK_TERPETAKAN') {
                $sheet->setCellValue('B' . $row, 'SUB TOTAL ' . $groupData['label'] . ($group_key == 'ASET_TETAP' ? ' (NETTO)' : ''));
                $totalCurrentToShow = ($group_key == 'ASET_TETAP') ? ($groupData['total_net_current'] ?? 0) : ($groupData['total_current'] ?? 0);
                $totalPrevToShow = ($group_key == 'ASET_TETAP') ? ($groupData['total_net_prev'] ?? 0) : ($groupData['total_prev'] ?? 0);

                // Untuk EKUITAS, total_current dari _prepareNeracaViewData sudah benar
                $sheet->setCellValueExplicit('C' . $row, $totalCurrentToShow, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('D' . $row, $totalPrevToShow, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => ExcelFill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]]);
                $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
                $row++;
                $sheet->getRowDimension($row)->setRowHeight(5);
                $row++;
            }
        }

        $sheet->setCellValue('B' . $row, 'JUMLAH ASET');
        $sheet->setCellValueExplicit('C' . $row, $grandTotalAset_current, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('D' . $row, $grandTotalAset_prev, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => ExcelFill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']]]);
        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
        $row++;
        $sheet->getRowDimension($row)->setRowHeight(10);
        $row++;

        $sheet->setCellValue('B' . $row, 'JUMLAH KEWAJIBAN & EKUITAS');
        $sheet->setCellValueExplicit('C' . $row, $grandTotalPasivaModal_current, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('D' . $row, $grandTotalPasivaModal_prev, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => ExcelFill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']]]);
        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
        $row++;

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setWidth(45);

        $writer = new Xlsx($spreadsheet);
        $filename = "Neraca_Komparatif_" . str_replace(' ', '_', $namaBulanCurrent) . "_" . $tahun . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

}

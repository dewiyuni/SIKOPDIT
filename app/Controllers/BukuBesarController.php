<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\BukuBesarModel;
use App\Models\JurnalKasModel;
use App\Models\SaldoAkunModel;
use App\Models\PemetaanAkunModel;
use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BukuBesarController extends BaseController
{
    protected $akunModel;
    protected $bukuBesarModel;
    protected $pemetaanModel;
    protected $saldoAkunModel;
    protected $jurnalKasModel;
    protected $bulanNames;


    public function __construct()
    {
        $this->akunModel = new AkunModel();
        $this->bukuBesarModel = new BukuBesarModel();
        $this->pemetaanModel = new PemetaanAkunModel();
        $this->saldoAkunModel = new SaldoAkunModel();
        $this->jurnalKasModel = new JurnalKasModel();
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
        $kodeInvKomputer = $getKode(148); // Ganti 148 dengan ID akun ASET 'Inventaris Komputer'
        $kodeInvMebel = $getKode(146);    // Ganti 146 dengan ID akun ASET 'Inventaris Mebel'
        $kodeInvGedung = $getKode(147);   // Ganti 147 dengan ID akun ASET 'Inventaris Gedung/Bangunan' (atau 138)
        $kodeInvKendaraan = $getKode(149); // Ganti 149 dengan ID akun ASET 'Inventaris Kendaraan'

        return [
            // --- ASET LANCAR (Urutan 1) ---
            $getKode(1) => ['ASET_LANCAR', 1, false, null], // Kas
            $getKode(2) => ['ASET_LANCAR', 1, false, null], // Simpanan di Bank
            $getKode(52) => ['ASET_LANCAR', 1, false, null], // Pinjaman Anggota (Piutang)
            $getKode(53) => ['ASET_LANCAR', 1, false, null], // Simpanan Deposito (Aset)

            // --- ASET TETAP (Urutan 3) ---
            $kodeInvKomputer => ['ASET_TETAP', 3, false, null],      // Inventaris Komputer
            $getKode(3) => ['ASET_TETAP', 3, true, $kodeInvKomputer],   // Akum. Peny. Komputer

            $kodeInvMebel => ['ASET_TETAP', 3, false, null],      // Inventaris Mebel
            $getKode(4) => ['ASET_TETAP', 3, true, $kodeInvMebel],      // Akum. Peny. Mebel

            $kodeInvGedung => ['ASET_TETAP', 3, false, null],      // Inventaris Gedung
            $getKode(5) => ['ASET_TETAP', 3, true, $kodeInvGedung],     // Akum. Peny. Gedung

            $kodeInvKendaraan => ['ASET_TETAP', 3, false, null],      // Inventaris Kendaraan
            $getKode(6) => ['ASET_TETAP', 3, true, $kodeInvKendaraan],  // Akum. Peny. Kendaraan

            // $getKode(ID_ASET_TERTANGGUH) => ['ASET_TETAP', 3, false, null], // Jika ada Aset Tertangguh
            // $getKode(40)      => ['ASET_TETAP', 3, true,  $getKode(ID_ASET_TERTANGGUH)], // Akum. Peny. Tertangguh

            // --- KEWAJIBAN JANGKA PENDEK (Urutan 4) ---
            $getKode(17) => ['KEWAJIBAN_PENDEK', 4, false, null], // Simpanan Non-Saham
            $getKode(20) => ['KEWAJIBAN_PENDEK', 4, false, null], // Simpanan Sukarela (SS)
            $getKode(24) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Dana Kesejahteraan
            $getKode(27) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Dana RAT
            $getKode(28) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Dana Pendampingan
            $getKode(29) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Penyisihan Pajak SHU
            $getKode(35) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Pajak Jasa Non Saham
            $getKode(43) => ['KEWAJIBAN_PENDEK', 4, false, null], // Jaminan PJKR
            $getKode(50) => ['KEWAJIBAN_PENDEK', 4, false, null], // Titipan Utang Pajak SHU
            // $getKode(18) => ['KEWAJIBAN_PENDEK', 4, false, null], // Jasa Simpanan Non-Saham (Jika Utang Jasa)

            // --- KEWAJIBAN JANGKA PANJANG (Urutan 5) ---
            $getKode(15) => ['KEWAJIBAN_PANJANG', 5, false, null], // Pinjaman dari BPD
            $getKode(25) => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Tunjangan Pesangon Karyawan

            // --- EKUITAS (MODAL) (Urutan 6) ---
            $getKode(26) => ['EKUITAS', 6, false, null], // Uang Pangkal
            $getKode(19) => ['EKUITAS', 6, false, null], // Simpanan Pokok (SP)
            $getKode(21) => ['EKUITAS', 6, false, null], // Simpanan Wajib (SW)
            $getKode(22) => ['EKUITAS', 6, false, null], // Simpanan Wajib Penyertaan (SWP)
            $getKode(8) => ['EKUITAS', 6, false, null], // Cadangan Aktiva Produktif (CAP)
            $getKode(14) => ['EKUITAS', 6, false, null], // Penyisihan Dana Pemilihan Pengurus
            $getKode(49) => ['EKUITAS', 6, false, null], // Modal Tetap
            $getKode(41) => ['EKUITAS', 6, false, null], // Penyisihan Dana Kesehatan
            $getKode(42) => ['EKUITAS', 6, false, null], // Tabungan Hari Tua Karyawan
            $getKode(44) => ['EKUITAS', 6, false, null], // Dana Pendidikan
            $getKode(45) => ['EKUITAS', 6, false, null], // Dana PDK
            $getKode(46) => ['EKUITAS', 6, false, null], // Dana Sosial
            $getKode(47) => ['EKUITAS', 6, false, null], // Simpanan Hasil Usaha
            $getKode(48) => ['EKUITAS', 6, false, null], // Dana Pengelola
            $getKode(51) => ['EKUITAS', 6, false, null], // Penyisihan Modal Simpanan Anggota
            $getKode(76) => ['EKUITAS', 6, false, null], // Dana Cadangan RAT
        ];
    }

    /**
     * Menampilkan Neraca Komparatif dengan format baru.
     */
    public function neraca()
    {
        $bulanParam = $this->request->getGet('bulan');
        $tahunParam = $this->request->getGet('tahun');

        $bulan = !empty($bulanParam) ? (int) $bulanParam : (int) date('n');
        $tahun = !empty($tahunParam) ? (int) $tahunParam : (int) date('Y');

        try {
            $currentDate = new \DateTimeImmutable("$tahun-$bulan-01");
        } catch (\Exception $e) {
            log_message('error', "Invalid date for neraca: tahun=$tahun, bulan=$bulan. Error: " . $e->getMessage());
            $currentDate = new \DateTimeImmutable(date('Y-m-01'));
            $bulan = (int) $currentDate->format('n');
            $tahun = (int) $currentDate->format('Y');
        }

        $prevDate = $currentDate->modify('-1 month');
        $prevBulan = (int) $prevDate->format('n');
        $prevTahun = (int) $prevDate->format('Y');

        $mappingData = $this->getNeracaMappingData();
        $listKodeAkunNeraca = array_keys($mappingData); // Kode Akun yang sudah benar dari mapping
        $listKodeAkunNeraca = array_filter($listKodeAkunNeraca, function ($kode) { // Filter kode yang tidak valid
            return strpos($kode, 'KODE_NOT_FOUND') === false;
        });


        $neracaRawData = [];
        if (!empty($listKodeAkunNeraca)) {
            $neracaRawData = $this->saldoAkunModel->getNeracaComparativeData(
                $listKodeAkunNeraca,
                $bulan,
                $tahun,
                $prevBulan,
                $prevTahun
            );
        }

        log_message('debug', "[NeracaController::neraca] Periode: {$bulan}-{$tahun}, Prev: {$prevBulan}-{$prevTahun}");
        log_message('debug', "[NeracaController::neraca] Mapping Keys (List Kode Akun Neraca): " . json_encode($listKodeAkunNeraca));
        log_message('debug', "[NeracaController::neraca] Neraca Raw Data (Count: " . count($neracaRawData) . "): " . json_encode($neracaRawData));

        $laporan = [
            'ASET_LANCAR' => ['label' => 'ASET LANCAR', 'urutan' => 1, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'ASET_TETAP' => ['label' => 'ASET TETAP', 'urutan' => 3, 'items' => [], 'total_current' => 0, 'total_prev' => 0, 'akumulasi_lookup' => [], 'total_net_current' => 0, 'total_net_prev' => 0],
            'KEWAJIBAN_PENDEK' => ['label' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 4, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'KEWAJIBAN_PANJANG' => ['label' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 5, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'EKUITAS' => ['label' => 'EKUITAS (MODAL)', 'urutan' => 6, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'TIDAK_TERPETAKAN' => ['label' => 'Akun Tidak Terpetakan', 'urutan' => 99, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
        ];
        if (in_array('ASET_TAK_LANCAR', array_column($mappingData, 0))) {
            $laporan['ASET_TAK_LANCAR'] = ['label' => 'ASET TAK LANCAR', 'urutan' => 2, 'items' => [], 'total_current' => 0, 'total_prev' => 0];
        }

        $akumulasiLookup = [];

        foreach ($neracaRawData as $item) {
            $kodeAkun = $item['kode_akun'];
            if (!isset($mappingData[$kodeAkun])) {
                log_message('warning', "[NeracaController::neraca] Kode Akun '{$kodeAkun}' dari database tidak ditemukan di mappingData.");
                $laporan['TIDAK_TERPETAKAN']['items'][$kodeAkun] = [
                    'kode' => $kodeAkun,
                    'nama' => $item['nama_akun'],
                    'saldo_current' => floatval($item['saldo_current'] ?? 0),
                    'saldo_prev' => floatval($item['saldo_prev'] ?? 0),
                    'is_akumulasi' => false
                ];
                continue;
            }

            $mapInfo = $mappingData[$kodeAkun];
            $kelompok = $mapInfo[0];
            $isAkumulasi = $mapInfo[2];
            $parentKode = $mapInfo[3]; // Kode akun aset tetap parent

            $dataItem = [
                'kode' => $kodeAkun,
                'nama' => $item['nama_akun'],
                'saldo_current' => floatval($item['saldo_current'] ?? 0),
                'saldo_prev' => floatval($item['saldo_prev'] ?? 0),
                'is_akumulasi' => $isAkumulasi
            ];

            if (isset($laporan[$kelompok])) {
                if ($isAkumulasi && $parentKode) {
                    $akumulasiLookup[$parentKode] = $dataItem; // Key adalah KODE AKUN PARENT ASET
                } else {
                    $laporan[$kelompok]['items'][$kodeAkun] = $dataItem;
                    $laporan[$kelompok]['total_current'] += $dataItem['saldo_current'];
                    $laporan[$kelompok]['total_prev'] += $dataItem['saldo_prev'];
                }
            } else {
                log_message('warning', "[NeracaController::neraca] Kelompok '{$kelompok}' untuk akun '{$kodeAkun}' tidak ada di struktur \$laporan.");
                $laporan['TIDAK_TERPETAKAN']['items'][$kodeAkun] = $dataItem;
            }
        }
        if (isset($laporan['ASET_TETAP'])) { // Pastikan grup ASET_TETAP ada
            $laporan['ASET_TETAP']['akumulasi_lookup'] = $akumulasiLookup;
        }


        foreach ($laporan as $kelompok => &$dataKelompok) {
            if (!empty($dataKelompok['items'])) {
                uasort($dataKelompok['items'], function ($a, $b) {
                    return strcmp($a['kode'], $b['kode']);
                });
            }
        }
        unset($dataKelompok);
        uasort($laporan, function ($a, $b) {
            return $a['urutan'] <=> $b['urutan'];
        });

        if (isset($laporan['ASET_TETAP'])) {
            $totalAkumCurrent = 0;
            $totalAkumPrev = 0;
            if (!empty($laporan['ASET_TETAP']['akumulasi_lookup'])) {
                foreach ($laporan['ASET_TETAP']['akumulasi_lookup'] as $akumItem) {
                    // Akumulasi penyusutan mengurangi aset, jadi saldonya (yg normal kredit) kita ambil sbg pengurang.
                    // Jika saldo_current sudah negatif (misal koreksi), biarkan. Jika positif, jadikan negatif.
                    $totalAkumCurrent += $akumItem['saldo_current']; // Akumulasi adalah kredit, jadi saldo positif
                    $totalAkumPrev += $akumItem['saldo_prev'];
                }
            }
            // Saldo akumulasi penyusutan adalah kredit. Untuk neraca, ia mengurangi aset.
            // Jadi jika saldo_current nya positif (normalnya begitu), kita kurangkan.
            $laporan['ASET_TETAP']['total_net_current'] = ($laporan['ASET_TETAP']['total_current'] ?? 0) - $totalAkumCurrent;
            $laporan['ASET_TETAP']['total_net_prev'] = ($laporan['ASET_TETAP']['total_prev'] ?? 0) - $totalAkumPrev;
        }


        $labaRugiBersihPeriode = $this->hitungLabaRugiBersih($bulan, $tahun);

        $grandTotalAset_current = ($laporan['ASET_LANCAR']['total_current'] ?? 0)
            + ($laporan['ASET_TAK_LANCAR']['total_current'] ?? 0)
            + ($laporan['ASET_TETAP']['total_net_current'] ?? 0);
        $grandTotalAset_prev = ($laporan['ASET_LANCAR']['total_prev'] ?? 0)
            + ($laporan['ASET_TAK_LANCAR']['total_prev'] ?? 0)
            + ($laporan['ASET_TETAP']['total_net_prev'] ?? 0);

        $grandTotalPasivaModal_current = ($laporan['KEWAJIBAN_PENDEK']['total_current'] ?? 0)
            + ($laporan['KEWAJIBAN_PANJANG']['total_current'] ?? 0)
            + ($laporan['EKUITAS']['total_current'] ?? 0)
            + $labaRugiBersihPeriode;
        $grandTotalPasivaModal_prev = ($laporan['KEWAJIBAN_PENDEK']['total_prev'] ?? 0)
            + ($laporan['KEWAJIBAN_PANJANG']['total_prev'] ?? 0)
            + ($laporan['EKUITAS']['total_prev'] ?? 0);

        $data = [
            'title' => 'Neraca Komparatif',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'prevBulan' => $prevBulan,
            'prevTahun' => $prevTahun,
            'laporan' => $laporan,
            'laba_rugi_bersih_current' => $labaRugiBersihPeriode,
            'grand_total_aset_current' => $grandTotalAset_current,
            'grand_total_aset_prev' => $grandTotalAset_prev,
            'grand_total_pasiva_modal_current' => $grandTotalPasivaModal_current,
            'grand_total_pasiva_modal_prev' => $grandTotalPasivaModal_prev,
            'bulanNames' => $this->bulanNames
        ];
        return view('admin/buku_besar/neraca', $data);
    }


    /**
     * Helper function untuk menghitung Laba Rugi Bersih periode tertentu.
     */
    private function hitungLabaRugiBersih($bulan, $tahun): float
    {
        // Pastikan kategori SAMA dengan SaldoAkunModel::getLaporanLabaRugi dan BukuBesarController::labaRugi
        $labaRugiData = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);
        $totalPendapatanLR = 0;
        $totalBebanLR = 0;

        $kategoriPendapatanActualLR = ['PENDAPATAN'];
        $kategoriBebanActualLR = [
            'BEBAN',
            'BEBAN PENYUSUTAN',
            // 'BEBAN PAJAK',
        ];

        if (!empty($labaRugiData)) {
            foreach ($labaRugiData as $itemLR) {
                $saldoLR = floatval($itemLR['saldo'] ?? 0);
                if (isset($itemLR['kategori'])) {
                    if (in_array($itemLR['kategori'], $kategoriPendapatanActualLR)) {
                        $totalPendapatanLR += $saldoLR;
                    } elseif (in_array($itemLR['kategori'], $kategoriBebanActualLR)) {
                        $totalBebanLR += $saldoLR;
                    }
                }
            }
        }
        return $totalPendapatanLR - $totalBebanLR;
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

    /**
     * Export Neraca Komparatif ke Excel
     */
    public function exportNeraca()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        // Ambil data terstruktur sama seperti di method neraca()
        // (Copy paste logic dari method neraca() untuk mendapatkan $data)

        // Tentukan periode sebelumnya
        $currentDate = new \DateTimeImmutable("$tahun-$bulan-01");
        $prevDate = $currentDate->modify('-1 month');
        $prevBulan = (int) $prevDate->format('n');
        $prevTahun = (int) $prevDate->format('Y');

        $mappingData = $this->getNeracaMappingData();
        $listKodeAkunNeraca = array_keys($mappingData);
        $neracaRawData = $this->saldoAkunModel->getNeracaComparativeData($listKodeAkunNeraca, $bulan, $tahun, $prevBulan, $prevTahun);

        // Olah data mentah (sama persis seperti di method neraca())
        $laporan = [
            'ASET_LANCAR' => ['label' => 'I.1 ASET LANCAR', 'urutan' => 1, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'ASET_TAK_LANCAR' => ['label' => 'I.2 ASET TAK LANCAR', 'urutan' => 2, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'ASET_TETAP' => ['label' => 'I.3 ASET TETAP', 'urutan' => 3, 'items' => [], 'total_current' => 0, 'total_prev' => 0, 'akumulasi_lookup' => []],
            'KEWAJIBAN_PENDEK' => ['label' => 'II.1 KEWAJIBAN JANGKA PENDEK', 'urutan' => 4, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'KEWAJIBAN_PANJANG' => ['label' => 'II.2 KEWAJIBAN JANGKA PANJANG', 'urutan' => 5, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'EKUITAS' => ['label' => 'II.3 EKUITAS (MODAL)', 'urutan' => 6, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'TIDAK_TERPETAKAN' => ['label' => 'Akun Tidak Terpetakan', 'urutan' => 99, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
        ];
        $akumulasiLookup = [];
        foreach ($neracaRawData as $item) { /* ... (logic loop sama persis seperti di neraca()) ... */
            $kodeAkun = $item['kode_akun'];
            $mapInfo = $mappingData[$kodeAkun] ?? ['TIDAK_TERPETAKAN', 99, false, null];
            $kelompok = $mapInfo[0];
            $isAkumulasi = $mapInfo[2];
            $parentKode = $mapInfo[3];
            $dataItem = ['kode' => $kodeAkun, 'nama' => $item['nama_akun'], 'saldo_current' => floatval($item['saldo_current'] ?? 0), 'saldo_prev' => floatval($item['saldo_prev'] ?? 0), 'is_akumulasi' => $isAkumulasi];
            if (isset($laporan[$kelompok])) {
                if ($isAkumulasi && $parentKode) {
                    $akumulasiLookup[$parentKode] = $dataItem;
                } else {
                    $laporan[$kelompok]['items'][$kodeAkun] = $dataItem;
                    $laporan[$kelompok]['total_current'] += $dataItem['saldo_current'];
                    $laporan[$kelompok]['total_prev'] += $dataItem['saldo_prev'];
                }
            } else {
                $laporan['TIDAK_TERPETAKAN']['items'][$kodeAkun] = $dataItem;
            }
        }
        $laporan['ASET_TETAP']['akumulasi_lookup'] = $akumulasiLookup;
        foreach ($laporan as $kelompok => &$dataKelompok) {
            if (!empty($dataKelompok['items'])) {
                ksort($dataKelompok['items']);
            }
        }
        unset($dataKelompok);
        uasort($laporan, function ($a, $b) {
            return $a['urutan'] <=> $b['urutan'];
        });
        $totalAkumCurrent = array_sum(array_column($akumulasiLookup, 'saldo_current'));
        $totalAkumPrev = array_sum(array_column($akumulasiLookup, 'saldo_prev'));
        $laporan['ASET_TETAP']['total_net_current'] = ($laporan['ASET_TETAP']['total_current'] ?? 0) - $totalAkumCurrent;
        $laporan['ASET_TETAP']['total_net_prev'] = ($laporan['ASET_TETAP']['total_prev'] ?? 0) - $totalAkumPrev;
        $labaRugiBersihPeriode = $this->hitungLabaRugiBersih($bulan, $tahun);
        $grandTotalAset_current = ($laporan['ASET_LANCAR']['total_current'] ?? 0) + ($laporan['ASET_TAK_LANCAR']['total_current'] ?? 0) + ($laporan['ASET_TETAP']['total_net_current'] ?? 0);
        $grandTotalAset_prev = ($laporan['ASET_LANCAR']['total_prev'] ?? 0) + ($laporan['ASET_TAK_LANCAR']['total_prev'] ?? 0) + ($laporan['ASET_TETAP']['total_net_prev'] ?? 0);
        $grandTotalPasivaModal_current = ($laporan['KEWAJIBAN_PENDEK']['total_current'] ?? 0) + ($laporan['KEWAJIBAN_PANJANG']['total_current'] ?? 0) + ($laporan['EKUITAS']['total_current'] ?? 0) + $labaRugiBersihPeriode;
        $grandTotalPasivaModal_prev = ($laporan['KEWAJIBAN_PENDEK']['total_prev'] ?? 0) + ($laporan['KEWAJIBAN_PANJANG']['total_prev'] ?? 0) + ($laporan['EKUITAS']['total_prev'] ?? 0);
        // Akhir copy paste logic neraca()


        // --- Mulai Pembuatan Excel ---
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Komparatif');
        $namaBulanCurrent = $this->bulanNames[$bulan] ?? $bulan;
        $namaBulanPrev = $this->bulanNames[$prevBulan] ?? $prevBulan;

        // Properties
        $spreadsheet->getProperties()->setCreator('Sistem Akuntansi')->setTitle("Neraca Komparatif");

        // Judul
        $sheet->mergeCells('A1:D1')->setCellValue('A1', "NERACA KOMPARATIF");
        $sheet->mergeCells('A2:D2')->setCellValue('A2', "Per " . date('t', strtotime("$tahun-$bulan-01")) . " " . $namaBulanCurrent . " " . $tahun . " dan " . date('t', strtotime("$prevTahun-$prevBulan-01")) . " " . $namaBulanPrev . " " . $prevTahun);
        $sheet->getStyle('A1:D1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('A2:D2')->applyFromArray(['font' => ['bold' => false, 'size' => 11], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);

        // Header Tabel
        $row = 4;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Uraian Akun');
        $sheet->setCellValue('C' . $row, $namaBulanCurrent . ', ' . $tahun);
        $sheet->setCellValue('D' . $row, $namaBulanPrev . ', ' . $prevTahun);
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
        ];
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // Format Angka default
        $numberFormat = '#,##0_);(#,##0)'; // Positif biasa, negatif dalam kurung

        $row++; // Mulai data dari baris 5

        // Fungsi helper untuk menulis baris grup
        $writeGroup = function (string $noInduk, array $groupData, &$currentRow, &$sheet, string $numberFormat) use (&$writeGroup, &$laporan, &$labaRugiBersihPeriode) {
            // Tulis header grup
            $sheet->setCellValue('A' . $currentRow, $noInduk);
            $sheet->setCellValue('B' . $currentRow, $groupData['label']);
            $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->setBold(true);
            $currentRow++;

            $subNo = 1;
            if (!empty($groupData['items'])) {
                foreach ($groupData['items'] as $kodeAkun => $item) {
                    $sheet->setCellValue('A' . $currentRow, '');
                    $sheet->setCellValue('B' . $currentRow, str_repeat(' ', 4) . $subNo++ . '. ' . $item['nama']); // Indentasi
                    $sheet->setCellValue('C' . $currentRow, $item['saldo_current']);
                    $sheet->setCellValue('D' . $currentRow, $item['saldo_prev']);
                    $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getNumberFormat()->setFormatCode($numberFormat);
                    $currentRow++;

                    // Khusus Aset Tetap, cek akumulasi
                    if ($groupData['label'] == 'ASET TETAP' && isset($groupData['akumulasi_lookup'][$kodeAkun])) {
                        $akum = $groupData['akumulasi_lookup'][$kodeAkun];
                        $sheet->setCellValue('B' . $currentRow, str_repeat(' ', 8) . '(Akumulasi Penyusutan)');
                        // Tampilkan akumulasi sebagai negatif
                        $sheet->setCellValue('C' . $currentRow, $akum['saldo_current'] > 0 ? -$akum['saldo_current'] : $akum['saldo_current']);
                        $sheet->setCellValue('D' . $currentRow, $akum['saldo_prev'] > 0 ? -$akum['saldo_prev'] : $akum['saldo_prev']);
                        $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle('B' . $currentRow . ':D' . $currentRow)->getFont()->setItalic(true);
                        $currentRow++;
                        // Subtotal netto per aset
                        $sheet->setCellValue('B' . $currentRow, str_repeat(' ', 8) . 'Nilai Buku ' . $item['nama']);
                        $sheet->setCellValue('C' . $currentRow, $item['saldo_current'] - $akum['saldo_current']);
                        $sheet->setCellValue('D' . $currentRow, $item['saldo_prev'] - $akum['saldo_prev']);
                        $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle('B' . $currentRow . ':D' . $currentRow)->applyFromArray(['font' => ['italic' => true], 'borders' => ['top' => ['borderStyle' => ExcelBorder::BORDER_THIN]]]);
                        $currentRow++;
                    }
                }
            }

            // Tulis Sub Total Grup
            $sheet->setCellValue('B' . $currentRow, 'SUB TOTAL ' . $groupData['label']);
            // Untuk Aset Tetap, tampilkan Total Netto
            $totalCurrentToShow = ($groupData['label'] == 'ASET TETAP') ? ($groupData['total_net_current'] ?? 0) : $groupData['total_current'];
            $totalPrevToShow = ($groupData['label'] == 'ASET TETAP') ? ($groupData['total_net_prev'] ?? 0) : $groupData['total_prev'];
            $sheet->setCellValue('C' . $currentRow, $totalCurrentToShow);
            $sheet->setCellValue('D' . $currentRow, $totalPrevToShow);
            $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]]);
            $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getNumberFormat()->setFormatCode($numberFormat);
            $currentRow++;
            $sheet->getRowDimension($currentRow)->setRowHeight(5); // Spacer
            $currentRow++;
        };

        // --- Tulis Data ke Excel ---
        $noInduk = 1;
        // ASET
        $writeGroup('I.' . $noInduk++, $laporan['ASET_LANCAR'], $row, $sheet, $numberFormat);
        if (!empty($laporan['ASET_TAK_LANCAR']['items'])) { // Hanya tampilkan jika ada isinya
            $writeGroup('I.' . $noInduk++, $laporan['ASET_TAK_LANCAR'], $row, $sheet, $numberFormat);
        }
        $writeGroup('I.' . $noInduk++, $laporan['ASET_TETAP'], $row, $sheet, $numberFormat);

        // TOTAL ASET
        $sheet->setCellValue('B' . $row, 'JUMLAH ASET');
        $sheet->setCellValue('C' . $row, $grandTotalAset_current);
        $sheet->setCellValue('D' . $row, $grandTotalAset_prev);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']]]);
        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
        $row++;
        $sheet->getRowDimension($row)->setRowHeight(10); // Spacer besar
        $row++;

        // KEWAJIBAN & EKUITAS
        $noInduk = 1;
        $writeGroup('II.' . $noInduk++, $laporan['KEWAJIBAN_PENDEK'], $row, $sheet, $numberFormat);
        $writeGroup('II.' . $noInduk++, $laporan['KEWAJIBAN_PANJANG'], $row, $sheet, $numberFormat);

        // EKUITAS (MODAL) - Tulis manual karena ada L/R Berjalan
        $groupEkuitas = $laporan['EKUITAS'];
        $sheet->setCellValue('A' . $row, 'II.' . $noInduk++);
        $sheet->setCellValue('B' . $row, $groupEkuitas['label']);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $currentRowEkuitas = $row + 1; // Start row untuk item ekuitas
        $subNoEkuitas = 1;
        if (!empty($groupEkuitas['items'])) {
            foreach ($groupEkuitas['items'] as $item) {
                $sheet->setCellValue('B' . $currentRowEkuitas, str_repeat(' ', 4) . $subNoEkuitas++ . '. ' . $item['nama']);
                $sheet->setCellValue('C' . $currentRowEkuitas, $item['saldo_current']);
                $sheet->setCellValue('D' . $currentRowEkuitas, $item['saldo_prev']);
                $sheet->getStyle('C' . $currentRowEkuitas . ':D' . $currentRowEkuitas)->getNumberFormat()->setFormatCode($numberFormat);
                $currentRowEkuitas++;
            }
        }
        // Laba Rugi Berjalan
        $sheet->setCellValue('B' . $currentRowEkuitas, str_repeat(' ', 4) . 'Laba (Rugi) Berjalan');
        $sheet->setCellValue('C' . $currentRowEkuitas, $labaRugiBersihPeriode);
        $sheet->setCellValue('D' . $currentRowEkuitas, '-'); // Tidak relevan untuk periode lalu
        $sheet->getStyle('C' . $currentRowEkuitas)->getNumberFormat()->setFormatCode($numberFormat);
        $currentRowEkuitas++;
        // Sub Total Ekuitas
        $sheet->setCellValue('B' . $currentRowEkuitas, 'SUB TOTAL ' . $groupEkuitas['label']);
        $sheet->setCellValue('C' . $currentRowEkuitas, $groupEkuitas['total_current'] + $labaRugiBersihPeriode);
        $sheet->setCellValue('D' . $currentRowEkuitas, $groupEkuitas['total_prev']);
        $sheet->getStyle('A' . $currentRowEkuitas . ':D' . $currentRowEkuitas)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]]);
        $sheet->getStyle('C' . $currentRowEkuitas . ':D' . $currentRowEkuitas)->getNumberFormat()->setFormatCode($numberFormat);
        $row = $currentRowEkuitas + 1; // Update row utama
        $sheet->getRowDimension($row)->setRowHeight(5); // Spacer
        $row++;


        // TOTAL KEWAJIBAN & EKUITAS
        $sheet->setCellValue('B' . $row, 'JUMLAH KEWAJIBAN & EKUITAS');
        $sheet->setCellValue('C' . $row, $grandTotalPasivaModal_current);
        $sheet->setCellValue('D' . $row, $grandTotalPasivaModal_prev);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']]]);
        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
        $row++;


        // --- Akhir Tulis Data ---

        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Atur lebar kolom B sedikit lebih lebar jika perlu
        $sheet->getColumnDimension('B')->setWidth(40);


        // --- Save & Download ---
        $writer = new Xlsx($spreadsheet);
        $filename = "Neraca_Komparatif_" . $namaBulanCurrent . "_" . $tahun . ".xlsx";
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

}

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

        // Model MENGEMBALIKAN data dengan kategori:
        // PEMASUKAN, BIAYA BIAYA, BIAYA PAJAK, PENYISIHAN BEBAN DANA, PENYUSUTAN PENYUSUTAN
        $laporanData = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);

        $pendapatanItems = []; // Array untuk menampung item pendapatan
        $bebanItems = [];      // Array untuk menampung item beban
        $totalPendapatan = 0;
        $totalBeban = 0;

        // Definisikan KATEGORI AKTUAL yang dianggap BEBAN (sesuai query Model)
        $kategoriBebanActual = [
            'BIAYA BIAYA',
            'BIAYA PAJAK',
            'PENYISIHAN BEBAN DANA',
            'PENYUSUTAN PENYUSUTAN'
        ];
        // Definisikan KATEGORI AKTUAL yang dianggap PENDAPATAN (sesuai query Model)
        $kategoriPendapatanActual = ['PEMASUKAN'];

        if (!empty($laporanData)) {
            // Proses hasil dari Model menggunakan KATEGORI AKTUAL
            foreach ($laporanData as $item) {
                $saldo = floatval($item['saldo'] ?? 0);
                if (isset($item['kategori'])) {
                    // Cek apakah kategori item termasuk dalam kategori pendapatan aktual
                    if (in_array($item['kategori'], $kategoriPendapatanActual)) {
                        $totalPendapatan += $saldo;
                        $pendapatanItems[] = $item; // Tambahkan ke list pendapatan
                    }
                    // Cek apakah kategori item termasuk dalam kategori beban aktual
                    elseif (in_array($item['kategori'], $kategoriBebanActual)) {
                        $totalBeban += $saldo;
                        $bebanItems[] = $item; // Tambahkan ke list beban
                    }
                }
            }
        }

        // Laba Rugi = Total Pendapatan - Total Beban
        $labaRugiBersih = $totalPendapatan - $totalBeban;

        // Siapkan data untuk view dengan variabel yang BENAR
        $data = [
            'title' => 'Laporan Laba Rugi',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'pendapatan_items' => $pendapatanItems, // <-- Kirim list pendapatan
            'beban_items' => $bebanItems,      // <-- Kirim list beban
            'total_pendapatan' => $totalPendapatan, // <-- Total yang sudah benar
            'total_beban' => $totalBeban,      // <-- Total yang sudah benar
            'laba_rugi_bersih' => $labaRugiBersih,
            'bulanNames' => $this->bulanNames // Pastikan $bulanNames ada dari construct
        ];

        // View laba_rugi.php Anda sudah benar (tidak perlu diubah lagi)
        return view('admin/buku_besar/laba_rugi', $data);
    }

    /**
     * Mengembalikan array mapping Neraca Komparatif.
     * !! VERIFIKASI DAN LENGKAPI MAPPING INI SESUAI COA ANDA !!
     */
    private function getNeracaMappingData(): array
    {
        // Format: 'KODE_AKUN' => ['GROUP_LAPORAN', URUTAN_GROUP, IS_AKUMULASI (bool), KODE_PARENT_ASET (jika IS_AKUMULASI true)]
        // GROUP_LAPORAN: ASET_LANCAR, ASET_TAK_LANCAR, ASET_TETAP, KEWAJIBAN_PENDEK, KEWAJIBAN_PANJANG, EKUITAS
        return [
            // --- ASET LANCAR (Urutan 1) ---
            'PNG002' => ['ASET_LANCAR', 1, false, null], // Simpanan di Bank (Diasumsikan Kas/Bank)
            'PNG003' => ['ASET_LANCAR', 1, false, null], // Simpanan DEPOSITO
            'PNG001' => ['ASET_LANCAR', 1, false, null], // Piutang Anggota (Piutang Biasa)

            // --- ASET TAK LANCAR (Urutan 2) - Contoh, sesuaikan ---
            // 'INV001' => ['ASET_TAK_LANCAR', 2, false, null], // Misal: Investasi Jk Panjang
            // 'INV002' => ['ASET_TAK_LANCAR', 2, false, null], // Misal: Simpanan di BKD

            // --- ASET TETAP (Urutan 3) ---
            'PNG028' => ['ASET_TETAP', 3, false, null], // Pembelian Inventaris Mebeler (Ini akun asetnya?) **VERIFIKASI KODE**
            'AKM001' => ['ASET_TETAP', 3, true, 'PNG028'], // Akum. Penyusutan Mebeler
            'PNG031' => ['ASET_TETAP', 3, false, null], // Inventaris Gedung/Bangunan (Ini akun asetnya?) **VERIFIKASI KODE**
            'AKM002' => ['ASET_TETAP', 3, true, 'PNG031'], // Akum. Penyusutan Gedung
            'PNG030' => ['ASET_TETAP', 3, false, null], // Pembelian Inventaris Komputer (Ini akun asetnya?) **VERIFIKASI KODE**
            'AKM004' => ['ASET_TETAP', 3, true, 'PNG030'], // Akum. Penyusutan Komputer
            // 'AST004' => ['ASET_TETAP', 3, false, null], // Inventaris Tanah **TAMBAHKAN KODE & AKUN JIKA ADA**
            // 'AKM005' => ['ASET_TETAP', 3, true, 'AST004'], // Akum. Penyusutan Tanah
            // 'AST005' => ['ASET_TETAP', 3, false, null], // Inventaris Kendaraan **TAMBAHKAN KODE & AKUN JIKA ADA**
            'AKM003' => ['ASET_TETAP', 3, true, 'AST005'], // Akum. Penyusutan Spd Mtr **PASTIKAN KODE PARENT AST005 BENAR**

            // --- KEWAJIBAN JANGKA PENDEK (Urutan 4) ---
            'PEM006' => ['KEWAJIBAN_PENDEK', 4, false, null], // S.Non Saham
            'PEM007' => ['KEWAJIBAN_PENDEK', 4, false, null], // S.Jasa Non Saham
            'PEM005' => ['KEWAJIBAN_PENDEK', 4, false, null], // SS (Simpanan Sukarela)
            // 'DANA01' => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Pengurus **TAMBAHKAN KODE JIKA ADA AKUN SALDO**
            // 'DANA02' => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Pendidikan **TAMBAHKAN KODE JIKA ADA AKUN SALDO**
            // 'DANA03' => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Karyawan **TAMBAHKAN KODE JIKA ADA AKUN SALDO**
            // 'DANA04' => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana PDK **TAMBAHKAN KODE JIKA ADA AKUN SALDO**
            // 'DANA05' => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Sosial **TAMBAHKAN KODE JIKA ADA AKUN SALDO**
            // 'DANA06' => ['KEWAJIBAN_PENDEK', 4, false, null], // Dana Insentif **TAMBAHKAN KODE JIKA ADA AKUN SALDO**
            'PPN002' => ['KEWAJIBAN_PENDEK', 4, false, null], // Penyisihan Dana RAT (Untuk tahun depan?)
            'PPN005' => ['KEWAJIBAN_PENDEK', 4, false, null], // Titip Dana Kesejahteraan
            'PPN010' => ['KEWAJIBAN_PENDEK', 4, false, null], // Penyisihan Pemilihan Pengurus
            'PEM012' => ['KEWAJIBAN_PENDEK', 4, false, null], // Titip Pajak (Js Non Shm) - **VERIFIKASI KODE & NAMA**
            // 'SHU001' => ['KEWAJIBAN_PENDEK', 4, false, null], // SHU Tahun Lalu (belum dibagi) **TAMBAHKAN KODE JIKA ADA**

            // --- KEWAJIBAN JANGKA PANJANG (Urutan 5) ---
            'PEM013' => ['KEWAJIBAN_PANJANG', 5, false, null], // Pinjaman dari BPD
            'PPN001' => ['KEWAJIBAN_PANJANG', 5, false, null], // Penyisihan Tab Hari Tua Karyawan
            'PPN009' => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Tunj. Pesangon Karyawan
            'PPN003' => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan CAP (Cad. Aktiva Produktif)
            'PPN008' => ['KEWAJIBAN_PANJANG', 5, false, null], // Titipan Penyisihan Pjk SHU
            'PPN007' => ['KEWAJIBAN_PANJANG', 5, false, null], // Titip Dana Pendampingan
            'PPN006' => ['KEWAJIBAN_PANJANG', 5, false, null], // Titip Dana RAT (Jangka Panjang?) - **VERIFIKASI**
            // 'KWJ001' => ['KEWAJIBAN_PANJANG', 5, false, null], // Dana Sehat (jika kewajiban jk panjang) **TAMBAHKAN KODE**
            // 'KWJ002' => ['KEWAJIBAN_PANJANG', 5, false, null], // Titip SP/SW (jika benar kewajiban) **TAMBAHKAN KODE**


            // --- EKUITAS (MODAL) (Urutan 6) ---
            'PEM001' => ['EKUITAS', 6, false, null], // Uang Pangkal
            'PEM002' => ['EKUITAS', 6, false, null], // SP (Simpanan Pokok)
            'PEM003' => ['EKUITAS', 6, false, null], // SW (Simpanan Wajib)
            'PEM004' => ['EKUITAS', 6, false, null], // SWP (Simp Wajib Penyertaan?)
            'PEM023' => ['EKUITAS', 6, false, null], // Hibah
            'PEM015' => ['EKUITAS', 6, false, null], // Dana Resiko (Re)
            'PEM021' => ['EKUITAS', 6, false, null], // Pemupukan Modal Tetap (Diasumsikan Ekuitas) **VERIFIKASI**
            // 'EKU001' => ['EKUITAS', 6, false, null], // Cadangan Likuiditas **TAMBAHKAN KODE JIKA ADA**
            // 'EKU002' => ['EKUITAS', 6, false, null], // Cadangan Koperasi **TAMBAHKAN KODE JIKA ADA**
            // SHU Tahun Berjalan akan ditambahkan secara terpisah
        ];
    }

    /**
     * Menampilkan Neraca Komparatif dengan format baru.
     */
    public function neraca()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        // Tentukan periode sebelumnya
        $currentDate = new \DateTimeImmutable("$tahun-$bulan-01"); // Use Immutable for safety
        $prevDate = $currentDate->modify('-1 month');
        $prevBulan = (int) $prevDate->format('n');
        $prevTahun = (int) $prevDate->format('Y');

        // 1. Dapatkan mapping dan daftar kode akun
        $mappingData = $this->getNeracaMappingData();
        $listKodeAkunNeraca = array_keys($mappingData);

        // 2. Ambil data saldo komparatif
        $neracaRawData = $this->saldoAkunModel->getNeracaComparativeData(
            $listKodeAkunNeraca,
            $bulan,
            $tahun,
            $prevBulan,
            $prevTahun
        );

        // 3. Olah data mentah menjadi struktur laporan
        $laporan = [
            'ASET_LANCAR' => ['label' => 'ASET LANCAR', 'urutan' => 1, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'ASET_TAK_LANCAR' => ['label' => 'ASET TAK LANCAR', 'urutan' => 2, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'ASET_TETAP' => ['label' => 'ASET TETAP', 'urutan' => 3, 'items' => [], 'total_current' => 0, 'total_prev' => 0, 'akumulasi_lookup' => []],
            'KEWAJIBAN_PENDEK' => ['label' => 'KEWAJIBAN JANGKA PENDEK', 'urutan' => 4, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'KEWAJIBAN_PANJANG' => ['label' => 'KEWAJIBAN JANGKA PANJANG', 'urutan' => 5, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'EKUITAS' => ['label' => 'EKUITAS (MODAL)', 'urutan' => 6, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
            'TIDAK_TERPETAKAN' => ['label' => 'Akun Tidak Terpetakan', 'urutan' => 99, 'items' => [], 'total_current' => 0, 'total_prev' => 0],
        ];

        $akumulasiLookup = []; // [parent_kode => data_akumulasi]

        foreach ($neracaRawData as $item) {
            $kodeAkun = $item['kode_akun'];
            // Ambil info dari mapping, gunakan default jika tidak ada
            $mapInfo = $mappingData[$kodeAkun] ?? ['TIDAK_TERPETAKAN', 99, false, null];
            $kelompok = $mapInfo[0];
            $isAkumulasi = $mapInfo[2];
            $parentKode = $mapInfo[3];

            $dataItem = [
                'kode' => $kodeAkun,
                'nama' => $item['nama_akun'],
                'saldo_current' => floatval($item['saldo_current'] ?? 0),
                'saldo_prev' => floatval($item['saldo_prev'] ?? 0),
                'is_akumulasi' => $isAkumulasi
            ];

            // Pastikan kelompok ada di $laporan sebelum menambah item
            if (isset($laporan[$kelompok])) {
                if ($isAkumulasi && $parentKode) {
                    $akumulasiLookup[$parentKode] = $dataItem;
                    // Saldo akumulasi (kredit) akan mengurangi aset tetap
                } else {
                    $laporan[$kelompok]['items'][$kodeAkun] = $dataItem;
                    // Akumulasi saldo bruto per kelompok
                    $laporan[$kelompok]['total_current'] += $dataItem['saldo_current'];
                    $laporan[$kelompok]['total_prev'] += $dataItem['saldo_prev'];
                }
            } else {
                // Masukkan ke Tidak Terpetakan jika kelompok tidak dikenal
                $laporan['TIDAK_TERPETAKAN']['items'][$kodeAkun] = $dataItem;
            }
        }
        $laporan['ASET_TETAP']['akumulasi_lookup'] = $akumulasiLookup; // Attach lookup ke grup Aset Tetap

        // Urutkan Akun dalam setiap kelompok berdasarkan Kode Akun
        foreach ($laporan as $kelompok => &$dataKelompok) {
            if (!empty($dataKelompok['items'])) {
                ksort($dataKelompok['items']); // ksort mengurutkan berdasarkan key (kode akun)
            }
        }
        unset($dataKelompok); // Hapus reference

        // Urutkan Kelompok Laporan utama berdasarkan 'urutan'
        uasort($laporan, function ($a, $b) {
            return $a['urutan'] <=> $b['urutan']; // PHP 7+ spaceship operator
        });


        // Hitung Total NETTO Aset Tetap
        $totalAkumCurrent = array_sum(array_column($akumulasiLookup, 'saldo_current'));
        $totalAkumPrev = array_sum(array_column($akumulasiLookup, 'saldo_prev'));
        $laporan['ASET_TETAP']['total_net_current'] = $laporan['ASET_TETAP']['total_current'] - $totalAkumCurrent;
        $laporan['ASET_TETAP']['total_net_prev'] = $laporan['ASET_TETAP']['total_prev'] - $totalAkumPrev;

        // 4. Hitung Laba Rugi Bersih Periode Berjalan (current period only)
        $labaRugiBersihPeriode = $this->hitungLabaRugiBersih($bulan, $tahun);

        // 5. Hitung Total Keseluruhan
        $grandTotalAset_current = ($laporan['ASET_LANCAR']['total_current'] ?? 0)
            + ($laporan['ASET_TAK_LANCAR']['total_current'] ?? 0)
            + ($laporan['ASET_TETAP']['total_net_current'] ?? 0); // Gunakan Netto
        $grandTotalAset_prev = ($laporan['ASET_LANCAR']['total_prev'] ?? 0)
            + ($laporan['ASET_TAK_LANCAR']['total_prev'] ?? 0)
            + ($laporan['ASET_TETAP']['total_net_prev'] ?? 0); // Gunakan Netto

        $grandTotalPasivaModal_current = ($laporan['KEWAJIBAN_PENDEK']['total_current'] ?? 0)
            + ($laporan['KEWAJIBAN_PANJANG']['total_current'] ?? 0)
            + ($laporan['EKUITAS']['total_current'] ?? 0)
            + $labaRugiBersihPeriode; // Tambah L/R periode ini
        $grandTotalPasivaModal_prev = ($laporan['KEWAJIBAN_PENDEK']['total_prev'] ?? 0)
            + ($laporan['KEWAJIBAN_PANJANG']['total_prev'] ?? 0)
            + ($laporan['EKUITAS']['total_prev'] ?? 0); // L/R lalu sdh masuk Ekuitas

        $data = [
            'title' => 'Neraca',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'prevBulan' => $prevBulan,
            'prevTahun' => $prevTahun,
            'laporan' => $laporan, // Data terstruktur siap pakai
            'laba_rugi_bersih_current' => $labaRugiBersihPeriode,
            'grand_total_aset_current' => $grandTotalAset_current,
            'grand_total_aset_prev' => $grandTotalAset_prev,
            'grand_total_pasiva_modal_current' => $grandTotalPasivaModal_current,
            'grand_total_pasiva_modal_prev' => $grandTotalPasivaModal_prev,
            'bulanNames' => $this->bulanNames
        ];

        // Nama view baru
        return view('admin/buku_besar/neraca', $data);
    }

    /**
     * Helper function untuk menghitung Laba Rugi Bersih periode tertentu.
     */
    private function hitungLabaRugiBersih($bulan, $tahun): float
    {
        $labaRugiData = $this->saldoAkunModel->getLaporanLabaRugi($bulan, $tahun);
        $totalPendapatanLR = 0;
        $totalBebanLR = 0;
        $kategoriBebanActualLR = ['BIAYA BIAYA', 'BIAYA PAJAK', 'PENYISIHAN BEBAN DANA', 'PENYUSUTAN PENYUSUTAN'];
        if (!empty($labaRugiData)) {
            foreach ($labaRugiData as $itemLR) {
                $saldoLR = floatval($itemLR['saldo'] ?? 0);
                if (isset($itemLR['kategori'])) {
                    if ($itemLR['kategori'] == 'PEMASUKAN') {
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

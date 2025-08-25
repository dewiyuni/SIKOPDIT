<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\AnggotaModel;
use App\Controllers\BaseController;
use App\Models\JurnalKasModel;
use App\Models\TransaksiPinjamanModel;
use App\Models\TransaksiSimpananModel;


class AuthController extends BaseController
{
    protected $authModel;
    protected $jurnalKasModel;
    protected $anggotaModel;
    protected $transaksiSimpananModel;
    protected $transaksiPinjamanModel;
    protected $db;
    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->jurnalKasModel = new JurnalKasModel();
        $this->anggotaModel = new AnggotaModel();
        $this->transaksiSimpananModel = new TransaksiSimpananModel();
        $this->transaksiPinjamanModel = new TransaksiPinjamanModel();
        $this->db = \Config\Database::connect();
    }
    public function login()
    {
        if (session()->get('is_logged_in')) {
            $role = session()->get('role');
            if ($role === 'admin')
                return redirect()->to('/admin/dashboard');
            if ($role === 'karyawan')
                return redirect()->to('/karyawan/dashboard');
            if ($role === 'anggota')
                return redirect()->to('/anggota/dashboard'); // 👈
        }
        return view('auth/login');
    }
    public function anggotaDashboard()
    {
        if (session()->get('role') !== 'anggota') {
            return redirect()->to('/login')->with('error', 'Akses ditolak');
        }

        $idAnggota = session()->get('anggota_id');

        // Hitung saldo simpanan per anggota
        $simpanan = $this->db->table('transaksi_simpanan')
            ->select('
            SUM(setor_sp - tarik_sp) as total_sp,
            SUM(setor_sw - tarik_sw) as total_sw,
            SUM(setor_swp - tarik_swp) as total_swp,
            SUM(setor_ss - tarik_ss) as total_ss
        ')
            ->where('id_anggota', $idAnggota)
            ->get()
            ->getRowArray();

        $totalSimpanan = array_sum($simpanan);

        // Hitung pinjaman aktif per anggota
        $pinjaman = $this->db->table('transaksi_pinjaman')
            ->select('SUM(jumlah_pinjaman - COALESCE((SELECT SUM(jumlah_angsuran) FROM angsuran WHERE angsuran.id_pinjaman = transaksi_pinjaman.id_pinjaman),0)) as total_pinjaman')
            ->where('id_anggota', $idAnggota)
            ->where('status', 'aktif')
            ->get()
            ->getRowArray();

        // Ambil 5 pinjaman terakhir
        $pinjamanTerakhir = $this->db->table('transaksi_pinjaman')
            ->where('id_anggota', $idAnggota)
            ->orderBy('tanggal_pinjaman', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // Ambil angsuran terakhir untuk setiap pinjaman
        // Ambil angsuran untuk setiap pinjaman terakhir
        foreach ($pinjamanTerakhir as &$p) {
            $p['angsuran'] = $this->db->table('angsuran')
                ->where('id_pinjaman', $p['id_pinjaman'])
                ->orderBy('tanggal_angsuran', 'ASC') // urut dari yang paling awal
                ->get()
                ->getResultArray();
        }

        $data = [
            'anggota' => $this->anggotaModel->find($idAnggota),
            'simpanan' => $simpanan,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $pinjaman['total_pinjaman'] ?? 0,
            'pinjamanTerakhir' => $pinjamanTerakhir,
        ];

        return view('dashboard_anggota', $data);
    }



    public function authenticate()
    {
        if (session()->get('is_logged_in')) {
            $role = session()->get('role');
            if ($role === 'admin')
                return redirect()->to('/admin/dashboard');
            if ($role === 'karyawan')
                return redirect()->to('/karyawan/dashboard');
            if ($role === 'anggota')
                return redirect()->to('/anggota/dashboard');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // 1) Cek di tabel users (admin/karyawan)
        $user = $this->authModel->getUserByEmail($email);
        if ($user && password_verify($password, $user->password)) {
            if ($user->status !== 'aktif') {
                session()->setFlashdata('error', 'Akun belum aktif.');
                return redirect()->to('/');
            }

            session()->set([
                'user_id' => $user->id_user,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role, // admin/karyawan
                'is_logged_in' => true,
            ]);

            return redirect()->to($user->role === 'admin' ? '/admin/dashboard' : '/karyawan/dashboard');
        }

        // 2) Fallback: cek di tabel anggota
        $anggota = $this->anggotaModel
            ->where('email', $email)
            ->where('status', 'aktif')
            ->first();

        if ($anggota && password_verify($password, $anggota->password)) {
            // Set session untuk anggota
            session()->set([
                'anggota_id' => $anggota->id_anggota,
                'nama' => $anggota->nama, // langsung pakai nama lengkap
                'email' => $anggota->email,
                'role' => 'anggota',
                'is_logged_in' => true,
            ]);

            return redirect()->to('/anggota/dashboard');
        }

        // Jika dua-duanya gagal
        session()->setFlashdata('error', 'Email atau password salah');
        return redirect()->to('/');
    }


    public function logout()
    {
        session()->destroy();
        return redirect()->to('auth/login');
    }

    // PASTIKAN ROUTE INI ADA DALAM GROUP ADMIN DENGAN FILTER roleCheck
    public function kelolaPengguna()
    {
        // Fallback cek peran jika filter route tidak berfungsi
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('karyawan/dashboard'))->with('error', 'Akses ditolak.');
        }

        $data['users'] = $this->authModel->findAll(); // Ambil semua user
        // Flash data success/error/errors dari simpan/update/delete akan otomatis tersedia
        return view('admin/kelola_pengguna', $data);
    }

    // PASTIKAN ROUTE INI ADA DALAM GROUP ADMIN DENGAN FILTER roleCheck
    public function tambahPengguna()
    {
        // Fallback cek peran jika filter route tidak berfungsi
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('karyawan/dashboard'))->with('error', 'Akses ditolak.');
        }
        // View ini akan menampilkan form tambah pengguna
        // Flash data error/errors dari simpanPengguna() akan otomatis tersedia
        return view('admin/tambah_pengguna');
    }

    // PASTIKAN ROUTE INI ADA DALAM GROUP ADMIN DENGAN FILTER roleCheck
    public function simpanPengguna()
    {
        // Fallback cek peran jika filter route tidak berfungsi
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('karyawan/dashboard'))->with('error', 'Akses ditolak.');
        }

        $rules = [
            'nama' => 'required',
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'Email {value} sudah digunakan.' // Pesan kustom untuk unique
                ]
            ],
            'password' => 'required|min_length[6]',
            'role' => 'required|in_list[admin,karyawan]',
            'status' => 'required|in_list[aktif,nonaktif]',
        ];

        // Lakukan validasi
        if (!$this->validate($rules)) {
            // Jika validasi gagal, kembali ke form dengan input sebelumnya dan error
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Data untuk disimpan
        $data = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        // Coba simpan data
        // Model insert mengembalikan ID jika berhasil, false jika gagal
        $insertedId = $this->authModel->insert($data);

        if ($insertedId !== false) {
            // Jika berhasil insert, redirect ke halaman daftar pengguna dengan pesan sukses
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('success', 'Pengguna baru berhasil ditambahkan.');
        } else {
            // Jika gagal insert (meskipun validasi lolos, misalnya masalah DB)
            // Anda bisa log $this->authModel->errors() untuk debugging
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pengguna. Silakan coba lagi.');
        }
    }

    // PASTIKAN ROUTE INI ADA DALAM GROUP ADMIN DENGAN FILTER roleCheck
    // Gunakan (:num) di route untuk ID
    public function editPengguna($id_user)
    {
        // Fallback cek peran jika filter route tidak berfungsi
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('karyawan/dashboard'))->with('error', 'Akses ditolak.');
        }

        $pengguna = $this->authModel->find($id_user);

        if (!$pengguna) {
            // Jika pengguna tidak ditemukan, redirect ke halaman daftar dengan pesan error
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('error', 'Pengguna tidak ditemukan.');
        }

        $data['pengguna'] = $pengguna;
        // Flash data error/errors dari updatePengguna() akan otomatis tersedia
        return view('admin/edit_pengguna', $data);
    }

    // PASTIKAN ROUTE INI ADA DALAM GROUP ADMIN DENGAN FILTER roleCheck
    // Route ini menerima POST dari form edit
    public function updatePengguna()
    {
        // Fallback cek peran jika filter route tidak berfungsi
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('karyawan/dashboard'))->with('error', 'Akses ditolak.');
        }

        $id_user = $this->request->getPost('id_user');

        // Pastikan ID pengguna ada dan pengguna tersebut ada di database
        $existingUser = $this->authModel->find($id_user);
        if (!$existingUser) {
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('error', 'Pengguna tidak ditemukan.');
        }

        // Definisikan aturan validasi
        $rules = [
            'id_user' => 'required|numeric', // Pastikan ID ada dan numeric
            'nama' => 'required',
            // Validasi unique email, kecuali untuk email pengguna saat ini ($id_user)
            'email' => [
                'rules' => "required|valid_email|is_unique[users.email,id_user,{$id_user}]",
                'errors' => [
                    'is_unique' => 'Email {value} sudah digunakan oleh pengguna lain.'
                ]
            ],
            // Password validation: opsional (permit_empty) jika field tidak diisi,
            // tapi jika diisi (if_exist), minimal 6 karakter.
            // Gunakan permit_empty karena field password di form edit bisa kosong
            'password' => 'permit_empty|min_length[6]',
            'role' => 'required|in_list[admin,karyawan]',
            'status' => 'required|in_list[aktif,nonaktif]',
        ];

        // Lakukan validasi
        if (!$this->validate($rules)) {
            // Jika validasi gagal, kembali ke form dengan input sebelumnya dan error
            // Menggunakan ID pengguna agar redirect back ke halaman edit yang benar
            return redirect()->to(site_url('admin/edit_pengguna/' . $id_user))->withInput()->with('errors', $this->validator->getErrors());
        }

        // Siapkan data untuk update
        $data = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        // Cek apakah field password diisi di form (tidak kosong)
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            // Jika diisi, hash password baru dan tambahkan ke data update
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Lakukan update menggunakan ID
        // Model update mengembalikan true jika ID valid (meskipun tidak ada baris berubah), false jika error DB
        $updated = $this->authModel->update($id_user, $data);

        if ($updated) {
            // Jika berhasil update (atau tidak ada perubahan tapi ID valid)
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('success', 'Pengguna berhasil diperbarui.');
        } else {
            // Ini bisa terjadi jika ada error DB saat update
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengguna. Silakan coba lagi.');
            // Note: Redirect back() di sini akan kembali ke URL updatePengguna, bukan edit_pengguna.
            // Lebih baik redirect ke edit_pengguna dengan ID lagi jika ingin user memperbaiki di sana.
            // Contoh: return redirect()->to(site_url('admin/edit_pengguna/' . $id_user))->withInput()->with('error', '...');
        }
    }

    // PASTIKAN ROUTE INI ADA DALAM GROUP ADMIN DENGAN FILTER roleCheck
    // PASTIKAN ROUTE INI MENGGUNAKAN METHOD POST ATAU DELETE
    public function hapusPengguna($id_user)
    {
        // Fallback cek peran jika filter route tidak berfungsi
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('karyawan/dashboard'))->with('error', 'Akses ditolak.');
        }

        // Cek apakah pengguna yang akan dihapus adalah admin atau pengguna yang sedang login
        if ($id_user == session()->get('user_id')) {
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cari pengguna yang akan dihapus
        $userToDelete = $this->authModel->find($id_user);
        if (!$userToDelete) {
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('error', 'Pengguna tidak ditemukan.');
        }

        // Cek lagi jika pengguna yang dihapus ternyata admin (meskipun tombol di view sudah disabled)
        if ($userToDelete->role === 'admin') {
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('error', 'Anda tidak dapat menghapus pengguna dengan peran Admin.');
        }

        // Lakukan penghapusan (hard delete karena useSoftDeletes=true, jadi delete($id, true) melakukan hard delete)
        if ($this->authModel->delete($id_user, true)) {
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('success', 'Pengguna berhasil dihapus.');
        } else {
            // Gagal menghapus
            // Anda bisa log $this->authModel->errors() untuk debugging
            return redirect()->to(site_url('admin/kelola_pengguna'))->with('error', 'Gagal menghapus pengguna.');
        }
    }


    // ================= Dashboard ============================================
    public function adminDashboard()
    {
        // Total Anggota Aktif
        $totalAnggota = $this->anggotaModel->where('status', 'aktif')->countAllResults();

        // Total Saldo Simpanan (menggunakan method dari model)
        $totalSimpanan = $this->transaksiSimpananModel->getTotalSimpanan();

        // Total Pinjaman Aktif (sisa pokok pinjaman yang belum lunas)
        $queryTotalPinjamanAktif = $this->db->query("
            SELECT SUM(tp.jumlah_pinjaman - COALESCE((SELECT SUM(a.jumlah_angsuran) FROM angsuran a WHERE a.id_pinjaman = tp.id_pinjaman), 0)) as total_sisa_pinjaman
            FROM transaksi_pinjaman tp
            WHERE tp.status = 'aktif'
        ");
        $totalPinjaman = $queryTotalPinjamanAktif->getRow()->total_sisa_pinjaman ?? 0;

        // Total Kas (Saldo Akhir Kas dari Jurnal Kas)
        // Asumsi DUM adalah pemasukan, DUK adalah pengeluaran
        $queryTotalKas = $this->db->query("
            SELECT SUM(CASE
                         WHEN kategori = 'DUM' THEN jumlah
                         WHEN kategori = 'DUK' THEN -jumlah
                         ELSE 0
                       END) as saldo_kas
            FROM jurnal_kas
        ");
        $totalKas = $queryTotalKas->getRow()->saldo_kas ?? 0;

        // Data untuk Grafik Bulanan
        $grafikSimpananPinjamanData = $this->_getGrafikSimpananPinjamanBulanan();
        $grafikKasBulananData = $this->_getGrafikKasBulanan();

        $dataDebug = [
            'grafikSimpananPinjamanLabels' => $grafikSimpananPinjamanData['labels'],
            'grafikSimpananData' => $grafikSimpananPinjamanData['simpanan'],
            'grafikPinjamanData' => $grafikSimpananPinjamanData['pinjaman'],
            'grafikKasLabels' => $grafikKasBulananData['labels'],
            'grafikKasMasukData' => $grafikKasBulananData['kas_masuk'],
            'grafikKasKeluarData' => $grafikKasBulananData['kas_keluar'],
        ];
        // dd($dataDebug); // UNCOMMENT BARIS INI UNTUK DEBUGGING
        return view('dashboard_admin', [
            'totalAnggota' => $totalAnggota,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $totalPinjaman,
            'totalKas' => $totalKas,
            'grafikSimpananPinjamanLabels' => json_encode($grafikSimpananPinjamanData['labels']),
            'grafikSimpananData' => json_encode($grafikSimpananPinjamanData['simpanan']),
            'grafikPinjamanData' => json_encode($grafikSimpananPinjamanData['pinjaman']),
            'grafikKasLabels' => json_encode($grafikKasBulananData['labels']),
            'grafikKasMasukData' => json_encode($grafikKasBulananData['kas_masuk']),
            'grafikKasKeluarData' => json_encode($grafikKasBulananData['kas_keluar']),
        ]);
    }

    private function _getGrafikSimpananPinjamanBulanan($limitBulan = 12)
    {
        // Data Simpanan Bulanan (Total Setoran)
        // Menggunakan kolom 'tanggal' dari tabel 'transaksi_simpanan'
        $simpananBulananQuery = $this->db->table('transaksi_simpanan')
            ->select("DATE_FORMAT(tanggal, '%Y-%m') as bulan, 
                      SUM(setor_sw + setor_swp + setor_ss + setor_sp) as total_setoran")
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC') // Urutkan ASC untuk data historis
            // ->limit($limitBulan) // Batasi jika datanya banyak, mungkin lebih baik filter berdasarkan tahun
            ->get()->getResultArray();

        // Data Pinjaman Bulanan (Total Pencairan)
        $pinjamanBulananQuery = $this->db->table('transaksi_pinjaman')
            ->select("DATE_FORMAT(tanggal_pinjaman, '%Y-%m') as bulan, 
                      SUM(jumlah_pinjaman) as total_pinjaman")
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC')
            // ->limit($limitBulan)
            ->get()->getResultArray();

        // Gabungkan label bulan dari kedua query dan buat unik
        $bulanMap = [];
        foreach ($simpananBulananQuery as $s) {
            $bulanMap[$s['bulan']] = ['simpanan' => (float) $s['total_setoran'], 'pinjaman' => 0];
        }
        foreach ($pinjamanBulananQuery as $p) {
            if (isset($bulanMap[$p['bulan']])) {
                $bulanMap[$p['bulan']]['pinjaman'] = (float) $p['total_pinjaman'];
            } else {
                $bulanMap[$p['bulan']] = ['simpanan' => 0, 'pinjaman' => (float) $p['total_pinjaman']];
            }
        }
        ksort($bulanMap); // Urutkan berdasarkan key (bulan)

        // Ambil $limitBulan terakhir jika data lebih banyak
        if (count($bulanMap) > $limitBulan) {
            $bulanMap = array_slice($bulanMap, -$limitBulan, $limitBulan, true);
        }

        $labels = array_keys($bulanMap);
        $dataSimpanan = array_column($bulanMap, 'simpanan');
        $dataPinjaman = array_column($bulanMap, 'pinjaman');

        return [
            'labels' => $labels,
            'simpanan' => $dataSimpanan,
            'pinjaman' => $dataPinjaman,
        ];
    }

    private function _getGrafikKasBulanan($limitBulan = 12)
    {
        // Data Kas Bulanan
        // Asumsi: 'DUM' = Pemasukan, 'DUK' = Pengeluaran
        // Menggunakan kolom 'tanggal' dari tabel 'jurnal_kas'
        $kasBulananQuery = $this->db->table('jurnal_kas')
            ->select("DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                      SUM(CASE WHEN kategori = 'DUM' THEN jumlah ELSE 0 END) as total_masuk,
                      SUM(CASE WHEN kategori = 'DUK' THEN jumlah ELSE 0 END) as total_keluar")
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC') // Urutkan ASC
            // ->limit($limitBulan)
            ->get()->getResultArray();

        // Ambil $limitBulan terakhir jika data lebih banyak
        if (count($kasBulananQuery) > $limitBulan) {
            $kasBulananQuery = array_slice($kasBulananQuery, -$limitBulan, $limitBulan);
        }

        $labels = [];
        $kasMasukData = [];
        $kasKeluarData = [];

        foreach ($kasBulananQuery as $item) {
            $labels[] = $item['bulan'];
            $kasMasukData[] = (float) $item['total_masuk'];
            $kasKeluarData[] = (float) $item['total_keluar'];
        }

        return [
            'labels' => $labels,
            'kas_masuk' => $kasMasukData,
            'kas_keluar' => $kasKeluarData
        ];
    }


    public function chartData()
    {
        $anggotaModel = new AnggotaModel();
        $simpananModel = new TransaksiSimpananModel();
        $pinjamanModel = new TransaksiPinjamanModel();

        // Ambil data anggota per dusun
        $dusunData = $anggotaModel->select('dusun, COUNT(*) as total')
            ->groupBy('dusun')
            ->findAll();

        // Ambil data anggota berdasarkan usia
        $usiaData = $anggotaModel->select("CASE 
                WHEN umur BETWEEN 18 AND 25 THEN '18-25'
                WHEN umur BETWEEN 26 AND 35 THEN '26-35'
                WHEN umur BETWEEN 36 AND 45 THEN '36-45'
                ELSE '46+' 
            END as usia_kategori, COUNT(*) as total")
            ->groupBy('usia_kategori')
            ->findAll();

        // Simpanan bulanan
        $simpananBulanan = $simpananModel->select("
        DATE_FORMAT(tsd.created_at, '%Y-%m') as bulan, 
        SUM(tsd.setor_sw) - SUM(tsd.tarik_sw) as saldo_sw,
        SUM(tsd.setor_swp) - SUM(tsd.tarik_swp) as saldo_swp,
        SUM(tsd.setor_ss) - SUM(tsd.tarik_ss) as saldo_ss,
        SUM(tsd.setor_sp) - SUM(tsd.tarik_sp) as saldo_sp")
            ->from('transaksi_simpanan_detail tsd')
            ->groupBy('bulan')
            ->findAll();

        // Pinjaman bulanan
        $pinjamanBulanan = $pinjamanModel->select("DATE_FORMAT(tanggal_pinjaman, '%Y-%m') as bulan, SUM(jumlah_pinjaman) as total")
            ->groupBy('bulan')
            ->findAll();

        return $this->response->setJSON([
            'dusunData' => $dusunData,
            'usiaData' => $usiaData,
            'simpananBulanan' => $simpananBulanan,
            'pinjamanBulanan' => $pinjamanBulanan
        ]);
    }

    public function karyawanDashboard()
    {
        // Total Anggota Aktif
        $totalAnggota = $this->anggotaModel->where('status', 'aktif')->countAllResults();

        // Total Saldo Simpanan
        $totalSimpanan = $this->transaksiSimpananModel->getTotalSimpanan();

        // Total Pinjaman Aktif (sisa pokok pinjaman yang belum lunas)
        $queryTotalPinjamanAktif = $this->db->query("
            SELECT SUM(tp.jumlah_pinjaman - COALESCE((SELECT SUM(a.jumlah_angsuran) FROM angsuran a WHERE a.id_pinjaman = tp.id_pinjaman), 0)) as total_sisa_pinjaman
            FROM transaksi_pinjaman tp
            WHERE tp.status = 'aktif'
        ");
        $totalPinjaman = $queryTotalPinjamanAktif->getRow()->total_sisa_pinjaman ?? 0;

        // Total Kas (Saldo Akhir Kas dari Jurnal Kas)
        // Karyawan mungkin tidak perlu melihat total kas keseluruhan,
        // atau mungkin perlu melihat kas yang relevan dengan operasional mereka.
        // Untuk saat ini, kita bisa tampilkan total kas yang sama dengan admin, atau kosongi jika tidak relevan.
        $queryTotalKas = $this->db->query("
            SELECT SUM(CASE
                         WHEN kategori = 'DUM' THEN jumlah
                         WHEN kategori = 'DUK' THEN -jumlah
                         ELSE 0
                       END) as saldo_kas
            FROM jurnal_kas
        ");
        $totalKas = $queryTotalKas->getRow()->saldo_kas ?? 0; // Atau set ke 0 jika tidak relevan untuk karyawan

        // Data untuk Grafik Bulanan (Sama seperti Admin)
        $grafikSimpananPinjamanData = $this->_getGrafikSimpananPinjamanBulanan(); // Method private yang sama
        $grafikKasBulananData = $this->_getGrafikKasBulanan(); // Method private yang sama

        return view('dashboard_karyawan', [
            'totalAnggota' => $totalAnggota,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $totalPinjaman,
            'totalKas' => $totalKas, // Kirim data total kas
            'grafikSimpananPinjamanLabels' => json_encode($grafikSimpananPinjamanData['labels']),
            'grafikSimpananData' => json_encode($grafikSimpananPinjamanData['simpanan']),
            'grafikPinjamanData' => json_encode($grafikSimpananPinjamanData['pinjaman']),
            'grafikKasLabels' => json_encode($grafikKasBulananData['labels']),
            'grafikKasMasukData' => json_encode($grafikKasBulananData['kas_masuk']),
            'grafikKasKeluarData' => json_encode($grafikKasBulananData['kas_keluar']),
        ]);
    }

}

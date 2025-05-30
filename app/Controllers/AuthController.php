<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\AnggotaModel;
use App\Controllers\BaseController;
use App\Models\TransaksiPinjamanModel;
use App\Models\TransaksiSimpananModel;


class AuthController extends BaseController
{
    protected $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }
    public function login()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to(session()->get('role') === 'admin' ? '/admin/dashboard' : '/karyawan/dashboard');
        }

        return view('auth/login');
    }


    public function authenticate()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to(session()->get('role') === 'admin' ? '/admin/dashboard' : '/karyawan/dashboard');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $auth = new AuthModel();
        $user = $auth->getUserByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            if ($user->status !== 'aktif') {
                session()->setFlashdata('error', 'Akun belum aktif.');
                return redirect()->to('/');
            }

            // Set session
            session()->set([
                'user_id' => $user->id_user,
                'nama' => $user->nama,
                'role' => $user->role,
                'is_logged_in' => true,
            ]);

            return redirect()->to($user->role === 'admin' ? '/admin/dashboard' : '/karyawan/dashboard');
        } else {
            session()->setFlashdata('error', 'Email atau password salah');
            return redirect()->to('/');
        }
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
        $anggotaModel = new \App\Models\AnggotaModel();
        $totalAnggota = $anggotaModel->countAll(); // Menghitung total anggota
        $simpananModel = new TransaksiSimpananModel(); // Pastikan model sudah dibuat
        $totalSimpanan = $simpananModel->getTotalSimpanan(); // Memanggil fungsi total simpanan
        $pinjamanModel = new TransaksiPinjamanModel(); // Pastikan model sudah dibuat
        $totalPinjaman = $pinjamanModel->getTotalPinjaman(); // Memanggil fungsi total pinjaman

        // Kirim data ke view
        return view('dashboard_admin', [
            'totalAnggota' => $totalAnggota,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $totalPinjaman
        ]);

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
        $anggotaModel = new \App\Models\AnggotaModel();
        $totalAnggota = $anggotaModel->countAll(); // Menghitung total anggota
        $simpananModel = new TransaksiSimpananModel(); // Pastikan model sudah dibuat
        $totalSimpanan = $simpananModel->getTotalSimpanan(); // Memanggil fungsi total simpanan
        $pinjamanModel = new TransaksiPinjamanModel(); // Pastikan model sudah dibuat
        $totalPinjaman = $pinjamanModel->getTotalPinjaman(); // Memanggil fungsi total pinjaman

        // Kirim data ke view
        return view('dashboard_karyawan', [
            'totalAnggota' => $totalAnggota,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $totalPinjaman
        ]);
    }


}

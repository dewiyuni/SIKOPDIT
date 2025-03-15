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

    // ================= Pengguna user ============================================

    public function kelolaPengguna()
    {
        $data['users'] = $this->authModel->findAll();
        return view('admin/kelola_pengguna', $data);
    }

    public function tambahPengguna()
    {
        return view('admin/tambah_pengguna');
    }

    public function simpanPengguna()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama' => 'required',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role' => 'required|in_list[admin,karyawan]',
        ]);

        // Cek apakah validasi gagal
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Jika lolos validasi, baru simpan ke database
        $data = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role'),
            'status' => 'aktif',
        ];

        $this->authModel->insert($data);

        return redirect()->to('/admin/kelola_pengguna')->with('success', 'Pengguna berhasil ditambahkan.');
    }


    public function editPengguna($id_user)
    {
        $data['pengguna'] = $this->authModel->find($id_user);
        if (!$data['pengguna']) {
            return redirect()->to('/admin/kelola_pengguna')->with('error', 'Pengguna tidak ditemukan');
        }
        return view('admin/edit_pengguna', $data);
    }

    public function updatePengguna()
    {
        $id_user = $this->request->getPost('id_user');

        $email = $this->request->getPost('email');
        $existingUser = $this->authModel->where('email', $email)->where('id_user !=', $id_user)->first();
        if ($existingUser) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan oleh pengguna lain.');
        }

        $this->authModel->update($id_user, $this->request->getPost());
        return redirect()->to('/admin/kelola_pengguna')->with('success', 'Pengguna berhasil diperbarui.');
    }
    public function hapusPengguna($id_user)
    {
        if (!$this->authModel->delete($id_user, true)) {
            return redirect()->to('/admin/kelola_pengguna')->with('error', 'Gagal menghapus pengguna.');
        }

        return redirect()->to('/admin/kelola_pengguna')->with('success', 'pengguna berhasil dihapus.');
    }

    // ================= Dashboard ============================================
    public function adminDashboard()
    {
        $anggotaModel = new \App\Models\AnggotaModel();
        $totalAnggota = $anggotaModel->countAll(); // Menghitung total anggota

        $simpananModel = new \App\Models\TransaksiSimpananModel();
        $pinjamanModel = new \App\Models\TransaksiPinjamanModel();
        $angsuranModel = new \App\Models\AngsuranModel();

        $totalSimpanan = $simpananModel->selectSum('saldo_total')->first()->saldo_total;
        $totalPinjaman = $pinjamanModel->selectSum('jumlah_pinjaman')->first()->jumlah_pinjaman;
        $totalAngsuran = $angsuranModel->selectSum('jumlah_angsuran')->first()->jumlah_angsuran;

        return view('dashboard_admin', [
            'totalAnggota' => $totalAnggota,
            'totalSimpanan' => $totalSimpanan ?? 0,
            'totalPinjaman' => $totalPinjaman ?? 0,
            'totalAngsuran' => $totalAngsuran ?? 0,

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
        $simpananBulanan = $simpananModel->select("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(saldo_total) as total")
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

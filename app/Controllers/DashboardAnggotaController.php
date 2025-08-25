<?php

namespace App\Controllers;

use App\Models\AnggotaModel;
use App\Models\KeuanganModel;
use App\Controllers\BaseController;
use App\Models\TransaksiSimpananModel;

class DashboardAnggotaController extends BaseController
{
    protected $transaksiModel;
    protected $keuanganModel;
    protected $anggotaModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiSimpananModel();
        $this->keuanganModel = new KeuanganModel();
        $this->anggotaModel = new AnggotaModel();
    }

    public function index()
    {
        $session = session();
        $idAnggota = $session->get('id_anggota');

        if (!$idAnggota) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu');
        }

        $db = \Config\Database::connect();

        // Ambil saldo simpanan anggota
        $simpanan = $db->table('transaksi_simpanan')
            ->select('
            SUM(setor_sp - tarik_sp) as total_sp,
            SUM(setor_sw - tarik_sw) as total_sw,
            SUM(setor_swp - tarik_swp) as total_swp,
            SUM(setor_ss - tarik_ss) as total_ss
        ')
            ->where('id_anggota', $idAnggota)
            ->get()
            ->getRowArray();

        // Hitung total semua saldo simpanan
        $totalSimpanan = array_sum($simpanan ?? []);

        // Ambil pinjaman aktif anggota
        $pinjaman = $db->table('pinjaman')
            ->select('SUM(jumlah - terbayar) as total_pinjaman')
            ->where('id_anggota', $idAnggota)
            ->where('status', 'berjalan')
            ->get()
            ->getRowArray();

        $data = [
            'title' => 'Dashboard Anggota',
            'simpanan' => $simpanan,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $pinjaman['total_pinjaman'] ?? 0,
        ];

        return view('dashboard_anggota', $data);
    }

    public function profile()
    {
        $session = session();
        $idAnggota = $session->get('anggota_id'); // pastikan saat login disimpan id_anggota

        // Ambil data anggota dari tabel anggota
        $anggota = $this->anggotaModel->find($idAnggota);

        if (!$anggota) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Anggota tidak ditemukan');
        }

        return view('anggota/profile', [
            'title' => 'Profil Saya',
            'anggota' => $anggota
        ]);
    }
    public function updatePassword()
    {
        $id = session()->get('id_anggota'); // Ambil ID anggota dari session
        $anggota = $this->anggotaModel->find($id); // balikan object

        if (!$anggota) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan');
        }

        $current = trim($this->request->getPost('current_password'));
        $new = trim($this->request->getPost('new_password'));
        $confirm = trim($this->request->getPost('confirm_password'));

        // ✅ akses pakai object
        if (!password_verify($current, $anggota->password)) {
            return redirect()->back()->with('error', 'Password lama salah');
        }

        if ($new !== $confirm) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok');
        }

        // ✅ update password baru
        $this->anggotaModel->update($id, [
            'password' => password_hash($new, PASSWORD_DEFAULT)
        ]);

        return redirect()->back()->with('success', 'Password berhasil diubah');
    }

}

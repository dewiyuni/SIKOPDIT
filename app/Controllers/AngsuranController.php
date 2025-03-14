<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AngsuranModel;
use App\Models\TransaksiPinjamanModel;
use CodeIgniter\HTTP\ResponseInterface;

class AngsuranController extends BaseController
{
    protected $angsuranModel;
    protected $pinjamanModel;

    public function __construct()
    {
        $this->angsuranModel = new AngsuranModel();
        $this->pinjamanModel = new TransaksiPinjamanModel();
    }

    public function index($id_pinjaman)
    {
        $data['pinjaman'] = $this->pinjamanModel->find($id_pinjaman);
        $data['angsuran'] = $this->angsuranModel->where('id_pinjaman', $id_pinjaman)->orderBy('tanggal_angsuran', 'ASC')->findAll();

        return view('karyawan/angsuran/index', $data);
    }

    public function tambah($id_pinjaman)
    {
        $data['pinjaman'] = $this->pinjamanModel->find($id_pinjaman);
        return view('karyawan/angsuran/tambah', $data);
    }

    public function simpan()
    {
        $id_pinjaman = $this->request->getPost('id_pinjaman');
        $tanggal_angsuran = $this->request->getPost('tanggal_angsuran');
        $jumlah_angsuran = $this->request->getPost('jumlah_angsuran');

        // Ambil sisa pinjaman terakhir
        $latestAngsuran = $this->angsuranModel->where('id_pinjaman', $id_pinjaman)->orderBy('id_angsuran', 'DESC')->first();
        $sisa_pinjaman = $latestAngsuran ? $latestAngsuran['sisa_pinjaman'] - $jumlah_angsuran : null;

        if ($sisa_pinjaman === null) {
            $pinjaman = $this->pinjamanModel->find($id_pinjaman);
            $sisa_pinjaman = $pinjaman['jumlah_pinjaman'] - $jumlah_angsuran;
        }

        // Simpan angsuran
        $this->angsuranModel->save([
            'id_pinjaman' => $id_pinjaman,
            'tanggal_angsuran' => $tanggal_angsuran,
            'jumlah_angsuran' => $jumlah_angsuran,
            'sisa_pinjaman' => $sisa_pinjaman,
            'status' => $sisa_pinjaman <= 0 ? 'lunas' : 'belum lunas',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update status pinjaman jika lunas
        if ($sisa_pinjaman <= 0) {
            $this->pinjamanModel->update($id_pinjaman, ['status' => 'lunas']);
        }

        return redirect()->to('/karyawan/angsuran/' . $id_pinjaman)->with('message', 'Angsuran berhasil ditambahkan.');
    }
}

<?php

namespace App\Controllers;

use App\Models\AkunKeuanganModel;
use CodeIgniter\Controller;

class AkunKeuangan extends BaseController
{
    public function index()
    {
        $model = new AkunKeuanganModel();
        $data['akun'] = $model->findAll(); // Mengambil semua data akun keuangan

        return view('admin/akun/index', $data);
    }

    // Tambahkan fungsi untuk menambah akun keuangan (Opsional)
    public function create()
    {
        return view('admin/akun/create');
    }

    public function store()
    {
        $model = new AkunKeuanganModel();
        $model->insert([
            'kode_akun' => $this->request->getPost('kode_akun'),
            'nama_akun' => $this->request->getPost('nama_akun'),
            'jenis' => $this->request->getPost('jenis')
        ]);

        return redirect()->to('/akun_keuangan');
    }
}

<?php

namespace App\Controllers;

use App\Models\AkunModel;
use CodeIgniter\Controller;

class AkunController extends Controller
{
    public function options()
    {
        $model = new AkunModel();

        // Ambil daftar akun yang akan digunakan di dropdown
        $akunOptions = $model->findAll(); // Ambil semua akun, bisa ditambah filter jika diperlukan

        // Kirim data akun dalam format JSON ke frontend
        return $this->response->setJSON($akunOptions);
    }
}

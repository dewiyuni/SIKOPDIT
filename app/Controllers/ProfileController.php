<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Controllers\BaseController;

class ProfileController extends BaseController
{
    protected $authModel;
    public function index()
    {
        $authModel = new AuthModel();
        $id_user = session()->get('id_user');

        if (!$id_user) {
            return redirect()->to('/login'); // Redirect jika belum login
        }

        $data['users'] = $authModel->getUserById($id_user);
        return view('layouts/navbar', $data);
    }
}

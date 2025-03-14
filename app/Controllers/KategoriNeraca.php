<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\KategoriNeracaModel;

class KategoriNeraca extends BaseController
{
    protected $kategoriNeracaModel;

    public function __construct()
    {
        $this->kategoriNeracaModel = new KategoriNeracaModel();
    }

    public function index()
    {
        $data['kategoriNeraca'] = $this->kategoriNeracaModel->findAll();
        return view('admin/neraca_awal/kategori_neraca/index', $data);
    }

    public function create()
    {
        return view('admin/neraca_awal/kategori_neraca/create');
    }

    public function store()
    {
        $this->kategoriNeracaModel->insert([
            'nama_kategori' => $this->request->getPost('nama_kategori')
        ]);

        return redirect()->to('/admin/neraca_awal/kategori_neraca')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data['kategori'] = $this->kategoriNeracaModel->find($id);
        return view('admin/neraca_awal/kategori_neraca/edit', $data);
    }

    public function update($id)
    {
        $this->kategoriNeracaModel->update($id, [
            'nama_kategori' => $this->request->getPost('nama_kategori')
        ]);

        return redirect()->to('/admin/neraca_awal/kategori_neraca')->with('success', 'Kategori berhasil diperbarui');
    }

    public function delete($id)
    {
        $this->kategoriNeracaModel->delete($id);
        return redirect()->to('/admin/neraca_awal/kategori_neraca')->with('success', 'Kategori berhasil dihapus');
    }
}

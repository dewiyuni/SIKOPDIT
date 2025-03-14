<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NeracaAwalModel;
use App\Models\KategoriNeracaModel;
use App\Models\DetailNeracaModel;

class NeracaAwalController extends BaseController
{
    protected $neracaAwalModel;
    protected $kategoriNeracaModel;
    protected $detailNeracaModel;
    protected $db;

    public function __construct()
    {
        $this->neracaAwalModel = new NeracaAwalModel();
        $this->kategoriNeracaModel = new KategoriNeracaModel();
        $this->detailNeracaModel = new DetailNeracaModel();
        $this->db = \Config\Database::connect();
    }

    // Halaman utama: Menampilkan daftar neraca awal berdasarkan bulan & tahun
    public function index()
    {
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        // Ambil data neraca berdasarkan bulan & tahun
        $neraca = $this->neracaAwalModel->where(['bulan' => $bulan, 'tahun' => $tahun])->first();

        // Ambil saldo bulan lalu
        $saldoBulanLalu = $this->neracaAwalModel->getSaldoBulanLalu($bulan, $tahun);

        // Jika tidak ada neraca, beri nilai default agar tidak error
        $id_neraca = $neraca['id_neraca_awal'] ?? null;

        $data = [
            'neraca' => $neraca ?? [],
            'saldoBulanLalu' => !empty($saldoBulanLalu) ? $saldoBulanLalu : ['saldo' => 0],
            'detailNeraca' => $id_neraca ? $this->detailNeracaModel->getDetailByNeraca($id_neraca) : [],
            'kategoriNeraca' => $this->kategoriNeracaModel->findAll(),
            'bulan' => $bulan,
            'tahun' => $tahun
        ];

        return view('admin/neraca_awal/index', $data);
    }


    // Halaman Tambah Neraca Awal
    public function create()
    {
        $bulan = date('n');
        $tahun = date('Y');

        return view('admin/neraca_awal/create', [
            'kategoriNeraca' => $this->kategoriNeracaModel->findAll(),
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
    }

    // Simpan Data Baru
    public function store()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');

        // Cek apakah bulan & tahun sudah ada
        $cek = $this->neracaAwalModel->where(['bulan' => $bulan, 'tahun' => $tahun])->first();
        if ($cek) {
            return redirect()->to('/admin/neraca_awal')->with('error', 'Data bulan ini sudah ada!');
        }

        // Gunakan transaksi database
        $this->db->transStart();

        // Simpan ke tabel neraca_awal
        $this->neracaAwalModel->insert([
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);

        $idNeraca = $this->neracaAwalModel->getInsertID(); // Ambil ID terbaru

        if (!$idNeraca) {
            $this->db->transRollback();
            return redirect()->to('/admin/neraca_awal/create')->with('error', 'Gagal menyimpan neraca awal');
        }

        // Simpan rincian kategori
        $kategori = $this->request->getPost('kategori') ?? [];
        $uraian = $this->request->getPost('uraian') ?? [];
        $nilai = $this->request->getPost('nilai') ?? [];

        $dataDetail = [];
        foreach ($kategori as $key => $idKategori) {
            if (!isset($uraian[$key]) || !isset($nilai[$key]) || empty($idKategori)) {
                continue; // Skip jika ada yang kosong
            }

            $dataDetail[] = [
                'id_neraca_awal' => $idNeraca,
                'id_kategori' => $idKategori,
                'uraian' => trim($uraian[$key]),
                'nilai' => floatval($nilai[$key]),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($dataDetail)) {
            $this->detailNeracaModel->insertBatch($dataDetail);
        }

        // Commit transaksi
        $this->db->transComplete();

        return redirect()->to('/admin/neraca_awal')->with('success', 'Neraca Awal berhasil ditambahkan');
    }

    // Halaman Edit
    public function edit($id)
    {
        $neraca = $this->neracaAwalModel->find($id);
        if (!$neraca) {
            return redirect()->to('/admin/neraca_awal')->with('error', 'Data tidak ditemukan');
        }

        return view('admin/neraca_awal/edit', [
            'neraca' => $neraca,
            'detailNeraca' => $this->detailNeracaModel->getDetailByNeraca($id),
            'kategoriNeraca' => $this->kategoriNeracaModel->findAll()
        ]);
    }

    // Update Data
    public function update($id)
    {
        $neraca = $this->neracaAwalModel->find($id);
        if (!$neraca) {
            return redirect()->to('/admin/neraca_awal')->with('error', 'Data tidak ditemukan');
        }

        $this->db->transStart(); // Mulai transaksi

        // Perbarui kategori
        $kategori = $this->request->getPost('kategori') ?? [];
        $uraian = $this->request->getPost('uraian') ?? [];
        $nilai = $this->request->getPost('nilai') ?? [];

        foreach ($kategori as $key => $idKategori) {
            if (!isset($uraian[$key]) || !isset($nilai[$key])) {
                continue;
            }

            $this->detailNeracaModel->updateOrInsert([
                'id_neraca_awal' => $id,
                'id_kategori' => $idKategori
            ], [
                'uraian' => trim($uraian[$key]),
                'nilai' => floatval($nilai[$key])
            ]);
        }

        $this->db->transComplete(); // Commit transaksi

        return redirect()->to('/admin/neraca_awal')->with('success', 'Data berhasil diperbarui');
    }

    // Hapus Data
    public function delete($id)
    {
        $neraca = $this->neracaAwalModel->find($id);
        if (!$neraca) {
            return redirect()->to('/admin/neraca_awal')->with('error', 'Data tidak ditemukan');
        }

        $this->db->transStart();
        $this->detailNeracaModel->where('id_neraca_awal', $id)->delete();
        $this->neracaAwalModel->delete($id);
        $this->db->transComplete();

        return redirect()->to('/admin/neraca_awal')->with('success', 'Data berhasil dihapus');
    }
}

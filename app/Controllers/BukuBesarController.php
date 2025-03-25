<?php

namespace App\Controllers;

use App\Models\JurnalKasModel;
use App\Models\BukuBesarModel;

class BukuBesarController extends BaseController
{
    protected $jurnalModel;
    protected $bukuBesarModel;

    public function __construct()
    {
        $this->jurnalModel = new JurnalKasModel();
        $this->bukuBesarModel = new BukuBesarModel();
    }

    public function prosesJurnalKeBukuBesar()
    {
        $jurnalKas = $this->jurnalModel->findAll();
        $saldo = 0;

        foreach ($jurnalKas as $jurnal) {
            $akun = $jurnal['kategori'] == 'DUM' ? 'Kas' : 'Beban';
            $debit = $jurnal['kategori'] == 'DUM' ? $jurnal['jumlah'] : 0;
            $kredit = $jurnal['kategori'] == 'DUK' ? $jurnal['jumlah'] : 0;

            $saldo += $debit - $kredit;

            $this->bukuBesarModel->insert([
                'tanggal' => $jurnal['tanggal'],
                'akun' => $akun,
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo' => $saldo
            ]);
        }

        return redirect()->to('/admin/buku_besar')->with('success', 'Jurnal berhasil diproses ke Buku Besar');
    }

    public function index()
    {
        $tahun = $this->request->getVar('tahun') ?? date('Y');
        $bulan = $this->request->getVar('bulan');

        $data['bukuBesar'] = $this->bukuBesarModel->getBukuBesar($tahun, $bulan);
        return view('admin/buku_besar/index', $data);
    }
    public function proses()
    {
        return view('buku_besar/proses'); // Pastikan ada view ini!
    }
}

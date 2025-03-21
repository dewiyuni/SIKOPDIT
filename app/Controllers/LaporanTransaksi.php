<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class LaporanTransaksi extends BaseController
{
    protected $laporanModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
    }

    public function index()
    {
        $data = [
            'laporan' => $this->laporanModel->getLaporanTransaksi()
        ];

        return view('karyawan/laporan_transaksi', $data);
    }

    public function cetak()
    {
        $data = [
            'laporan' => $this->laporanModel->getLaporanTransaksi()
        ];

        // Set opsi Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('karyawan/cetak_laporan', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Menyimpan file PDF ke browser tanpa mendownload otomatis
        $dompdf->stream('Laporan_Transaksi.pdf', ['Attachment' => false]);
    }
}

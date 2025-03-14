<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class LaporanTransaksi extends BaseController
{
    public function index()
    {
        $laporanModel = new LaporanModel();
        $data['laporan'] = $laporanModel->getLaporanTransaksi();

        return view('karyawan/laporan_transaksi', $data);
    }

    public function cetak()
    {
        $laporanModel = new LaporanModel();
        $data['laporan'] = $laporanModel->getLaporanTransaksi();

        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf->setOptions($options);

        $html = view('karyawan/cetak_laporan', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Transaksi.pdf', ["Attachment" => false]);
    }
}

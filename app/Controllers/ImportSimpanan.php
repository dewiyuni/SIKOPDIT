<?php
namespace App\Controllers;

use App\Models\AnggotaModel;
use App\Models\TransaksiSimpananModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\TransaksiSimpananDetailModel;

class ImportSimpanan extends BaseController
{
    public function index()
    {
        return view('karyawan/transaksi_simpanan/import_simpanan');
    }

    public function upload()
    {
        $file = $this->request->getFile('file_excel');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->to('/karyawan/transaksi_simpanan/import_simpanan')->with('error', 'Gagal mengupload file.');
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        if (empty($data) || count($data) < 2) {
            return redirect()->to('/karyawan/transaksi_simpanan/import_simpanan')->with('error', 'File Excel kosong atau format tidak sesuai.');
        }

        $anggotaModel = new AnggotaModel();
        $transaksiModel = new TransaksiSimpananModel();
        $transaksiDetailModel = new TransaksiSimpananDetailModel();

        foreach ($data as $index => $row) {
            if ($index == 0)
                continue; // Lewati header

            // **Pastikan semua data ada dan tidak null**
            $nama = isset($row[1]) ? trim($row[1]) : null;
            $no_ba = isset($row[2]) ? trim($row[2]) : null;
            $nik = isset($row[3]) ? trim($row[3]) : null;
            $sw = isset($row[4]) ? (float) $row[4] : 0;
            $swp = isset($row[5]) ? (float) $row[5] : 0;
            $ss = isset($row[6]) ? (float) $row[6] : 0;
            $sp = isset($row[7]) ? (float) $row[7] : 0;
            $tanggal = isset($row[8]) ? trim($row[8]) : null;
            $keterangan = isset($row[9]) ? trim($row[9]) : null;
            $dusun = isset($row[10]) ? trim($row[10]) : null;

            // **Validasi wajib isi**
            if (empty($nama) || empty($no_ba) || empty($nik) || empty($tanggal)) {
                return redirect()->to('/karyawan/transaksi_simpanan/import_simpanan')
                    ->with('error', "Data tidak lengkap di baris " . ($index + 1));
            }

            // **Cek apakah anggota sudah ada**
            $anggota = $anggotaModel->where('no_ba', $no_ba)->orWhere('nik', $nik)->first();

            if (!$anggota) {
                // **Tambah anggota baru jika belum ada**
                $anggotaModel->insert([
                    'nama' => $nama,
                    'no_ba' => $no_ba,
                    'nik' => $nik,
                    'dusun' => $dusun // Simpan informasi dusun
                ]);
                $id_anggota = $anggotaModel->insertID();
            } else {
                $id_anggota = $anggota->id_anggota;
            }

            // **Simpan transaksi simpanan**
            $transaksiModel->insert([
                'id_anggota' => $id_anggota,
                'tanggal' => $tanggal,
                'saldo_sw' => $sw,
                'saldo_swp' => $swp,
                'saldo_ss' => $ss,
                'saldo_sp' => $sp,
                'saldo_total' => $sw + $swp + $ss + $sp,
                'keterangan' => $keterangan
            ]);

            $id_transaksi = $transaksiModel->insertID();

            // **Simpan transaksi simpanan detail**
            $jenis_simpanan = [
                ['id' => 1, 'nama' => 'SW', 'setor' => $sw],
                ['id' => 2, 'nama' => 'SWP', 'setor' => $swp],
                ['id' => 3, 'nama' => 'SS', 'setor' => $ss],
                ['id' => 4, 'nama' => 'SP', 'setor' => $sp],
            ];

            foreach ($jenis_simpanan as $simpanan) {
                if ($simpanan['setor'] > 0) {
                    $transaksiDetailModel->insert([
                        'id_simpanan' => $id_transaksi,
                        'id_jenis_simpanan' => $simpanan['id'],
                        'setor' => $simpanan['setor'],
                        'tarik' => 0,
                        'saldo_akhir' => $simpanan['setor'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        return redirect()->to('/karyawan/transaksi_simpanan/import_simpanan')->with('success', 'Data transaksi berhasil diimpor.');
    }

}

<?php

namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\Controller;
use App\Models\AnggotaModel;
use App\Models\KeuanganModel;
use App\Models\TransaksiSimpananModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AnggotaController extends Controller
{
    protected $anggotaModel;
    protected $authModel;
    protected $transaksiModel;
    protected $keuanganModel;

    // Definisikan konstanta untuk nominal awal agar konsisten
    private const UANG_PANGKAL = 10000;
    private const SIMPANAN_POKOK_AWAL = 50000; // Simpanan Pokok yang masuk ke keuangan koperasi
    private const SETOR_SW_AWAL = 75000;      // Simpanan Wajib awal
    private const SETOR_SWP_AWAL = 0;         // Simpanan Wajib Penyertaan awal
    private const SETOR_SS_AWAL = 5000;       // Simpanan Sukarela awal
    // Simpanan Pokok yang masuk ke saldo transaksi simpanan. Bisa sama dengan SIMPANAN_POKOK_AWAL atau berbeda
    // Sesuai logika Anda di simpanAnggota(), setor_sp adalah 10000
    private const SETOR_SP_TRANSAKSI_AWAL = 10000;


    public function __construct()
    {
        $this->anggotaModel = new AnggotaModel();
        $this->authModel = new AuthModel(); // Meskipun tidak digunakan di fungsi yang Anda berikan, saya biarkan
        $this->transaksiModel = new TransaksiSimpananModel();
        $this->keuanganModel = new KeuanganModel();
        helper(['form', 'url']); // Load helper jika belum di autoload
    }

    public function anggota()
    {
        $data['anggota'] = $this->anggotaModel->getAnggotaWithTransaksi();
        return view('admin/anggota', $data);
    }

    public function tambahAnggota()
    {
        return view('admin/tambah_anggota');
    }

    public function simpanAnggota()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'no_ba' => [
                'rules' => 'required|is_unique[anggota.no_ba]',
                'errors' => [
                    'required' => 'Nomor BA wajib diisi.',
                    'is_unique' => 'Nomor BA sudah terdaftar, gunakan nomor lain.'
                ]
            ],
            'nama' => 'required',
            'nik' => [
                'rules' => 'required|numeric|min_length[16]|max_length[16]|is_unique[anggota.nik]',
                'errors' => [
                    'required' => 'NIK wajib diisi.',
                    'numeric' => 'NIK hanya boleh berisi angka.',
                    'min_length' => 'NIK harus terdiri dari 16 digit.',
                    'max_length' => 'NIK harus terdiri dari 16 digit.',
                    'is_unique' => 'NIK ini sudah terdaftar dalam sistem.'
                ]
            ],
            'dusun' => 'required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'tgl_lahir' => 'required|valid_date[Y-m-d]', // Pastikan format tanggal dari form adalah Y-m-d
            'nama_pasangan' => 'required',
            'status' => 'required|in_list[aktif,nonaktif,keluar]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $dataAnggota = [
            'no_ba' => $this->request->getPost('no_ba'),
            'nama' => $this->request->getPost('nama'),
            'nik' => $this->request->getPost('nik'),
            'dusun' => $this->request->getPost('dusun'),
            'alamat' => $this->request->getPost('alamat'),
            'pekerjaan' => $this->request->getPost('pekerjaan'),
            'tgl_lahir' => $this->request->getPost('tgl_lahir'),
            'nama_pasangan' => $this->request->getPost('nama_pasangan'),
            'status' => $this->request->getPost('status'),
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $this->anggotaModel->insert($dataAnggota);
            $id_anggota = $this->anggotaModel->insertID();

            if (!$id_anggota) {
                throw new \Exception('Gagal menambahkan anggota ke database.');
            }

            // Saldo Awal Simpanan
            $saldo_total_awal = (self::SETOR_SW_AWAL + self::SETOR_SWP_AWAL + self::SETOR_SS_AWAL + self::SETOR_SP_TRANSAKSI_AWAL);

            $this->transaksiModel->insert([
                'id_anggota' => $id_anggota,
                'tanggal' => date('Y-m-d'),
                'setor_sw' => self::SETOR_SW_AWAL,
                'setor_swp' => self::SETOR_SWP_AWAL,
                'setor_ss' => self::SETOR_SS_AWAL,
                'setor_sp' => self::SETOR_SP_TRANSAKSI_AWAL,
                'tarik_sw' => 0,
                'tarik_swp' => 0,
                'tarik_ss' => 0,
                'tarik_sp' => 0,
                'saldo_total' => $saldo_total_awal,
                'keterangan' => 'Pendaftaran Anggota Baru'
            ]);

            $this->keuanganModel->insert([
                'id_anggota' => $id_anggota,
                'keterangan' => 'Pembayaran Uang Pangkal an. ' . $dataAnggota['nama'],
                'jumlah' => self::UANG_PANGKAL,
                'jenis' => 'penerimaan',
                'tanggal' => date('Y-m-d H:i:s')
            ]);

            $this->keuanganModel->insert([
                'id_anggota' => $id_anggota,
                'keterangan' => 'Pembayaran Simpanan Pokok an. ' . $dataAnggota['nama'],
                'jumlah' => self::SIMPANAN_POKOK_AWAL,
                'jenis' => 'penerimaan',
                'tanggal' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data anggota beserta transaksi awal.');
            }

            return redirect()->to(site_url('admin/anggota'))->with('success', 'Anggota berhasil ditambahkan, Simpanan Pokok & Uang Pangkal tercatat.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[simpanAnggota] ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function editAnggota($id_anggota)
    {
        $anggota = $this->anggotaModel->find($id_anggota);
        if (!$anggota) {
            return redirect()->to('/admin/anggota')->with('error', 'Anggota tidak ditemukan.');
        }
        return view('admin/edit_anggota', ['anggota' => $anggota]);
    }

    public function updateAnggota()
    {
        $id_anggota = $this->request->getPost('id_anggota');
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_anggota' => 'required|numeric',
            'nama' => 'required',
            'nik' => "required|numeric|min_length[16]|max_length[16]|is_unique[anggota.nik,id_anggota,{$id_anggota}]",
            'no_ba' => "required|is_unique[anggota.no_ba,id_anggota,{$id_anggota}]",
            'dusun' => 'required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'tgl_lahir' => 'required|valid_date[Y-m-d]',
            'nama_pasangan' => 'required',
            'status' => 'required|in_list[aktif,nonaktif,keluar]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nik' => $this->request->getPost('nik'),
            'no_ba' => $this->request->getPost('no_ba'),
            'dusun' => $this->request->getPost('dusun'),
            'alamat' => $this->request->getPost('alamat'),
            'pekerjaan' => $this->request->getPost('pekerjaan'),
            'tgl_lahir' => $this->request->getPost('tgl_lahir'),
            'nama_pasangan' => $this->request->getPost('nama_pasangan'),
            'status' => $this->request->getPost('status'),
        ];

        if ($this->anggotaModel->update($id_anggota, $data)) {
            return redirect()->to('/admin/anggota')->with('success', 'Anggota berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui anggota.');
        }
    }

    public function hapusAnggota($id)
    {
        $anggota = $this->anggotaModel->find($id);
        if (!$anggota) {
            return redirect()->to('admin/anggota')->with('error', 'Data anggota tidak ditemukan.');
        }

        // Sebaiknya gunakan transaksi jika ada data terkait yang juga perlu dihapus
        // Untuk saat ini, hanya hapus anggota. Pertimbangkan foreign key constraint atau hapus manual data terkait.
        if ($this->anggotaModel->delete($id)) {
            return redirect()->to('admin/anggota')->with('success', 'Anggota berhasil dihapus.');
        } else {
            return redirect()->to('admin/anggota')->with('error', 'Gagal menghapus anggota.');
        }
    }



    public function importExcelAnggota()
    {
        $file = $this->request->getFile('file_excel_anggota');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak terunggah.');
        }
        if (!in_array($file->getExtension(), ['xlsx', 'xls'])) {
            return redirect()->back()->with('error', 'Format file harus .xlsx atau .xls.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumnLetter = $sheet->getHighestColumn();

            $headerRow = $sheet->rangeToArray('A1:' . $highestColumnLetter . '1', null, true, true, true)[1];
            $header = array_map('strtolower', array_map('trim', $headerRow));

            $columnMap = [
                'nama' => ['nama'],
                'nik' => ['nik'],
                'no_ba' => ['no ba', 'no_ba'],
                'dusun' => ['dusun'],
                'alamat' => ['alamat'],
                'pekerjaan' => ['pekerjaan'],
                'tgl_lahir' => ['tanggal lahir', 'tgl lahir'],
                'nama_pasangan' => ['nama pasangan'],
                'status' => ['status'],
                'saldo_sw' => ['saldo sw', 'saldo_sw'],
                'saldo_swp' => ['saldo swp', 'saldo_swp'],
                'saldo_ss' => ['saldo ss', 'saldo_ss'],
                'saldo_sp' => ['saldo sp', 'saldo_sp']
            ];

            $fieldKeys = [];
            foreach ($columnMap as $dbField => $excelHeaders) {
                foreach ($excelHeaders as $excelHeader) {
                    $key = array_search($excelHeader, $header);
                    if ($key !== false) {
                        $fieldKeys[$dbField] = $key;
                        break;
                    }
                }
            }

            $required = ['nama', 'nik', 'no_ba', 'dusun', 'alamat', 'pekerjaan', 'tgl_lahir', 'nama_pasangan', 'status'];
            foreach ($required as $field) {
                if (!isset($fieldKeys[$field])) {
                    return redirect()->back()->with('error', "Kolom $field tidak ditemukan di file Excel.");
                }
            }

            $dataInsert = [];
            for ($r = 2; $r <= $highestRow; $r++) {
                $row = $sheet->rangeToArray('A' . $r . ':' . $highestColumnLetter . $r, null, true, true, true)[$r];
                if (!$row || empty($row[$fieldKeys['nama']]))
                    continue;

                $tglLahir = $this->parseExcelDate($row[$fieldKeys['tgl_lahir']]);

                $dataInsert[] = [
                    'nama' => trim($row[$fieldKeys['nama']]),
                    'nik' => trim($row[$fieldKeys['nik']]),
                    'no_ba' => trim($row[$fieldKeys['no_ba']]),
                    'dusun' => trim($row[$fieldKeys['dusun']]),
                    'alamat' => trim($row[$fieldKeys['alamat']]),
                    'pekerjaan' => trim($row[$fieldKeys['pekerjaan']]),
                    'tgl_lahir' => $tglLahir ?: null,
                    'nama_pasangan' => trim($row[$fieldKeys['nama_pasangan']]),
                    'status' => strtolower(trim($row[$fieldKeys['status']])),
                    'saldo_sw' => isset($fieldKeys['saldo_sw']) ? (float) $row[$fieldKeys['saldo_sw']] : 0,
                    'saldo_swp' => isset($fieldKeys['saldo_swp']) ? (float) $row[$fieldKeys['saldo_swp']] : 0,
                    'saldo_ss' => isset($fieldKeys['saldo_ss']) ? (float) $row[$fieldKeys['saldo_ss']] : 0,
                    'saldo_sp' => isset($fieldKeys['saldo_sp']) ? (float) $row[$fieldKeys['saldo_sp']] : 0,
                ];
            }

            $db = \Config\Database::connect();
            $db->transStart();

            foreach ($dataInsert as $data) {
                $anggotaData = [
                    'nama' => $data['nama'],
                    'nik' => $data['nik'],
                    'no_ba' => $data['no_ba'],
                    'dusun' => $data['dusun'],
                    'alamat' => $data['alamat'],
                    'pekerjaan' => $data['pekerjaan'],
                    'tgl_lahir' => $data['tgl_lahir'],
                    'nama_pasangan' => $data['nama_pasangan'],
                    'status' => $data['status'],
                ];
                $this->anggotaModel->insert($anggotaData);
                $idAnggota = $this->anggotaModel->insertID();

                $saldoTotal = $data['saldo_sw'] + $data['saldo_swp'] + $data['saldo_ss'] + $data['saldo_sp'];
                $this->transaksiModel->insert([
                    'id_anggota' => $idAnggota,
                    'tanggal' => date('Y-m-d'),
                    'setor_sw' => $data['saldo_sw'],
                    'setor_swp' => $data['saldo_swp'],
                    'setor_ss' => $data['saldo_ss'],
                    'setor_sp' => $data['saldo_sp'],
                    'tarik_sw' => 0,
                    'tarik_swp' => 0,
                    'tarik_ss' => 0,
                    'tarik_sp' => 0,
                    'saldo_total' => $saldoTotal,
                    'keterangan' => 'Impor saldo awal dari Excel'
                ]);
            }

            $db->transComplete();

            return redirect()->to('admin/anggota')->with('success', 'Data anggota berhasil diimpor.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        }
    }


    // Helper function untuk parsing tanggal dari Excel (bisa string atau serial number)
    private function parseExcelDate($dateValue)
    {
        if (empty($dateValue))
            return null;

        if (is_numeric($dateValue)) {
            // Cek apakah ini adalah angka float yang sangat kecil atau besar (bukan tanggal Excel)
            // Misalnya NIK bisa jadi angka, tapi bukan serial date.
            // Batas wajar serial date Excel adalah sekitar 1 (untuk 1 Jan 1900) hingga 2958465 (untuk 31 Des 9999)
            // Namun, kita juga perlu hati-hati jika NIK tidak sengaja terdeteksi sebagai numeric.
            // Fungsi ini akan dipanggil untuk kolom tanggal lahir, jadi asumsi numeric di sini adalah serial date.
            if ($dateValue > 0 && $dateValue < 2958466) { // Batas wajar untuk serial date
                try {
                    return ExcelDate::excelToDateTimeObject(floatval($dateValue))->format('Y-m-d');
                } catch (\Exception $e) { /* Abaikan, coba parsing sebagai string */
                }
            }
        }

        $dateString = (string) $dateValue;
        // Format umum dari Indonesia dan internasional, prioritaskan dd/mm/yyyy dan yyyy-mm-dd
        $formats = [
            'd/m/Y',
            'Y-m-d',
            'd-m-Y', // Paling umum
            'm/d/Y',                  // Format US
            'd.m.Y',
            'Y.m.d',         // Dengan titik
            'd M Y',
            'd F Y',         // Dengan nama bulan singkat/panjang
            'j/n/y',
            'j-n-y',         // Format pendek
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $dateString); // '!' untuk parsing ketat
            if ($date && $date->format($format) === $dateString) {
                // Cek apakah tanggal valid (misal 30/02/2023 tidak valid)
                if (checkdate((int) $date->format('n'), (int) $date->format('j'), (int) $date->format('Y'))) {
                    // Pastikan tahunnya wajar (misal tidak 0012 atau 3050)
                    $year = (int) $date->format('Y');
                    if ($year >= 1900 && $year <= (int) date('Y') + 5) { // Batas tahun dari 1900 s/d tahun ini + 5
                        return $date->format('Y-m-d');
                    }
                }
            }
        }

        // Fallback dengan strtotime (kurang reliable untuk format beragam tapi bisa jadi usaha terakhir)
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            $year = (int) date('Y', $timestamp);
            if (checkdate((int) date('n', $timestamp), (int) date('j', $timestamp), $year)) {
                if ($year >= 1900 && $year <= (int) date('Y') + 5) {
                    return date('Y-m-d', $timestamp);
                }
            }
        }
        return null; // Gagal parsing
    }
}
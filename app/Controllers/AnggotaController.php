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

            if ($highestRow <= 1) {
                return redirect()->back()->with('error', 'File Excel kosong atau hanya berisi header.');
            }

            $headerRow = $sheet->rangeToArray('A1:' . $highestColumnLetter . '1', null, true, true, true)[1];
            $header = array_map('strtolower', array_map('trim', $headerRow));

            $columnMap = [
                'nama' => ['nama', 'nama lengkap'],
                'nik' => ['nik', 'nomor nik'],
                'no_ba' => ['no ba', 'no. ba', 'no_ba', 'nomor ba'],
                'dusun' => ['dusun'],
                'alamat' => ['alamat', 'alamat lengkap'],
                'pekerjaan' => ['pekerjaan'],
                'tgl_lahir' => ['tanggal lahir', 'tgl lahir', 'tgl_lahir', 'birth date'],
                'nama_pasangan' => ['nama pasangan', 'pasangan'],
                'status' => ['status', 'status keanggotaan']
            ];

            $fieldKeys = []; // Akan berisi [dbField => ExcelColumnLetter]
            $missingHeaders = [];
            $foundHeaders = [];

            foreach ($columnMap as $dbField => $excelHeaders) {
                $found = false;
                foreach ($excelHeaders as $excelHeader) {
                    $colKey = array_search($excelHeader, $header); // $colKey akan 'A', 'B', dst.
                    if ($colKey !== false) {
                        $fieldKeys[$dbField] = $colKey;
                        $foundHeaders[] = $header[$colKey]; // Simpan header asli yang ditemukan
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $missingHeaders[] = ucfirst(str_replace('_', ' ', $dbField)) . " (alternatif: " . implode("/", $excelHeaders) . ")";
                }
            }

            $requiredDbFields = ['nama', 'nik', 'no_ba', 'dusun', 'alamat', 'pekerjaan', 'tgl_lahir', 'nama_pasangan', 'status'];
            $actualMissing = [];
            foreach ($requiredDbFields as $reqField) {
                if (!isset($fieldKeys[$reqField])) {
                    $actualMissing[] = ucfirst(str_replace('_', ' ', $reqField)) . " (alternatif: " . implode("/", $columnMap[$reqField]) . ")";
                }
            }

            if (!empty($actualMissing)) {
                return redirect()->back()->with('error', 'Header kolom wajib berikut tidak ditemukan: ' . implode(', ', $actualMissing) . '.<br>Header yang terdeteksi di file Anda: ' . implode(', ', $header) . '.');
            }

            $anggotaToInsert = [];
            $rowErrors = []; // Menyimpan error per baris: ['row_num' => x, 'errors' => []]
            $excelDataRows = $sheet->rangeToArray('A2:' . $highestColumnLetter . $highestRow, null, true, true, true);

            $tempNiksInExcel = [];
            $tempNoBasInExcel = [];

            // Iterasi pertama: Kumpulkan data dan lakukan validasi dasar + cek duplikasi internal Excel
            for ($r = 2; $r <= $highestRow; $r++) {
                $rowDataFromSheet = $excelDataRows[$r] ?? null;
                if ($rowDataFromSheet === null)
                    continue; // Baris kosong

                $data = [];
                $currentLineErrors = [];
                $excelRowNum = $r; // Baris aktual di Excel

                // Ambil data berdasarkan fieldKeys
                foreach ($fieldKeys as $dbField => $excelColKey) {
                    $data[$dbField] = isset($rowDataFromSheet[$excelColKey]) ? trim((string) $rowDataFromSheet[$excelColKey]) : null;
                }

                // Cek apakah baris ini kosong (semua kolom penting null/kosong)
                $isEmptyRow = true;
                foreach ($requiredDbFields as $reqField) {
                    if (!empty($data[$reqField])) {
                        $isEmptyRow = false;
                        break;
                    }
                }
                if ($isEmptyRow) {
                    // Bisa jadi baris kosong di akhir file, abaikan atau catat jika perlu
                    continue;
                }


                // --- Validasi per baris ---
                if (empty($data['nama']))
                    $currentLineErrors['nama'] = "Nama wajib diisi.";

                if (empty($data['nik'])) {
                    $currentLineErrors['nik'] = "NIK wajib diisi.";
                } else if (!preg_match('/^\d{16}$/', $data['nik'])) {
                    $currentLineErrors['nik'] = "NIK '{$data['nik']}' tidak valid (harus 16 digit angka).";
                } else {
                    if (isset($tempNiksInExcel[$data['nik']])) {
                        $currentLineErrors['nik'] = "NIK '{$data['nik']}' duplikat dengan baris ke-{$tempNiksInExcel[$data['nik']]} dalam file Excel ini.";
                    } else {
                        $tempNiksInExcel[$data['nik']] = $excelRowNum;
                    }
                }

                if (empty($data['no_ba'])) {
                    $currentLineErrors['no_ba'] = "No BA wajib diisi.";
                } else {
                    if (isset($tempNoBasInExcel[$data['no_ba']])) {
                        $currentLineErrors['no_ba'] = "No BA '{$data['no_ba']}' duplikat dengan baris ke-{$tempNoBasInExcel[$data['no_ba']]} dalam file Excel ini.";
                    } else {
                        $tempNoBasInExcel[$data['no_ba']] = $excelRowNum;
                    }
                }

                if (empty($data['dusun']))
                    $currentLineErrors['dusun'] = "Dusun wajib diisi.";
                if (empty($data['alamat']))
                    $currentLineErrors['alamat'] = "Alamat wajib diisi.";
                if (empty($data['pekerjaan']))
                    $currentLineErrors['pekerjaan'] = "Pekerjaan wajib diisi.";
                if (empty($data['nama_pasangan']))
                    $currentLineErrors['nama_pasangan'] = "Nama Pasangan wajib diisi.";

                $parsedTglLahir = $this->parseExcelDate($data['tgl_lahir']);
                if (!$parsedTglLahir && !empty($data['tgl_lahir'])) { // Hanya error jika tgl_lahir diisi tapi formatnya salah
                    $currentLineErrors['tgl_lahir'] = "Format Tanggal Lahir '{$data['tgl_lahir']}' tidak valid. Gunakan format dd/mm/yyyy atau yyyy-mm-dd.";
                } else if (empty($data['tgl_lahir'])) {
                    $currentLineErrors['tgl_lahir'] = "Tanggal Lahir wajib diisi.";
                } else {
                    $data['tgl_lahir'] = $parsedTglLahir;
                }

                $data['status'] = strtolower($data['status'] ?? '');
                if (!in_array($data['status'], ['aktif', 'nonaktif', 'keluar'])) {
                    $currentLineErrors['status'] = "Status '{$data['status']}' tidak valid (harus aktif, nonaktif, atau keluar).";
                }
                if (empty($data['status']))
                    $currentLineErrors['status'] = "Status wajib diisi.";


                // Cek keunikan NIK dan No BA di database (jika tidak ada error sebelumnya di NIK/NoBA)
                if (empty($currentLineErrors['nik'])) {
                    if ($this->anggotaModel->where('nik', $data['nik'])->first()) {
                        $currentLineErrors['nik_db'] = "NIK '{$data['nik']}' sudah terdaftar di sistem.";
                    }
                }
                if (empty($currentLineErrors['no_ba'])) {
                    if ($this->anggotaModel->where('no_ba', $data['no_ba'])->first()) {
                        $currentLineErrors['no_ba_db'] = "No BA '{$data['no_ba']}' sudah terdaftar di sistem.";
                    }
                }

                if (!empty($currentLineErrors)) {
                    $rowErrors[] = ['row_num' => $excelRowNum, 'nama' => $data['nama'] ?? 'N/A', 'errors' => $currentLineErrors];
                } else {
                    $anggotaToInsert[] = $data; // Kumpulkan data yang valid
                }
            } // End for loop row

            if (!empty($rowErrors)) {
                $errorMessagesHtml = "Ditemukan error pada data Excel:<br><ul style='text-align:left;'>";
                foreach ($rowErrors as $err) {
                    $errorMessagesHtml .= "<li><b>Baris {$err['row_num']} (Nama: {$err['nama']}):</b><ul>";
                    foreach ($err['errors'] as $field => $msg) {
                        $errorMessagesHtml .= "<li>" . ucfirst(str_replace('_', ' ', $field)) . ": {$msg}</li>";
                    }
                    $errorMessagesHtml .= "</ul></li>";
                }
                $errorMessagesHtml .= "</ul>";
                if (count($rowErrors) > 20) {
                    $errorMessagesHtml = "Ditemukan lebih dari 20 baris error. Berikut adalah 20 error pertama:<br><ul style='text-align:left;'>";
                    $limitedErrors = array_slice($rowErrors, 0, 20);
                    foreach ($limitedErrors as $err) {
                        $errorMessagesHtml .= "<li><b>Baris {$err['row_num']} (Nama: {$err['nama']}):</b><ul>";
                        foreach ($err['errors'] as $field => $msg) {
                            $errorMessagesHtml .= "<li>" . ucfirst(str_replace('_', ' ', $field)) . ": {$msg}</li>";
                        }
                        $errorMessagesHtml .= "</ul></li>";
                    }
                    $errorMessagesHtml .= "</ul>... dan lainnya.";
                }
                return redirect()->back()->with('error_html', $errorMessagesHtml); // Gunakan 'error_html' jika view Anda bisa render HTML
            }

            if (empty($anggotaToInsert)) {
                return redirect()->back()->with('error', 'Tidak ada data anggota yang valid untuk diimpor setelah validasi.');
            }

            // --- Proses Batch Insert dan Transaksi Awal ---
            $db = \Config\Database::connect();
            $db->transStart();
            $successCount = 0;
            $failedInserts = [];

            try {
                foreach ($anggotaToInsert as $dataAnggota) {
                    // Pastikan semua field yang dibutuhkan model ada dan tidak null jika required di DB
                    $insertData = [
                        'nama' => $dataAnggota['nama'],
                        'nik' => $dataAnggota['nik'],
                        'no_ba' => $dataAnggota['no_ba'],
                        'dusun' => $dataAnggota['dusun'],
                        'alamat' => $dataAnggota['alamat'],
                        'pekerjaan' => $dataAnggota['pekerjaan'],
                        'tgl_lahir' => $dataAnggota['tgl_lahir'],
                        'nama_pasangan' => $dataAnggota['nama_pasangan'],
                        'status' => $dataAnggota['status'],
                    ];

                    if ($this->anggotaModel->insert($insertData)) {
                        $id_anggota = $this->anggotaModel->insertID();
                        $successCount++;

                        // Otomatis buat transaksi simpanan awal
                        $saldo_total_awal_import = (self::SETOR_SW_AWAL + self::SETOR_SWP_AWAL + self::SETOR_SS_AWAL + self::SETOR_SP_TRANSAKSI_AWAL);

                        $this->transaksiModel->insert([
                            'id_anggota' => $id_anggota,
                            'tanggal' => date('Y-m-d'),
                            'setor_sw' => self::SETOR_SW_AWAL,
                            'setor_swp' => self::SETOR_SWP_AWAL,
                            'setor_ss' => self::SETOR_SS_AWAL,
                            'setor_sp' => self::SETOR_SP_TRANSAKSI_AWAL, // Menggunakan konstanta yang konsisten
                            'tarik_sw' => 0,
                            'tarik_swp' => 0,
                            'tarik_ss' => 0,
                            'tarik_sp' => 0,
                            'saldo_total' => $saldo_total_awal_import,
                            'keterangan' => 'Pendaftaran Anggota via Impor Excel'
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
                            'keterangan' => 'Setoran Simpanan Pokok Awal an. ' . $dataAnggota['nama'],
                            'jumlah' => self::SIMPANAN_POKOK_AWAL,
                            'jenis' => 'penerimaan',
                            'tanggal' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        // Seharusnya tidak terjadi jika validasi di atas sudah benar, tapi sebagai fallback
                        $failedInserts[] = "Gagal menyimpan data anggota: " . ($dataAnggota['nama'] ?? 'N/A') . ". Error DB: " . json_encode($this->anggotaModel->errors());
                    }
                }

                if (!empty($failedInserts)) {
                    // Jika ada kegagalan insert, seluruh transaksi dibatalkan
                    throw new \Exception("Beberapa data gagal diinsert ke database: " . implode(", ", $failedInserts));
                }

                $db->transComplete();

                if ($db->transStatus() === FALSE) {
                    $db->transRollback(); // CI4 otomatis rollback jika transComplete mendeteksi kegagalan
                    return redirect()->back()->with('error', 'Gagal melakukan transaksi impor anggota. Tidak ada data yang disimpan. Error: ' . ($db->error()['message'] ?? 'Unknown DB error'));
                }

                $finalMessage = "Berhasil mengimpor {$successCount} data anggota.";
                return redirect()->to('admin/anggota')->with('success', $finalMessage);

            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', '[importExcelAnggota - Transaksi] ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat impor dalam transaksi: ' . $e->getMessage());
            }

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            log_message('error', '[importExcelAnggota - PhpSpreadsheet] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses file Excel (Library Error): ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', '[importExcelAnggota - General] ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
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
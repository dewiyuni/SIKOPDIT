<?php

namespace App\Controllers;

use App\Models\JurnalKasModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\RESTful\ResourceController;

class JurnalKasController extends ResourceController
{
    protected $jurnalkasModel;
    protected $format = 'json';
    protected $db;

    public function __construct()
    {
        $this->jurnalkasModel = new JurnalKasModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['jurnal_kas_harian'] = $this->jurnalkasModel->orderBy('tanggal', 'ASC')->findAll(); // Tambahkan orderBy

        log_message('debug', json_encode($data['jurnal_kas_harian'])); // Debugging

        return view('admin/jurnal_neraca/jurnal_kas_harian', $data);
    }

    public function getData()
    {
        return $this->respond($this->jurnalkasModel->findAll());
    }

    public function createKas()
    {
        $this->db->transStart(); // Mulai transaksi
        try {
            $data = $this->request->getJSON();

            // Validasi apakah format data sesuai
            if (!isset($data->data) || !is_array($data->data) || empty($data->data)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Format data tidak valid atau kosong'
                ], 400);
            }

            log_message('debug', 'Data diterima: ' . json_encode($data));

            $insertData = [];

            foreach ($data->data as $row) {
                $tanggal = isset($row->tanggal) ? trim($row->tanggal) : null;
                $uraian = isset($row->uraian) ? trim($row->uraian) : null;
                $kategori = isset($row->kategori) ? trim($row->kategori) : null;
                $jumlah = isset($row->jumlah) ? floatval($row->jumlah) : null;

                // Validasi data
                if (empty($tanggal) || empty($uraian) || empty($kategori) || $jumlah === null) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'Semua field harus diisi dan tidak boleh kosong'
                    ], 400);
                }

                // Format tanggal
                $tanggal = date('Y-m-d', strtotime($tanggal));

                log_message('debug', "Menyimpan data dengan tanggal: $tanggal");

                $insertData[] = [
                    'tanggal' => $tanggal,
                    'uraian' => $uraian,
                    'kategori' => $kategori,
                    'jumlah' => $jumlah
                ];
            }

            // Insert batch jika ada data
            if (!empty($insertData)) {
                $this->jurnalkasModel->insertBatch($insertData);
            }

            $this->db->transComplete(); // Selesaikan transaksi

            return $this->respond([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'inserted' => count($insertData)
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback(); // Rollback transaksi jika error
            return $this->respond([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }


    public function simpan()
    {
        $data = $this->request->getJSON();

        log_message('debug', print_r($data, true));

        if (!is_array($data) || empty($data)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Format data tidak valid atau kosong'
            ], 400);
        }

        log_message('debug', 'Data diterima: ' . json_encode($data));

        foreach ($data as $row) {
            $tanggal = isset($row->tanggal) ? trim($row->tanggal) : null;
            $uraian = isset($row->uraian) ? trim($row->uraian) : null;
            $jumlah = isset($row->jumlah) ? floatval($row->jumlah) : null;
            $kategori = isset($row->kategori) ? trim($row->kategori) : null;

            // Validasi input
            if (empty($tanggal) || empty($uraian) || empty($kategori) || !isset($jumlah)) {
                continue; // Lewati jika ada data kosong
            }

            // Format tanggal ke `Y-m-d`
            $tanggal = date('Y-m-d', strtotime($tanggal));

            log_message('debug', "Menyimpan: $tanggal - $uraian - $jumlah - $kategori");

            // Cek apakah data sudah ada
            $existingData = $this->jurnalkasModel->where([
                'tanggal' => $tanggal,
                'uraian' => $uraian,
                'kategori' => $kategori
            ])->first();

            if ($existingData) {
                log_message('debug', 'Update Data: ' . json_encode($row));
                $this->jurnalkasModel->update($existingData['id'], ['jumlah' => $jumlah]);
            } else {
                log_message('debug', 'Insert Data: ' . json_encode($row));
                $this->jurnalkasModel->insert([
                    'tanggal' => $tanggal,
                    'uraian' => $uraian,
                    'kategori' => $kategori,
                    'jumlah' => $jumlah
                ]);
            }
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Data berhasil disimpan'
        ]);
    }


    private function saveOrUpdateKas($row, $kategori, $jumlah)
    {
        $existing = $this->jurnalkasModel->where(['tanggal' => $row->tanggal, 'kategori' => $kategori])->first();

        if ($existing) {
            $this->jurnalkasModel->update($existing['id'], ['uraian' => $row->uraian, 'jumlah' => $jumlah]);
        } else {
            $this->jurnalkasModel->insert(['tanggal' => $row->tanggal, 'uraian' => $row->uraian, 'kategori' => $kategori, 'jumlah' => $jumlah]);
        }
    }

    public function update($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('ID tidak boleh kosong');
        }

        $dataLama = $this->jurnalkasModel->find($id);
        if (!$dataLama) {
            return $this->failNotFound('Data tidak ditemukan');
        }

        $dataBaru = $this->request->getJSON(true);

        $tanggal = isset($dataBaru->tanggal) ? trim($dataBaru->tanggal) : null;
        $uraian = isset($dataBaru->uraian) ? trim($dataBaru->uraian) : null;
        $kategori = isset($dataBaru->kategori) ? trim($dataBaru->kategori) : null;
        $jumlah = isset($dataBaru->jumlah) ? (float) $dataBaru->jumlah : null;

        if (empty($tanggal) || empty($uraian) || empty($kategori) || $jumlah === null) {
            return $this->failValidationErrors('Semua field harus diisi');
        }

        // Pastikan format tanggal benar
        $tanggal = date('Y-m-d', strtotime($tanggal));

        try {
            $this->jurnalkasModel->update($id, [
                'tanggal' => $tanggal,
                'uraian' => $uraian,
                'kategori' => $kategori,
                'jumlah' => $jumlah
            ]);
            return $this->respondUpdated([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('ID tidak boleh kosong');
        }

        $dataLama = $this->jurnalkasModel->find($id);
        if (!$dataLama) {
            return $this->failNotFound('Data tidak ditemukan');
        }

        $this->jurnalkasModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function exportExcel()
    {
        $model = new JurnalKasModel();
        $data = $model->getRekapBulanan();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['No', 'Kategori', 'Uraian', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Total'];
        $columnLetters = range('A', 'P');

        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
        }

        $rowNum = 2;
        $no = 1;
        $lastCategory = null;

        foreach ($data as $row) {
            if ($lastCategory !== $row['kategori']) {
                $sheet->setCellValue('A' . $rowNum, '');
                $sheet->setCellValue('B' . $rowNum, strtoupper($row['kategori']));
                $sheet->mergeCells("B$rowNum:P$rowNum");
                $sheet->getStyle("B$rowNum")->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $rowNum++;
            }

            // Isi Data
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $row['kategori']);
            $sheet->setCellValue('C' . $rowNum, $row['uraian']);

            for ($i = 0; $i < 12; $i++) {
                $bulan = strtolower($headers[$i + 3]);
                $sheet->setCellValue($columnLetters[$i + 3] . $rowNum, $row[$bulan] ?? 0);
            }

            $sheet->setCellValue('P' . $rowNum, $row['total']);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'jurnal_kas_rekap.xlsx';
        $filePath = WRITEPATH . 'uploads/' . $filename;
        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function importExcel()
    {
        $file = $this->request->getFile('file_excel');

        // Validasi file
        if (!$file->isValid() || !in_array($file->getExtension(), ['xlsx', 'xls'])) {
            session()->setFlashdata('error', 'File tidak valid atau format tidak didukung.');
            return redirect()->back();
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dataToInsert = [];
            $errorRows = []; // Untuk mencatat baris dengan format tanggal yang bermasalah

            foreach ($rows as $key => $row) {
                if ($key == 0)
                    continue; // Lewati header

                // ========================== [ PENGOLAHAN TANGGAL ] ==========================
                $tanggal = null;
                $originalDate = '';

                if (!empty($row[0])) {
                    $originalDate = trim($row[0]);
                    $excelDate = $originalDate;

                    // Cek apakah formatnya serial date (angka dari Excel)
                    if (is_numeric($excelDate)) {
                        $tanggal = date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($excelDate));
                        log_message('info', "Tanggal Excel (numeric): $excelDate -> $tanggal");
                    }
                    // Prioritaskan format MM/DD/YYYY yang ada di data Anda
                    else if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $excelDate, $matches)) {
                        $month = $matches[1];
                        $day = $matches[2];
                        $year = $matches[3];

                        // Validasi tanggal (cek apakah bulan dan hari valid)
                        if (checkdate(intval($month), intval($day), intval($year))) {
                            $tanggal = "$year-$month-$day";
                            log_message('info', "Tanggal MM/DD/YYYY: $excelDate -> $tanggal");
                        } else {
                            // Coba dengan asumsi format DD/MM/YYYY
                            if (checkdate(intval($day), intval($month), intval($year))) {
                                $tanggal = "$year-$day-$month";
                                log_message('info', "Tanggal DD/MM/YYYY: $excelDate -> $tanggal");
                            }
                        }
                    }
                    // Coba format lainnya jika format utama tidak cocok
                    else {
                        // Coba dengan format yang umum
                        $formats = ['m/d/Y', 'd/m/Y', 'Y-m-d'];

                        foreach ($formats as $format) {
                            $tanggalObj = \DateTime::createFromFormat($format, $excelDate);
                            if ($tanggalObj && $tanggalObj->format($format) === $excelDate) {
                                $tanggal = $tanggalObj->format('Y-m-d');
                                log_message('info', "Tanggal format $format: $excelDate -> $tanggal");
                                break;
                            }
                        }
                    }
                }

                // Jika tanggal tetap null, catat sebagai error dan lanjutkan ke baris berikutnya
                if (!$tanggal) {
                    $errorRows[] = $key + 1; // +1 karena index array dimulai dari 0
                    log_message('warning', "Baris " . ($key + 1) . ": Format tanggal tidak valid - '$originalDate'");
                    continue;
                }
                // ==============================================================================

                $uraian = isset($row[1]) ? trim($row[1]) : '';

                if (empty($uraian)) {
                    continue;
                }

                // Pastikan nilai numerik diproses dengan benar
                $dum = isset($row[2]) ? $this->parseNumericValue($row[2]) : 0;
                $duk = isset($row[3]) ? $this->parseNumericValue($row[3]) : 0;

                if ($dum > 0) {
                    $dataToInsert[] = [
                        'tanggal' => $tanggal,
                        'uraian' => $uraian,
                        'kategori' => 'DUM',
                        'jumlah' => $dum,
                    ];
                }

                if ($duk > 0) {
                    $dataToInsert[] = [
                        'tanggal' => $tanggal,
                        'uraian' => $uraian,
                        'kategori' => 'DUK',
                        'jumlah' => $duk,
                    ];
                }
            }

            if (!empty($dataToInsert)) {
                $insertCount = 0;
                $updateCount = 0;

                foreach ($dataToInsert as $data) {
                    $existing = $this->jurnalkasModel->where([
                        'tanggal' => $data['tanggal'],
                        'uraian' => $data['uraian'],
                        'kategori' => $data['kategori']
                    ])->first();

                    if ($existing) {
                        $this->jurnalkasModel->update($existing['id'], ['jumlah' => $existing['jumlah'] + $data['jumlah']]);
                        $updateCount++;
                    } else {
                        $this->jurnalkasModel->insert($data);
                        $insertCount++;
                    }
                }

                // ✅ Panggil fungsi update total harian setelah import sukses
                $this->jurnalkasModel->updateTotalHarian();

                $message = "Import berhasil: $insertCount data baru, $updateCount data diperbarui.";
                if (!empty($errorRows)) {
                    $message .= " Baris dengan format tanggal tidak valid: " . implode(', ', $errorRows);
                }

                session()->setFlashdata('success', $message);
            } else {
                session()->setFlashdata('error', 'Tidak ada data yang valid untuk diimport.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Kesalahan saat mengimport Excel: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat mengimport data: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/jurnal_neraca')); // Sesuaikan dengan route tujuan
    }

    /**
     * Helper function untuk memproses nilai numerik dari Excel
     * Menangani nilai yang mungkin berformat string dengan pemisah ribuan
     */
    private function parseNumericValue($value)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }

        // Jika string, bersihkan pemisah ribuan dan ganti koma desimal dengan titik
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
            if (is_numeric($value)) {
                return floatval($value);
            }
        }

        return 0;
    }

}

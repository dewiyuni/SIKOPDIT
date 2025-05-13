<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\JurnalKasModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\RESTful\ResourceController;

class JurnalKasController extends ResourceController
{
    protected $jurnalkasModel;
    protected $akunModel;
    protected $format = 'json';
    protected $db;

    public function __construct()
    {
        $this->jurnalkasModel = new JurnalKasModel();
        $this->akunModel = new AkunModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['jurnal_kas'] = $this->jurnalkasModel->orderBy('tanggal', 'ASC')->findAll(); // Tambahkan orderBy
        $data['akun'] = $this->akunModel->findAll();

        log_message('debug', json_encode($data['jurnal_kas'])); // Debugging

        return view('admin/jurnal/jurnal_kas', $data);
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
        $json = $this->request->getJSON(true);
        $model = new JurnalKasModel();

        $success = 0;
        $failed = 0;

        foreach ($json as $item) {
            // If ID exists, update existing record
            if (!empty($item['id'])) {
                $result = $model->update($item['id'], [
                    'tanggal' => $item['tanggal'],
                    'uraian' => $item['uraian'],
                    'jumlah' => $item['jumlah'],
                    'kategori' => $item['kategori']
                ]);
            } else {
                // Otherwise insert new record
                $result = $model->insert([
                    'tanggal' => $item['tanggal'],
                    'uraian' => $item['uraian'],
                    'jumlah' => $item['jumlah'],
                    'kategori' => $item['kategori']
                ]);
            }

            if ($result) {
                $success++;
            } else {
                $failed++;
            }
        }

        return $this->response->setJSON([
            'status' => ($failed == 0) ? 'success' : 'partial',
            'message' => "Berhasil menyimpan $success data" . ($failed > 0 ? ", gagal menyimpan $failed data" : "")
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
        // Check if the ID is provided
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak boleh kosong.']);
        }

        $json = $this->request->getJSON(true); // Get JSON data as an array

        // Validate input data
        if (empty($json['tanggal']) || empty($json['uraian']) || empty($json['jumlah']) || empty($json['kategori'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        }

        // Prepare data for update
        $data = [
            'tanggal' => $json['tanggal'],
            'uraian' => $json['uraian'],
            'jumlah' => $json['jumlah'],
            'kategori' => $json['kategori']
        ];

        // Update the record in the database
        $updated = $this->jurnalkasModel->update($id, $data);

        // Check if the update was successful
        if ($updated) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
        } else {
            // Log the error
            log_message('error', 'Failed to update record with ID: ' . $id);
            log_message('error', 'Data: ' . json_encode($data));
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
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

        // Debug: Check if there's any data
        log_message('debug', "Data count for export: " . count($data));

        if (empty($data)) {
            // If no data, create a simple Excel with a message
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Tidak ada data untuk ditampilkan');
            $sheet->setCellValue('A2', 'Silakan tambahkan data jurnal kas terlebih dahulu');

            $writer = new Xlsx($spreadsheet);
            $filename = 'jurnal_kas_kosong.xlsx';
            $filePath = WRITEPATH . 'uploads/' . $filename;

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            $writer->save($filePath);

            return $this->response->download($filePath, null)->setFileName($filename);
        }

        // If we have data, proceed with creating the detailed Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Jurnal Kas');

        // Header
        $headers = ['No', 'Kategori', 'Uraian', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Total'];
        $columnLetters = range('A', 'P');

        // Apply header styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
        }
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

        // Freeze the header row
        $sheet->freezePane('A2');

        $rowNum = 2;
        $no = 1;
        $lastCategory = null;
        $categoryStartRow = 2;
        $grandTotalDum = array_fill(0, 12, 0);
        $grandTotalDuk = array_fill(0, 12, 0);

        foreach ($data as $row) {
            // Debug: Check each row's data
            log_message('debug', "Processing row: " . json_encode($row));

            // If category changes, add category header and calculate subtotals
            if ($lastCategory !== null && $lastCategory !== $row['kategori']) {
                // Add category subtotal row
                $sheet->setCellValue('A' . $rowNum, '');
                $sheet->setCellValue('B' . $rowNum, 'Subtotal ' . strtoupper($lastCategory));

                // Calculate subtotals for each month
                for ($i = 0; $i < 12; $i++) {
                    $colLetter = $columnLetters[$i + 3];
                    $subtotalFormula = "=SUM({$colLetter}{$categoryStartRow}:{$colLetter}" . ($rowNum - 1) . ")";
                    $sheet->setCellValue($colLetter . $rowNum, $subtotalFormula);
                }

                // Calculate total for the category
                $sheet->setCellValue('P' . $rowNum, "=SUM(D{$rowNum}:O{$rowNum})");

                // Style the subtotal row
                $subtotalStyle = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2EFDA'],
                    ],
                ];
                $sheet->getStyle("A{$rowNum}:P{$rowNum}")->applyFromArray($subtotalStyle);

                $rowNum++;
                $categoryStartRow = $rowNum;
            }

            // If category changes, add category header
            if ($lastCategory !== $row['kategori']) {
                $sheet->setCellValue('A' . $rowNum, '');
                $sheet->setCellValue('B' . $rowNum, strtoupper($row['kategori']));
                $sheet->mergeCells("B{$rowNum}:P{$rowNum}");

                $categoryStyle = [
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'BDD7EE'],
                    ],
                ];
                $sheet->getStyle("B{$rowNum}")->applyFromArray($categoryStyle);

                $rowNum++;
                $categoryStartRow = $rowNum;
                $lastCategory = $row['kategori'];
            }

            // Isi Data
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $row['kategori']);
            $sheet->setCellValue('C' . $rowNum, $row['uraian']);

            // Calculate row total
            $rowTotal = 0;
            $monthNames = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];

            for ($i = 0; $i < 12; $i++) {
                $bulan = $monthNames[$i];
                $value = isset($row[$bulan]) ? floatval($row[$bulan]) : 0;

                // Debug: Check each month's value
                log_message('debug', "Month: $bulan, Value: $value");

                $sheet->setCellValue($columnLetters[$i + 3] . $rowNum, $value);
                $rowTotal += $value;

                // Add to grand total based on category
                if (strtoupper($row['kategori']) == 'DUM') {
                    $grandTotalDum[$i] += $value;
                } else if (strtoupper($row['kategori']) == 'DUK') {
                    $grandTotalDuk[$i] += $value;
                }
            }

            // Set the total for this row
            $total = isset($row['total']) ? floatval($row['total']) : $rowTotal;
            $sheet->setCellValue('P' . $rowNum, $total);

            // Apply number format
            $sheet->getStyle("D{$rowNum}:P{$rowNum}")->getNumberFormat()
                ->setFormatCode('#,##0');

            $rowNum++;
        }

        // Add the last category subtotal
        if ($lastCategory !== null) {
            $sheet->setCellValue('A' . $rowNum, '');
            $sheet->setCellValue('B' . $rowNum, 'Subtotal ' . strtoupper($lastCategory));

            for ($i = 0; $i < 12; $i++) {
                $colLetter = $columnLetters[$i + 3];
                $subtotalFormula = "=SUM({$colLetter}{$categoryStartRow}:{$colLetter}" . ($rowNum - 1) . ")";
                $sheet->setCellValue($colLetter . $rowNum, $subtotalFormula);
            }

            $sheet->setCellValue('P' . $rowNum, "=SUM(D{$rowNum}:O{$rowNum})");

            $subtotalStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'],
                ],
            ];
            $sheet->getStyle("A{$rowNum}:P{$rowNum}")->applyFromArray($subtotalStyle);

            $rowNum++;
        }

        // Add Grand Total row
        $rowNum += 1; // Add some space
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, 'GRAND TOTAL DUM');

        $totalDum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sheet->setCellValue($columnLetters[$i + 3] . $rowNum, $grandTotalDum[$i]);
            $totalDum += $grandTotalDum[$i];
        }
        $sheet->setCellValue('P' . $rowNum, $totalDum);

        $grandTotalStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C6E0B4'],
            ],
        ];
        $sheet->getStyle("A{$rowNum}:P{$rowNum}")->applyFromArray($grandTotalStyle);

        $rowNum++;

        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, 'GRAND TOTAL DUK');

        $totalDuk = 0;
        for ($i = 0; $i < 12; $i++) {
            $sheet->setCellValue($columnLetters[$i + 3] . $rowNum, $grandTotalDuk[$i]);
            $totalDuk += $grandTotalDuk[$i];
        }
        $sheet->setCellValue('P' . $rowNum, $totalDuk);
        $sheet->getStyle("A{$rowNum}:P{$rowNum}")->applyFromArray($grandTotalStyle);

        $rowNum++;

        // Add Saldo row (DUM - DUK)
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, 'SALDO (DUM - DUK)');

        for ($i = 0; $i < 12; $i++) {
            $saldo = $grandTotalDum[$i] - $grandTotalDuk[$i];
            $sheet->setCellValue($columnLetters[$i + 3] . $rowNum, $saldo);
        }

        $totalSaldo = $totalDum - $totalDuk;
        $sheet->setCellValue('P' . $rowNum, $totalSaldo);

        $saldoStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFEB9C'],
            ],
        ];
        $sheet->getStyle("A{$rowNum}:P{$rowNum}")->applyFromArray($saldoStyle);

        // Apply number format to all numeric cells
        $sheet->getStyle("D2:P{$rowNum}")->getNumberFormat()
            ->setFormatCode('#,##0');

        // Auto-size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply borders to all cells
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle("A1:P{$rowNum}")->applyFromArray($borderStyle);

        // Add a timestamp and some metadata
        $rowNum += 2;
        $sheet->setCellValue('A' . $rowNum, 'Dicetak pada: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A' . $rowNum . ':P' . $rowNum);

        $writer = new Xlsx($spreadsheet);
        $filename = 'jurnal_kas_rekap_' . date('Y-m-d') . '.xlsx';
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportMonthlyExcel($year, $month)
    {
        // Validate inputs
        $year = (int) $year;
        $month = (int) $month;

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            return redirect()->to(base_url('admin/jurnal/monthly'))->with('error', 'Periode tidak valid');
        }

        // Get month name for display
        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $namaBulan = $bulanNames[$month];

        // Format month with leading zero for query
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Get data for the selected month and year
        $jurnalKas = $this->jurnalkasModel
            ->where("DATE_FORMAT(tanggal, '%Y-%m') = '$year-$monthFormatted'")
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        // Separate DUM and DUK data
        $dumData = array_filter($jurnalKas, function ($item) {
            return $item['kategori'] == 'DUM';
        });

        $dukData = array_filter($jurnalKas, function ($item) {
            return $item['kategori'] == 'DUK';
        });

        // Calculate totals
        $totalDum = 0;
        $totalDuk = 0;

        foreach ($jurnalKas as $item) {
            if ($item['kategori'] == 'DUM') {
                $totalDum += $item['jumlah'];
            } else if ($item['kategori'] == 'DUK') {
                $totalDuk += $item['jumlah'];
            }
        }

        $saldo = $totalDum - $totalDuk;

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Jurnal Kas')
            ->setLastModifiedBy('Sistem Jurnal Kas')
            ->setTitle("Jurnal Kas $namaBulan $year")
            ->setSubject("Laporan Jurnal Kas $namaBulan $year")
            ->setDescription("Laporan Jurnal Kas untuk periode $namaBulan $year");

        // Get the active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Jurnal Kas $namaBulan $year");

        // Add title
        $sheet->setCellValue('A1', "LAPORAN JURNAL KAS");
        $sheet->setCellValue('A2', "Periode: $namaBulan $year");

        // Merge cells for title
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');

        // Style the title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($titleStyle);

        $subtitleStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:D2')->applyFromArray($subtitleStyle);

        // Add summary section
        $sheet->setCellValue('A4', 'RINGKASAN:');
        $sheet->setCellValue('A5', 'Total DUM:');
        $sheet->setCellValue('B5', $totalDum);
        $sheet->setCellValue('A6', 'Total DUK:');
        $sheet->setCellValue('B6', $totalDuk);
        $sheet->setCellValue('A7', 'Saldo:');
        $sheet->setCellValue('B7', $saldo);

        // Style summary
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A5:A7')->getFont()->setBold(true);
        $sheet->getStyle('B5:B7')->getNumberFormat()->setFormatCode('#,##0');

        // Add DUM section
        $sheet->setCellValue('A9', 'DATA DUM');
        $sheet->getStyle('A9')->getFont()->setBold(true);

        // DUM Headers
        $sheet->setCellValue('A10', 'No');
        $sheet->setCellValue('B10', 'Tanggal');
        $sheet->setCellValue('C10', 'Uraian');
        $sheet->setCellValue('D10', 'Jumlah');

        // Style DUM Headers
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D9E1F2',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A10:D10')->applyFromArray($headerStyle);

        // Add DUM data
        $row = 11;
        $no = 1;

        if (empty($dumData)) {
            $sheet->setCellValue('A' . $row, 'Tidak ada data DUM');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row++;
        } else {
            foreach ($dumData as $item) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($item['tanggal'])));
                $sheet->setCellValue('C' . $row, $item['uraian']);
                $sheet->setCellValue('D' . $row, $item['jumlah']);

                // Apply number format to amount column
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');

                $row++;
            }
        }

        // Add DUM total row
        $sheet->setCellValue('A' . $row, 'Total DUM');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $totalDum);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);

        // Add borders to DUM data
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A10:D' . $row)->applyFromArray($borderStyle);

        // Add DUK section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'DATA DUK');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        // DUK Headers
        $row++;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Tanggal');
        $sheet->setCellValue('C' . $row, 'Uraian');
        $sheet->setCellValue('D' . $row, 'Jumlah');

        // Style DUK Headers
        $dukHeaderStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'F8CBAD',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($dukHeaderStyle);

        // Add DUK data
        $startDukRow = $row + 1;
        $row++;
        $no = 1;

        if (empty($dukData)) {
            $sheet->setCellValue('A' . $row, 'Tidak ada data DUK');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row++;
        } else {
            foreach ($dukData as $item) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($item['tanggal'])));
                $sheet->setCellValue('C' . $row, $item['uraian']);
                $sheet->setCellValue('D' . $row, $item['jumlah']);

                // Apply number format to amount column
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');

                $row++;
            }
        }

        // Add DUK total row
        $sheet->setCellValue('A' . $row, 'Total DUK');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $totalDuk);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);

        // Add borders to DUK data
        $sheet->getStyle('A' . $startDukRow . ':D' . $row)->applyFromArray($borderStyle);

        // Add saldo section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SALDO (DUM - DUK):');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $saldo);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);

        // Add footer with date
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Dicetak pada: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A' . $row . ':D' . $row);

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);
        $filename = "Jurnal_Kas_{$namaBulan}_{$year}.xlsx";
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }


    public function importExcel()
    {
        $file = $this->request->getFile('file_excel');

        // Get target year and month if provided
        $targetYear = $this->request->getPost('target_year');
        $targetMonth = $this->request->getPost('target_month');

        // Validate file
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
            $invalidDateRows = []; // For tracking invalid dates

            // If there are no rows or only header, return error
            if (count($rows) <= 1) {
                session()->setFlashdata('error', 'File Excel kosong atau hanya berisi header.');
                return redirect()->back();
            }

            // Check header row to determine column indexes
            $headerRow = $rows[0];
            $tanggalColIndex = null;
            $uraianColIndex = null;
            $dumColIndex = null;
            $dukColIndex = null;

            foreach ($headerRow as $index => $header) {
                $header = strtolower(trim($header));
                if (strpos($header, 'tanggal') !== false) {
                    $tanggalColIndex = $index;
                } else if (strpos($header, 'uraian') !== false) {
                    $uraianColIndex = $index;
                } else if (strpos($header, 'dum') !== false) {
                    $dumColIndex = $index;
                } else if (strpos($header, 'duk') !== false) {
                    $dukColIndex = $index;
                }
            }

            // If we couldn't identify all required columns, return error
            if ($tanggalColIndex === null || $uraianColIndex === null || $dumColIndex === null || $dukColIndex === null) {
                session()->setFlashdata('error', 'Format header Excel tidak sesuai. Pastikan ada kolom Tanggal, Uraian, DUM, dan DUK.');
                return redirect()->back();
            }

            // Default date if we need to use it
            $defaultDate = null;
            if ($targetYear && $targetMonth) {
                // Use the first day of the target month as default
                $defaultDate = "$targetYear-" . str_pad($targetMonth, 2, '0', STR_PAD_LEFT) . "-01";
            } else {
                // Use current first day of current month as default
                $defaultDate = date('Y-m-01');
            }

            foreach ($rows as $key => $row) {
                if ($key == 0)
                    continue; // Skip header row

                // Skip empty rows
                if (empty($row[$uraianColIndex]) && empty($row[$dumColIndex]) && empty($row[$dukColIndex])) {
                    continue;
                }

                // Process date
                $tanggal = $defaultDate; // Start with default date
                $originalDate = '';

                if (!empty($row[$tanggalColIndex])) {
                    $originalDate = trim($row[$tanggalColIndex]);

                    // Try to parse the date
                    $parsedDate = $this->parseDate($originalDate);
                    if ($parsedDate) {
                        $tanggal = $parsedDate;
                    } else {
                        // If date parsing failed, log the error but continue with default date
                        $invalidDateRows[] = $key + 1;
                        log_message('warning', "Row " . ($key + 1) . ": Invalid date format - '$originalDate'. Using default date.");
                    }
                }

                // If targeting specific month/year and we have a valid date, check if it matches
                if ($targetYear && $targetMonth && $tanggal !== $defaultDate) {
                    $rowYear = date('Y', strtotime($tanggal));
                    $rowMonth = date('n', strtotime($tanggal));

                    if ($rowYear != $targetYear || $rowMonth != $targetMonth) {
                        // Force the date to be in the target month/year
                        $day = date('d', strtotime($tanggal));
                        $tanggal = "$targetYear-" . str_pad($targetMonth, 2, '0', STR_PAD_LEFT) . "-" . $day;

                        // Check if this is a valid date (e.g., Feb 30 is not valid)
                        if (!checkdate($targetMonth, $day, $targetYear)) {
                            // If invalid, use the last day of the month
                            $tanggal = date('Y-m-t', strtotime("$targetYear-$targetMonth-01"));
                        }
                    }
                }

                // Get uraian
                $uraian = isset($row[$uraianColIndex]) ? trim($row[$uraianColIndex]) : '';
                if (empty($uraian))
                    continue;

                // Process DUM and DUK values
                $dum = isset($row[$dumColIndex]) ? $this->parseNumericValue($row[$dumColIndex]) : 0;
                $duk = isset($row[$dukColIndex]) ? $this->parseNumericValue($row[$dukColIndex]) : 0;

                // Only add records with non-zero values
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
                        $this->jurnalkasModel->update($existing['id'], ['jumlah' => $data['jumlah']]);
                        $updateCount++;
                    } else {
                        $this->jurnalkasModel->insert($data);
                        $insertCount++;
                    }
                }

                // REMOVED: Call to updateTotalHarian() that was causing the error
                // Instead, just calculate totals for the message
                $totalDum = 0;
                $totalDuk = 0;
                foreach ($dataToInsert as $data) {
                    if ($data['kategori'] == 'DUM') {
                        $totalDum += $data['jumlah'];
                    } else {
                        $totalDuk += $data['jumlah'];
                    }
                }

                $message = "Import berhasil: $insertCount data baru, $updateCount data diperbarui. ";
                $message .= "Total DUM: " . number_format($totalDum, 0, ',', '.') . ", ";
                $message .= "Total DUK: " . number_format($totalDuk, 0, ',', '.') . ".";

                if (!empty($invalidDateRows)) {
                    $message .= " Baris dengan format tanggal tidak valid: " . implode(', ', $invalidDateRows) .
                        ". Tanggal default digunakan untuk baris-baris tersebut.";
                }

                session()->setFlashdata('success', $message);
            } else {
                session()->setFlashdata('error', 'Tidak ada data yang valid untuk diimport.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Kesalahan saat mengimport Excel: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat mengimport data: ' . $e->getMessage());
        }

        // Redirect based on whether we came from monthly view or not
        if ($targetYear && $targetMonth) {
            return redirect()->to(base_url("admin/jurnal/monthly/details/$targetYear/$targetMonth"));
        } else {
            return redirect()->to(base_url('admin/jurnal'));
        }
    }


    /**
     * Helper method to parse dates in various formats
     * @param string $dateString The date string to parse
     * @return string|null MySQL formatted date (Y-m-d) or null if parsing fails
     */
    private function parseDate($dateString)
    {
        // Handle Excel serial dates (numeric)
        if (is_numeric($dateString)) {
            return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($dateString));
        }

        // Try various date formats
        $formats = [
            'm/d/Y', // 02/28/2023
            'd/m/Y', // 28/02/2023
            'Y-m-d', // 2023-02-28
            'Y/m/d', // 2023/02/28
            'd-m-Y', // 28-02-2023
            'j F Y', // 28 February 2023
            'F j, Y', // February 28, 2023
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date && $date->format($format) == $dateString) {
                // Check if the date is valid (not like Feb 31)
                $month = $date->format('n');
                $day = $date->format('j');
                $year = $date->format('Y');

                if (checkdate($month, $day, $year)) {
                    return $date->format('Y-m-d');
                }
            }
        }

        // If we have MM/DD/YYYY format but with invalid day (like 02/31/2023)
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateString, $matches)) {
            $month = (int) $matches[1];
            $day = (int) $matches[2];
            $year = (int) $matches[3];

            // If month is valid but day is not, use last day of month
            if ($month >= 1 && $month <= 12) {
                // Get last day of the month
                $lastDay = date('t', strtotime("$year-$month-01"));
                if ($day > $lastDay) {
                    return "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-$lastDay";
                }
            }
        }

        // If all parsing attempts fail
        return null;
    }

    /**
     * Helper function untuk memproses nilai numerik dari Excel
     * Menangani nilai yang mungkin berformat string dengan pemisah ribuan
     */
    private function parseNumericValue($value)
    {
        // If already numeric, just return it
        if (is_numeric($value)) {
            return floatval($value);
        }

        // Handle empty values
        if (empty($value) || $value === '-') {
            return 0;
        }

        // Remove currency symbols and text
        $value = preg_replace('/[^\d.,\-]/', '', $value);

        // Handle different number formats
        // Indonesian/European format: 1.234,56
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        // US/UK format: 1,234.56
        else if (preg_match('/^\d{1,3}(,\d{3})+(.\d+)?$/', $value)) {
            $value = str_replace(',', '', $value);
        }
        // Simple comma as decimal: 1234,56
        else if (preg_match('/^\d+(,\d+)$/', $value)) {
            $value = str_replace(',', '.', $value);
        }

        // Final check if value is numeric
        if (is_numeric($value)) {
            return floatval($value);
        }

        return 0;
    }


    /**
     * Helper function untuk memproses nilai numerik dari Excel
     * Menangani nilai yang mungkin berformat string dengan pemisah ribuan
     */

    public function prosesJurnalKeBukuBesar()
    {
        $jurnalModel = new \App\Models\JurnalKasModel();
        $bukuBesarModel = new \App\Models\BukuBesarModel();

        // Ambil semua data jurnal yang belum diproses
        $jurnalEntries = $jurnalModel->findAll();

        foreach ($jurnalEntries as $entry) {
            $debit = 0;
            $kredit = 0;

            if ($entry['kategori'] === 'Pemasukan') {
                $debit = $entry['jumlah'];
            } else {
                $kredit = $entry['jumlah'];
            }

            // Simpan ke Buku Besar
            $bukuBesarModel->insert([
                'tanggal' => $entry['tanggal'],
                'akun' => $entry['uraian'],
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo' => 0, // Bisa dihitung berdasarkan saldo sebelumnya
            ]);

            // Tandai jurnal sebagai sudah diproses
            $jurnalEntries = $jurnalModel->findAll();

            if (empty($jurnalEntries)) {
                return redirect()->back()->with('error', 'Tidak ada jurnal yang perlu diproses.');
            }


        }

        return redirect()->to(base_url('admin/buku_besar'))->with('success', 'Jurnal berhasil diproses ke Buku Besar.');
    }

    public function monthlyView()
    {
        // Get all available months and years from the database
        $db = \Config\Database::connect();
        $query = $db->query(
            "SELECT DISTINCT 
            YEAR(tanggal) as tahun, 
            MONTH(tanggal) as bulan,
            SUM(CASE WHEN kategori = 'DUM' THEN jumlah ELSE 0 END) as total_dum,
            SUM(CASE WHEN kategori = 'DUK' THEN jumlah ELSE 0 END) as total_duk,
            SUM(CASE WHEN kategori = 'DUM' THEN jumlah ELSE 0 END) - 
            SUM(CASE WHEN kategori = 'DUK' THEN jumlah ELSE 0 END) as saldo
        FROM jurnal_kas
        GROUP BY YEAR(tanggal), MONTH(tanggal)
        ORDER BY tahun DESC, bulan DESC"
        );

        $data['monthly_data'] = $query->getResultArray();

        // Add month names for display
        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        foreach ($data['monthly_data'] as &$item) {
            $item['nama_bulan'] = $bulanNames[(int) $item['bulan']];
        }

        return view('admin/jurnal/monthly_view', $data);
    }

    public function monthlyDetails($year, $month)
    {
        // Validate inputs
        $year = (int) $year;
        $month = (int) $month;

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            return redirect()->to(base_url('admin/jurnal/monthly'))->with('error', 'Periode tidak valid');
        }

        // Get month name for display
        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Format month with leading zero for query
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Get data for the selected month and year
        $data['jurnal_kas'] = $this->jurnalkasModel
            ->where("DATE_FORMAT(tanggal, '%Y-%m') = '$year-$monthFormatted'")
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        // Calculate totals
        $totalDUM = 0;
        $totalDUK = 0;

        foreach ($data['jurnal_kas'] as $item) {
            if ($item['kategori'] == 'DUM') {
                $totalDUM += $item['jumlah'];
            } else if ($item['kategori'] == 'DUK') {
                $totalDUK += $item['jumlah'];
            }
        }

        $data['total_dum'] = $totalDUM;
        $data['total_duk'] = $totalDUK;
        $data['saldo'] = $totalDUM - $totalDUK;
        $data['year'] = $year;
        $data['month'] = $month;
        $data['nama_bulan'] = $bulanNames[$month];

        return view('admin/jurnal/monthly_details', $data);
    }

    public function updateTotalHarian()
    {
        // This method previously tried to update total_dum and total_duk columns
        // Since these columns don't exist, we'll just log the totals instead
        $db = \Config\Database::connect();
        $query = $db->query(
            "SELECT tanggal, 
                   SUM(CASE WHEN kategori = 'DUM' THEN jumlah ELSE 0 END) AS total_dum,
                   SUM(CASE WHEN kategori = 'DUK' THEN jumlah ELSE 0 END) AS total_duk
            FROM jurnal_kas
            GROUP BY tanggal"
        );

        $results = $query->getResultArray();

        // Just log the results instead of updating non-existent columns
        foreach ($results as $row) {
            log_message('info', "Tanggal: {$row['tanggal']}, Total DUM: {$row['total_dum']}, Total DUK: {$row['total_duk']}");
        }

        return true;
    }


}

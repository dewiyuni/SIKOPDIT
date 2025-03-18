<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AnggotaModel;
use App\Models\JenisSimpananModel;
use App\Models\TransaksiSimpananModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\TransaksiSimpananDetailModel;

class TransaksiSimpanan extends Controller
{
    protected $transaksiModel;
    protected $detailModel;
    protected $anggotaModel;
    protected $jenisSimpananModel;
    protected $db;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiSimpananModel();
        $this->detailModel = new TransaksiSimpananDetailModel();
        $this->anggotaModel = new AnggotaModel();
        $this->jenisSimpananModel = new JenisSimpananModel();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        $data['transaksi'] = $this->transaksiModel->getLatestTransaksiPerAnggota();

        return view('karyawan/transaksi_simpanan/index', $data);
        // Saldo awal
    }

    public function create()
    {
        $data['anggota'] = $this->anggotaModel->findAll();
        return view('karyawan/transaksi_simpanan/create', $data);
    }
    public function store()
    {
        $id_anggota = $this->request->getPost('id_anggota');
        $tanggal = $this->request->getPost('tanggal');

        // Cek apakah sudah ada transaksi dengan id_anggota dan tanggal yang sama
        $cekTransaksi = $this->transaksiModel
            ->where('id_anggota', $id_anggota)
            ->where('tanggal', $tanggal)
            ->first();

        if ($cekTransaksi) {
            return redirect()->back()->with('error', 'Transaksi pada tanggal ini sudah ada!');
        }

        $data = [
            'id_anggota' => $id_anggota,
            'tanggal' => $tanggal,
            'setor_sw' => $this->request->getPost('setor_sw') ?? 0,
            'setor_swp' => $this->request->getPost('setor_swp') ?? 0,
            'setor_ss' => $this->request->getPost('setor_ss') ?? 0,
            'tarik_sw' => $this->request->getPost('tarik_sw') ?? 0,
            'tarik_swp' => $this->request->getPost('tarik_swp') ?? 0,
            'tarik_ss' => $this->request->getPost('tarik_ss') ?? 0,
            'keterangan' => $this->request->getPost('keterangan'),
        ];

        $this->transaksiModel->insert($data);

        return redirect()->to('/karyawan/transaksi_simpanan/')->with('message', 'Transaksi berhasil disimpan');
    }
    public function setor()
    {
        $id_anggota = $this->request->getPost('id_anggota');
        $setor_sw = $this->request->getPost('setor_sw');
        $setor_ss = $this->request->getPost('setor_ss');

        // Ambil ID Jenis Simpanan dari database
        $id_simpanan_wajib = $this->jenisSimpananModel->where('nama_simpanan', 'Simpanan Wajib')->first()->id_jenis_simpanan;
        $id_simpanan_sukarela = $this->jenisSimpananModel->where('nama_simpanan', 'Simpanan Sukarela')->first()->id_jenis_simpanan;

        // **Cek apakah transaksi simpanan sudah ada untuk anggota ini**
        $transaksi = $this->transaksiModel->where('id_anggota', $id_anggota)->orderBy('id_transaksi_simpanan', 'DESC')->first();

        if ($transaksi) {
            // Jika transaksi sudah ada, gunakan ID transaksi yang ada
            $id_transaksi_simpanan = $transaksi->id_transaksi_simpanan;
            $saldo_sw = $transaksi->saldo_sw;
            $saldo_ss = $transaksi->saldo_ss;
        } else {
            // Jika transaksi belum ada, buat transaksi baru
            $this->transaksiModel->insert([
                'id_anggota' => $id_anggota,
                'tanggal' => date('Y-m-d H:i:s'),
                'saldo_sw' => 0,
                'saldo_ss' => 0,
                'saldo_total' => 0,
                'keterangan' => 'Setoran Awal'
            ]);

            $id_transaksi_simpanan = $this->transaksiModel->insertID();
            $saldo_sw = 0;
            $saldo_ss = 0;
        }

        // Insert ke transaksi_simpanan_detail untuk SW
        $this->detailModel->insert([
            'id_transaksi_simpanan' => $id_transaksi_simpanan,
            'id_jenis_simpanan' => $id_simpanan_wajib,
            'setor' => $setor_sw,
            'tarik' => 0,
            'saldo_akhir' => $saldo_sw + $setor_sw,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Insert ke transaksi_simpanan_detail untuk SS (jika ada)
        if ($setor_ss > 0) {
            $this->detailModel->insert([
                'id_transaksi_simpanan' => $id_transaksi_simpanan,
                'id_jenis_simpanan' => $id_simpanan_sukarela,
                'setor' => $setor_ss,
                'tarik' => 0,
                'saldo_akhir' => $saldo_ss + $setor_ss,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // **Update saldo di transaksi_simpanan**
        $this->transaksiModel->update($id_transaksi_simpanan, [
            'saldo_sw' => $saldo_sw + $setor_sw,
            'saldo_ss' => $saldo_ss + $setor_ss,
            'saldo_total' => ($saldo_sw + $setor_sw) + ($saldo_ss + $setor_ss)
        ]);

        return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Setoran berhasil ditambahkan.');
    }
    public function tarik()
    {
        $id_anggota = $this->request->getPost('id_anggota');
        $tarik_ss = $this->request->getPost('tarik_ss');

        // Validasi input tidak boleh kosong
        if (!$id_anggota || !$tarik_ss) {
            return redirect()->back()->with('error', 'Data tidak lengkap. Pastikan semua field diisi.')->withInput();
        }

        // Pastikan input adalah angka positif
        if (!is_numeric($tarik_ss) || $tarik_ss <= 0) {
            return redirect()->back()->with('error', 'Jumlah penarikan harus lebih dari 0.')->withInput();
        }

        // Ambil ID Jenis Simpanan dari database
        $jenis_simpanan = $this->jenisSimpananModel->where('nama_simpanan', 'Simpanan Sukarela')->first();
        if (!$jenis_simpanan) {
            return redirect()->back()->with('error', 'Jenis simpanan tidak ditemukan.')->withInput();
        }
        $id_simpanan_sukarela = $jenis_simpanan->id_jenis_simpanan;

        // Ambil transaksi terakhir untuk anggota terkait
        $transaksi = $this->transaksiModel->where('id_anggota', $id_anggota)->orderBy('id_transaksi_simpanan', 'DESC')->first();

        if ($transaksi) {
            $id_transaksi_simpanan = $transaksi->id_transaksi_simpanan;
            $saldo_ss = $transaksi->saldo_ss;
            $saldo_total = $transaksi->saldo_total;
        } else {
            // Jika tidak ada transaksi, buat transaksi baru dengan saldo awal 0
            $this->transaksiModel->insert([
                'id_anggota' => $id_anggota,
                'tanggal' => date('Y-m-d H:i:s'),
                'saldo_sw' => 0,
                'saldo_ss' => 0,
                'saldo_total' => 0,
                'keterangan' => 'Setoran Awal'
            ]);

            $id_transaksi_simpanan = $this->transaksiModel->insertID();
            $saldo_ss = 0;
            $saldo_total = 0;
        }

        // Cek apakah saldo mencukupi sebelum melanjutkan transaksi
        if ($tarik_ss > $saldo_ss) {
            return redirect()->back()->with('error', 'Saldo Simpanan Sukarela tidak mencukupi.');
        }

        // Cek apakah saldo total tidak menjadi negatif
        if (($saldo_total - $tarik_ss) < 0) {
            return redirect()->back()->with('error', 'Saldo total tidak mencukupi.');
        }

        // Insert transaksi detail untuk penarikan
        if ($tarik_ss > 0) {
            $this->detailModel->insert([
                'id_transaksi_simpanan' => $id_transaksi_simpanan,
                'id_jenis_simpanan' => $id_simpanan_sukarela,
                'setor' => 0,
                'tarik' => $tarik_ss,
                'saldo_akhir' => $saldo_ss - $tarik_ss,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Update saldo di transaksi_simpanan
        $this->transaksiModel->update($id_transaksi_simpanan, [
            'saldo_ss' => $saldo_ss - $tarik_ss,
            'saldo_total' => $saldo_total - $tarik_ss
        ]);

        return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Penarikan berhasil dilakukan.');
    }


    public function proses()
    {
        $transaksiModel = new \App\Models\TransaksiSimpananDetailModel();
        $simpananModel = new \App\Models\TransaksiSimpananModel();

        $id_anggota = $this->request->getPost('id_anggota');
        $id_jenis_simpanan = $this->request->getPost('id_jenis_simpanan');
        $jumlah = (int) $this->request->getPost('jumlah');

        // Simpan transaksi utama di transaksi_simpanan dan ambil ID-nya
        $simpananModel->insert([
            'id_anggota' => $id_anggota,
            'tanggal' => date('Y-m-d'),
            'saldo_sw' => 0, // Pastikan sesuai dengan tabel
            'saldo_swp' => 0,
            'saldo_ss' => 0,
            'saldo_total' => 0 + 10000,
            'keterangan' => '',
        ]);

        $id_transaksi_simpanan = $simpananModel->insertID();

        // Cek apakah insert berhasil
        if (!$id_transaksi_simpanan) {
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi utama.');
        }

        // Ambil saldo terakhir berdasarkan jenis simpanan (Setelah id_transaksi_simpanan tersedia)
        $lastSaldo = $transaksiModel->where('id_transaksi_simpanan', $id_transaksi_simpanan)
            ->where('id_jenis_simpanan', $id_jenis_simpanan)
            ->orderBy('created_at', 'DESC')
            ->first();

        $saldo_sebelumnya = $lastSaldo ? $lastSaldo['saldo_akhir'] : 0;
        $saldo_akhir = $saldo_sebelumnya + $jumlah;

        // Simpan transaksi detail
        $transaksiModel->insert([
            'id_transaksi_simpanan' => $id_transaksi_simpanan,
            'id_anggota' => $id_anggota,
            'id_jenis_simpanan' => $id_jenis_simpanan,
            'setor' => $jumlah,
            'tarik' => 0,
            'saldo_akhir' => $saldo_akhir,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/karyawan/transaksi_simpanan/')->with('message', 'Transaksi berhasil!');
    }
    private function form_transaksi($id_anggota, $mode)
    {
        if (!$id_anggota) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'ID Anggota tidak ditemukan.');
        }

        $anggota = $this->anggotaModel->find($id_anggota);
        if (!$anggota) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Anggota tidak ditemukan.');
        }

        $jenis_simpanan = $this->jenisSimpananModel->findAll();
        $view = $mode === 'setor' ? 'karyawan/transaksi_simpanan/setor_form' : 'karyawan/transaksi_simpanan/tarik_form';

        return view($view, [
            'anggota' => $anggota,
            'jenis_simpanan' => $jenis_simpanan,
        ]);
    }
    public function setor_form($id_anggota)
    {
        return $this->form_transaksi($id_anggota, 'setor');
    }
    public function tarik_form($id_anggota)
    {
        return $this->form_transaksi($id_anggota, 'tarik');
    }
    public function detail($id_anggota)
    {
        $anggotaModel = new \App\Models\AnggotaModel();
        $transaksiSimpananModel = new \App\Models\TransaksiSimpananModel();
        $transaksiDetailModel = new \App\Models\TransaksiSimpananDetailModel();

        // Ambil data anggota
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Anggota tidak ditemukan.');
        }

        // Ambil data transaksi simpanan utama (yang berisi saldo_swp)
        $transaksi_simpanan = $transaksiSimpananModel->getTransaksiByAnggota($id_anggota);

        // Debug transaksi simpanan
        // echo '<pre>';
        // print_r($transaksi_simpanan);
        // echo '</pre>';
        // exit;

        // Ambil detail transaksi simpanan
        $detail_transaksi = $transaksiDetailModel->getDetailTransaksiByAnggota($id_anggota);

        // Kelompokkan transaksi berdasarkan tanggal transaksi
        $transaksi_dikelompokkan = [];

        // Pertama, kelompokkan berdasarkan tanggal dari transaksi utama
        foreach ($transaksi_simpanan as $transaksi) {
            $tanggal_key = date('Y-m-d H:i:s', strtotime($transaksi->created_at));

            if (!isset($transaksi_dikelompokkan[$tanggal_key])) {
                $transaksi_dikelompokkan[$tanggal_key] = [
                    'waktu' => $tanggal_key,
                    'id_transaksi' => $transaksi->id_transaksi_simpanan,
                    'setor_sw' => 0,
                    'tarik_sw' => 0,
                    'setor_swp' => 0, // Ini akan diisi dari data transaksi utama
                    'tarik_swp' => 0,
                    'setor_ss' => 0,
                    'tarik_ss' => 0,
                    'setor_sp' => 0,
                    'tarik_sp' => 0,
                ];
            }

            // Isi data SWP dari transaksi utama (jika ada perubahan)
            if (isset($transaksi->saldo_swp) && $transaksi->saldo_swp > 0) {
                // Jika ini adalah transaksi pertama, anggap sebagai setoran
                // Jika ada transaksi sebelumnya, hitung selisih untuk menentukan setor/tarik
                $transaksi_dikelompokkan[$tanggal_key]['setor_swp'] = $transaksi->saldo_swp;
            }
        }

        // Kemudian, tambahkan detail dari transaksi_simpanan_detail
        foreach ($detail_transaksi as $detail) {
            $tanggal_key = date('Y-m-d H:i:s', strtotime($detail->created_at));

            if (!isset($transaksi_dikelompokkan[$tanggal_key])) {
                $transaksi_dikelompokkan[$tanggal_key] = [
                    'waktu' => $tanggal_key,
                    'id_transaksi' => $detail->id_transaksi_simpanan,
                    'setor_sw' => 0,
                    'tarik_sw' => 0,
                    'setor_swp' => 0,
                    'tarik_swp' => 0,
                    'setor_ss' => 0,
                    'tarik_ss' => 0,
                    'setor_sp' => 0,
                    'tarik_sp' => 0,
                ];
            }

            // Tentukan jenis simpanan dan tambahkan nilai setor/tarik
            switch ($detail->nama_simpanan) {
                case 'Simpanan Wajib':
                    $transaksi_dikelompokkan[$tanggal_key]['setor_sw'] += $detail->setor;
                    $transaksi_dikelompokkan[$tanggal_key]['tarik_sw'] += $detail->tarik;
                    break;
                case 'Simpanan Sukarela':
                    $transaksi_dikelompokkan[$tanggal_key]['setor_ss'] += $detail->setor;
                    $transaksi_dikelompokkan[$tanggal_key]['tarik_ss'] += $detail->tarik;
                    break;
                case 'Simpanan Pokok':
                    $transaksi_dikelompokkan[$tanggal_key]['setor_sp'] += $detail->setor;
                    $transaksi_dikelompokkan[$tanggal_key]['tarik_sp'] += $detail->tarik;
                    break;
            }
        }

        // Debug data yang dikelompokkan
        // echo '<pre>';
        // print_r($transaksi_dikelompokkan);
        // echo '</pre>';
        // exit;

        // Konversi array asosiatif menjadi array numerik untuk view
        $riwayat_transaksi_final = [];
        foreach ($transaksi_dikelompokkan as $transaksi) {
            $riwayat_transaksi_final[] = (object) $transaksi;
        }

        // Urutkan berdasarkan waktu (terbaru dulu)
        usort($riwayat_transaksi_final, function ($a, $b) {
            return strtotime($b->waktu) - strtotime($a->waktu);
        });

        // Hitung total untuk setiap jenis simpanan
        $total_sw_setor = 0;
        $total_sw_tarik = 0;
        $total_swp_setor = 0;
        $total_swp_tarik = 0;
        $total_ss_setor = 0;
        $total_ss_tarik = 0;
        $total_sp_setor = 0;
        $total_sp_tarik = 0;

        foreach ($riwayat_transaksi_final as $transaksi) {
            $total_sw_setor += $transaksi->setor_sw;
            $total_sw_tarik += $transaksi->tarik_sw;
            $total_swp_setor += $transaksi->setor_swp;
            $total_swp_tarik += $transaksi->tarik_swp;
            $total_ss_setor += $transaksi->setor_ss;
            $total_ss_tarik += $transaksi->tarik_ss;
            $total_sp_setor += $transaksi->setor_sp;
            $total_sp_tarik += $transaksi->tarik_sp;
        }

        // Ambil saldo akhir dari transaksi terakhir
        $saldo_akhir = $transaksiSimpananModel->getLastSaldo($id_anggota);

        // Konversi ke objek untuk konsistensi
        $saldo_akhir = (object) [
            'sw' => $saldo_akhir['saldo_sw'],
            'swp' => $saldo_akhir['saldo_swp'],
            'ss' => $saldo_akhir['saldo_ss'],
            'sp' => $saldo_akhir['saldo_sp']
        ];

        return view('karyawan/transaksi_simpanan/detail', [
            'anggota' => $anggota,
            'riwayat_transaksi' => $riwayat_transaksi_final,
            'saldo_akhir' => $saldo_akhir,
            'total_sw_setor' => $total_sw_setor,
            'total_sw_tarik' => $total_sw_tarik,
            'total_swp_setor' => $total_swp_setor,
            'total_swp_tarik' => $total_swp_tarik,
            'total_ss_setor' => $total_ss_setor,
            'total_ss_tarik' => $total_ss_tarik,
            'total_sp_setor' => $total_sp_setor,
            'total_sp_tarik' => $total_sp_tarik
        ]);
    }


    public function simpan()
    {
        $transaksiModel = new TransaksiSimpananModel();

        $data = [
            'id_anggota' => $this->request->getPost('id_anggota'),
            'id_jenis_simpanan' => $this->request->getPost('id_jenis_simpanan'),
            'setor' => $this->request->getPost('setor') ?? 0,
            'tarik' => $this->request->getPost('tarik') ?? 0,
        ];

        if ($transaksiModel->simpanTransaksi($data)) {
            return redirect()->to('/transaksi-simpanan')->with('success', 'Transaksi berhasil disimpan!');
        } else {
            return redirect()->to('/transaksi-simpanan')->with('error', 'Gagal menyimpan transaksi.');
        }
    }
    public function edit($id_transaksi_simpanan)
    {
        $transaksi = $this->transaksiModel->where('id_transaksi_simpanan', $id_transaksi_simpanan)->first();
        if (!$transaksi) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Data tidak ditemukan');
        }

        // Ambil semua detail transaksi simpanan
        $details = $this->detailModel
            ->where('id_transaksi_simpanan', $id_transaksi_simpanan)
            ->findAll();

        // Ubah menjadi array asosiatif berdasarkan id_jenis_simpanan
        $detailData = [];
        foreach ($details as $detail) {
            $detailData[$detail->id_jenis_simpanan] = $detail;
        }

        $data = [
            'title' => 'Edit Transaksi Simpanan',
            'transaksi' => $transaksi,
            'details' => $detailData, // Kirim array detail
        ];

        return view('karyawan/transaksi_simpanan/edit', $data);
    }
    // Method untuk update transaksi simpanan
    public function update($id)
    {
        $transaksi = $this->transaksiModel->find($id);

        if (!$transaksi) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        // Start a database transaction
        $this->db->transStart();

        try {
            // Track changes to calculate new totals
            $total_setor_sw = 0;
            $total_tarik_sw = 0;
            $total_setor_swp = 0;
            $total_tarik_swp = 0;
            $total_setor_ss = 0;
            $total_tarik_ss = 0;
            $total_setor_sp = 0;
            $total_tarik_sp = 0;

            // Map jenis simpanan codes to IDs
            $jenis_ids = [
                'sw' => 1,  // Simpanan Wajib
                'swp' => 2, // Simpanan Wajib Pokok
                'ss' => 3,  // Simpanan Sukarela
                'sp' => 4   // Simpanan Pokok
            ];

            // Process each type of transaction
            foreach (['sw', 'swp', 'ss', 'sp'] as $jenis) {
                if ($this->request->getPost('edit_' . $jenis)) {
                    $id_detail = $this->request->getPost('id_detail_' . $jenis);

                    // Get values and remove formatting (dots as thousand separators)
                    $setor_str = $this->request->getPost('setor_' . $jenis);
                    $tarik_str = $this->request->getPost('tarik_' . $jenis);

                    // Convert to integers, handling empty values
                    $setor = empty($setor_str) ? 0 : (int) str_replace('.', '', $setor_str);
                    $tarik = empty($tarik_str) ? 0 : (int) str_replace('.', '', $tarik_str);

                    // If the detail record exists, update it
                    if (!empty($id_detail)) {
                        $detail = $this->detailModel->find($id_detail);
                        if ($detail) {
                            $this->detailModel->update($id_detail, [
                                'setor' => $setor,
                                'tarik' => $tarik,
                            ]);
                        }
                    } else {
                        // If no detail record exists but values are provided, create a new one
                        if ($setor > 0 || $tarik > 0) {
                            $this->detailModel->insert([
                                'id_transaksi_simpanan' => $id,
                                'id_jenis_simpanan' => $jenis_ids[$jenis],
                                'setor' => $setor,
                                'tarik' => $tarik,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    // Track totals for the main transaction record
                    switch ($jenis) {
                        case 'sw':
                            $total_setor_sw += $setor;
                            $total_tarik_sw += $tarik;
                            break;
                        case 'swp':
                            $total_setor_swp += $setor;
                            $total_tarik_swp += $tarik;
                            break;
                        case 'ss':
                            $total_setor_ss += $setor;
                            $total_tarik_ss += $tarik;
                            break;
                        case 'sp':
                            $total_setor_sp += $setor;
                            $total_tarik_sp += $tarik;
                            break;
                    }
                }
            }

            // Recalculate all saldos based on all detail records for this transaction
            $details = $this->detailModel->where('id_transaksi_simpanan', $id)->findAll();

            $saldo_sw = 0;
            $saldo_swp = 0;
            $saldo_ss = 0;
            $saldo_sp = 0;

            foreach ($details as $detail) {
                switch ($detail->id_jenis_simpanan) {
                    case 1: // SW
                        $saldo_sw += ($detail->setor - $detail->tarik);
                        break;
                    case 2: // SWP
                        $saldo_swp += ($detail->setor - $detail->tarik);
                        break;
                    case 3: // SS
                        $saldo_ss += ($detail->setor - $detail->tarik);
                        break;
                    case 4: // SP
                        $saldo_sp += ($detail->setor - $detail->tarik);
                        break;
                }
            }

            // Update the main transaction record with new totals
            $this->transaksiModel->update($id, [
                'saldo_sw' => $saldo_sw,
                'saldo_swp' => $saldo_swp,
                'saldo_ss' => $saldo_ss,
                'saldo_sp' => $saldo_sp,
                'saldo_total' => $saldo_sw + $saldo_swp + $saldo_ss + $saldo_sp
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal memperbarui transaksi.");
            }

            return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id_transaksi)
    {
        $id_transaksi = (int) $id_transaksi;

        // First, find the transaction to get the anggota_id for redirection
        $transaksi = $this->db->table('transaksi_simpanan')
            ->select('id_anggota')
            ->where('id_transaksi_simpanan', $id_transaksi)
            ->get()
            ->getRow();

        if (!$transaksi) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Transaksi tidak ditemukan.');
        }

        $id_anggota = $transaksi->id_anggota;

        $this->db->transStart();

        try {
            // Get the timestamp of this transaction
            $transaction_details = $this->db->table('transaksi_simpanan_detail')
                ->select('created_at')
                ->where('id_transaksi_simpanan', $id_transaksi)
                ->get()
                ->getRow();

            if ($transaction_details) {
                $timestamp = $transaction_details->created_at;

                // Delete all detail records with this timestamp for this transaction
                $this->db->table('transaksi_simpanan_detail')
                    ->where('id_transaksi_simpanan', $id_transaksi)
                    ->where('created_at', $timestamp)
                    ->delete();
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menghapus detail transaksi.");
            }

            return redirect()->to('karyawan/transaksi_simpanan/detail/' . $id_anggota)
                ->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->to('karyawan/transaksi_simpanan/detail/' . $id_anggota)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== Jenis simpanan ==================================== 
    public function jenisSimpanan()
    {
        $jenisSimpananModel = new \App\Models\JenisSimpananModel();
        $data['jenis_simpanan'] = $jenisSimpananModel->findAll();
        return view('admin/jenis_simpanan', $data);
    }
    public function tambahJenisSimpanan()
    {
        return view('admin/tambah_jenis_simpanan');
    }
    public function simpanJenisSimpanan()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama_simpanan' => 'required|is_unique[jenis_simpanan.nama_simpanan]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $jenisSimpananModel = new \App\Models\JenisSimpananModel();
        $jenisSimpananModel->insert([
            'nama_simpanan' => $this->request->getPost('nama_simpanan'),
        ]);

        return redirect()->to('/admin/jenis_simpanan')->with('success', 'Jenis Simpanan berhasil ditambahkan.');
    }
    public function editJenisSimpanan($id_jenis_simpanan)
    {
        $jenisSimpananModel = new \App\Models\JenisSimpananModel();
        $data['jenis_simpanan'] = $jenisSimpananModel->find($id_jenis_simpanan);

        if (!$data['jenis_simpanan']) {
            return redirect()->to('/admin/jenis_simpanan')->with('error', 'Data tidak ditemukan');
        }

        return view('admin/edit_jenis_simpanan', $data);
    }
    public function updateJenisSimpanan()
    {
        $id_jenis_simpanan = $this->request->getPost('id_jenis_simpanan');

        $jenisSimpananModel = new \App\Models\JenisSimpananModel();
        $jenisSimpananModel->update($id_jenis_simpanan, [
            'nama_simpanan' => $this->request->getPost('nama_simpanan'),
        ]);

        return redirect()->to('/admin/jenis_simpanan')->with('success', 'Jenis Simpanan berhasil diperbarui.');
    }
    public function hapusJenisSimpanan($id_jenis_simpanan)
    {
        $jenisSimpananModel = new \App\Models\JenisSimpananModel();
        $jenisSimpananModel->delete($id_jenis_simpanan);

        return redirect()->to('/admin/jenis_simpanan')->with('success', 'Jenis Simpanan berhasil dihapus.');
    }

    // =============== Import ecxel ==================
}

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
        $transaksiModel = new TransaksiSimpananModel();

        // Pastikan metode mengembalikan data yang benar
        $data['transaksi'] = $transaksiModel->getLatestTransaksiPerAnggota();

        return view('karyawan/transaksi_simpanan/index', $data);
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
        }

        // Cek apakah saldo mencukupi sebelum melanjutkan transaksi
        if ($tarik_ss > $saldo_ss) {
            return redirect()->back()->with('error', 'Saldo Simpanan Sukarela tidak mencukupi.');
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
            'saldo_total' => ($saldo_ss - $tarik_ss)
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
        $transaksiDetailModel = new \App\Models\TransaksiSimpananDetailModel();

        // Saldo awal sebagai objek
        $saldo_awal = (object) [
            'sw' => 75000,
            'swp' => 0,
            'ss' => 5000,
            'sp' => 10000
        ];

        // Ambil data anggota
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Anggota tidak ditemukan.');
        }

        // Ambil riwayat transaksi simpanan - diubah untuk menampilkan per transaksi, bukan per hari
        $riwayat_transaksi = $transaksiDetailModel
            ->select("
                transaksi_simpanan_detail.id_detail,
                transaksi_simpanan_detail.id_transaksi_simpanan,
                transaksi_simpanan_detail.created_at as waktu_transaksi,
                jenis_simpanan.nama_simpanan,
                transaksi_simpanan_detail.setor,
                transaksi_simpanan_detail.tarik
            ")
            ->join('jenis_simpanan', 'jenis_simpanan.id_jenis_simpanan = transaksi_simpanan_detail.id_jenis_simpanan')
            ->join('transaksi_simpanan', 'transaksi_simpanan.id_transaksi_simpanan = transaksi_simpanan_detail.id_transaksi_simpanan')
            ->where('transaksi_simpanan.id_anggota', $id_anggota)
            ->orderBy('transaksi_simpanan_detail.created_at', 'DESC')
            ->findAll();

        // Kelompokkan transaksi berdasarkan waktu untuk tampilan yang lebih terorganisir
        $transaksi_dikelompokkan = [];

        foreach ($riwayat_transaksi as $transaksi) {
            $waktu_key = $transaksi->waktu_transaksi;

            if (!isset($transaksi_dikelompokkan[$waktu_key])) {
                $transaksi_dikelompokkan[$waktu_key] = [
                    'waktu' => $transaksi->waktu_transaksi,
                    'id_transaksi' => $transaksi->id_transaksi_simpanan,
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
            switch ($transaksi->nama_simpanan) {
                case 'Simpanan Wajib':
                    $transaksi_dikelompokkan[$waktu_key]['setor_sw'] += $transaksi->setor;
                    $transaksi_dikelompokkan[$waktu_key]['tarik_sw'] += $transaksi->tarik;
                    break;
                case 'Simpanan Wajib Pokok':
                    $transaksi_dikelompokkan[$waktu_key]['setor_swp'] += $transaksi->setor;
                    $transaksi_dikelompokkan[$waktu_key]['tarik_swp'] += $transaksi->tarik;
                    break;
                case 'Simpanan Sukarela':
                    $transaksi_dikelompokkan[$waktu_key]['setor_ss'] += $transaksi->setor;
                    $transaksi_dikelompokkan[$waktu_key]['tarik_ss'] += $transaksi->tarik;
                    break;
                case 'Simpanan Pokok':
                    $transaksi_dikelompokkan[$waktu_key]['setor_sp'] += $transaksi->setor;
                    $transaksi_dikelompokkan[$waktu_key]['tarik_sp'] += $transaksi->tarik;
                    break;
            }
        }

        // Konversi array asosiatif menjadi array numerik untuk view
        $riwayat_transaksi_final = [];
        foreach ($transaksi_dikelompokkan as $transaksi) {
            $riwayat_transaksi_final[] = (object) $transaksi;
        }

        // Hitung saldo berjalan
        $saldo = clone $saldo_awal; // Buat salinan saldo awal
        foreach ($riwayat_transaksi_final as &$row) {
            $row->saldo_sw = $saldo->sw + $row->setor_sw - $row->tarik_sw;
            $row->saldo_swp = $saldo->swp + $row->setor_swp - $row->tarik_swp;
            $row->saldo_ss = $saldo->ss + $row->setor_ss - $row->tarik_ss;
            $row->saldo_sp = $saldo->sp + $row->setor_sp - $row->tarik_sp;

            // Perbarui saldo berjalan
            $saldo->sw = $row->saldo_sw;
            $saldo->swp = $row->saldo_swp;
            $saldo->ss = $row->saldo_ss;
            $saldo->sp = $row->saldo_sp;
        }

        return view('karyawan/transaksi_simpanan/detail', [
            'anggota' => $anggota,
            'riwayat_transaksi' => $riwayat_transaksi_final,
            'saldo_awal' => $saldo_awal,
            'saldo_akhir' => $saldo
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

        foreach (['SW', 'SWP', 'SS', 'SP'] as $jenis) {
            if ($this->request->getPost('edit_' . strtolower($jenis))) {
                $id_detail = $this->request->getPost('id_detail_' . strtolower($jenis));
                $setor = $this->request->getPost('setor_' . strtolower($jenis));
                $tarik = $this->request->getPost('tarik_' . strtolower($jenis));

                $this->detailModel->update($id_detail, [
                    'setor' => $setor,
                    'tarik' => $tarik,
                ]);
            }
        }

        return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Transaksi diperbarui.');
    }
    public function delete($id_detail)
    {
        $id_detail = (int) $id_detail; // Pastikan integer

        // Cek apakah id_detail ada
        $detail = $this->detailModel->find($id_detail);
        if (!$detail) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Detail transaksi tidak ditemukan.');
        }

        // Hapus hanya satu detail transaksi
        $this->detailModel->delete($id_detail);

        return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Detail transaksi berhasil dihapus.');
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

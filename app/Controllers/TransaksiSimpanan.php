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
    protected $anggotaModel;
    protected $jenisSimpananModel;
    protected $db;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiSimpananModel();
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
        $setor_sw = (int) $this->request->getPost('setor_sw');
        $setor_ss = (int) $this->request->getPost('setor_ss');

        // **Cek apakah ada transaksi terakhir anggota ini**
        $transaksi = $this->transaksiModel
            ->where('id_anggota', $id_anggota)
            ->orderBy('id_simpanan', 'DESC')
            ->first();

        if ($transaksi) {
            // Jika transaksi sudah ada, ambil saldo terakhir
            $saldo_sw = $transaksi->saldo_sw;
            $saldo_ss = $transaksi->saldo_ss;
            $saldo_total = $transaksi->saldo_total;
        } else {
            // Jika belum ada transaksi, mulai saldo dari nol
            $saldo_sw = 0;
            $saldo_ss = 0;
            $saldo_total = 0;
        }

        // **Hitung saldo baru setelah setoran**
        $saldo_sw_baru = $saldo_sw + $setor_sw;
        $saldo_ss_baru = $saldo_ss + $setor_ss;
        $saldo_total_baru = $saldo_sw_baru + $saldo_ss_baru;

        // **Tambahkan transaksi baru ke `transaksi_simpanan`**
        $this->transaksiModel->insert([
            'id_anggota' => $id_anggota,
            'tanggal' => date('Y-m-d H:i:s'),
            'setor_sw' => $setor_sw,
            'setor_ss' => $setor_ss,
            'saldo_sw' => $saldo_sw_baru,
            'saldo_ss' => $saldo_ss_baru,
            'saldo_total' => $saldo_total_baru,
            'keterangan' => 'Setoran Simpanan'
        ]);

        return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Setoran berhasil ditambahkan.');
    }

    public function tarik()
    {
        $id_anggota = $this->request->getPost('id_anggota');
        $tarik_ss = (int) $this->request->getPost('tarik_ss');

        // Validasi input tidak boleh kosong
        if (!$id_anggota || !$tarik_ss) {
            return redirect()->back()->with('error', 'Data tidak lengkap. Pastikan semua field diisi.')->withInput();
        }

        // Pastikan input adalah angka positif
        if ($tarik_ss <= 0) {
            return redirect()->back()->with('error', 'Jumlah penarikan harus lebih dari 0.')->withInput();
        }

        // Ambil total saldo simpanan sukarela (setor - tarik)
        $lastSaldo = $this->transaksiModel
            ->select('SUM(setor_ss) - SUM(tarik_ss) as saldo_ss')
            ->where('id_anggota', $id_anggota)
            ->first();

        $saldo_ss = $lastSaldo->saldo_ss ?? 0;

        // Cek apakah saldo mencukupi sebelum melanjutkan transaksi
        if ($tarik_ss > $saldo_ss) {
            return redirect()->back()->with('error', 'Saldo Simpanan Sukarela tidak mencukupi.');
        }

        // Simpan transaksi penarikan
        $this->transaksiModel->insert([
            'id_anggota' => $id_anggota,
            'tanggal' => date('Y-m-d'),
            'setor_ss' => 0,
            'tarik_ss' => $tarik_ss,
            'keterangan' => 'Penarikan Simpanan Sukarela',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Penarikan berhasil dilakukan.');
    }

    public function proses()
    {
        $simpananModel = new \App\Models\TransaksiSimpananModel();

        $id_anggota = $this->request->getPost('id_anggota');
        $id_jenis_simpanan = $this->request->getPost('id_jenis_simpanan');
        $jumlah = (int) $this->request->getPost('jumlah');

        // Ambil total saldo sebelumnya (setor - tarik)
        $lastSaldo = $simpananModel
            ->select('SUM(setor_sw) - SUM(tarik_sw) as saldo_sw, 
                  SUM(setor_swp) - SUM(tarik_swp) as saldo_swp, 
                  SUM(setor_ss) - SUM(tarik_ss) as saldo_ss, 
                  SUM(setor_sp) - SUM(tarik_sp) as saldo_sp')
            ->where('id_anggota', $id_anggota)
            ->first();

        // Jika tidak ada transaksi sebelumnya, mulai dari 0
        $saldo_sw = $lastSaldo->saldo_sw ?? 0;
        $saldo_swp = $lastSaldo->saldo_swp ?? 0;
        $saldo_ss = $lastSaldo->saldo_ss ?? 0;
        $saldo_sp = $lastSaldo->saldo_sp ?? 0;

        // Update saldo sesuai jenis simpanan yang dipilih
        $setor_sw = $tarik_sw = 0;
        $setor_swp = $tarik_swp = 0;
        $setor_ss = $tarik_ss = 0;
        $setor_sp = $tarik_sp = 0;

        switch ($id_jenis_simpanan) {
            case 1: // Simpanan Wajib
                $setor_sw = $jumlah;
                $saldo_sw += $jumlah;
                break;
            case 2: // Simpanan SWP
                $setor_swp = $jumlah;
                $saldo_swp += $jumlah;
                break;
            case 3: // Simpanan Sukarela
                $setor_ss = $jumlah;
                $saldo_ss += $jumlah;
                break;
            case 4: // Simpanan Pokok
                $setor_sp = $jumlah;
                $saldo_sp += $jumlah;
                break;
        }

        // Simpan transaksi baru
        $simpananModel->insert([
            'id_anggota' => $id_anggota,
            'tanggal' => date('Y-m-d'),
            'setor_sw' => $setor_sw,
            'tarik_sw' => $tarik_sw,
            'setor_swp' => $setor_swp,
            'tarik_swp' => $tarik_swp,
            'setor_ss' => $setor_ss,
            'tarik_ss' => $tarik_ss,
            'setor_sp' => $setor_sp,
            'tarik_sp' => $tarik_sp,
            'keterangan' => 'Setoran baru',
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

        // Ambil data anggota
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Anggota tidak ditemukan.');
        }

        // Ambil data transaksi simpanan langsung dari `transaksi_simpanan`
        $transaksi_simpanan = $transaksiSimpananModel->getTransaksiByAnggota($id_anggota);

        // Kelompokkan transaksi berdasarkan tanggal transaksi
        $transaksi_dikelompokkan = [];

        foreach ($transaksi_simpanan as $transaksi) {
            $tanggal_key = date('Y-m-d H:i:s', strtotime($transaksi->created_at));

            if (!isset($transaksi_dikelompokkan[$tanggal_key])) {
                $transaksi_dikelompokkan[$tanggal_key] = [
                    'waktu' => $tanggal_key,
                    'id_transaksi' => $transaksi->id_simpanan,
                    'setor_sw' => $transaksi->setor_sw ?? 0,
                    'tarik_sw' => $transaksi->tarik_sw ?? 0,
                    'setor_swp' => $transaksi->setor_swp ?? 0,
                    'tarik_swp' => $transaksi->tarik_swp ?? 0,
                    'setor_ss' => $transaksi->setor_ss ?? 0,
                    'tarik_ss' => $transaksi->tarik_ss ?? 0,
                    'setor_sp' => $transaksi->setor_sp ?? 0,
                    'tarik_sp' => $transaksi->tarik_sp ?? 0,
                ];
            }
        }

        // Konversi array asosiatif menjadi array numerik untuk view
        $riwayat_transaksi_final = array_values($transaksi_dikelompokkan);

        // Urutkan berdasarkan waktu (terbaru dulu)
        usort($riwayat_transaksi_final, function ($a, $b) {
            return strtotime($b['waktu']) - strtotime($a['waktu']);
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
            $total_sw_setor += $transaksi['setor_sw'];
            $total_sw_tarik += $transaksi['tarik_sw'];
            $total_swp_setor += $transaksi['setor_swp'];
            $total_swp_tarik += $transaksi['tarik_swp'];
            $total_ss_setor += $transaksi['setor_ss'];
            $total_ss_tarik += $transaksi['tarik_ss'];
            $total_sp_setor += $transaksi['setor_sp'];
            $total_sp_tarik += $transaksi['tarik_sp'];
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
    public function edit($id_simpanan)
    {
        // Ambil transaksi berdasarkan ID
        $transaksi = $this->transaksiModel->where('id_simpanan', $id_simpanan)->first();

        if (!$transaksi) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Transaksi Simpanan',
            'transaksi' => $transaksi, // Langsung kirim data transaksi
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

        // Mulai transaksi database
        $this->transaksiModel->db->transStart();

        try {
            // Konversi input setor & tarik ke integer
            $setor_sw = (int) str_replace('.', '', $this->request->getPost('setor_sw') ?? '0');
            $tarik_sw = (int) str_replace('.', '', $this->request->getPost('tarik_sw') ?? '0');
            $setor_ss = (int) str_replace('.', '', $this->request->getPost('setor_ss') ?? '0');
            $tarik_ss = (int) str_replace('.', '', $this->request->getPost('tarik_ss') ?? '0');
            $setor_sp = (int) str_replace('.', '', $this->request->getPost('setor_sp') ?? '0');
            $tarik_sp = (int) str_replace('.', '', $this->request->getPost('tarik_sp') ?? '0');

            // Hitung saldo baru
            $saldo_sw = $transaksi['saldo_sw'] + $setor_sw - $tarik_sw;
            $saldo_ss = $transaksi['saldo_ss'] + $setor_ss - $tarik_ss;
            $saldo_sp = $transaksi['saldo_sp'] + $setor_sp - $tarik_sp;
            $saldo_total = $saldo_sw + $saldo_ss + $saldo_sp;

            // Update transaksi_simpanan
            $this->transaksiModel->update($id, [
                'setor_sw' => $setor_sw,
                'tarik_sw' => $tarik_sw,
                'setor_ss' => $setor_ss,
                'tarik_ss' => $tarik_ss,
                'setor_sp' => $setor_sp,
                'tarik_sp' => $tarik_sp,
                'saldo_sw' => $saldo_sw,
                'saldo_ss' => $saldo_ss,
                'saldo_sp' => $saldo_sp,
                'saldo_total' => $saldo_total
            ]);

            // Selesaikan transaksi database
            $this->transaksiModel->db->transComplete();

            if ($this->transaksiModel->db->transStatus() === false) {
                throw new \Exception("Gagal memperbarui transaksi.");
            }

            return redirect()->to('karyawan/transaksi_simpanan')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            $this->transaksiModel->db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function delete($id_transaksi)
    {
        $id_transaksi = (int) $id_transaksi;

        // Ambil informasi anggota dari transaksi
        $transaksi = $this->db->table('transaksi_simpanan')
            ->select('id_anggota')
            ->where('id_simpanan', $id_transaksi)
            ->get()
            ->getRow();

        if (!$transaksi) {
            return redirect()->to('karyawan/transaksi_simpanan')->with('error', 'Transaksi tidak ditemukan.');
        }

        $id_anggota = $transaksi->id_anggota;

        $this->db->transStart();

        try {
            // Hapus transaksi utama karena tidak ada lagi detail transaksi
            $this->db->table('transaksi_simpanan')
                ->where('id_simpanan', $id_transaksi)
                ->delete();

            // Jika transaksi dihapus, mungkin perlu update saldo anggota
            $this->updateSaldoAnggota($id_anggota);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menghapus transaksi.");
            }

            return redirect()->to('karyawan/transaksi_simpanan/detail/' . $id_anggota)
                ->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->to('karyawan/transaksi_simpanan/detail/' . $id_anggota)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Fungsi untuk update saldo anggota setelah transaksi dihapus.
     */
    private function updateSaldoAnggota($id_anggota)
    {
        // Hitung saldo terbaru berdasarkan transaksi yang masih ada
        $saldo = $this->db->table('transaksi_simpanan')
            ->where('id_anggota', $id_anggota)
            ->selectSum('saldo_total')
            ->get()
            ->getRow()
            ->saldo_total ?? 0;

        // Update saldo anggota di tabel anggota (jika ada kolom saldo)
        $this->db->table('anggota')
            ->where('id_anggota', $id_anggota)
            ->update(['saldo' => $saldo]);
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

    public function update_field()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Validasi nama field untuk mencegah SQL injection
        $allowed_fields = [
            'setor_sw',
            'tarik_sw',
            'setor_swp',
            'tarik_swp',
            'setor_ss',
            'tarik_ss',
            'setor_sp',
            'tarik_sp'
        ];

        if (!in_array($field, $allowed_fields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid field']);
        }

        // Konversi nilai ke integer
        $value = empty($value) ? 0 : (int) str_replace('.', '', $value);

        $this->db->transStart();

        try {
            // **Update langsung ke `transaksi_simpanan`**
            $this->db->table('transaksi_simpanan')
                ->where('id_simpanan', $id)
                ->update([$field => $value]);

            // Recalculate saldo setelah update
            $this->recalculateSaldo($id);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Failed to update transaction");
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function recalculateSaldo($id_transaksi)
    {
        // Ambil transaksi berdasarkan ID
        $transaksi = $this->transaksiModel->where('id_simpanan', $id_transaksi)->first();

        if (!$transaksi) {
            return;
        }

        // Hitung saldo berdasarkan setor - tarik langsung dari transaksi_simpanan
        $saldo_sw = $transaksi['setor_sw'] - $transaksi['tarik_sw'];
        $saldo_swp = $transaksi['setor_swp'] - $transaksi['tarik_swp'];
        $saldo_ss = $transaksi['setor_ss'] - $transaksi['tarik_ss'];
        $saldo_sp = $transaksi['setor_sp'] - $transaksi['tarik_sp'];

        // Update saldo di transaksi_simpanan
        $this->transaksiModel->update($id_transaksi, [
            'saldo_sw' => $saldo_sw,
            'saldo_swp' => $saldo_swp,
            'saldo_ss' => $saldo_ss,
            'saldo_sp' => $saldo_sp,
            'saldo_total' => $saldo_sw + $saldo_swp + $saldo_ss + $saldo_sp
        ]);
    }

}

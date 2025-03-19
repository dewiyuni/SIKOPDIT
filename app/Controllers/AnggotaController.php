<?php
namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\Controller;
use App\Models\AnggotaModel;
use App\Models\KeuanganModel;
use App\Models\TransaksiSimpananModel;

class AnggotaController extends Controller
{
    protected $anggotaModel;
    protected $authModel;
    protected $transaksiModel;
    protected $keuanganModel;

    public function __construct()
    {
        $this->anggotaModel = new AnggotaModel();
        $this->authModel = new AuthModel();
        $this->transaksiModel = new TransaksiSimpananModel();
        $this->keuanganModel = new KeuanganModel();
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
            'tgl_lahir' => 'required|valid_date',
            'nama_pasangan' => 'required',
            'status' => 'required|in_list[aktif,nonaktif]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // **1️⃣ Simpan Data Anggota**
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

        $this->anggotaModel->insert($dataAnggota);
        $id_anggota = $this->anggotaModel->insertID();

        if (!$id_anggota) {
            return redirect()->back()->with('error', 'Gagal menambahkan anggota.');
        }

        // **2️⃣ Saldo Awal Simpanan**
        $uang_pangkal = 10000;  // Uang Pangkal (sekali saat pendaftaran)
        $simpanan_pokok = 50000; // Simpanan Pokok (sekali saat pendaftaran)

        $setor_sw = 75000;    // Simpanan Wajib
        $setor_swp = 0;       // Simpanan Wajib Penyertaan (hanya di awal)
        $setor_ss = 5000;     // Simpanan Sukarela
        $setor_sp = 10000;    // Simpanan Pokok masuk saldo SP

        $tarik_sw = 0;
        $tarik_swp = 0;
        $tarik_ss = 0;
        $tarik_sp = 0;

        $saldo_total = ($setor_sw + $setor_swp + $setor_ss + $setor_sp) - ($tarik_sw + $tarik_swp + $tarik_ss + $tarik_sp);

        // **3️⃣ Simpan ke `transaksi_simpanan`**
        $this->transaksiModel->insert([
            'id_anggota' => $id_anggota,
            'tanggal' => date('Y-m-d'),
            'setor_sw' => $setor_sw,
            'setor_swp' => $setor_swp,
            'setor_ss' => $setor_ss,
            'setor_sp' => $setor_sp,
            'tarik_sw' => $tarik_sw,
            'tarik_swp' => $tarik_swp,
            'tarik_ss' => $tarik_ss,
            'tarik_sp' => $tarik_sp,
            'saldo_total' => $saldo_total,
            'keterangan' => 'Saldo awal pendaftaran'
        ]);

        // **4️⃣ Catat Uang Pangkal & Simpanan Pokok di `keuangan_koperasi`**
        $this->keuanganModel->insert([
            'id_anggota' => $id_anggota,
            'keterangan' => 'Pembayaran Uang Pangkal',
            'jumlah' => $uang_pangkal,
            'jenis' => 'penerimaan',
            'tanggal' => date('Y-m-d H:i:s')
        ]);

        $this->keuanganModel->insert([
            'id_anggota' => $id_anggota,
            'keterangan' => 'Pembayaran Simpanan Pokok',
            'jumlah' => $simpanan_pokok,
            'jenis' => 'penerimaan',
            'tanggal' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(site_url('admin/anggota'))->with('success', 'Anggota berhasil ditambahkan, Simpanan Pokok & Uang Pangkal tercatat.');
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
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_anggota' => 'required|numeric',
            'nama' => 'required',
            'nik' => 'required|numeric|min_length[16]|max_length[16]|is_unique[anggota.nik,id_anggota,{id_anggota}]',
            'no_ba' => 'required',
            'dusun' => 'required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'tgl_lahir' => 'required|valid_date',
            'nama_pasangan' => 'required',
            'status' => 'required|in_list[aktif,nonaktif]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $id_anggota = $this->request->getPost('id_anggota');

        // ✅ Ambil data inputan kecuali id_anggota
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

        $db = \Config\Database::connect();
        $query = $db->table('anggota')->where('id_anggota', $id_anggota)->update($data);
        return redirect()->to('/admin/anggota')->with('success', 'Anggota berhasil diperbarui.');
    }

    public function hapusAnggota($id)
    {
        $anggotaModel = new \App\Models\AnggotaModel();

        // Periksa apakah anggota dengan ID ini ada
        $anggota = $anggotaModel->find($id);
        if (!$anggota) {
            return redirect()->to('admin/anggota')->with('error', 'Data anggota tidak ditemukan.');
        }

        // Hapus anggota
        $anggotaModel->delete($id);

        return redirect()->to('admin/anggota')->with('success', 'Anggota berhasil dihapus.');
    }

}

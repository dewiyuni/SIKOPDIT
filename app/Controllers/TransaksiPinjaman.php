<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AnggotaModel;
use App\Models\AngsuranModel;
use CodeIgniter\Database\RawSql;
use App\Models\TransaksiPinjamanModel;
use App\Models\TransaksiSimpananModel;


class TransaksiPinjaman extends BaseController
{
    protected $transaksiPinjamanModel;
    protected $transaksiSimpananModel;
    protected $anggotaModel;
    protected $angsuranModel;
    protected $db;

    public function __construct()
    {
        $this->transaksiPinjamanModel = new TransaksiPinjamanModel();
        $this->transaksiSimpananModel = new TransaksiSimpananModel();
        $this->anggotaModel = new AnggotaModel();
        $this->angsuranModel = new AngsuranModel();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        $pinjaman = $this->transaksiPinjamanModel
            ->select('transaksi_pinjaman.*, anggota.nama, anggota.no_ba, 
              (transaksi_pinjaman.jumlah_pinjaman - COALESCE(SUM(angsuran.jumlah_angsuran), 0)) AS saldo_terakhir')
            ->join('anggota', 'anggota.id_anggota = transaksi_pinjaman.id_anggota', 'left')
            ->join('angsuran', 'angsuran.id_pinjaman = transaksi_pinjaman.id_pinjaman', 'left')
            ->groupBy('transaksi_pinjaman.id_pinjaman') // Pastikan data tidak duplikat karena join
            ->findAll();

        foreach ($pinjaman as &$row) {
            $angsuran = $this->angsuranModel->where('id_pinjaman', $row->id_pinjaman)
                ->selectSum(select: 'jumlah_angsuran')
                ->get()
                ->getRow();
            $row->total_angsuran = $angsuran ? $angsuran->jumlah_angsuran : 0;
        }

        return view('karyawan/transaksi_pinjaman/index', ['pinjaman' => $pinjaman]);
    }
    public function tambah()
    {
        // Ambil data anggota untuk ditampilkan di dropdown
        $anggotaModel = new \App\Models\AnggotaModel();
        $data['anggota'] = $anggotaModel->findAll();

        return view('karyawan/transaksi_pinjaman/tambah', $data);
    }
    // Tambahkan di bagian atas
    public function simpan()
    {
        // Validasi input
        if (
            !$this->validate([
                'id_anggota' => 'required|integer',
                'jumlah_pinjaman' => 'required|greater_than[0]',
                'jangka_waktu' => 'required|integer',
                'jaminan' => 'required'
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Pastikan semua data terisi dengan benar.');
        }

        // Ambil data dari form
        $id_anggota = $this->request->getPost('id_anggota');
        $jumlah_pinjaman = $this->request->getPost('jumlah_pinjaman');
        $jangka_waktu = $this->request->getPost('jangka_waktu');
        $bunga = $this->request->getPost('bunga');
        $jaminan = $this->request->getPost('jaminan');

        // Periksa apakah anggota ada
        if (!$this->anggotaModel->find($id_anggota)) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan.');
        }

        // Periksa apakah jumlah pinjaman valid
        if ($jumlah_pinjaman <= 0) {
            return redirect()->back()->with('error', 'Jumlah pinjaman harus lebih dari 0.');
        }

        // Mulai transaksi
        $this->db->transStart();

        try {
            // Simpan pinjaman
            $this->transaksiPinjamanModel->insert([
                'id_anggota' => $id_anggota,
                'tanggal_pinjaman' => date('Y-m-d H:i:s'),
                'jumlah_pinjaman' => $jumlah_pinjaman,
                'jangka_waktu' => $jangka_waktu,
                'jaminan' => $jaminan,
                'status' => 'berjalan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Perhitungan SWP (Simpanan Wajib Pinjaman)
            $swp = $jumlah_pinjaman * 0.02;

            // Pastikan model transaksi simpanan sudah dideklarasikan sebelumnya
            if (method_exists($this->transaksiSimpananModel, 'updateSaldoSWP')) {
                $this->transaksiSimpananModel->updateSaldoSWP($id_anggota, $swp);
            } else {
                throw new \Exception("Method updateSaldoSWP tidak ditemukan.");
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan data pinjaman.");
            }

            return redirect()->to('/karyawan/transaksi_pinjaman/')->with('success', 'Pinjaman berhasil ditambahkan.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error saat menyimpan pinjaman: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function edit($id_angsuran)
    {
        $pinjamanModel = new transaksiPinjamanModel();
        $angsuranModel = new AngsuranModel();

        // Cari data angsuran berdasarkan id_angsuran
        $angsuran = $angsuranModel->find($id_angsuran);
        if (!$angsuran) {
            return redirect()->to('/karyawan/transaksi_pinjaman')->with('error', 'Angsuran tidak ditemukan.');
        }

        // Cari pinjaman berdasarkan id_pinjaman dari angsuran yang dipilih
        $data['pinjaman'] = $pinjamanModel->find($angsuran->id_pinjaman);
        $data['angsuran'] = $angsuran; // Hanya kirim satu angsuran, bukan findAll()

        if (!$data['pinjaman']) {
            return redirect()->to('/karyawan/transaksi_pinjaman/detail')->with('error', 'Pinjaman tidak ditemukan.');
        }

        return view('karyawan/transaksi_pinjaman/edit', $data);
    }

    public function update($id_angsuran)
    {
        $angsuranModel = new AngsuranModel();

        // Pastikan id_angsuran valid
        $angsuran = $angsuranModel->find($id_angsuran);
        if (!$angsuran) {
            return redirect()->back()->with('error', 'Angsuran tidak ditemukan.');
        }

        $data = [
            'tanggal_angsuran' => $this->request->getPost('tanggal_angsuran'),
            'jumlah_angsuran' => $this->request->getPost('jumlah_angsuran'),
        ];

        // Update hanya jika data tidak kosong
        if (!empty($data['tanggal_angsuran']) && !empty($data['jumlah_angsuran'])) {
            $angsuranModel->update($id_angsuran, $data);
            return redirect()->to('/karyawan/transaksi_pinjaman/detail/' . $angsuran->id_pinjaman)
                ->with('success', 'Data angsuran berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Data tidak valid atau kosong.');
        }
    }


    public function delete($id_angsuran)
    {
        $id_angsuran = (int) $id_angsuran; // Konversi ke integer

        // Cek apakah angsuran ada dalam database
        $angsuran = $this->angsuranModel->find($id_angsuran);
        if (!$angsuran) {
            return redirect()->to('karyawan/transaksi_pinjaman')->with('error', 'Data angsuran tidak ditemukan.');
        }

        // Mulai transaksi database
        $this->db->transStart();

        try {
            // Ambil data pinjaman terkait
            $pinjaman = $this->transaksiPinjamanModel->find($angsuran->id_pinjaman);
            if (!$pinjaman) {
                throw new \Exception("Pinjaman terkait tidak ditemukan.");
            }

            // Update saldo pinjaman setelah angsuran dihapus
            $this->transaksiPinjamanModel->update($pinjaman->id_pinjaman, [
                'saldo_terakhir' => $pinjaman->saldo_terakhir + $angsuran->jumlah_angsuran
            ]);

            // Hapus data angsuran
            $this->angsuranModel->delete($id_angsuran);

            // Selesaikan transaksi
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menghapus angsuran.");
            }

            return redirect()->to('karyawan/transaksi_pinjaman')->with('success', 'Angsuran berhasil dihapus.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->to('karyawan/transaksi_pinjaman')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function detail($id)
    {
        $model = new TransaksiPinjamanModel();

        try {
            // Ambil data pinjaman berdasarkan ID
            $pinjaman = $model->getDataById($id);

            // Jika data pinjaman tidak ditemukan, tampilkan error 404
            if (!$pinjaman) {
                return redirect()->to('karyawan/transaksi_pinjaman')->with('error', 'Data pinjaman tidak ditemukan.');
            }

            // Ambil data angsuran berdasarkan ID pinjaman
            $angsuran = $model->getAngsuranByPinjaman($id);

            // Jika tidak ada data angsuran
            if (!$angsuran) {
                return redirect()->to('karyawan/transaksi_pinjaman')->with('error', 'Tidak ada riwayat angsuran untuk pinjaman ini.');
            }

            // Kirim data ke tampilan
            return view('karyawan/transaksi_pinjaman/detail', [
                'pinjaman' => $pinjaman,
                'angsuran' => $angsuran
            ]);

        } catch (\Exception $e) {
            // Tangani kesalahan dan tampilkan pesan error
            return redirect()->to('karyawan/transaksi_pinjaman')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tambahAngsuran($id_pinjaman)
    {
        // Memuat model
        $db = \Config\Database::connect();

        // Ambil data pinjaman berdasarkan ID
        $builder = $db->table('transaksi_pinjaman');
        $builder->select('transaksi_pinjaman.*, anggota.nama');
        $builder->join('anggota', 'anggota.id_anggota = transaksi_pinjaman.id_anggota');
        $builder->where('transaksi_pinjaman.id_pinjaman', $id_pinjaman);
        $pinjaman = $builder->get()->getRow();

        // Validasi jika data pinjaman tidak ditemukan
        if (!$pinjaman) {
            return redirect()->to('/karyawan/transaksi_pinjaman')->with('error', 'Pinjaman tidak ditemukan');
        }

        // Hitung sisa pinjaman (misalnya total pinjaman - total angsuran)
        $totalAngsuranBuilder = $db->table('angsuran');
        $totalAngsuranBuilder->selectSum('jumlah_angsuran');
        $totalAngsuranBuilder->where('id_pinjaman', $id_pinjaman);
        $totalAngsuran = $totalAngsuranBuilder->get()->getRow();

        $sisaPinjaman = $pinjaman->jumlah_pinjaman - ($totalAngsuran->jumlah_angsuran ?? 0);

        // Kirim data pinjaman dan sisa pinjaman ke view
        return view('karyawan/transaksi_pinjaman/tambah_angsuran', [
            'pinjaman' => $pinjaman,
            'sisa_pinjaman' => $sisaPinjaman
        ]);
    }
    public function simpanAngsuran()
    {
        // Validasi input
        if (
            !$this->validate([
                'id_pinjaman' => 'required|integer',
                'tanggal_angsuran' => 'required|valid_date[Y-m-d]',
                'jumlah_angsuran' => 'required|greater_than[0]',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Pastikan semua data diisi dengan benar.');
        }

        // Ambil data dari form
        $idPinjaman = $this->request->getPost('id_pinjaman');
        $tanggalAngsuran = $this->request->getPost('tanggal_angsuran');
        $jumlahAngsuran = (float) $this->request->getPost('jumlah_angsuran');

        // Cek apakah pinjaman valid
        $pinjaman = $this->transaksiPinjamanModel->find($idPinjaman);
        if (!$pinjaman) {
            return redirect()->back()->with('error', 'Pinjaman tidak ditemukan.');
        }

        // Hitung total angsuran yang sudah dibayar
        $totalAngsuran = (float) $this->angsuranModel
            ->where('id_pinjaman', $idPinjaman)
            ->selectSum('jumlah_angsuran')
            ->first()->jumlah_angsuran ?? 0;

        $sisaPinjaman = $pinjaman->jumlah_pinjaman - $totalAngsuran;

        if ($jumlahAngsuran > $sisaPinjaman) {
            return redirect()->back()->with('error', 'Jumlah angsuran melebihi sisa pinjaman.');
        }

        // Mulai transaksi database
        $this->db->transStart();
        try {
            // Simpan angsuran baru
            $this->angsuranModel->insert([
                'id_pinjaman' => $idPinjaman,
                'tanggal_angsuran' => $tanggalAngsuran,
                'jumlah_angsuran' => $jumlahAngsuran,
                'sisa_pinjaman' => $sisaPinjaman - $jumlahAngsuran,
                'status' => ($sisaPinjaman - $jumlahAngsuran) == 0 ? 'Lunas' : 'Berjalan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // **UPDATE saldo di `transaksi_pinjaman`**
            $this->transaksiPinjamanModel->update($idPinjaman, [
                'status' => ($sisaPinjaman - $jumlahAngsuran) == 0 ? 'Lunas' : 'Berjalan',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan angsuran.");
            }

            return redirect()->to('/karyawan/transaksi_pinjaman')->with('success', 'Angsuran berhasil ditambahkan.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error saat menyimpan angsuran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}

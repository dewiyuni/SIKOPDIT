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
        // First, get all loans with basic information
        $pinjaman = $this->transaksiPinjamanModel
            ->select('transaksi_pinjaman.*, anggota.nama, anggota.no_ba')
            ->join('anggota', 'anggota.id_anggota = transaksi_pinjaman.id_anggota', 'left')
            ->findAll();

        // For each loan, get the latest payment record to determine the current status
        foreach ($pinjaman as &$row) {
            // Get the latest payment record for this loan
            $latestPayment = $this->angsuranModel
                ->where('id_pinjaman', $row->id_pinjaman)
                ->orderBy('id_angsuran', 'DESC') // Get the most recent payment
                ->first();

            if ($latestPayment) {
                // If there's a payment record, use its sisa_pinjaman value
                $row->saldo_terakhir = $latestPayment->sisa_pinjaman;
                $row->status_pembayaran = $latestPayment->status;
            } else {
                // If no payment records, the remaining balance is the full loan amount
                $row->saldo_terakhir = $row->jumlah_pinjaman;
                $row->status_pembayaran = 'belum bayar';
            }

            // Calculate total payments made
            $totalPayments = $this->angsuranModel
                ->selectSum('jumlah_angsuran')
                ->where('id_pinjaman', $row->id_pinjaman)
                ->get()
                ->getRow();

            $row->total_angsuran = $totalPayments ? $totalPayments->jumlah_angsuran : 0;
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
                'jaminan' => 'permit_empty' // Make jaminan optional
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Pastikan semua data terisi dengan benar.');
        }

        // Ambil data dari form
        $id_anggota = $this->request->getPost('id_anggota');
        $jumlah_pinjaman = $this->request->getPost('jumlah_pinjaman');
        $jangka_waktu = $this->request->getPost('jangka_waktu');
        $jaminan = $this->request->getPost('jaminan') ?: 'Tidak ada'; // Default to 'Tidak ada' if not provided
        $tanggal_pinjaman = $this->request->getPost('tanggal_pinjaman') ?: date('Y-m-d');

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
            $data_pinjaman = [
                'id_anggota' => $id_anggota,
                'tanggal_pinjaman' => $tanggal_pinjaman,
                'jumlah_pinjaman' => $jumlah_pinjaman,
                'jangka_waktu' => $jangka_waktu,
                'jaminan' => $jaminan,
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert pinjaman
            $this->transaksiPinjamanModel->insert($data_pinjaman);

            // Get the ID of the newly inserted loan
            $id_pinjaman = $this->transaksiPinjamanModel->getInsertID();

            if (!$id_pinjaman) {
                throw new \Exception("Gagal mendapatkan ID pinjaman.");
            }

            // Perhitungan SWP (Simpanan Wajib Pinjaman)
            $swp = $jumlah_pinjaman * 0.025;

            // Update saldo SWP anggota dengan ID pinjaman
            $this->transaksiSimpananModel->updateSaldoSWP($id_anggota, $swp, $id_pinjaman);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan data pinjaman.");
            }

            return redirect()->to('/karyawan/transaksi_pinjaman/')->with('message', 'Pinjaman berhasil ditambahkan.');
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

        // Ambil data dari form
        $data = [
            'tanggal_angsuran' => $this->request->getPost('tanggal_angsuran'),
            'jumlah_angsuran' => $this->request->getPost('jumlah_angsuran'),
            'bunga' => str_replace(',', '.', $this->request->getPost('bunga')) // Ensure correct decimal format
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
        $id_angsuran = (int) $id_angsuran; // Convert to integer

        // Check if the installment exists in the database
        $angsuran = $this->angsuranModel->find($id_angsuran);
        if (!$angsuran) {
            return redirect()->to('karyawan/transaksi_pinjaman')->with('error', 'Data angsuran tidak ditemukan.');
        }

        // Get the related loan ID
        $id_pinjaman = $angsuran->id_pinjaman;

        // Delete the installment data
        $this->angsuranModel->delete($id_angsuran);

        // Redirect to the loan detail page after deletion
        return redirect()->to('karyawan/transaksi_pinjaman/detail/' . $id_pinjaman)
            ->with('success', 'Angsuran berhasil dihapus.');
    }
    public function detail($id)
    {
        // Initialize models
        $pinjamanModel = new TransaksiPinjamanModel();
        $anggotaModel = new AnggotaModel();
        $angsuranModel = new AngsuranModel();

        // Get loan data with JOIN to get member information in one query
        $pinjaman = $pinjamanModel
            ->select('transaksi_pinjaman.*, anggota.nama, anggota.no_ba, anggota.nik, anggota.alamat')
            ->join('anggota', 'anggota.id_anggota = transaksi_pinjaman.id_anggota')
            ->find($id);

        if (!$pinjaman) {
            return redirect()->to('/karyawan/transaksi_pinjaman')->with('error', 'Data pinjaman tidak ditemukan');
        }

        // Get installment data ordered by date
        $angsuran = $angsuranModel
            ->where('id_pinjaman', $id)
            ->orderBy('tanggal_angsuran', 'ASC')
            ->findAll();

        // Calculate loan statistics
        $totalAngsuran = 0;
        $totalBunga = 0;

        foreach ($angsuran as $row) {
            $totalAngsuran += $row->jumlah_angsuran;

            // Calculate interest amount for each installment using the interest rate from the database
            // Interest is calculated as a percentage of the total loan amount
            $jumlahBunga = ($row->bunga / 100) * $pinjaman->jumlah_pinjaman;
            $totalBunga += $jumlahBunga;
        }

        // Calculate remaining balance
        $sisaPinjaman = $pinjaman->jumlah_pinjaman - $totalAngsuran;
        $sisaPinjaman = max(0, $sisaPinjaman); // Ensure it's not negative

        // Calculate payment percentage
        $persentaseLunas = 0;
        if ($pinjaman->jumlah_pinjaman > 0) {
            $persentaseLunas = min(100, round(($totalAngsuran / $pinjaman->jumlah_pinjaman) * 100));
        }

        // Get the interest rate from the first installment (if available)
        $bungaPerbulan = 2; // Default value
        if (!empty($angsuran)) {
            $bungaPerbulan = $angsuran[0]->bunga;
        }

        // Calculate fixed interest amount per installment
        $totalBungaAwal = ($bungaPerbulan / 100) * $pinjaman->jumlah_pinjaman;

        // Calculate monthly installment amount (principal only)
        $angsuranPerBulan = 0;
        if ($pinjaman->jangka_waktu > 0) {
            $angsuranPerBulan = $pinjaman->jumlah_pinjaman / $pinjaman->jangka_waktu;
        }

        $data = [
            'pinjaman' => $pinjaman,
            'angsuran' => $angsuran,
            'totalAngsuran' => $totalAngsuran,
            'totalBunga' => $totalBunga,
            'sisaPinjaman' => $sisaPinjaman,
            'persentaseLunas' => $persentaseLunas,
            'bungaPerbulan' => $bungaPerbulan,
            'totalBungaAwal' => $totalBungaAwal,
            'angsuranPerBulan' => $angsuranPerBulan,
            'title' => 'Detail Pinjaman'
        ];

        // Return the view with the correct path
        return view('karyawan/transaksi_pinjaman/detail', $data);
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
        $id_pinjaman = $this->request->getPost('id_pinjaman');
        $tanggal_angsuran = $this->request->getPost('tanggal_angsuran');
        $jumlah_angsuran = str_replace('.', '', $this->request->getPost('jumlah_angsuran')); // Hilangkan pemisah ribuan

        // Process the bunga value correctly
        $bunga = $this->request->getPost('bunga');
        // Remove any % sign if present and replace comma with dot for decimal
        $bunga = str_replace(['%', ','], ['', '.'], $bunga);
        // Convert to float to ensure proper decimal handling
        $bunga = (float) $bunga;

        // Ambil data pinjaman berdasarkan ID
        $pinjaman = $this->transaksiPinjamanModel->where('id_pinjaman', $id_pinjaman)->first();
        if (!$pinjaman) {
            return redirect()->back()->with('error', 'Pinjaman tidak ditemukan.');
        }

        // Hitung total angsuran sebelumnya
        $totalAngsuran = $this->angsuranModel
            ->where('id_pinjaman', $id_pinjaman)
            ->selectSum('jumlah_angsuran')
            ->get()
            ->getRow()
            ->jumlah_angsuran ?? 0;

        // Hitung sisa pinjaman setelah angsuran baru
        $sisa_pinjaman = $pinjaman->jumlah_pinjaman - $totalAngsuran - $jumlah_angsuran;

        // Tentukan status pinjaman
        $status = ($sisa_pinjaman <= 0) ? 'lunas' : 'belum lunas';

        // Simpan angsuran ke database (termasuk sisa pinjaman & bunga)
        $this->angsuranModel->insert([
            'id_pinjaman' => $id_pinjaman,
            'tanggal_angsuran' => $tanggal_angsuran,
            'jumlah_angsuran' => $jumlah_angsuran,
            'bunga' => $bunga, // Now properly formatted for decimal storage
            'sisa_pinjaman' => max(0, $sisa_pinjaman), // Jangan sampai negatif
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update status pinjaman jika lunas
        if ($status == 'lunas') {
            $this->transaksiPinjamanModel->update($id_pinjaman, ['status' => 'lunas']);
        }

        // Redirect back to the detail page with a success message
        return redirect()->to('karyawan/transaksi_pinjaman/detail/' . $id_pinjaman)->with('message', 'Angsuran berhasil ditambahkan.');
    }


}

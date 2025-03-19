<?php

namespace App\Models;

use CodeIgniter\Model;

class AngsuranModel extends Model
{
    protected $table = 'angsuran';
    protected $primaryKey = 'id_angsuran';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_pinjaman',
        'tanggal_angsuran',
        'bunga',
        'jumlah_angsuran',
        'sisa_pinjaman',
        'status',
        'created_at',
        'updated_at'
    ];

    public function simpanAngsuran($data)
    {
        $pinjamanModel = model('TransaksiPinjamanModel');
        $pinjaman = $pinjamanModel->find($data['id_pinjaman']);

        if (!$pinjaman) {
            throw new \Exception("Pinjaman tidak ditemukan.");
        }

        // Hitung total angsuran sebelumnya
        $totalAngsuran = $this->selectSum('jumlah_angsuran')
            ->where('id_pinjaman', $data['id_pinjaman'])
            ->get()
            ->getRow()->jumlah_angsuran ?? 0;

        // Hitung sisa pinjaman
        $sisaPinjaman = $pinjaman->jumlah_pinjaman - $totalAngsuran;
        $data['sisa_pinjaman'] = max(0, $sisaPinjaman - $data['jumlah_angsuran']);

        // Update status angsuran
        $data['status'] = ($data['sisa_pinjaman'] <= 0) ? 'lunas' : 'belum lunas';

        // Simpan angsuran ke database
        $this->save($data);

        // Perbarui transaksi pinjaman
        $pinjamanModel->update($data['id_pinjaman'], ['sisa_pinjaman' => $data['sisa_pinjaman']]);
    }
}

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
        'jumlah_angsuran',
        'sisa_pinjaman',
        'status',
        'created_at',
        'updated_at'
    ];

    public function simpanAngsuran($data)
    {
        // Menghitung sisa pinjaman yang tersisa
        $pinjaman = model('TransaksiPinjamanModel')->find($data['id_pinjaman']);
        $totalAngsuran = $this->selectSum('jumlah_angsuran')
            ->where('id_pinjaman', $data['id_pinjaman'])
            ->get()
            ->getRow();

        // Jika tidak ada angsuran sebelumnya, anggap seluruh pinjaman adalah saldo awal
        $sisaPinjaman = $pinjaman->jumlah_pinjaman - ($totalAngsuran->jumlah_angsuran ?? 0);

        // Update sisa pinjaman
        $data['sisa_pinjaman'] = $sisaPinjaman - $data['jumlah_angsuran'];

        // Insert data angsuran ke dalam database
        $this->save($data);

        // Perbarui transaksi pinjaman dengan sisa pinjaman yang baru
        model('TransaksiPinjamanModel')->update($data['id_pinjaman'], ['sisa_pinjaman' => $data['sisa_pinjaman']]);
    }
}

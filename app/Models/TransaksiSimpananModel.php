<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiSimpananModel extends Model
{
    protected $table = 'transaksi_simpanan';
    protected $primaryKey = 'id_simpanan';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'id_anggota',
        'id_pinjaman',
        'tanggal',
        'setor_sw',
        'tarik_sw',
        'setor_swp',
        'tarik_swp',
        'setor_ss',
        'tarik_ss',
        'setor_sp',
        'tarik_sp',
        'created_at',
        'updated_at'
    ];

    public function hitungSaldo($id_anggota)
    {
        return $this->db->table($this->table)
            ->select('id_anggota, 
        COALESCE(SUM(setor_sw) - SUM(tarik_sw), 0) AS saldo_sw, 
        COALESCE(SUM(setor_swp) - SUM(tarik_swp), 0) AS saldo_swp, 
        COALESCE(SUM(setor_ss) - SUM(tarik_ss), 0) AS saldo_ss, 
        COALESCE(SUM(setor_sp) - SUM(tarik_sp), 10000) AS saldo_sp, 
        COALESCE(
            (SUM(setor_sw) + SUM(setor_swp) + SUM(setor_ss) + SUM(setor_sp)) - 
            (SUM(tarik_sw) + SUM(tarik_swp) + SUM(tarik_ss) + SUM(tarik_sp)), 
            10000
        ) AS saldo_total')

            ->where('id_anggota', $id_anggota)
            ->groupBy('id_anggota')
            ->get()
            ->getRow();
    }


    public function simpanTransaksi($data)
    {
        $this->db->transStart();

        // Simpan transaksi baru
        $this->insert($data);

        // Hitung saldo terbaru
        $saldoBaru = $this->hitungSaldo($data['id_anggota']);

        // Pastikan saldo minimal 10.000 (batasan dari sistem)
        $saldoTotal = max(10000, $saldoBaru->saldo_total);

        // Update saldo di tabel anggota
        $this->db->table('anggota')
            ->where('id_anggota', $data['id_anggota'])
            ->update(['saldo_total' => $saldoTotal]);

        return $this->db->transComplete();
    }


    public function getTransaksiWithAnggota()
    {
        return $this->db->table('transaksi_simpanan')
            ->select('transaksi_simpanan.*, anggota.nama, anggota.no_ba, 
                  COALESCE(transaksi_simpanan.saldo_sp, 10000) AS saldo_sp') // Default Rp10.000 jika NULL
            ->join('anggota', 'anggota.id_anggota = transaksi_simpanan.id_anggota', 'left')
            ->orderBy('transaksi_simpanan.tanggal', 'DESC')
            ->get()
            ->getResult();
    }

    public function getLastSaldo($id_anggota)
    {
        $lastTransaksi = $this->where('id_anggota', $id_anggota)
            ->orderBy('tanggal', 'DESC')
            ->first();

        if (!$lastTransaksi) {
            // Jika tidak ada transaksi, kembalikan nilai default
            return [
                'saldo_sw' => 0,
                'saldo_swp' => 0,
                'saldo_ss' => 0,
                'saldo_sp' => 10000, // Default SP untuk anggota baru
            ];
        }

        // Pastikan setiap properti ada sebelum diakses
        return [
            'saldo_sw' => isset($lastTransaksi->setor_sw, $lastTransaksi->tarik_sw) ? ($lastTransaksi->setor_sw - $lastTransaksi->tarik_sw) : 0,
            'saldo_swp' => isset($lastTransaksi->setor_swp, $lastTransaksi->tarik_swp) ? ($lastTransaksi->setor_swp - $lastTransaksi->tarik_swp) : 0,
            'saldo_ss' => isset($lastTransaksi->setor_ss, $lastTransaksi->tarik_ss) ? ($lastTransaksi->setor_ss - $lastTransaksi->tarik_ss) : 0,
            'saldo_sp' => isset($lastTransaksi->setor_sp, $lastTransaksi->tarik_sp) ? ($lastTransaksi->setor_sp - $lastTransaksi->tarik_sp) : 10000,
        ];
    }



    public function getTransaksiByAnggota($id_anggota)
    {
        return $this->where('id_anggota', $id_anggota)
            ->orderBy('tanggal', 'DESC')
            ->findAll();
    }
    public function getLatestTransaksiPerAnggota()
    {
        return $this->db->table('anggota a')
            ->select('a.nama, a.no_ba, a.id_anggota, 
                COALESCE(SUM(ts.setor_sw - ts.tarik_sw), 0) AS saldo_sw, 
                COALESCE(SUM(ts.setor_swp - ts.tarik_swp), 0) AS saldo_swp, 
                COALESCE(SUM(ts.setor_ss - ts.tarik_ss), 0) AS saldo_ss, 
                COALESCE(SUM(ts.setor_sp - ts.tarik_sp), 0) AS saldo_sp,
                (COALESCE(SUM(ts.setor_sw - ts.tarik_sw), 0) + 
                 COALESCE(SUM(ts.setor_swp - ts.tarik_swp), 0) + 
                 COALESCE(SUM(ts.setor_ss - ts.tarik_ss), 0) + 
                 COALESCE(SUM(ts.setor_sp - ts.tarik_sp), 0)) AS saldo_total'
            )
            ->join('transaksi_simpanan ts', 'a.id_anggota = ts.id_anggota', 'left')
            ->groupBy('a.id_anggota')
            ->get()
            ->getResult();
    }


    public function getTransaksiWithSaldoSP()
    {
        return $this->db->table('transaksi_simpanan')
            ->select('transaksi_simpanan.*, anggota.nama AS nama_anggota, anggota.no_ba')
            ->join('anggota', 'anggota.id_anggota = transaksi_simpanan.id_anggota', 'left')
            ->get()
            ->getResult();
    }
    public function updateSaldoSWP($id_anggota, $swp)
    {
        // Ambil total saldo terbaru anggota
        $totalSaldo = $this->db->table('transaksi_simpanan')
            ->select('
                SUM(setor_sw - tarik_sw) AS saldo_sw,
                SUM(setor_swp - tarik_swp) AS saldo_swp,
                SUM(setor_ss - tarik_ss) AS saldo_ss,
                SUM(setor_sp - tarik_sp) AS saldo_sp
            ')
            ->where('id_anggota', $id_anggota)
            ->get()
            ->getRow();

        // Hitung saldo total dari seluruh jenis simpanan
        $saldo_total = ($totalSaldo->saldo_sw + $totalSaldo->saldo_swp + $totalSaldo->saldo_ss + $totalSaldo->saldo_sp) + $swp;

        // Simpan transaksi baru
        $this->insert([
            'id_anggota' => $id_anggota,
            'tanggal' => date('Y-m-d'),
            'setor_swp' => $swp
        ]);
    }


    // ====== dashboard total simpanan =========
    public function getTotalSimpanan()
    {
        $query = $this->db->table('transaksi_simpanan AS t1')
            ->select('SUM(
            (t1.setor_sw + t1.setor_swp + t1.setor_ss + t1.setor_sp) - 
            (t1.tarik_sw + t1.tarik_swp + t1.tarik_ss + t1.tarik_sp)
        ) AS total_saldo')
            ->get();

        $result = $query->getRow();

        return $result->total_saldo ?? 0;
    }

}

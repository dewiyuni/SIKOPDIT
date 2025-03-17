<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiSimpananModel extends Model
{
    protected $table = 'transaksi_simpanan';
    protected $primaryKey = 'id_transaksi_simpanan';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'id_anggota',
        'tanggal',
        'saldo_sw',
        'saldo_swp',
        'saldo_ss',
        'saldo_sp',
        'saldo_total',
        'keterangan',
        'created_at',
        'updated_at'
    ];

    public function hitungSaldo($id_anggota)
    {
        return $this->select('
            id_anggota,
            SUM(CASE WHEN id_jenis_simpanan = 1 THEN setor - tarik ELSE 0 END) AS saldo_sw,
            SUM(CASE WHEN id_jenis_simpanan = 2 THEN setor - tarik ELSE 0 END) AS saldo_swp,
            SUM(CASE WHEN id_jenis_simpanan = 3 THEN setor - tarik ELSE 0 END) AS saldo_ss,
            SUM(CASE WHEN id_jenis_simpanan = 4 THEN setor - tarik ELSE 0 END) AS saldo_sp
        ')
            ->where('id_anggota', $id_anggota)
            ->groupBy('id_anggota')
            ->first();
    }

    public function simpanTransaksi($data)
    {
        $idAnggota = $data['id_anggota'];
        $tanggal = $data['tanggal'];

        // Cek apakah transaksi sudah ada di tanggal yang sama
        $transaksiExist = $this->where('id_anggota', $idAnggota)->where('tanggal', $tanggal)->first();

        // Ambil saldo terakhir
        $saldoSebelumnya = $this->select('saldo_sw, saldo_swp, saldo_ss, saldo_total')
            ->where('id_anggota', $idAnggota)
            ->orderBy('created_at', 'DESC')
            ->first();

        $saldoSebelumnya = $saldoSebelumnya ? (object) $saldoSebelumnya : (object) [
            'saldo_sw' => 0,
            'saldo_swp' => 0,
            'saldo_ss' => 0,
            'saldo_total' => 10000 // Minimal saldo total
        ];

        // Validasi sebelum perhitungan
        if (!empty($data['tarik_sw']) || !empty($data['tarik_swp'])) {
            return ['error' => 'Penarikan hanya diperbolehkan untuk Simpanan Sukarela (SS).'];
        }

        if ($saldoSebelumnya->saldo_ss < ($data['tarik_ss'] ?? 0)) {
            return ['error' => 'Saldo Simpanan Sukarela (SS) tidak mencukupi untuk penarikan.'];
        }

        // Hitung saldo baru (untuk setor dan tarik)
        $saldoBaru = [
            'saldo_sw' => max(0, $saldoSebelumnya->saldo_sw + ($data['setor_sw'] ?? 0) - ($data['tarik_sw'] ?? 0)),
            'saldo_swp' => max(0, $saldoSebelumnya->saldo_swp + ($data['setor_swp'] ?? 0) - ($data['tarik_swp'] ?? 0)),
            'saldo_ss' => max(0, $saldoSebelumnya->saldo_ss + ($data['setor_ss'] ?? 0) - ($data['tarik_ss'] ?? 0)),
            'saldo_total' => max(10000, ($saldoSebelumnya->saldo_total +
                ($data['setor_sw'] ?? 0) + ($data['setor_swp'] ?? 0) + ($data['setor_ss'] ?? 0) -
                ($data['tarik_sw'] ?? 0) - ($data['tarik_swp'] ?? 0) - ($data['tarik_ss'] ?? 0)))
        ];

        // Pastikan saldo total tidak kurang dari Rp10.000 setelah transaksi
        if ($saldoBaru['saldo_total'] < 10000) {
            return ['error' => 'Saldo total tidak boleh kurang dari Rp10.000'];
        }

        // Data transaksi utama
        $transaksiData = [
            'id_anggota' => $idAnggota,
            'tanggal' => $tanggal,
            'saldo_sw' => $saldoBaru['saldo_sw'],
            'saldo_swp' => $saldoBaru['saldo_swp'],
            'saldo_ss' => $saldoBaru['saldo_ss'],
            'saldo_total' => $saldoBaru['saldo_total'],
            'keterangan' => $data['keterangan'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($transaksiExist) {
            $this->update($transaksiExist->id_transaksi_simpanan, $transaksiData);
            $idTransaksi = $transaksiExist->id_transaksi_simpanan;
        } else {
            $transaksiData['created_at'] = date('Y-m-d H:i:s');
            $this->insert($transaksiData);
            $idTransaksi = $this->insertID();

            if (!$idTransaksi) {
                return ['error' => 'Gagal menyimpan transaksi.'];
            }
        }

        // Simpan detail transaksi
        $jenisSimpanan = [1 => 'sw', 2 => 'swp', 3 => 'ss'];

        foreach ($jenisSimpanan as $idJenis => $field) {
            if (!empty($data['setor_' . $field]) || !empty($data['tarik_' . $field])) {
                $detailExist = $this->db->table('transaksi_simpanan_detail')
                    ->where('id_transaksi_simpanan', $idTransaksi)
                    ->where('id_jenis_simpanan', $idJenis)
                    ->countAllResults();

                if ($detailExist == 0) {
                    $this->db->table('transaksi_simpanan_detail')->insert([
                        'id_transaksi_simpanan' => $idTransaksi,
                        'id_jenis_simpanan' => $idJenis,
                        'setor' => $data['setor_' . $field] ?? 0,
                        'tarik' => $data['tarik_' . $field] ?? 0,
                        'saldo_akhir' => $saldoBaru['saldo_' . $field],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        return true; // Kembalikan `true` jika berhasil
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

        return [
            'saldo_sw' => $lastTransaksi ? $lastTransaksi->saldo_sw : 0,
            'saldo_swp' => $lastTransaksi ? $lastTransaksi->saldo_swp : 0,
            'saldo_ss' => $lastTransaksi ? $lastTransaksi->saldo_ss : 0,
            'saldo_sp' => $lastTransaksi ? $lastTransaksi->saldo_sp : 10000, // Default SP untuk anggota baru
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
        return $this->db->table('transaksi_simpanan t1')
            ->select('a.nama, a.no_ba, 
                      t1.saldo_sw, t1.saldo_swp, t1.saldo_ss, t1.saldo_sp,
                      (t1.saldo_sw + t1.saldo_swp + t1.saldo_ss + t1.saldo_sp) AS saldo_total,
                      a.id_anggota')
            ->join('anggota a', 'a.id_anggota = t1.id_anggota', 'left')
            ->join(
                '(SELECT id_anggota, MAX(updated_at) AS max_date FROM transaksi_simpanan GROUP BY id_anggota) t2',
                't1.id_anggota = t2.id_anggota AND t1.updated_at = t2.max_date',
                'inner'
            )
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
        $transaksi = $this->where('id_anggota', $id_anggota)->orderBy('id_transaksi_simpanan', 'DESC')->first();
        if ($transaksi) {
            $this->update($transaksi->id_transaksi_simpanan, [
                'saldo_swp' => $transaksi->saldo_swp + $swp,
                'saldo_total' => $transaksi->saldo_total + $swp
            ]);
        } else {
            // Jika tidak ada transaksi sebelumnya, buat baru
            $this->insert([
                'id_anggota' => $id_anggota,
                'tanggal' => date('d-m-Y'),
                'saldo_sw' => 0,
                'saldo_swp' => $swp,
                'saldo_ss' => 0,
                'saldo_total' => $swp
            ]);
        }
    }

    // ====== dashboard total simpanan =========
    public function getTotalSimpanan()
    {
        $query = $this->db->table('transaksi_simpanan AS ts')
            ->select('SUM(ts.saldo_total) AS total_saldo')
            ->where('ts.id_transaksi_simpanan IN (
                SELECT MAX(id_transaksi_simpanan) 
                FROM transaksi_simpanan 
                GROUP BY id_anggota
            )')
            ->get();

        $result = $query->getRow();

        return $result->total_saldo ?? 0;
    }

}

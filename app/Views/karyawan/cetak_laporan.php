<!DOCTYPE html>
<html>

<head>
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center;">Laporan Transaksi Simpanan dan Pinjaman</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Transaksi</th>
                <th>Nama Anggota</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan)): ?>
                <?php $no = 1; ?>
                <?php foreach ($laporan as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($row->jenis_transaksi) ?></td>
                        <td><?= esc($row->nama_anggota) ?></td>
                        <td><?= date('d M Y', strtotime($row->tanggal_transaksi)) ?></td>
                        <td>Rp <?= number_format($row->jumlah, 0, ',', '.') ?></td>
                        <td><?= esc($row->keterangan) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Belum ada data transaksi</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
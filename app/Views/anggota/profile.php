<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container mt-4">
    <h3 class="mb-3"><?= esc($title); ?></h3>

    <div class="card shadow-sm p-3">
        <!-- Bagian atas: Foto statis & Nama -->
        <div class="text-center mb-4">
            <i class="bi bi-person-circle" style="font-size: 120px; color: #6c757d;"></i>
            <h5 class="mt-2"><?= esc($anggota->nama); ?></h5>
        </div>

        <!-- Bagian bawah: Detail 2 kolom -->
        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>NIK</strong></td>
                        <td>: <?= esc($anggota->nik ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Alamat</strong></td>
                        <td>: <?= esc($anggota->alamat ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Pekerjaan</strong></td>
                        <td>: <?= esc($anggota->pekerjaan ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Lahir</strong></td>
                        <td>: <?= !empty($anggota->tgl_lahir) ? date('d-m-Y', strtotime($anggota->tgl_lahir)) : '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Umur</strong></td>
                        <td>:
                            <?php
                            if (!empty($anggota->tgl_lahir)) {
                                $lahir = new DateTime($anggota->tgl_lahir);
                                $sekarang = new DateTime();
                                echo $sekarang->diff($lahir)->y . ' tahun';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Nama Istri/Suami</strong></td>
                        <td>: <?= esc($anggota->nama_pasangan ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nomor HP</strong></td>
                        <td>: <?= esc($anggota->no_hp ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>: <?= esc($anggota->status ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>: <?= esc($anggota->email ?? '-'); ?></td>
                    </tr>
                </table>
            </div>
            <!-- Tombol Ganti Password -->
            <button class="btn btn-sm btn-primary mt-2" type="button" data-bs-toggle="collapse"
                data-bs-target="#formPassword" aria-expanded="false" aria-controls="formPassword">
                Ganti Password
            </button>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success mt-2"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mt-2"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <!-- Form Ganti Password -->
            <div class="collapse mt-3" id="formPassword">
                <form action="<?= base_url('anggota/update_password') ?>" method="post">
                    <div class="mb-2">
                        <label for="current_password" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="current_password" name="current_password"
                            required>
                    </div>
                    <div class="mb-2">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-2">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                </form>
            </div>

        </div>

    </div>
</div>

<?= $this->endSection(); ?>
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::login');
$routes->get('/login', 'AuthController::login');
$routes->get('auth/login', 'AuthController::login');
$routes->post('/auth/authenticate', 'AuthController::authenticate');
$routes->get('auth/logout', 'AuthController::logout');

$routes->group('admin', ['filter' => 'roleCheck:admin'], function ($routes) {
    $routes->get('dashboard', 'AuthController::adminDashboard');
    $routes->get('/dashboard/chart-data', 'DashboardController::getChartData');
});

$routes->group('karyawan', ['filter' => 'roleCheck:karyawan'], function ($routes) {
    $routes->get('dashboard', 'AuthController::karyawanDashboard');
});

// ====================== Admin routes ================================
$routes->get('admin/anggota', 'AnggotaController::anggota');
$routes->get('admin/tambah_anggota', 'AnggotaController::tambahAnggota');
$routes->post('admin/simpanAnggota', 'AnggotaController::simpanAnggota');
$routes->get('admin/edit_anggota/(:segment)', 'AnggotaController::editAnggota/$1');  // Route untuk form edit
$routes->post('/admin/updateAnggota', 'AnggotaController::updateAnggota');
$routes->get('admin/detail_anggota/(:num)', 'AnggotaController::detailAnggota/$1');
$routes->post('/admin/hapus_anggota/(:num)', 'AnggotaController::hapusAnggota/$1');
$routes->get('admin/kelola_pengguna', 'AuthController::kelolaPengguna');
$routes->get('admin/tambah_pengguna', 'AuthController::tambahPengguna');
$routes->post('admin/simpan_pengguna', 'AuthController::simpanPengguna');
$routes->get('admin/edit_pengguna/(:segment)', 'AuthController::editPengguna/$1');  // Route untuk form edit
$routes->post('admin/updatePengguna', 'AuthController::updatePengguna');
$routes->get('admin/hapus_pengguna/(:num)', 'AuthController::hapusPengguna/$1');

// ====================== Admin routes ================================
$routes->get('admin/jenis_simpanan', 'TransaksiSimpanan::jenisSimpanan');
$routes->get('admin/tambah_jenis_simpanan', 'TransaksiSimpanan::tambahJenisSimpanan');
$routes->post('admin/simpan_jenis_simpanan', 'TransaksiSimpanan::simpanJenisSimpanan');
$routes->get('admin/edit_jenis_simpanan/(:num)', 'TransaksiSimpanan::editJenisSimpanan/$1');
$routes->post('admin/update_jenis_simpanan', 'TransaksiSimpanan::updateJenisSimpanan');
$routes->get('admin/hapus_jenis_simpanan/(:num)', 'TransaksiSimpanan::hapusJenisSimpanan/$1');
$routes->get('karyawan/transaksi_simpanan/setor_form/(:num)', 'TransaksiSimpanan::setor_form/$1');


$routes->get('/karyawan/transaksi_simpanan/', 'TransaksiSimpanan::index');
$routes->get('/karyawan/transaksi_simpanan/create', 'TransaksiSimpanan::create');
$routes->post('/karyawan/transaksi_simpanan/store', 'TransaksiSimpanan::store');
$routes->get('karyawan/transaksi_simpanan/detail/(:segment)', 'TransaksiSimpanan::detail/$1');  // Route untuk form edit
$routes->post('karyawan/transaksi_simpanan/setor', 'TransaksiSimpanan::setor');
$routes->get('karyawan/transaksi_simpanan/setor_form/(:num)', 'TransaksiSimpanan::setor_form/$1');
$routes->post('karyawan/transaksi_simpanan/tarik', 'TransaksiSimpanan::tarik');
$routes->get('karyawan/transaksi_simpanan/tarik_form/(:num)', 'TransaksiSimpanan::tarik_form/$1');
$routes->post('karyawan/transaksi_simpanan/proses', 'TransaksiSimpanan::proses');

$routes->get('karyawan/transaksi_simpanan/edit/(:num)', 'TransaksiSimpanan::edit/$1');
$routes->post('karyawan/transaksi_simpanan/update/(:num)', 'TransaksiSimpanan::update/$1');
$routes->get('karyawan/transaksi_simpanan/delete/(:num)', 'TransaksiSimpanan::delete/$1');
$routes->post('karyawan/transaksi_simpanan/delete/(:num)', 'TransaksiSimpanan::delete/$1');

$routes->get('karyawan/transaksi_simpanan/import_simpanan', 'ImportSimpanan::index');
$routes->post('karyawan/transaksi_simpanan/import_simpanan/upload', 'ImportSimpanan::upload');

$routes->get('karyawan/transaksi_pinjaman', 'TransaksiPinjaman::index');
$routes->get('karyawan/transaksi_pinjaman/tambah', 'TransaksiPinjaman::tambah');
$routes->post('karyawan/transaksi_pinjaman/simpan', 'TransaksiPinjaman::simpan');
$routes->get('karyawan/transaksi_pinjaman/edit/(:num)', 'TransaksiPinjaman::edit/$1');
$routes->post('karyawan/transaksi_pinjaman/update/(:num)', 'TransaksiPinjaman::update/$1');
$routes->get('karyawan/transaksi_pinjaman/delete/(:num)', 'TransaksiPinjaman::delete/$1');
$routes->get('karyawan/transaksi_pinjaman/detail/(:num)', 'TransaksiPinjaman::detail/$1');
$routes->get('karyawan/transaksi_pinjaman/tambahAngsuran/(:segment)', 'TransaksiPinjaman::tambahAngsuran/$1');
$routes->post('karyawan/transaksi_pinjaman/simpan_angsuran', 'TransaksiPinjaman::simpanAngsuran');

$routes->get('karyawan/laporan_transaksi', 'LaporanTransaksi::index');
$routes->get('karyawan/laporan_transaksi/cetak', 'LaporanTransaksi::cetak');

// =============== jurnal harian ====================
$routes->get('admin/jurnal', 'JurnalKasController::index');
$routes->post('admin/jurnal/tambah', 'JurnalKasController::tambahJurnalKas');
$routes->post('admin/jurnal/update', 'JurnalKasController::update');
$routes->get('/jurnal', 'JurnalKasController::index');
$routes->get('/jurnal/data', 'JurnalKasController::getData');
$routes->post('/jurnal/update', 'JurnalKasController::updateData');

$routes->post('api/updateKas', 'JurnalKasController::updateKas');
$routes->post('admin/jurnal_harian', 'JurnalKasController::create');
$routes->post('admin/jurnal_kas/simpan', 'JurnalKasController::simpan');

$routes->put('admin/jurnal_kas/update', 'JurnalKasController::update');

$routes->get('admin/jurnal/jurnal_kas', 'JurnalKasController::index');
$routes->get('jurnal', 'JurnalKasController::getData'); // Menampilkan semua data
$routes->post('jurnal/create', 'JurnalKasController::createKas'); // Menambah data
$routes->put('jurnal/update', 'JurnalKasController::updateKas'); // Mengupdate data
$routes->post('/jurnal/saveDUK', 'JurnalKasController::saveDUK');
$routes->post('/jurnal/simpan', 'JurnalKasController::simpan');
$routes->post('/jurnal/createKas', 'JurnalKasController::createKas');
$routes->put('jurnal/update/(:num)', 'JurnalKasController::update/$1');
$routes->delete('admin/jurnal_kas/delete/(:num)', 'JurnalKasController::delete/$1'); //hapus

$routes->get('export-excel', 'JurnalKasController::exportExcel');
$routes->post('admin/jurnal/import_excel', 'JurnalKasController::importExcel');
$routes->get('admin/jurnal/prosesJurnalKeBukuBesar', 'JurnalKasController::prosesJurnalKeBukuBesar');
$routes->get('admin/jurnal/monthly/export/(:segment)/(:segment)', 'JurnalKasController::exportMonthlyExcel/$1/$2');


// View by month/year
$routes->get('admin/jurnal/monthly', 'JurnalKasController::monthlyView');
$routes->get('admin/jurnal/monthly/details/(:segment)/(:segment)', 'JurnalKasController::monthlyDetails/$1/$2');


$routes->group('admin', function ($routes) {
    $routes->get('neraca', 'NeracaAwalController::index');
    $routes->get('neraca/create', 'NeracaAwalController::create');
    $routes->post('neraca/store', 'NeracaAwalController::store');
    $routes->get('neraca/edit/(:num)', 'NeracaAwalController::edit/$1');
    $routes->post('neraca/update/(:num)', 'NeracaAwalController::update/$1');
    $routes->get('neraca/delete/(:num)', 'NeracaAwalController::delete/$1');
    $routes->get('neraca/kategori_neraca', 'KategoriNeraca::index');
    $routes->get('neraca/kategori_neraca/create', 'KategoriNeraca::create');
    $routes->post('neraca/kategori_neraca/store', 'KategoriNeraca::store');
    $routes->get('neraca/kategori_neraca/edit/(:num)', 'KategoriNeraca::edit/$1');
    $routes->post('neraca/kategori_neraca/update/(:num)', 'KategoriNeraca::update/$1');
    $routes->get('neraca/kategori_neraca/delete/(:num)', 'KategoriNeraca::delete/$1');
});
$routes->group('admin', function ($routes) {
    $routes->get('akun', 'AkunKeuangan::index');
    $routes->get('akun/create', 'AkunKeuangan::create');
    $routes->post('akun/store', 'AkunKeuangan::store');
    $routes->get('akun/edit/(:num)', 'AkunKeuangan::edit/$1');
    $routes->post('akun/update/(:num)', 'AkunKeuangan::update/$1');
    $routes->get('akun/delete/(:num)', 'AkunKeuangan::delete/$1');
});


// ============== Buku Besar ====================
$routes->group('admin/buku_besar', function ($routes) {
    $routes->get('/', 'BukuBesarController::index'); // Menampilkan daftar buku besar
    $routes->get('create', 'BukuBesarController::create'); // Form tambah data
    $routes->post('store', 'BukuBesarController::store'); // Proses simpan data baru
    $routes->get('edit/(:num)', 'BukuBesarController::edit/$1'); // Form edit berdasarkan ID
    $routes->post('update/(:num)', 'BukuBesarController::update/$1'); // Proses update
    $routes->get('delete/(:num)', 'BukuBesarController::delete/$1'); // Hapus data berdasarkan ID
    $routes->get('proses', 'BukuBesarController::proses'); // Pastikan ada metode ini di controller!
});

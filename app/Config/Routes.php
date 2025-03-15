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
$routes->get('admin/jurnal_neraca', 'JurnalKasController::index');
$routes->post('admin/jurnal_neraca/tambah', 'JurnalKasController::tambahJurnalKas');
$routes->post('admin/jurnal_neraca/update', 'JurnalKasController::update');
$routes->get('/jurnal_neraca', 'JurnalKasController::index');
$routes->get('/jurnal_neraca/data', 'JurnalKasController::getData');
$routes->post('/jurnal_neraca/update', 'JurnalKasController::updateData');

$routes->post('api/updateKas', 'JurnalKasController::updateKas');
$routes->post('admin/jurnal_harian', 'JurnalKasController::create');
$routes->post('admin/jurnal_kas_harian/simpan', 'JurnalKasController::simpan');
// $routes->match(['put', 'post'], 'jurnal_/updateKas', 'JurnalKasController::updateKas');
$routes->get('admin/jurnal_neraca/jurnal_kas_harian', 'JurnalKasController::index');
$routes->get('jurnal_neraca', 'JurnalKasController::getData'); // Menampilkan semua data
$routes->post('jurnal_neraca/create', 'JurnalKasController::createKas'); // Menambah data
$routes->put('jurnal_neraca/update', 'JurnalKasController::updateKas'); // Mengupdate data
$routes->post('/jurnal/saveDUK', 'JurnalKasController::saveDUK');
$routes->post('/jurnal/simpan', 'JurnalKasController::simpan');
$routes->post('/jurnal/createKas', 'JurnalKasController::createKas');
$routes->put('jurnal/update/(:num)', 'JurnalKasController::update/$1');
$routes->delete('admin/jurnal_kas_harian/delete/(:num)', 'JurnalKasController::delete/$1'); //hapus


$routes->get('export-excel', 'JurnalKasController::exportExcel');
$routes->post('admin/jurnal_neraca/import_excel', 'JurnalKasController::importExcel');

$routes->group('admin', function ($routes) {
    $routes->get('neraca_awal', 'NeracaAwalController::index');
    $routes->get('neraca_awal/create', 'NeracaAwalController::create');
    $routes->post('neraca_awal/store', 'NeracaAwalController::store');
    $routes->get('neraca_awal/edit/(:num)', 'NeracaAwalController::edit/$1');
    $routes->post('neraca_awal/update/(:num)', 'NeracaAwalController::update/$1');
    $routes->get('neraca_awal/delete/(:num)', 'NeracaAwalController::delete/$1');
    $routes->get('neraca_awal/kategori_neraca', 'KategoriNeraca::index');
    $routes->get('neraca_awal/kategori_neraca/create', 'KategoriNeraca::create');
    $routes->post('neraca_awal/kategori_neraca/store', 'KategoriNeraca::store');
    $routes->get('neraca_awal/kategori_neraca/edit/(:num)', 'KategoriNeraca::edit/$1');
    $routes->post('neraca_awal/kategori_neraca/update/(:num)', 'KategoriNeraca::update/$1');
    $routes->get('neraca_awal/kategori_neraca/delete/(:num)', 'KategoriNeraca::delete/$1');

});


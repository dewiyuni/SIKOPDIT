<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('role');
        $isLoggedIn = session()->get('is_logged_in');

        // Jika belum login, arahkan ke login
        if (!$isLoggedIn) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek apakah pengguna mencoba mengakses halaman '/' atau '/login' setelah login
        $currentURI = service('uri')->getPath();
        if (in_array($currentURI, ['', 'login'])) {
            return redirect()->to($role === 'admin' ? '/admin/dashboard' : '/karyawan/dashboard');
        }

        // Cek apakah role yang diperlukan sesuai
        if ($arguments && !in_array($role, $arguments)) {
            return redirect()->to('/unauthorized')->with('error', 'Akses ditolak!');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan setelah request
    }
}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="<?= base_url('asset/dist/css/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
            /* Warna latar belakang */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            /* Lebar maksimal */
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-container img {
            max-width: 200px;
            /* Ukuran maksimal logo */
            width: 100%;
            /* Biar fleksibel */
            height: auto;
            /* Menjaga aspek rasio */
            display: block;
            /* Agar tidak ada spasi di bawah */
            margin: 0 auto 20px;
            /* Tengah dan beri jarak bawah */
        }

        @media (max-width: 768px) {
            .login-container img {
                max-width: 120px;
                /* Lebih kecil di layar mobile */
            }
        }
    </style>
</head>

<body>
    <div class="login-container">

        <!-- Kolom Form Login -->
        <h2 class="text-center mb-3">Login</h2>
        <img src="<?= base_url("assets/logo.png") ?>" alt="Logo" class="logo">

        <form action="<?= site_url('auth/authenticate') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_hash() ?>">
            <?= csrf_field(); ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= old('email') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
    </div>
    </div>
    </div>

</body>

</html>
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (user()) {
    redirect('admin/index.php');
}

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim((string)($_POST['username'] ?? ''));
    $p = (string)($_POST['password'] ?? '');
    if ($u === '' || $p === '') {
        $galat = 'Username dan kata sandi wajib diisi.';
    } elseif (user_login($u, $p)) {
        redirect('admin/index.php');
    } else {
        // Pesan sengaja sama untuk username salah maupun kata sandi salah.
        $galat = 'Username atau kata sandi salah.';
    }
}
$prof = profile();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b1f4b">
<title>Login Administrator | <?= e($prof['nama_toko']) ?></title>
<link rel="stylesheet" href="<?= e(url('asset/css/style.css')) ?>">
<script src="<?= e(url('asset/js/app.js')) ?>" defer></script>
</head>
<body>
<?= icon_sprite() ?>
<div class="auth">
  <aside class="auth-side">
    <a class="brand" href="<?= e(url('index.php')) ?>"><?= brand_html() ?></a>
    <div>
      <h2>Panel pengelolaan e-katalog</h2>
      <p>Kelola produk, kategori, dan perbarui harga serta stok secara massal dari berkas ekspor SIP.</p>
      <ul class="auth-points">
        <li><?= ico('upload') ?> Impor data massal dengan validasi berkas</li>
        <li><?= ico('shield') ?> Akses terlindungi dan peran pengguna</li>
        <li><?= ico('clock') ?> Riwayat setiap impor tercatat</li>
      </ul>
    </div>
    <small style="color:#93c5fd">&copy; <?= date('Y') ?> <?= e($prof['nama_toko']) ?></small>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <h1>Login Administrator</h1>
      <p class="sub">Masuk untuk mengelola e-katalog.</p>
      <?php if ($galat): ?><div class="alert err"><?= ico('alert') ?><span><?= e($galat) ?></span></div><?php endif; ?>
      <form method="post" autocomplete="off" data-loading="Memeriksa...">
        <?= csrf_field() ?>
        <label for="u">Username</label>
        <div class="input-icon"><?= ico('user') ?><input type="text" id="u" name="username" value="<?= e($_POST['username'] ?? '') ?>" autofocus></div>
        <label for="p">Kata sandi</label>
        <div class="input-icon"><?= ico('shield') ?><input type="password" id="p" name="password">
          <button type="button" class="pw-toggle" aria-label="Tampilkan kata sandi"><?= ico('eye') ?></button></div>
        <p><button type="submit" class="btn-block">Masuk</button></p>
      </form>
      <p class="auth-back"><a href="<?= e(url('index.php')) ?>"><?= ico('chev-left') ?> Kembali ke situs</a></p>
    </div>
  </main>
</div>
</body>
</html>

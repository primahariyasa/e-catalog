<?php
require_once __DIR__ . '/includes/layout.php';

$p = profile();
$kategori = db()->query('SELECT id_kategori, nama_kategori FROM kategori ORDER BY urutan, nama_kategori')->fetchAll();
$terbaru = db()->query('SELECT p.*, k.nama_kategori FROM produk p
    LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
    WHERE p.tampil = 1 ORDER BY p.id_produk DESC LIMIT 8')->fetchAll();

pub_head('Beranda', 'beranda');
?>
<section class="hero">
  <span class="eyebrow"><?= ico('zap') ?> Katalog produk terbaru</span>
  <h1>Selamat datang di E-Katalog <?= e($p['nama_toko']) ?></h1>
  <?php if (trim((string)$p['tagline']) !== ''): ?><p><?= e($p['tagline']) ?></p><?php endif; ?>
  <?= search_form() ?>
  <div class="hero-cta"><a class="btn" href="<?= e(url('katalog.php')) ?>">Lihat Semua Produk <?= ico('arrow-right') ?></a></div>
</section>

<div class="section-head"><h2>Kategori</h2></div>
<div class="chips">
  <?php foreach ($kategori as $k): ?>
    <a class="chip" href="<?= e(url('katalog.php?kategori=' . (int)$k['id_kategori'])) ?>"><?= e($k['nama_kategori']) ?></a>
  <?php endforeach; ?>
</div>

<div class="section-head">
  <h2>Produk Terbaru</h2>
  <a href="<?= e(url('katalog.php')) ?>">Lihat semua <?= ico('chev-right') ?></a>
</div>
<?php if (!$terbaru): ?>
  <div class="notfound"><?= ico('box') ?>Belum ada produk.</div>
<?php else: ?>
  <div class="grid"><?php foreach ($terbaru as $row) { echo render_card($row); } ?></div>
<?php endif; ?>
<?php pub_foot();

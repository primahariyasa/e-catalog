<?php
require_once __DIR__ . '/includes/layout.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT p.*, k.nama_kategori FROM produk p
    LEFT JOIN kategori k ON k.id_kategori = p.id_kategori WHERE p.id_produk = ? AND p.tampil = 1');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    pub_head('Produk tidak ditemukan', 'katalog');
    echo '<div class="notfound">' . ico('search') . 'Produk Tidak Ditemukan</div><p style="text-align:center"><a class="btn" href="' . e(url('katalog.php')) . '">' . ico('chev-left') . ' Kembali ke Katalog</a></p>';
    pub_foot();
    exit;
}

/* Pengingat stok (tabel pengingat_stok) untuk produk yang habis */
$galat = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (int)$p['stok'] === 0) {
    csrf_check();
    $nama = trim((string)($_POST['nama_konsumen'] ?? ''));
    $wa   = normalisasi_wa((string)($_POST['no_whatsapp'] ?? ''));
    $cat  = trim((string)($_POST['catatan'] ?? ''));
    if ($nama === '' || mb_strlen($nama) > 100) { $galat[] = 'Nama wajib diisi (maksimal 100 karakter).'; }
    if (!preg_match('/^62\d{8,13}$/', $wa))      { $galat[] = 'Nomor WhatsApp tidak valid (contoh: 081234567890).'; }
    if (mb_strlen($cat) > 255)                   { $galat[] = 'Catatan maksimal 255 karakter.'; }
    if (!$galat) {
        db()->prepare('INSERT INTO pengingat_stok (id_produk, nama_konsumen, no_whatsapp, catatan) VALUES (?,?,?,?)')
            ->execute([$p['id_produk'], $nama, $wa, $cat !== '' ? $cat : null]);
        flash('Terima kasih. Kami akan menghubungi Anda melalui WhatsApp saat produk tersedia kembali.');
        redirect('produk.php?id=' . $p['id_produk']);
    }
}

[$akhir, $promo] = harga_akhir($p);
$stok = (int)$p['stok'];
$prof = profile();
$wa_txt = 'Halo, saya ingin menanyakan produk: ' . $p['nama_produk'] . ' (SKU ' . $p['sku'] . ')';
$wa_link = 'https://wa.me/' . preg_replace('/\D+/', '', $prof['no_telp']) . '?text=' . rawurlencode($wa_txt);

pub_head($p['nama_produk'], 'katalog');
$f = flash();
?>
<nav class="crumbs" aria-label="Breadcrumb">
  <a href="<?= e(url('index.php')) ?>"><?= ico('home') ?></a><?= ico('chev-right') ?>
  <a href="<?= e(url('katalog.php')) ?>">Katalog</a><?= ico('chev-right') ?>
  <span><?= e($p['nama_produk']) ?></span>
</nav>
<?php if ($f): ?><div class="alert ok" data-autoclose="6000"><?= ico('check') ?><span><?= e($f['msg']) ?></span><button type="button" class="alert-x" aria-label="Tutup"><?= ico('x') ?></button></div><?php endif; ?>

<div class="detail">
  <div class="imgbox" title="Klik untuk memperbesar">
    <?php if (!empty($p['gambar'])): ?>
      <img src="<?= e(img_src($p['gambar'])) ?>" alt="<?= e($p['nama_produk']) ?>" onerror="this.parentNode.classList.add('noimg');this.remove()">
    <?php endif; ?>
    <?php if ($promo): ?><span class="ribbon"><?= ico('tag') ?> Promo</span><?php endif; ?>
  </div>
  <div>
    <?php if (!empty($p['nama_kategori'])): ?><div class="card-cat"><?= e($p['nama_kategori']) ?></div><?php endif; ?>
    <h1><?= e($p['nama_produk']) ?></h1>
    <?php if ($promo): ?>
      <div class="price"><span class="price-now"><?= rupiah($akhir) ?></span> <s class="price-old"><?= rupiah($p['harga']) ?></s></div>
      <div class="promo-tag"><?= ico('tag') ?> Promo: <?= e($promo['nama_promo']) ?></div>
    <?php else: ?>
      <div class="price"><span class="price-now"><?= rupiah($p['harga']) ?></span></div>
    <?php endif; ?>
    <p><span class="badge <?= $stok > 0 ? 'ok' : 'out' ?>"><i></i><?= status_stok($stok) ?><?= $stok > 0 ? ' (stok ' . $stok . ')' : '' ?></span></p>
    <table class="kv">
      <tr><td>SKU</td><td>: <?= e($p['sku']) ?></td></tr>
      <tr><td>Kategori</td><td>: <?= e($p['nama_kategori'] ?? '-') ?></td></tr>
      <?php if (!empty($p['warna'])): ?><tr><td>Warna</td><td>: <?= e($p['warna']) ?></td></tr><?php endif; ?>
    </table>
    <h2 style="margin:16px 0 8px">Spesifikasi</h2>
    <div class="spec"><?= trim((string)$p['deskripsi']) !== '' ? e(trim($p['deskripsi'])) : 'Belum ada spesifikasi.' ?></div>
    <div class="actions">
      <a class="btn primary" href="<?= e($wa_link) ?>" target="_blank" rel="noopener"><?= ico('chat') ?> Tanya via WhatsApp</a>
      <a class="btn" href="<?= e(url('katalog.php')) ?>"><?= ico('chev-left') ?> Kembali ke Katalog</a>
    </div>
  </div>
</div>

<?php if ($stok === 0): ?>
  <section class="box stock-box">
    <h2><?= ico('bell') ?> Ingatkan saya saat stok tersedia</h2>
    <p class="muted" style="margin:4px 0 0">Tinggalkan nama dan nomor WhatsApp, kami akan menghubungi Anda saat produk ini tersedia kembali.</p>
    <?php if ($galat): ?><div class="alert err"><?= ico('alert') ?><span><ul><?php foreach ($galat as $g) { echo '<li>' . e($g) . '</li>'; } ?></ul></span></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="row">
        <div><label>Nama</label><input type="text" name="nama_konsumen" maxlength="100" value="<?= e($_POST['nama_konsumen'] ?? '') ?>" required></div>
        <div><label>Nomor WhatsApp</label><input type="text" name="no_whatsapp" maxlength="20" placeholder="08xxxxxxxxxx" value="<?= e($_POST['no_whatsapp'] ?? '') ?>" required></div>
      </div>
      <label>Catatan (opsional)</label><input type="text" name="catatan" maxlength="255" value="<?= e($_POST['catatan'] ?? '') ?>">
      <p><button type="submit"><?= ico('bell') ?> Kirim Permintaan</button></p>
    </form>
  </section>
<?php endif; ?>
<?php pub_foot();

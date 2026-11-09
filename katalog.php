<?php
require_once __DIR__ . '/includes/layout.php';

$q    = trim((string)($_GET['q'] ?? ''));
$kat  = (int)($_GET['kategori'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ['p.tampil = 1'];
$par = [];
if ($kat > 0) {
    $where[] = 'p.id_kategori = ?';
    $par[] = $kat;
}
if ($q !== '') {
    // Kata kunci memakai parameter terikat (NFR-08); karakter % _ \ di-escape agar dibaca sebagai teks biasa.
    $like = '%' . addcslashes($q, '\\%_') . '%';
    $where[] = '(p.nama_produk LIKE ? OR p.sku LIKE ?)';
    $par[] = $like;
    $par[] = $like;
}
$w = implode(' AND ', $where);

$st = db()->prepare("SELECT COUNT(*) FROM produk p WHERE $w");
$st->execute($par);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / PER_PAGE));
$page  = min($page, $pages);
$off   = ($page - 1) * PER_PAGE;

$st = db()->prepare("SELECT p.*, k.nama_kategori FROM produk p
    LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
    WHERE $w ORDER BY (p.stok > 0) DESC, p.id_produk DESC LIMIT " . (int)PER_PAGE . " OFFSET " . (int)$off);
$st->execute($par);
$rows = $st->fetchAll();

function page_link(int $n, string $q, int $kat): string
{
    $a = ['page' => $n];
    if ($q !== '') { $a['q'] = $q; }
    if ($kat > 0) { $a['kategori'] = $kat; }
    return url('katalog.php?' . http_build_query($a));
}

pub_head('Katalog Produk', 'katalog');
?>
<h1>Katalog Produk</h1>
<?= search_form($q, $kat, true) ?>
<div class="result-info"><?= ico('grid') ?><span>Menampilkan <b><?= (int)$total ?></b> produk<?= $q !== '' ? ' untuk kata kunci "' . e($q) . '"' : '' ?></span></div>

<?php if (!$rows): ?>
  <div class="notfound"><?= ico('search') ?>Produk Tidak Ditemukan<span class="muted" style="font-weight:400">Coba kata kunci atau kategori lain.</span></div>
<?php else: ?>
  <div class="grid"><?php foreach ($rows as $r) { echo render_card($r); } ?></div>
  <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Halaman">
      <?php if ($page > 1): ?><a href="<?= e(page_link($page - 1, $q, $kat)) ?>"><?= ico('chev-left') ?> Sebelumnya</a><?php endif; ?>
      <?php for ($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++): ?>
        <?= $i === $page ? '<span class="cur">' . $i . '</span>' : '<a href="' . e(page_link($i, $q, $kat)) . '">' . $i . '</a>' ?>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a href="<?= e(page_link($page + 1, $q, $kat)) ?>">Berikutnya <?= ico('chev-right') ?></a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
<?php pub_foot();

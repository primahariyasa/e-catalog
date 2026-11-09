<?php
require_once __DIR__ . '/includes/layout.php';

$p = profile();
$wa = preg_replace('/\D+/', '', (string)$p['no_telp']);
pub_head('Tentang Kami dan Kontak', 'tentang');
?>
<section class="hero" style="padding-bottom:26px">
  <span class="eyebrow"><?= ico('info') ?> Tentang Kami</span>
  <h1><?= e($p['nama_toko']) ?></h1>
  <?php if (trim((string)$p['tagline']) !== ''): ?><p style="margin-bottom:0"><?= nl2br(e($p['tagline'])) ?></p><?php endif; ?>
</section>

<div class="section-head" id="kontak"><h2>Kontak Toko</h2></div>
<div class="info-grid">
  <div class="info-card"><span class="ic"><?= ico('pin') ?></span><div><b>Alamat</b><?= e($p['alamat']) ?></div></div>
  <div class="info-card"><span class="ic"><?= ico('clock') ?></span><div><b>Jam operasional</b><?= e($p['jam_operasional']) ?></div></div>
  <div class="info-card"><span class="ic"><?= ico('chat') ?></span><div><b>WhatsApp</b><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= e($p['no_telp']) ?></a></div></div>
  <?php if (!empty($p['email'])): ?>
  <div class="info-card"><span class="ic"><?= ico('mail') ?></span><div><b>Email</b><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></div></div>
  <?php endif; ?>
  <?php if (!empty($p['maps_url'])): ?>
  <div class="info-card"><span class="ic"><?= ico('external') ?></span><div><b>Peta</b><a href="<?= e($p['maps_url']) ?>" target="_blank" rel="noopener">Buka di Google Maps</a></div></div>
  <?php endif; ?>
</div>
<?php pub_foot();

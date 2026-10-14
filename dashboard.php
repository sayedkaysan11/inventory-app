<?php
require_once __DIR__ . '/../helpers/auth.php';
require_login();
$pdo = db();

$total_products = (int) $pdo->query('SELECT COUNT(id) FROM products')->fetchColumn();
$inv_value      = (float) $pdo->query('SELECT COALESCE(SUM(stock * price),0) FROM products')->fetchColumn();
$low            = $pdo->query('SELECT p.sku, p.name, p.stock, p.minimum_stock FROM products p WHERE p.stock <= p.minimum_stock ORDER BY p.stock')->fetchAll();
$mov = $pdo->query("SELECT COALESCE(SUM(CASE WHEN type='IN' THEN quantity END),0) AS total_in,
                           COALESCE(SUM(CASE WHEN type='OUT' THEN quantity END),0) AS total_out
                    FROM stock_movements")->fetch();

// Laporan per kategori: JOIN + GROUP BY + COUNT/SUM/AVG
$per_cat = $pdo->query('SELECT c.name, COUNT(p.id) AS total_products, COALESCE(SUM(p.stock),0) AS total_stock,
                               COALESCE(AVG(p.price),0) AS average_price
                        FROM categories c LEFT JOIN products p ON p.category_id = c.id
                        GROUP BY c.id, c.name ORDER BY c.name')->fetchAll();

// Laporan inventory per produk (slide 28)
$report = $pdo->query("SELECT p.sku, p.name, p.stock,
                              COALESCE(SUM(CASE WHEN sm.type='IN'  THEN sm.quantity END),0) AS total_in,
                              COALESCE(SUM(CASE WHEN sm.type='OUT' THEN sm.quantity END),0) AS total_out
                       FROM products p LEFT JOIN stock_movements sm ON sm.product_id = p.id
                       GROUP BY p.id, p.sku, p.name, p.stock ORDER BY p.name")->fetchAll();

// Kategori dengan >= 2 produk (contoh HAVING)
$having = $pdo->query('SELECT c.name, COUNT(p.id) AS n FROM categories c JOIN products p ON p.category_id=c.id
                       GROUP BY c.id, c.name HAVING COUNT(p.id) >= 2')->fetchAll();

$title = 'Dashboard';
require __DIR__ . '/../views/header.php';
?>
<h1>Dashboard</h1>
<div class="grid">
  <div class="card stat">Total produk<b><?= e($total_products) ?></b></div>
  <div class="card stat">Nilai inventory<b><?= e(rupiah($inv_value)) ?></b></div>
  <div class="card stat">Produk stok rendah<b><?= e(count($low)) ?></b></div>
  <div class="card stat">Barang masuk<b><?= e($mov['total_in']) ?></b></div>
  <div class="card stat">Barang keluar<b><?= e($mov['total_out']) ?></b></div>
</div>

<h2>Produk stok rendah</h2>
<table><tr><th>SKU</th><th>Nama</th><th>Stok</th><th>Minimum</th></tr>
<?php foreach ($low as $r): ?>
  <tr class="low"><td><?= e($r['sku']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['stock']) ?></td><td><?= e($r['minimum_stock']) ?></td></tr>
<?php endforeach; if (!$low): ?><tr><td colspan="4">Semua stok aman.</td></tr><?php endif; ?>
</table>

<h2>Ringkasan per kategori</h2>
<table><tr><th>Kategori</th><th>Jumlah produk</th><th>Total stok</th><th>Rata-rata harga</th></tr>
<?php foreach ($per_cat as $r): ?>
  <tr><td><?= e($r['name']) ?></td><td><?= e($r['total_products']) ?></td><td><?= e($r['total_stock']) ?></td><td><?= e(rupiah($r['average_price'])) ?></td></tr>
<?php endforeach; ?>
</table>

<h2>Laporan inventory</h2>
<table><tr><th>SKU</th><th>Produk</th><th>Total masuk</th><th>Total keluar</th><th>Stok saat ini</th></tr>
<?php foreach ($report as $r): ?>
  <tr><td><?= e($r['sku']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['total_in']) ?></td><td><?= e($r['total_out']) ?></td><td><?= e($r['stock']) ?></td></tr>
<?php endforeach; ?>
</table>
<p class="muted">Kategori dengan minimal 2 produk (HAVING):
  <?= e(implode(', ', array_map(fn($r) => $r['name'] . ' (' . $r['n'] . ')', $having)) ?: '-') ?></p>
<?php require __DIR__ . '/../views/footer.php'; ?>

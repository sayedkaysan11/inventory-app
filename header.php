<?php
// Variabel: $title (string)
$u = current_user();
$flash = flash();
$nav = base_url('');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Inventory') ?> - Inventory App</title>
<link rel="stylesheet" href="<?= e(base_url('style.css')) ?>">
</head>
<body>
<?php if ($u): ?>
<header class="topbar">
  <strong>📦 Inventory</strong>
  <nav>
    <a href="<?= e(base_url('dashboard.php')) ?>">Dashboard</a>
    <a href="<?= e(base_url('products/index.php')) ?>">Produk</a>
    <a href="<?= e(base_url('stock/index.php')) ?>">Stok</a>
    <?php if ($u['role'] === 'admin'): ?>
      <a href="<?= e(base_url('categories/index.php')) ?>">Kategori</a>
      <a href="<?= e(base_url('suppliers/index.php')) ?>">Supplier</a>
      <a href="<?= e(base_url('users/index.php')) ?>">User</a>
    <?php endif; ?>
  </nav>
  <span class="who"><?= e($u['username']) ?> (<?= e($u['role']) ?>)
    <a href="<?= e(base_url('logout.php')) ?>">Logout</a></span>
</header>
<?php endif; ?>
<main class="container">
<?php if ($flash): ?>
  <div class="alert <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>

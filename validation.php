<?php
// Syntactic validation: tipe, format, panjang
function post_int(string $key): ?int
{
    $v = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    return $v === false ? null : $v;
}

function post_decimal(string $key): ?float
{
    $v = filter_input(INPUT_POST, $key, FILTER_VALIDATE_FLOAT);
    return $v === false ? null : $v;
}

function post_str(string $key, int $max = 255): string
{
    return mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
}

/** Validasi form produk (syntactic + semantic). Mengembalikan [data, errors]. */
function validate_product(PDO $pdo, ?int $ignoreId = null): array
{
    $errors = [];
    $d = [
        'category_id'   => post_int('category_id'),
        'sku'           => strtoupper(post_str('sku', 50)),
        'name'          => post_str('name', 150),
        'price'         => post_decimal('price'),
        'minimum_stock' => post_int('minimum_stock'),
    ];
    if ($d['sku'] === '') $errors[] = 'SKU wajib diisi.';
    if ($d['name'] === '') $errors[] = 'Nama produk wajib diisi.';
    if ($d['price'] === null || $d['price'] < 0) $errors[] = 'Harga tidak boleh negatif.';
    if ($d['minimum_stock'] === null || $d['minimum_stock'] < 0) $errors[] = 'Stok minimum tidak boleh negatif.';

    // Semantic: kategori harus ada
    $st = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
    $st->execute(['id' => $d['category_id'] ?? 0]);
    if (!$st->fetchColumn()) $errors[] = 'Kategori tidak valid.';

    // Semantic: SKU harus unik
    if ($d['sku'] !== '') {
        $st = $pdo->prepare('SELECT COUNT(*) FROM products WHERE sku = :sku AND id <> :id');
        $st->execute(['sku' => $d['sku'], 'id' => $ignoreId ?? 0]);
        if ($st->fetchColumn()) $errors[] = 'SKU sudah dipakai produk lain.';
    }
    return [$d, $errors];
}

# Inventory Management System (Mini Project 3)

Aplikasi inventory berbasis PHP (PDO) + MySQL/MariaDB: relasi tabel, transaksi stok, laporan, login berbasis role, dan keamanan dasar.
Mata kuliah Pemrograman Web - Pertemuan 4: Relational Database + Advanced PHP.

## Cara menjalankan (XAMPP / PHP lokal)
1. Pastikan PHP 8+ dan MySQL/MariaDB aktif (XAMPP: start Apache + MySQL).
2. Import database: buka phpMyAdmin -> Import -> pilih `database/schema.sql`
   (atau `mysql -u root < database/schema.sql`).
3. Cek `config/database.php` (default: user `root`, password kosong).
4. Jalankan server dari folder proyek:
   ```
   php -S localhost:8000 -t public
   ```
   lalu buka http://localhost:8000
   (atau letakkan folder di `htdocs` dan buka `http://localhost/inventory-app/public/`).

**Akun demo:** `admin` / `admin123` (admin), `staff` / `staff123` (staff)

## Fitur
- Login/logout, `password_hash` + `password_verify`, `session_regenerate_id`, CSRF token
- Role **admin** (kelola user, kategori, supplier, produk, transaksi) dan **staff** (lihat produk, catat transaksi)
- CRUD kategori, produk, supplier; relasi many-to-many produk-supplier
- Search (nama/SKU), filter kategori, sorting (allowlist), pagination - parameter tetap terbawa antar halaman
- Barang masuk/keluar dengan **database transaction** (`SELECT ... FOR UPDATE`, commit/rollback)
- Dashboard: total produk, nilai inventory, stok rendah, total masuk/keluar, laporan per kategori (JOIN, GROUP BY, COUNT, SUM, AVG, HAVING)

## ERD
Lihat [docs/ERD.md](docs/ERD.md) (diagram Mermaid, tampil otomatis di GitHub).

## Penjelasan Business Rule
| Rule | Implementasi |
|---|---|
| SKU unik | `UNIQUE` di DB + cek semantic di `validate_product()` |
| Harga & quantity tidak negatif | validasi server + `CHECK` constraint |
| Barang keluar <= stok | `record_movement()` mengunci baris (`FOR UPDATE`) lalu membandingkan stok sebelum UPDATE |
| Stok tidak boleh negatif | rule di atas + `CHECK (stock >= 0)` |
| Produk berhistori tidak boleh dihapus | cek `stock_movements` sebelum DELETE + FK `ON DELETE RESTRICT` |
| Kategori terpakai tidak bisa dihapus | FK `ON DELETE RESTRICT` |
| Halaman internal wajib login | `require_login()` |
| Staff tidak boleh akses admin | `require_role('admin')` -> HTTP 403 (dicek di server, bukan hanya disembunyikan dari menu) |
| Sorting hanya kolom diizinkan | allowlist `name/price/stock`, arah hanya ASC/DESC |
| Stok & histori konsisten | UPDATE stok + INSERT histori dalam satu transaction |

## Keamanan
- **SQL Injection:** semua query dengan input memakai prepared statement (PDO, emulate prepares off)
- **XSS:** semua output lewat `e()` = `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
- **Validasi input:** `filter_input`/`filter_var` + validasi semantik di server
- **Password:** `password_hash()` (bcrypt)
- **CSRF:** token pada semua form POST

## Pengujian (sesuai tugas)
| Kasus | Hasil |
|---|---|
| Validation error: harga negatif + SKU duplikat | Ditolak dengan pesan error (`docs/screenshots/05`) |
| Akses terlarang: staff membuka `/users` | HTTP 403 (`docs/screenshots/08`) |
| Barang keluar melebihi stok | Ditolak, stok tetap (`docs/screenshots/06`) |
| SQL Injection di login (`admin' OR '1'='1`) | Login gagal |
| `<script>` pada nama produk | Tampil sebagai teks, tidak dieksekusi |
| `sort=name;DROP TABLE products` | Diabaikan (allowlist), tabel aman |

## Screenshot
Ada di folder `docs/screenshots/`.

## Struktur folder
```
inventory-app/
├── config/database.php
├── helpers/ (auth.php, validation.php, escape.php)
├── public/  (login, dashboard, products/, categories/, suppliers/, stock/, users/)
├── views/   (header, footer, errors)
├── database/schema.sql
└── docs/    (ERD.md, screenshots/)
```

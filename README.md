# BookStore

Aplikasi toko buku online berbasis **PHP** dan **MySQL** (mysqli), dengan panel **admin** untuk master data & transaksi, serta **halaman pengguna** untuk katalog, keranjang (wishlist → checkout), pesanan, kontak, dan konfirmasi penerimaan paket.

---

## Persyaratan lingkungan

- **PHP** 7.3+ (disarankan 8.x) dengan ekstensi `mysqli`, `json`, `session`, `fileinfo` (unggah gambar).
- **MySQL** / MariaDB.
- Web server (Apache/Nginx) atau **Laragon** / XAMPP / stack sejenis.

---

## Instalasi cepat

1. Letakkan folder proyek di document root (mis. `C:\laragon\www\BookStore`).
2. Buat database **MySQL** bernama `bookstore` (atau sesuaikan nama di `config/database.php`).
3. Jalankan skema tabel (lihat bagian [Struktur database](#struktur-database)). Sesuaikan isi master **`m_status`**, **`m_status_pengiriman`**, dan user admin pertama.
4. Atur kredensial di `config/database.php`:

   ```php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $database = 'bookstore';
   ```

5. Pastikan folder unggahan dapat ditulis oleh web server:
   - `assets/admin/img/buku/`
   - `assets/admin/img/bukti_pembayaran/`
6. Buka aplikasi melalui browser (mis. `http://localhost/BookStore/`).

---

## Struktur folder (ringkas)

| Path | Fungsi |
|------|--------|
| `index.php` | Landing pengguna: hero, tentang, COD, katalog, keranjang (modal), kontak, pesanan (jika login). |
| `config/database.php` | Koneksi MySQL (`$conn`). |
| `includes/users/` | Layout, navbar, footer, `init.php`, modul UI pengguna, `cart-modal.php`. |
| `includes/admin/` | Layout admin, sidebar, header, `init.php`. |
| `users/proses.php` | POST: login/register, booking keranjang, upload bukti, batal, kontak, konfirmasi terima paket. |
| `users/modules/semua-buku/` | Halaman katalog penuh + filter. |
| `admin/` | Entry login admin (`admin/index.php`). |
| `admin/modules/` | Modul: `dashboard`, `kategori`, `buku`, `user`, `transaksi`, `laporan`, `pesan-user`. |
| `admin/proses.php` | Logout admin (sesuai penggunaan di proyek). |
| `function/helpers.php` | Helper global, konstanta status transaksi, statistik dashboard, laporan. |
| `function/users/` | Event/buku pengguna, tiket/pesanan, auth, kontak. |
| `function/admin/` | Auth admin. |
| `assets/users/` | CSS/JS tema pengguna. |
| `assets/admin/` | Aset admin; gambar buku & bukti di subfolder `img/`. |

---

## Struktur database

Tabel di bawah disusun berdasarkan **penggunaan di kode**. Tipe kolom dapat Anda sesuaikan (INT vs BIGINT, VARCHAR length, dll.) selama relasi dan nama kolom yang dipakai query tetap konsisten.

### Master & pengguna

#### `users`

Akun **admin** (`role = 'admin'`) dan **pengguna** (`role = 'user'`).

| Kolom (umum) | Keterangan |
|----------------|------------|
| `id` | PK, AI |
| `nama` | Nama tampilan |
| `email` | Unik, untuk login |
| `password` | Hash (`password_hash` PHP) |
| `role` | `admin` / `user` |
| `is_active` | 1 = aktif |
| `created_at` | Datetime |
| `created_by`, `updated_at`, `updated_by` | Opsional; dipakai di beberapa modul |

#### `m_kategori`

| Kolom | Keterangan |
|-------|------------|
| `id` | PK |
| `initial` | Kode/singkatan |
| `nama_kategori` | Nama |
| `is_active` | 0/1 |
| `created_at`, `created_by`, `updated_at`, `updated_by` | Audit |

#### `m_buku`

| Kolom | Keterangan |
|-------|------------|
| `id` | PK |
| `id_kategori` | FK → `m_kategori.id` |
| `judul_buku`, `deskripsi`, `penulis`, `penerbit` | Data buku |
| `tahun_penerbit` | Disimpan sebagai string/data tanggal sesuai form |
| `harga` | Decimal |
| `stok` | Integer; **dikurangi saat admin konfirmasi pembayaran (paid)** |
| `gambar` | Nama file di `assets/admin/img/buku/` |
| `is_active` | 0/1 — buku tampil di katalog jika aktif |
| `created_at`, `created_by`, `updated_at`, `updated_by` | Audit |

#### `m_status`

Status alur **pembayaran / transaksi** (header pesanan).

| `id` (di kode) | Arti |
|----------------|------|
| `1` | Menunggu Pembayaran — `STATUS_MENUNGGU_PEMBAYARAN` |
| `2` | Menunggu Konfirmasi — `STATUS_MENUNGGU_KONFIRMASI` |
| `3` | Terkonfirmasi — `STATUS_TERKONFIRMASI` |
| `4` | Dibatalkan — `STATUS_DIBATALKAN` |

Kolom yang dipakai: minimal `id`, `status` (label tampilan).

#### `m_status_pengiriman`

Master status **pengiriman** (paket). Contoh label: *Menyiapkan*, *Dikirim*, *Diterima*, dll.

| Kolom | Keterangan |
|-------|------------|
| `id` | PK |
| `status_pengiriman` | Teks tampilan |

**Penting untuk fitur pengguna:** jika admin set status teks **"Dikirim"** (tanpa peduli besar/kecil huruf di logika), pengguna melihat tombol **Sampai Tujuan**. Setelah klik, sistem mencari ID untuk salah satu nama: **Diterima**, **Sampai Tujuan**, **Selesai**, **Paket diterima** — minimal satu harus ada di tabel ini.

#### `m_contact`

Pesan form **Hubungi Kami** di halaman pengguna.

| Kolom | Keterangan |
|-------|------------|
| `id` | PK (disarankan AI) |
| `nama`, `email`, `deskripsi` | Wajib |

Contoh DDL:

```sql
CREATE TABLE m_contact (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL,
  deskripsi TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### Transaksi penjualan

#### `trans_h_pesanan` (header)

| Kolom | Keterangan |
|-------|------------|
| `id` | PK |
| `kode_transaksi` | Unik, format `BK-YYYYMMDD-XXXX` |
| `id_user` | FK → `users.id` (pemesan) |
| `bukti_pembayaran` | Nama file di `assets/admin/img/bukti_pembayaran/` |
| `deskripsi` | Catatan/teks header (bisa berisi ringkasan pemesanan) |
| `is_paid` | 0/1 |
| `id_status` | FK → `m_status.id` |
| `id_pengirim` | FK → `m_status_pengiriman.id` (nullable) |
| `deskripsi_pengiriman` | Resi/catatan pengiriman dari admin |
| `created_at` | Waktu buat |
| `updated_at`, `updated_by` | Audit (digunakan admin & beberapa update pengguna) |

#### `trans_d_pesanan` (detail)

Satu header dapat berisi **beberapa baris** (beberapa judul buku / jumlah).

| Kolom | Keterangan |
|-------|------------|
| `id` | PK |
| `id_header` | FK → `trans_h_pesanan.id` |
| `id_buku` | FK → `m_buku.id` |
| `harga_satuan` | Harga per buku saat transaksi |
| `jumlah_buku` | Qty |
| `total_harga` | `harga_satuan * jumlah_buku` |

---

## Alur bisnis (ringkas)

1. **Pengguna** menambah buku ke **keranjang** (localStorage), lalu **checkout** → dibuat `trans_h_pesanan` + `trans_d_pesanan`, status **Menunggu Pembayaran** (stok **belum** dipotong).
2. Pengguna **unggah bukti** → status **Menunggu Konfirmasi**.
3. **Admin** konfirmasi **Paid** → `is_paid = 1`, status **Terkonfirmasi**, dan **stok `m_buku` dikurangi** per baris detail (dengan cek `stok >= jumlah`).
4. Admin memperbarui **status pengiriman** & `deskripsi_pengiriman`.
5. Jika status pengiriman **Dikirim**, pengguna dapat klik **Sampai Tujuan** (update `id_pengirim` ke status “diterima” sesuai master).

---

## URL & autentikasi

| Konteks | Lokasi |
|---------|--------|
| Beranda pengguna | `/` → `index.php` (path relatif folder proyek) |
| Katalog penuh | `users/modules/semua-buku/` |
| Proses POST pengguna | `users/proses.php` |
| Login admin | `admin/index.php` (form login dashboard) |
| Modul admin | `admin/modules/<modul>/` |

Konstanta `LOGIN_URL` di `includes/admin/init.php` diarahkan ke `BASE_URL . 'login'`. Jika di lingkungan Anda tidak ada route `/login`, sesuaikan agar mengarah ke halaman login admin yang benar (mis. `admin/index.php`).

---

## Fitur modul admin

- **Dashboard** — statistik ringkas.
- **Kategori Buku** — CRUD kategori.
- **Buku** — CRUD buku + unggah sampul.
- **User** — daftar pengguna (view/detail).
- **Transaksi Pesanan** — konfirmasi/batalkan, unggah/view bukti, update pengiriman; **paid memotong stok**.
- **Laporan Penjualan Buku** — agregasi penjualan.
- **Pesan User** — baca pesan dari `m_contact` (view + modal).

---

## Pengembangan

- Gaya kode mengikuti pola mysqli + escape string yang sudah ada di proyek.
- Untuk **keamanan** produksi, pertimbangkan prepared statements, CSRF pada form, dan pembatasan akses file unggahan.

---

# Prototipe E-Katalog Norton Bali Smartphone (v2: tampilan biru modern)

> **Yang berubah pada v2 hanya tampilan.** Seluruh fitur, logika PHP, aturan validasi impor, dan struktur basis data
> sama seperti versi sebelumnya (`includes/auth.php`, `includes/importer.php`, `includes/xlsx.php`, `includes/helpers.php`,
> `includes/db.php`, dan `includes/config.php` tidak diubah).

## Ringkasan pembaruan tampilan
- **Palet biru**: navy `#0b1f4b`, biru `#1d4ed8` / `#2563eb` / `#3b82f6`, latar `#f5f8ff`; merah hanya untuk galat dan penghapusan.
- **Halaman publik**: header lengket dengan efek bayangan saat gulir, hero berlatar gradien dengan kolom pencarian, kategori berbentuk pil,
  kartu produk dengan efek *hover*, label promo, lencana stok, dan kerangka pemuat gambar; halaman detail dengan breadcrumb, tombol aksi,
  dan perbesaran gambar; footer informatif.
- **Panel admin**: sidebar navy dengan ikon, kartu statistik beranimasi, tabel modern, area tarik-dan-lepas untuk berkas impor,
  pratinjau foto produk, dan lencana status riwayat impor.
- **Halaman login**: tata letak dua kolom, ikon pada isian, tombol tampil/sembunyi kata sandi.
- **Interaksi (JavaScript opsional)** pada `asset/js/app.js`: dialog konfirmasi hapus, indikator "Memproses..." saat impor,
  pesan yang menutup otomatis, tombol kembali ke atas, animasi kemunculan kartu. Tanpa JavaScript, seluruh fitur tetap berjalan.
- Tanpa pustaka dan font eksternal, sehingga tetap berjalan tanpa internet. Ikon berupa SVG sprite di `includes/layout.php`.
- Tetap responsif (NFR-03): menu ringkas pada layar kecil, kisi produk dua kolom, sidebar admin menjadi bilah horizontal.

Berkas yang berubah: `includes/layout.php`, `asset/css/style.css`, `asset/js/app.js` (baru), `index.php`, `katalog.php`, `produk.php`,
`tentang.php`, `login.php`, dan seluruh berkas di folder `admin/` (hanya markup tampilan).

---


Aplikasi uji coba (PHP + MySQL/MariaDB, tanpa framework dan tanpa pustaka eksternal) dengan tata letak
mengikuti wireframe **Gambar 3.9 dan 3.10** BAB III (kini bertema biru modern). Seluruh data dibaca dari database
`u963005915_e_catalog` milik Anda.

## 1. Kebutuhan
- PHP 7.2 atau lebih baru (disarankan 8.x) dengan ekstensi: `pdo_mysql`, `mbstring`, `zip` (untuk .xlsx).
  Semuanya sudah aktif bawaan pada XAMPP dan Laragon.
- MySQL 5.7+ atau MariaDB 10.3+.

## 2. Langkah menjalankan di localhost

**A. XAMPP / Laragon**
1. Ekstrak ZIP ke `C:\xampp\htdocs\e-katalog` (Laragon: `C:\laragon\www\e-katalog`).
2. Jalankan Apache dan MySQL, buka phpMyAdmin.
3. Buat database kosong bernama `u963005915_e_catalog` (collation `utf8mb4_unicode_ci`), lalu **Import**
   file `database/u963005915_e_catalog.sql`.
4. Jika user/kata sandi MySQL Anda bukan `root` / kosong, ubah `includes/config.php`.
5. Buka `http://localhost/e-katalog/`.

**B. Tanpa XAMPP (server bawaan PHP)**
```
cd e-katalog
php -S localhost:8000
```
Lalu buka `http://localhost:8000/` (pastikan MySQL sudah berjalan dan database sudah diimpor).

## 3. Akun admin
Database Anda berisi akun `admin` (superadmin) dan `staff`, tetapi kata sandinya tersimpan sebagai hash
bcrypt sehingga tidak dapat dibaca. Jika Anda lupa kata sandinya, tetapkan yang baru:
- Peramban (hanya dari localhost): `http://localhost/e-katalog/tools/reset_password.php`
- atau terminal: `php tools/reset_password.php admin KataSandiBaru123`

Hapus folder `tools/` sebelum aplikasi ditaruh di server publik. Login admin: `http://localhost/e-katalog/login.php`.

## 4. Peta halaman (sesuai Gambar 3.3)
| Halaman | Berkas | Kebutuhan |
|---|---|---|
| Beranda | `index.php` | FR-03, FR-05, NFR-03 |
| Katalog (pencarian, filter, halaman) | `katalog.php` | FR-03, FR-05, FR-06, FR-08 |
| Detail Produk (+ pengingat stok bila habis) | `produk.php` | FR-06, FR-08 |
| Tentang Kami dan Kontak | `tentang.php` | |
| Login / Logout | `login.php`, `logout.php` | FR-01, FR-09, NFR-01/02/08 |
| Dashboard | `admin/index.php` | |
| Kelola Kategori | `admin/kategori.php` | FR-07 |
| Kelola Produk | `admin/produk.php` | FR-02 |
| Impor Data Massal | `admin/impor.php` | FR-04, FR-10 |
| Riwayat Impor | `admin/riwayat.php` | FR-11 |

## 5. Kebijakan impor data massal (Subbab 3.5.4)
- Format `.csv` (pemisah koma, titik koma, atau tab) dan `.xlsx`, maksimal 5 MB. Judul kolom: `SKU`, `Nama Barang`
  (atau `Product`), `Stok` (atau `Qty`), `Harga`.
- **Semua atau tidak sama sekali**: satu pelanggaran (SKU kosong/ganda, stok atau harga tidak sah, kolom hilang,
  ekstensi salah) membatalkan seluruh impor dan tidak ada data yang berubah. Kegagalan penyimpanan di-*rollback*.
- SKU sudah ada: hanya `nama_produk`, `harga`, `stok` diperbarui. SKU baru: ditambahkan tanpa kategori, foto, dan deskripsi
  (atur `IMPOR_PRODUK_BARU_TAMPIL` di `config.php` bila ingin disembunyikan dulu). Produk yang tidak ada di berkas tidak diubah/dihapus.
- Format harga `1500000`, `1.500.000`, `1,500,000.00`, dan `Rp 1.500.000` dikenali.
- Setiap impor dicatat pada tabel `riwayat_impor`. **Tabel ini tidak ada di database awal Anda**, jadi dibuat otomatis
  saat halaman Impor/Riwayat dibuka (`CREATE TABLE IF NOT EXISTS`). SQL manualnya ada di `database/riwayat_impor.sql`.
- Berkas `.xls` (Excel lama) belum didukung karena pembaca XLSX dibuat tanpa Composer. Simpan ulang sebagai `.xlsx` atau `.csv`.

## 6. Berkas contoh uji (`contoh_impor/`)
| Berkas | Hasil yang diharapkan | Skenario |
|---|---|---|
| `01_impor_valid.csv`, `04_impor_valid.xlsx` | Berhasil (impor pertama): 10 produk diperbarui, 2 ditambahkan | UJI-C01, C02 |
| `02_impor_valid_titik_koma.csv` | Berhasil (pemisah `;`) | UJI-C02 |
| `03_impor_stok_nol.csv` | Berhasil; produk berstatus Habis di katalog | UJI-C06 |
| `05_impor_format_rupiah.csv` | Berhasil (harga berformat Rp) | UJI-C01 |
| `10_sku_kosong.csv` | Ditolak, baris 5 dilaporkan | UJI-C11 |
| `11_sku_ganda.csv` | Ditolak, baris ganda dilaporkan | UJI-C12 |
| `12_stok_tidak_sah.csv` | Ditolak (negatif, desimal, teks) | UJI-C13 |
| `13_harga_tidak_sah.csv` | Ditolak (negatif, huruf) | UJI-C14 |
| `14_kolom_harga_hilang.csv` | Ditolak, kolom Harga hilang | UJI-C10 |
| `15_campuran_satu_baris_salah.csv` | Ditolak seluruhnya; tidak ada baris tersimpan | UJI-C15 |
| `16_hanya_judul_kolom.csv` | Ditolak (tidak ada baris data) | UJI-C09 |
| `17_ekstensi_salah.pdf` | Ditolak (ekstensi) | UJI-C07 |

UJI-C16 (kegagalan penyimpanan): centang *Mode uji* pada halaman Impor lalu unggah `01_impor_valid.csv`; proses dihentikan
di tengah dan seluruh perubahan di-*rollback*. Matikan mode ini dengan `MODE_UJI = false` di `config.php`.
Data uji yang masuk (SKU `TEST-xxx`) dapat dihapus lewat Kelola Produk.

## 7. Penyesuaian terhadap database Anda
Rancangan BAB III dan database nyata berbeda; aplikasi mengikuti **database nyata**:

| BAB III | Database Anda |
|---|---|
| `tb_admin` | `users` (ada `role` dan `status`) |
| `tb_kategori` | `kategori` (ada `urutan`) |
| `tb_produk` (PK `sku`) | `produk` (PK `id_produk`, `sku` bersifat UNIQUE; ada `warna`, `tampil`) |
| `spesifikasi` | `deskripsi` |
| kategori bawaan "Belum Dikategorikan" | `id_kategori` boleh NULL (produk tanpa kategori) |
| `tb_riwayat_impor` | tidak ada, dibuat otomatis sebagai `riwayat_impor` |
| tidak ada | `atur_promo`, `atur_promo_produk` (harga promo dihitung otomatis), `pengingat_stok`, `media`, `profile` |

Perbarui ERD dan kamus data di BAB III agar sesuai, atau sebaliknya.

## 8. Catatan prototipe
- Peran `staff` tidak dapat menghapus produk/kategori; `admin` dan `superadmin` dapat.
- Pengelolaan promo dan pengingat stok belum memiliki halaman admin (hanya tampil/tercatat).
- Gambar produk pada database berupa URL `nbcbali.com`; bila offline, kotak gambar menampilkan silang wireframe.
  Foto yang diunggah disimpan di `asset/img/` dan dicatat pada tabel `media`.
- Folder `asset/img/` harus dapat ditulis oleh PHP.

# Instalasi awal Co-Labora

## Status fondasi

Repository ini memakai CodeIgniter 3.1.13 dan ditargetkan untuk PHP 8.0.0 sesuai lingkungan pengembangan yang ditentukan. Runtime yang tersedia di workspace implementasi berbeda, sehingga uji runtime PHP 8.0 tetap harus dilakukan di lingkungan target. Satu instalasi dan satu basis data digunakan untuk satu perusahaan; jangan berbagi basis data antarperusahaan.

Fondasi awal menyediakan autentikasi, peran Administrator Perusahaan, HRD/Payroll, dan Finance, serta migrasi tabel pengguna. Modul bisnis belum lengkap. Admin mengelola akun aplikasi dan tidak mendapat akses payroll otomatis.

## Prasyarat

- PHP 8.0 dengan ekstensi `mysqli`, `mbstring`, dan `openssl`.
- MySQL/MariaDB dengan dukungan InnoDB dan `utf8mb4`.
- HTTPS di lingkungan produksi.
- Web server yang mengarahkan permintaan aplikasi ke `index.php`.

## Konfigurasi

Atur environment variable di konfigurasi PHP-FPM/container atau mekanisme rahasia deployment; jangan simpan nilainya di repository:

| Variable | Kegunaan |
| --- | --- |
| `CI_ENV` | Gunakan `production` untuk deployment; default aplikasi adalah `production`. |
| `COLABORA_BASE_URL` | URL aplikasi, dengan garis miring di akhir. |
| `COLABORA_DB_HOST` | Host MySQL; default `localhost`. |
| `COLABORA_DB_NAME` | Nama basis data khusus instalasi ini. |
| `COLABORA_DB_USER` | Pengguna basis data dengan hak minimum yang diperlukan. |
| `COLABORA_DB_PASSWORD` | Kata sandi basis data. |
| `COLABORA_ENCRYPTION_KEY` | Kunci rahasia acak yang hanya tersedia di server; wajib sebelum menyimpan rahasia integrasi. |

Buat basis data dan akun database dengan charset `utf8mb4`. Pastikan direktori `application/cache` dan `application/logs` hanya dapat ditulis oleh akun aplikasi.

## Migrasi dan akun administrator awal

Jalankan migrasi dari CLI, bukan melalui URL web:

```sh
php index.php migrate latest
```

Masukkan data administrator melalui prompt shell, lalu jalankan perintah setup. Dengan cara ini kata sandi tidak menjadi bagian dari argumen perintah atau riwayat shell:

```sh
read -r -p "Nama administrator: " COLABORA_ADMIN_NAME
read -r -p "Email administrator: " COLABORA_ADMIN_EMAIL
read -r -s -p "Kata sandi administrator: " COLABORA_ADMIN_PASSWORD
printf '\n'
export COLABORA_ADMIN_NAME COLABORA_ADMIN_EMAIL COLABORA_ADMIN_PASSWORD
php index.php setup create_admin
unset COLABORA_ADMIN_NAME COLABORA_ADMIN_EMAIL COLABORA_ADMIN_PASSWORD
```

Gunakan kata sandi unik minimal 12 karakter dan hapus variabel dari shell setelah setup. Perintah hanya membuat akun administrator apabila tabel pengguna masih kosong. Administrator kemudian dapat membuat akun HRD/Payroll, Finance, atau administrator tambahan.

Setelah instalasi, pastikan `CI_ENV=production`, HTTPS aktif, dan endpoint migrasi hanya digunakan melalui CLI. Jika TLS dihentikan di reverse proxy, konfigurasikan web server agar PHP mengenali koneksi sebagai HTTPS; jangan mempercayai header forwarding yang dapat dikirim langsung oleh klien. Jangan menampilkan error PHP/database kepada pengguna.

## Catatan integrasi

Pengaturan dan koneksi Co-Balance belum diimplementasikan. Endpoint yang diketahui hanya uji koneksi `GET /api/koneksi`; API master jasa, penjualan, payroll, dan transaksi belum tersedia. Jangan mengaktifkan sinkronisasi atau menyimpan token sebelum kontrak endpoint dan izin tersedia serta kunci enkripsi server telah disiapkan.

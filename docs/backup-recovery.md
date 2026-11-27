# Backup dan pemulihan

Setiap instalasi Co-Labora memakai basis data khusus untuk satu perusahaan. Batasi akses backup karena data karyawan dan payroll bersifat sensitif. Simpan backup terenkripsi di lokasi terpisah dari server aplikasi, batasi aksesnya, dan jangan taruh file backup di repository.

Gunakan akun database melalui file opsi MySQL di luar repository dengan izin baca hanya untuk pemilik (`0600`); jangan memasukkan kata sandi dalam argumen perintah atau log. Contoh berikut memakai environment variable nama database yang sama dengan konfigurasi instalasi:

```sh
umask 077
mysqldump --single-transaction --routines --triggers "$COLABORA_DB_NAME" > colabora-backup.sql
```

Enkripsi file backup sesuai pengelolaan kunci organisasi sebelum menyalinnya ke penyimpanan terpisah. Catat waktu backup, versi aplikasi, versi migrasi, dan lokasi kunci enkripsi secara terpisah dari file backup.

## Pemulihan

1. Siapkan host dan basis data terisolasi, dengan charset `utf8mb4`; jangan menimpa produksi saat uji pemulihan.
2. Dekripsi salinan backup ke lokasi sementara dengan akses terbatas, lalu pulihkan menggunakan akun database yang berwenang.
3. Pulihkan kunci konfigurasi aplikasi dari secret store yang disetujui. Tanpa kunci yang cocok, rahasia terenkripsi pada implementasi integrasi mendatang tidak dapat dipulihkan.
4. Jalankan aplikasi pada lingkungan terisolasi; periksa versi migrasi, jumlah akun, akses login, dan integritas data sebelum mengalihkan layanan.
5. Catat hasil uji pemulihan dan hapus salinan sementara secara aman.

Skema dan data Co-Labora saat ini masih pada fondasi awal; jadwalkan dan uji backup/pemulihan ulang ketika tabel bisnis dan integrasi ditambahkan.

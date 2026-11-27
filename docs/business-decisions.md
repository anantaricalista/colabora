# Keputusan kebutuhan Co-Labora

Dokumen ini mencatat keputusan yang sudah disepakati. Hal-hal yang belum diputuskan tidak dijadikan perilaku aplikasi.

## Dasar produk

- Co-Labora berjalan mandiri, terpisah dari Co-Balance.
- Setiap perusahaan menggunakan instalasi dan basis data terpisah.
- Integrasi Co-Balance bersifat opsional per perusahaan. Endpoint selain pemeriksaan koneksi belum tersedia dan tidak dipanggil oleh aplikasi.
- Administrator perusahaan mengelola akun dan pengaturan integrasi; akses payroll tidak otomatis diberikan kepada administrator.
- Satu akun dapat memegang beberapa peran: Administrator Perusahaan, HRD/Payroll, Finance, dan Karyawan.

## Proyek dan tugas

- Administrator membuat proyek dan memilih koordinator. Proyek langsung aktif.
- Koordinator harus merupakan akun aktif dengan peran Karyawan.
- Koordinator mengelola tugas dan menjadi satu-satunya pihak yang menetapkan satu penanggung jawab per tugas. Koordinator boleh menetapkan dirinya sendiri.
- Karyawan hanya melihat proyek yang memiliki tugas untuknya dan hanya melihat tugas yang ditugaskan kepadanya. Koordinator melihat seluruh tugas proyek yang dikoordinasikannya.
- Status tugas: belum selesai atau selesai. Karyawan yang ditugaskan dapat menandai selesai; hanya koordinator yang dapat mengubah kembali menjadi belum selesai.
- Tenggat tugas opsional. Urgensi normal/mendesak hanya merupakan prioritas dan tidak memblokir tugas lain.
- Progres dihitung dari tugas selesai dengan bobot sama. Proyek aktif otomatis selesai ketika sedikitnya satu tugas ada dan seluruh tugas selesai. Koordinator juga dapat menyelesaikan proyek lebih awal dan membuka kembali proyek selesai.
- Administrator perusahaan dapat mengganti koordinator kapan saja dengan akun karyawan aktif.

## Karyawan dan absensi

- Setiap karyawan memiliki akun aplikasi. Administrator mengelola akun/peran; HRD/Payroll mengelola profil karyawan: nomor karyawan internal, jabatan, departemen opsional, tanggal mulai, dan status aktif/nonaktif.
- Profil nonaktif memblokir login; status akun yang dinonaktifkan Administrator tetap dihormati.
- Absensi MVP memakai satu check-in dan satu check-out per tanggal perusahaan; aplikasi hanya menyimpan waktu mentah, tanpa menghitung terlambat, lembur, atau potongan.
- Zona waktu perusahaan dipilih Administrator pada Pengaturan: WIB (default), WITA, atau WIT. Tidak ada geofence; koordinat hanya bukti lokasi.
- Setiap tindakan mandiri mengambil satu foto dan koordinat baru; aplikasi tidak menampilkan pratinjau video langsung. Izin kamera/lokasi dan persetujuan eksplisit diperlukan.
- Foto dan koordinat disimpan sebagai BLOB terenkripsi di basis data. Karyawan hanya dapat melihat bukti miliknya; HRD/Payroll yang ditunjuk dapat melihat bukti untuk verifikasi.
- Bukti dari browser adalah data kiriman perangkat dan bukan verifikasi anti-pemalsuan; geolokasi tidak dibatasi geofence.
- Jika perangkat/izin tidak tersedia, check-in/check-out mandiri diblokir dan HRD/Payroll dapat membuat pengecualian manual dengan alasan wajib.
- HRD/Payroll dapat mengoreksi waktu; setiap perubahan mencatat nilai lama/baru, pelaku, waktu, dan alasan pada riwayat audit yang tidak diedit melalui aplikasi.
- Foto absensi tidak dihapus otomatis dan hanya foto yang dapat dihapus manual; catatan absensi, waktu, koordinat, dan audit tetap disimpan. Aplikasi belum menyediakan alur penghapusan foto, sehingga pihak berwenang dan prosedurnya harus disepakati sebelum penggunaan produksi.
- Optimasi backup khusus absensi ditunda sampai uji fungsional lokal. Backup database biasa mencakup BLOB bukti dan tetap perlu dilindungi sejak awal.

## Integrasi Co-Balance

- Setiap item jasa pada satu penjualan akan membuat proyek tersendiri, dengan referensi transaksi penjualan bersama untuk menghubungkan proyek-proyek tersebut.
- Jika penjualan sumber dibatalkan atau dikoreksi, proyek dan riwayatnya dipertahankan serta ditandai untuk tinjauan manual.
- Keputusan ini belum dapat dijalankan sampai API Co-Balance yang diperlukan tersedia dan kontraknya diverifikasi.

## Penggajian

- HRD/Payroll menyusun penggajian; Finance memeriksa anggaran, PPh 21, dan iuran BPJS, lalu menjadi verifikator/persetujuan akhir.
- Persetujuan Finance menjadi dasar penerbitan slip dan penyiapan draft integrasi ketika API tersedia.
- Akuntansi mengikuti pengakuan utang gaji terlebih dahulu, kemudian pencatatan pembayaran Kas/Bank.
- Komponen pendapatan dan potongan dapat ditambahkan melalui master. Komponen tetap dapat ditetapkan per karyawan; komponen nominal variabel seperti bonus/lembur diisi HRD per periode setelah jenisnya terdaftar.
- Komponen potongan dapat diatur per karyawan. Komponen nominal tetap atau persentase gaji pokok dapat dikonfigurasi; pajak/iuran dapat dinonaktifkan bila dikelola di sistem lain. Rumus resmi PPh 21 belum disepakati dan bukan bagian implementasi saat ini.

## Belum diputuskan

- Status dan alur persetujuan proyek di luar aturan aktif/selesai yang telah dipilih.
- Data pelanggan, master jasa mandiri, dan model hubungan layanan dengan proyek.
- Formula, dasar pajak, periode efektif, pembulatan, dan perlakuan hukum payroll; aturan tersebut perlu ditinjau sebelum penghitungan otomatis diterapkan.
- Pihak yang berwenang dan prosedur penghapusan manual foto absensi.
- Kontrak endpoint, izin, status pengiriman, retry, dan idempotensi integrasi transaksi.

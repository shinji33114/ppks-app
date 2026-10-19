# Panduan Penggunaan Sistem PPKS

Panduan ini dibuat untuk membantu pengguna baru, termasuk yang belum terbiasa menggunakan website. Ikuti langkahnya satu per satu. Akun Admin menjalankan seluruh tahapan pelayanan, sedangkan Super Admin juga dapat mengelola akun pengguna.

## 1. Kegunaan Website

Sistem PPKS adalah website untuk membantu petugas Dinas Sosial Kabupaten Tuban mencatat dan mengelola pelayanan bagi Pemerlu Pelayanan Kesejahteraan Sosial (PPKS). Data klien dicatat secara bertahap agar petugas dapat melihat perkembangan penanganan dengan lebih teratur.

Melalui website ini, petugas dapat:

- Mendaftarkan klien baru dan mencatat informasi awal.
- Melengkapi assessment atau penilaian kondisi klien.
- Mencatat rencana dan perkembangan penanganan.
- Membuat dan memproses surat terkait penyelesaian atau rujukan klien.
- Melihat seluruh data klien dan memantau statistik pelayanan.
- Mengelola akun pengguna.

## 2. Cara Mengakses Website

1. Pastikan telepon genggam, tablet, atau komputer terhubung ke internet.
2. Buka aplikasi peramban (browser), misalnya Google Chrome, Microsoft Edge, atau Safari.
3. Ketik atau buka alamat website yang diberikan oleh petugas/pengelola sistem. Alamat website berbeda-beda menurut tempat pemasangan; mintalah alamat yang benar kepada Admin.
4. Pada halaman masuk, ketik alamat email dan kata sandi akun Anda.
5. Tekan tombol **Masuk**. Setelah berhasil, halaman utama akan terbuka.

Setiap pengguna harus memakai akun sendiri. Jika belum memiliki akun atau akun tidak dapat digunakan, hubungi Admin. Jangan membagikan kata sandi kepada orang lain.

### Kredensial Admin dan Super Admin

Email akun Admin dan Super Admin serta kata sandi awal diberikan langsung oleh pengelola sistem melalui saluran privat. Jangan menuliskan kata sandi aktif, kata sandi awal, atau kredensial login di README, repositori, maupun grup percakapan. Setelah login pertama, segera ganti kata sandi melalui menu **Akun Saya**. Jika kata sandi awal belum diterima atau tidak dapat digunakan, hubungi pengelola sistem untuk meminta bantuan.

### Jika lupa kata sandi

Gunakan tautan **Lupa kata sandi** pada halaman masuk jika tersedia dan ikuti petunjuk yang dikirim ke email akun. Jika tidak menerima email atau masih kesulitan, mintalah bantuan Admin.

## 3. Cara Menggunakan Website

### Mengenali halaman utama

- **Menu di sisi kiri** digunakan untuk berpindah halaman. Pada telepon genggam, menu mungkin perlu dibuka melalui tombol menu.
- **Dashboard Statistik** adalah halaman utama setelah masuk, berisi ringkasan dan statistik pelayanan PPKS.
- **Data Klien** menampilkan seluruh data klien, termasuk klien yang sudah selesai atau dirujuk.
- Semua akun Admin memiliki akses ke menu yang sama. Tombol tindakan tetap mengikuti status dan tahapan klien.
- Ikon lonceng digunakan untuk melihat pemberitahuan, jika ada.
- Menu nama pengguna/profil menyediakan halaman **Akun Saya** dan tombol **Keluar**.

### Alur pelayanan secara umum

1. **Tahap 1 — Registrasi Awal:** Admin mencatat informasi dan dokumen awal klien.
2. **Tahap 2 — Assessment:** Admin melengkapi assessment klien yang menunggu.
3. **Tahap 3 — Penanganan:** Admin mencatat rencana dan perkembangan pelayanan.
4. **Penyelesaian atau rujukan:** Admin menerbitkan surat keluar atau rujukan. Klien langsung ditandai selesai/dirujuk dan surat dapat dilihat, dicetak, serta diunduh tanpa tahap persetujuan.

Satu akun dapat menjalankan semua tahap, tetapi urutan dan aturan status tetap berlaku. Contohnya, assessment hanya dapat dimulai untuk klien yang masih berstatus draft; data Tahap 1 terkunci setelah assessment dimulai; dan catatan penanganan tidak dapat ditambahkan setelah klien diarsipkan.

### Menggunakan halaman Data Klien, Assessment, dan Penanganan

- **Data Klien:** Kartu progres menampilkan jumlah klien pada setiap tahap. Pilih kartu untuk melihat daftar klien pada tahap tersebut. Untuk mencari klien berdasarkan tanggal masuk, pilih tanggal pada filter; daftar akan langsung diperbarui. Filter tanggal dapat digunakan bersamaan dengan filter tahap, dan dapat dihapus untuk kembali ke daftar yang lebih luas.
- **Memulai Assessment:** Buka detail klien yang masih berstatus draft, lalu tekan **Mulai Assessment** di bawah foto klien. Formulir assessment terbuka untuk klien tersebut dan data Tahap 1 akan terkunci. Lengkapi semua bagian formulir; jika data tidak tersedia, gunakan tanda **-** atau pilihan **Tidak diketahui** sesuai petunjuk, kemudian simpan assessment.
- **Assessment:** Menu Assessment menampilkan antrean klien yang menunggu. Tekan **Mulai Assessment** untuk membuka formulir klien terkait. Assessment yang telah tersimpan dapat diperbarui selama klien belum masuk tahap penanganan atau diarsipkan.
- **Penanganan:** Menu Penanganan menampilkan klien yang siap atau sedang ditangani. Pilih **Kelola** untuk menambahkan catatan tindakan, melihat riwayat pendampingan, atau mengisi formulir surat keluar/rujukan. Penerbitan surat menyelesaikan proses klien sesuai jenis surat.

### Mengisi dan menyimpan formulir

1. Buka menu yang sesuai dengan tugas Anda.
2. Pilih klien atau tekan tombol untuk membuat data baru.
3. Isi kolom yang tersedia dengan teliti. Kolom bertanda wajib tidak boleh dikosongkan.
4. Periksa kembali nama, tanggal, dan informasi lainnya sebelum menyimpan.
5. Tekan **Simpan** atau tombol yang tersedia, lalu tunggu sampai muncul pemberitahuan berhasil.
6. Jika muncul pesan kesalahan, baca pesan tersebut dan perbaiki kolom yang disebutkan.

Data klien bersifat rahasia. Gunakan hanya untuk pekerjaan pelayanan dan jangan mengirimkan atau memperlihatkannya kepada pihak yang tidak berwenang.

## 4. Role, Tugas, dan Hak Akses

Role adalah jenis hak akses akun. **Admin** dapat menjalankan seluruh tahapan pelayanan. **Super Admin** memiliki akses yang sama, ditambah hak untuk mengelola akun dan menetapkan role. Saat migrasi, akun Admin dengan ID terkecil dipromosikan menjadi Super Admin pertama. Role tidak mengubah atau melewati urutan tahapan pelayanan.

### Cara Super Admin menambahkan pengguna

Hanya Super Admin yang dapat membuka menu Manajemen Pengguna dan membuat akun baru. Akun baru dapat diberi role Admin atau Super Admin.

1. Masuk ke website menggunakan akun Super Admin.
2. Pilih menu **Manajemen Pengguna** di bagian menu sebelah kiri.
3. Pada halaman daftar pengguna, tekan tombol **Tambah Pengguna**.
4. Isi kolom **Nama** dengan nama orang yang akan diberi akun.
5. Isi **Email** dengan alamat email yang benar dan belum pernah dipakai untuk akun lain.
6. Pilih role **Admin** untuk akses operasional atau **Super Admin** untuk akses operasional sekaligus manajemen akun.
7. Buat **Password** minimal 8 karakter, lalu ketik password yang sama pada kolom **Konfirmasi Password**.
8. Tekan **Simpan Pengguna**. Jika akun berhasil dibuat, pengguna akan muncul pada daftar pengguna dengan status **Aktif**.
9. Berikan nama pengguna, email, dan password awal kepada pemilik akun secara pribadi. Sarankan pengguna mengganti password melalui menu **Akun Saya** setelah masuk. Jangan mengirim password di grup atau membagikannya kepada orang lain.

Jika ada pesan kesalahan, periksa kolom yang ditandai. Email harus unik, role harus dipilih, dan kedua isian password harus sama. Akun yang dibuat langsung aktif dan dapat masuk menggunakan email serta password awal.

### Cara menonaktifkan atau mengaktifkan akun

1. Buka menu **Manajemen Pengguna**.
2. Cari nama pengguna pada daftar. Gunakan nomor halaman di bagian bawah daftar jika nama belum terlihat.
3. Periksa nama, email, dan role agar tidak salah memilih akun.
4. Tekan **Nonaktifkan** untuk mencegah akun tersebut digunakan masuk. Tekan **Aktifkan** untuk mengizinkan akun yang nonaktif digunakan kembali.
5. Akun yang sedang digunakan tidak dapat dinonaktifkan sendiri. Sistem juga menjaga agar setidaknya ada satu akun Super Admin yang aktif.

## 5. Istilah yang Sering Digunakan

- **Klien**: orang yang sedang atau telah menerima pelayanan.
- **Registrasi**: pencatatan informasi awal klien.
- **Assessment**: pengumpulan informasi untuk memahami kondisi dan kebutuhan klien.
- **Penanganan**: rencana dan kegiatan pelayanan untuk klien.
- **Arsip**: data klien yang sudah selesai ditangani atau dirujuk.
- **Rujukan**: pengalihan atau rekomendasi pelayanan ke pihak/layanan lain.
- **Role**: hak akses pengguna. Admin mengelola pelayanan; Super Admin juga mengelola akun dan role.

## 6. Bantuan

Jika menu tidak terlihat, tindakan tidak dapat dilakukan, atau muncul pesan yang tidak dipahami:

1. Pastikan Anda masuk menggunakan akun yang benar.
2. Baca pesan pada layar dan catat nama menu atau klien yang sedang dibuka.
3. Hubungi Admin atau administrator sistem untuk meminta bantuan.

Gunakan akun pribadi dan jaga kerahasiaan data klien. Walaupun semua akun memiliki akses Admin, aturan tahapan dan status klien tetap membatasi tindakan yang dapat dilakukan pada setiap tahap.

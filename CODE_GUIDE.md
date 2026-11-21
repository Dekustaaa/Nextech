# Panduan Struktur & Komentar Kode Nexatech

Dokumen ini menjelaskan pembagian folder, alur request, dan alasan komentar
ditulis seperti sekarang. Tujuannya agar perubahan berikutnya tidak mengembalikan
duplikasi CSS/JS atau header/footer yang berbeda antarhalaman.

## 1. Prinsip utama

Nexatech memakai pemisahan sederhana:

| Bagian | Tanggung jawab |
| --- | --- |
| Halaman (`mainpage.php`, `pages/`) | Menyiapkan data dan menampilkan markup. |
| `actions/` | Memproses form/aksi (POST/GET), memanggil validasi, redirect, dan flash message. |
| `validations.php` | Aturan validasi input dan pesan error yang dipakai bersama. |
| `repositories/` | Query database dan aturan pengambilan data. |
| `components/` | Layout, header, footer, dan sidebar yang dipakai bersama. |
| `assets/` | Semua CSS dan JavaScript publik maupun admin. |
| `views/` | Halaman lama/user dan seluruh halaman admin. |

Aturan pentingnya:

- Jangan menyalin header/footer ke halaman baru; panggil layout bersama.
- Jangan menaruh CSS/JS di folder halaman atau component; taruh di `assets/`.
- Jangan menaruh query SQL baru di halaman jika query tersebut bisa menjadi
  fungsi repository.
- Komentar menjelaskan **apa** dan **kenapa**, bukan mengulang kode
  (`$x = 1; // isi x dengan 1` tidak berguna).

## 2. Struktur folder

```text
PWL-SAS/
├── mainpage.php                   # Beranda publik
├── CODE_GUIDE.md                  # Dokumen ini
├── validations.php                # Aturan validasi input bersama
├── config/
│   └── database.php               # Koneksi database
├── actions/                       # Handler form dan aksi
├── repositories/                  # Query database
├── components/
│   ├── landing/
│   │   ├── layout.php             # atas()/bawah() untuk halaman publik
│   │   ├── header.php             # Header publik tunggal
│   │   ├── footer.php             # Footer publik tunggal
│   │   └── sidebar.php            # Sidebar lama (tidak dipakai layout baru)
│   └── admin/
│       └── layout_admin.php       # admin_atas()/admin_bawah()
├── pages/
│   ├── categories/index.php       # Katalog kategori + brand
│   └── technician/index.php       # Booking teknisi
├── views/                         # Halaman auth, cart, checkout, admin, dll.
└── assets/
    ├── css/
    │   ├── theme.css              # Tema publik bersama
    │   ├── technician.css         # Khusus booking teknisi
    │   └── admin.css              # Tema panel admin
    └── js/
        ├── preferences.js         # Terapkan bahasa + tema publik sebelum paint
        ├── i18n.js                # Kamus dan mesin bahasa ID/EN
        ├── theme.js               # Dropdown, menu, toast, dan tema publik
        ├── technician.js          # Interaksi booking teknisi
        └── admin.js               # Sidebar/tema admin + Lucide
```

## 3. Alur halaman publik

Contoh halaman baru:

```php
<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../components/landing/layout.php";

$activeNav = "products";                 // menu yang disorot di header
$extraHead = '<link rel="stylesheet" href="/PWL-SAS/assets/css/page.css">';
atas("Judul - Nexatech", $extraHead);
?>

<section>Isi halaman...</section>

<?php
$extraScripts = '<script src="/PWL-SAS/assets/js/page.js"></script>';
bawah($extraScripts);
```

Yang terjadi:

1. `layout.php` memuat `preferences.js` lebih dulu agar tema/bahasa tersimpan
   langsung aktif, lalu `assets/css/theme.css` dan konfigurasi Tailwind.
2. `header.php` membaca session untuk status login dan jumlah keranjang.
3. Halaman mencetak isi di antara `atas()` dan `bawah()`.
4. `bawah()` memuat `i18n.js`, `theme.js`, footer, lalu script khusus halaman.
   Urutan ini penting: kamus bahasa harus siap sebelum komponen lain memakai
   `window.techI18n`.

Halaman teknisi memakai pola ini:

- `pages/technician/index.php` memanggil `atas()`/`bawah()`.
- CSS khusus dimuat lewat `$extraHead` (`assets/css/technician.css`).
- Data teknisi/layanan dikirim ke JavaScript sebelum `assets/js/technician.js`.
- Form dikirim ke `actions/technician_booking.php`; aturan input dicek lewat
  `validations.php`, lalu data disimpan melalui repository.
- Header dan footer yang tampil identik dengan beranda karena memakai component
  yang sama, bukan salinan markup.

## 4. Alur admin

1. Halaman di `views/admin/` memanggil `cek_admin()`.
2. Halaman memuat repository yang dibutuhkan.
3. `admin_atas($judul, $desc, $nama_file)` mencetak sidebar, topbar, dan header.
4. Isi halaman ditutup dengan `admin_bawah()`.
5. `assets/js/admin.js` menangani menu mobile, toggle tema, dan ikon Lucide.
6. Halaman login admin memakai `i18n.js` + `admin.js` langsung karena tidak
   memakai layout dashboard, tetapi tetap mendapat kontrol bahasa/tema.
7. `assets/css/admin.css` menyimpan seluruh tampilan panel admin, termasuk
   override tema gelap untuk dashboard dan halaman login.

Perubahan admin yang dilakukan hanya visual/tema. Aksi seperti simpan produk,
ubah status pesanan, dan logout tetap memakai handler di `actions/` yang sama.

## 5. Validasi input

Semua aturan validasi form ada di `validations.php`:

- Helper dasar: `input_teks()`, `input_angka()`, `validasi_email()`,
  `validasi_telepon()`, `validasi_tanggal()`, `validasi_jam()`, `validasi_id()`,
  dan `validasi_pilihan()`.
- Validator per form: `validasi_login()`, `validasi_register()`,
  `validasi_profil()`, `validasi_kategori()`, `validasi_produk()`,
  `validasi_booking()`, `validasi_rating()`, `validasi_payment()`,
  `validasi_update_booking()`, dan `validasi_order_status()`.
- Setiap validator mengembalikan `null` bila lolos atau string pesan error bila
  gagal. Action tinggal menampilkan pesan tersebut lewat flash message.
- Validasi ini hanya memeriksa bentuk input. Pengecekan yang membutuhkan
  database — seperti email unik, layanan yang dipilih, atau teknisi yang masih
  tersedia — tetap dilakukan di action/repository.

## 6. Bahasa dan tema

### Bahasa (ID/EN)

- Semua halaman publik dan admin memakai kamus bersama di `assets/js/i18n.js`.
- Pilihan bahasa disimpan di `localStorage` dengan key `techhub-lang`; nilai yang
  didukung hanya `id` dan `en`.
- `preferences.js` menulis `<html lang>` dan `data-lang` sebelum halaman dilukis.
  `i18n.js` kemudian menerjemahkan teks statis dan atribut `placeholder`,
  `title`, `aria-label`, serta `alt`. Atribut pada `textarea` ikut diterjemahkan,
  tetapi isi `textarea` tidak disentuh agar data ketikan pengguna aman.
- `document.title` diterjemahkan per segmen dengan pemisah ` - ` atau ` | `,
  jadi judul tab seperti "Keranjang - Nexatech" ikut berubah tanpa mengubah
  format judul halaman admin.
- Tombol bahasa memakai hook `data-lang-toggle`; label kode bahasa memakai
  `data-lang-label`. Hook yang sama dipakai header publik, menu mobile, topbar
  admin, dan halaman login admin.
- Setelah bahasa berubah, `i18n.js` mengirim event
  `techhub:languagechange`. Komponen yang merender teks sendiri (mis.
  `theme.js`, `admin.js`, dan `technician.js`) mendengarkan event ini agar label
  dan isi dinamis ikut berubah.
- Teks yang dibuat JavaScript sebaiknya memakai `window.techI18n.t(key)` atau
  `translate()`. Jika node dirender ulang oleh JavaScript, tandai node/wadahnya
  dengan `data-i18n-skip` agar mesin i18n tidak menimpa hasil render tersebut;
  contohnya jumlah ulasan, status online, dan daftar modal di halaman teknisi.
- Entry kamus ditulis `add("teks Indonesia", "English text", ["alias"])`.
  Alias dipakai untuk halaman yang sumber aslinya berbahasa Inggris. Pola
  dinamis (jumlah produk, tanggal relatif, rating) ditambahkan lewat
  `pattern(...)`.

### Tema publik

- Preferensi publik disimpan di `techhub-theme` (`dark`/`light`), terpisah dari
  tema admin.
- `preferences.js` memasang class `dark` pada `<html>` sebelum CSS dimuat agar
  tidak ada kedipan tema terang.
- Tombol memakai `data-theme-toggle`; label teks memakai `data-theme-label`.
  `theme.js` menangani klik, menyimpan pilihan, memperbarui `aria-pressed`, dan
  menyinkronkan label setelah bahasa berubah.
- Override warna gelap publik ada di `assets/css/theme.css`; komponen khusus
  teknisi ditambahkan di `assets/css/technician.css`.

### Tema admin

- Preferensi admin memakai key `techhub-admin-theme`.
- `admin.js` memasang class `dark` dan menangani `data-admin-theme-toggle`.
- Warna gelap dashboard dan halaman login admin diatur di `assets/css/admin.css`.

## 7. Peta komentar

- **File-level docblock** ada di setiap file PHP utama: menjelaskan peran file
  dalam alur request dan alasan pemisahannya.
- **Komentar section** memisahkan bagian besar: auth, validasi, query, render,
  empty state, dan form.
- **Komentar inline** dipakai pada keputusan yang tidak langsung terlihat:
  validasi server, redirect setelah POST, pemilihan teknisi otomatis, agregasi
  cart, cache-buster `filemtime()`, dan inisialisasi tema sebelum CSS dimuat.
- CSS diberi header bagian agar aturan mudah dicari.
- JavaScript diberi komentar pada alur event, akses keyboard, dan alasan helper
  dipakai bersama.

## 8. Catatan konfigurasi

- Aplikasi dijalankan dari folder `PWL-SAS`, sehingga banyak URL memakai prefix
  `/PWL-SAS/`. Jika folder proyek diganti, prefix tersebut harus disesuaikan.
- Koneksi database ada di `config/database.php` (default `localhost`, user
  `root`, database `techhub`). File ini langsung membuka `$conn`; action dan
  view cukup me-`require_once` file tersebut.
- Login demo:
  - Admin: `mama@gmail.com` / `sondang`
  - User: `user@gmail.com` / `user`
- `components/landing/sidebar.php` adalah komponen lama. Layout publik saat ini
  memakai menu di `header.php`, jadi sidebar tersebut tidak dipanggil halaman
  baru dan dipertahankan hanya untuk kompatibilitas.

## 9. Checklist setelah mengubah kode

1. Jalankan lint semua file PHP:

   ```powershell
   C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l <file>
   ```

2. Buka halaman publik: beranda, katalog, booking teknisi, cart, checkout,
   profil, detail produk, auth, dan Nexsearch.
3. Login sebagai admin dan cek halaman dashboard serta satu halaman CRUD.
4. Ganti bahasa ID/EN di halaman publik dan admin, lalu refresh untuk memastikan
   pilihan tersimpan. Periksa label tombol, menu mobile, judul tab, placeholder
   textarea, dan teks dinamis.
5. Ganti tema publik terang/gelap dan tema admin, lalu refresh. Pastikan teks,
   input, tabel, dan kartu tetap terbaca.
6. Buka console browser; selain peringatan Tailwind CDN, tidak boleh ada error
   JavaScript.
7. Pastikan tidak ada CSS/JS baru yang ditaruh di luar `assets/`.
8. Pastikan komentar baru menjelaskan alasan, bukan hanya mengulang perintah.

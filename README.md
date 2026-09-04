# Surat Digital KUA

Aplikasi web untuk pembuatan surat digital di Kantor Urusan Agama (KUA) berbasis **Laravel 13** dengan alur persetujuan, ekspor PDF berkop KUA, laporan kinerja pegawai (PDF & Word), permohonan surat secara online, dan fitur layanan publik.

## Fitur

- **Autentikasi & role**: staf KUA (membuat surat), Operator KUA (mengelola konten, data master, & persetujuan surat), Kepala KUA (mengelola surat, laporan kinerja, & kritik saran), dan Superadmin KUA (akses penuh ke semua fitur)
- **Master data**: 11 jenis surat dengan field dinamis (SPN, SKU, SPC, SUP, SIN, SP, SPD, SPA, SPM, SKN, PNL), template surat, pengaturan KUA (kop, alamat, kepala KUA, penanda posisi TTD), halaman statis & menu navbar dinamis
- **Modul surat**: alur `draft → diajukan → disetujui → terbit`, PDF berkop KUA, nomor surat diisi manual (contoh: `B.001/KUA.01.01.01/PW.01/01/2026`)
- **Laporan kinerja pegawai (lapkin)**: pencatatan kegiatan harian, master data harian & tema pekerjaan, template kalimat, ekspor **PDF & Word** (laporan per pegawai dan rekap per bulan/tahun)
- **Permohonan online**: masyarakat mengisi form tanpa login (SPD, SPA, SKN, PNL); staf/operator memverifikasi dan membuat surat dari data permohonan (terisi otomatis)
- **Layanan publik**: pengumuman, daftar pegawai, pusat unduhan, layanan pernikahan, keagamaan & wakaf (accordion topik berisi persyaratan, alur, dan SOP), dan kritik & saran
- **Dashboard**: statistik surat & permohonan
- **Email**: notifikasi lupa password via SMTP Gmail

## Persyaratan

- PHP 8.3+, Composer, MySQL 8 / MariaDB
- Node.js + NPM (untuk aset frontend)

## Instalasi (Lokal / VPS)

```bash
composer install
cp .env.example .env
php artisan key:generate
# atur kredensial MySQL di .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate --force
php artisan db:seed --force        # membuat user awal & data master
php artisan storage:link           # symlink public/storage -> storage/app/public (untuk logo/upload)
npm install && npm run build
```

Halaman login berada di **`/yukmasuk`**.

### HTTPS & Mixed Content

Aplikasi **wajib diakses lewat HTTPS di produksi** (fetch dari modal kegiatan diblokir browser jika URL-nya `http://`). Secara default semua URL dipaksa `https` di lingkungan non-`local`/`testing`.

- Saat deploy, set di `.env`: `APP_URL=https://domain-anda` (jangan `http://`).
- Jika menjalankan http-only (mis. di LAN/lokal non-dev), set `APP_FORCE_HTTPS=false` di `.env`.
- Disarankan nginx meneruskan `proxy_set_header X-Forwarded-Proto $scheme;` di blok `location /`.

User awal (ubah via `.env` sebelum seed):

| Role | Email default | Password default |
|---|---|---|
| Staf | `staf@kua.local` | `password` |
| Operator | `operator@kua.local` | `password` |
| Kepala | `kepala@kua.local` | `password` |
| Superadmin | `superadmin@kua.local` | `password` |

⚠️ Wajib mengganti password default setelah deploy pertama.

### Email (SMTP Gmail)

Aplikasi mengirim email untuk fitur lupa password. Default `.env.example` sudah diarahkan ke SMTP Gmail (`MAIL_MAILER=smtp`) — cukup isi di `.env`:

| Variabel | Isi |
|---|---|
| `MAIL_USERNAME` | alamat Gmail pengirim (mis. `misal.kuakamu@gmail.com`) |
| `MAIL_PASSWORD` | App Password Gmail 16 karakter |
| `MAIL_FROM_ADDRESS` | alamat pengirim (sama dengan `MAIL_USERNAME`) |

Cara membuat App Password:
1. Aktifkan **2-Step Verification**: `https://myaccount.google.com/security`
2. Buat App Password: Security → App passwords → pilih "Mail"

Atau jalankan sekali (backup `.env`, set konfigurasi SMTP, kirim email test):

```bash
bash scripts/setup-mail.sh misal.kuakamu@gmail.com 'xxxx xxxx xxxx xxxx'
```

> `MAIL_SCHEME` opsional — kosongkan, otomatis mengikuti port (`587` → smtp, `465` → smtps).

## Nomor Surat

Nomor surat **diisi manual** oleh pengguna di form pembuatan/penyuntingan surat (tidak ada penomoran otomatis). Contoh format:

```
B.001/KUA.01.01.01/PW.01/01/2026
```

Surat baru dimulai sebagai `draft`; setelah dilengkapi nomor & tanggal, surat dapat diajukan, disetujui, lalu diterbitkan.

## Kop & Tanda Tangan PDF

- Kop surat dapat dikonfigurasi di **Pengaturan KUA** (`/kua-settings`): logo, teks kop (judul/sub-judul), ukuran kop, dan alamat.
- Penanda posisi tanda tangan memakai simbol **anchor `^`** (dapat dimatikan via pengaturan `kop_anchor`); Kepala KUA menandatangani surat fisik di posisi tersebut.

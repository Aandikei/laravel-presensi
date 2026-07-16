# Laravel Presensi

Sistem manajemen absensi dan poin siswa berbasis web. Multi-sekolah, multi-role, dengan export background via queue.

## Stack

- Laravel 12
- Breeze (Auth)
- Spatie Laravel Permission (Multi-role)
- Tailwind CSS v4 + Windmill Dashboard
- Alpine.js
- Yajra DataTables
- Maatwebsite Excel
- DomPDF
- Queue (Database)

## Fitur

- Multi-role: super_admin, admin_sekolah, guru, wali_kelas, siswa, orang_tua, kepala_sekolah, wakil_kepala_sekolah
- Multi-instansi (satu super admin manage banyak sekolah)
- Master Data: Tahun Ajaran, Guru, Siswa, Kelas, Mata Pelajaran, Kurikulum, Jadwal
- Absensi harian dengan auto-lock setelah grace period
- Poin siswa (master poin, catat pelanggaran, rekap per status)
- Laporan & Export (Excel/PDF) via background queue
- Export: Absensi, Poin, Rekap Guru, Data Siswa, Data Guru, Data Kelas, Log Poin, Jadwal

## Default Accounts

| Role          | Email                 | Password |
|---------------|-----------------------|----------|
| Super Admin   | superadmin@app.com    | password |

> Admin sekolah, guru, dan user lainnya dibuat melalui fitur manajemen user setelah login.

## Setup

```bash
git clone <repo-url> laravel-presensi
cd laravel-presensi
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sesuaikan database di `.env`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
```

### Konfigurasi .env

Sesuaikan database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi tambahan:

```env
ABSENSI_LOCK_GRACE=30
```

| Variabel             | Default | Keterangan                                                    |
|----------------------|---------|---------------------------------------------------------------|
| `ABSENSI_LOCK_GRACE` | `30`    | Batas waktu (menit) setelah jam_selesai jadwal sebelum absensi otomatis terkunci. Guru masih bisa edit selama masa ini. |

### Queue Worker

Export berjalan di background. Jalankan worker:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

Restart worker setelah ada perubahan kode:

```bash
php artisan queue:restart
```

### Jalankan Aplikasi

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

## Struktur Folder

```
app/Exports/                Class export (Maatwebsite Excel)
app/Jobs/GenerateExport.php Background job export
app/Http/Controllers/
  Admin/                    Controller admin sekolah
  SuperAdmin/               Controller super admin
  Guru/                     Controller role guru
  Auth/                     Controller login, register
routes/
  admin.php                 Route admin
  superadmin.php            Route super admin
resources/views/
  admin/                    View admin
  superadmin/               View super admin
  guru/                     View guru
  components/partials/      Sidebar, navbar
config/absensi.php          Konfigurasi absensi
```

# Laravel Boilerplate

Boilerplate Laravel 12 dengan multi-role authentication siap pakai.

## Stack
- Laravel 12
- Breeze (Auth)
- Spatie Laravel Permission (Multi-role)
- Tailwind CSS v4
- Windmill Dashboard
- Alpine.js

## Fitur
- Login, Register, Forgot Password, Reset Password
- Multi-role: super_admin, admin, user
- Dashboard per role
- Proteksi route per role (middleware Spatie)
- Layout Windmill Dashboard (dark mode support)
- Seeder role, permission, dan user default

---

## Requirement
Pastikan sudah terinstall di komputer:
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB
- Git

---

## Setup Project Baru dari Boilerplate Ini

### 1. Clone Repository
```bash
git clone https://github.com/username/laravel-boilerplate.git nama-project
cd nama-project
```

### 2. Install Dependency PHP
```bash
composer install
```

### 3. Install Dependency JavaScript
```bash
npm install
```

### 4. Buat File .env
```bash
cp .env.example .env
```

### 5. Generate App Key
```bash
php artisan key:generate
```

### 6. Setting Database
Buka file `.env`, sesuaikan bagian ini:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_baru
DB_USERNAME=root
DB_PASSWORD=
```
Buat database baru di MySQL/phpMyAdmin sesuai nama yang lo isi di atas.

### 7. Jalankan Migration & Seeder
```bash
php artisan migrate --seed
```
Perintah ini akan:
- Membuat semua tabel di database
- Membuat role: super_admin, admin, user
- Membuat permission dasar
- Membuat akun default (lihat bagian Default Accounts)

### 8. Jalankan Vite (Asset)
Buka terminal baru, jalankan:
```bash
npm run dev
```
Biarkan terminal ini tetap berjalan selama development.

### 9. Jalankan Laravel
Di terminal lain:
```bash
php artisan serve
```

### 10. Buka di Browser
http://localhost:8000

---

## Default Accounts
Setelah menjalankan seeder, akun berikut tersedia:

| Role | Email | Password |
|---|---|---|
| Super Admin | superadmin@app.com | password |
| Admin | admin@app.com | password |
| User | user@app.com | password |

> **Penting:** Ganti password default setelah setup di production!

---

## Struktur Folder Penting
app/Http/Controllers/
├── Admin/          → Controller khusus admin
├── SuperAdmin/     → Controller khusus super admin
└── User/           → Controller khusus user
routes/
├── web.php         → Route utama
├── admin.php       → Route admin
├── superadmin.php  → Route super admin
└── user.php        → Route user
resources/views/
├── admin/          → View admin
├── superadmin/     → View super admin
├── user/           → View user
├── auth/           → View login, register, dll
└── components/
├── layouts/        → Layout component per role
└── partials/       → Navbar, sidebar
---

## Cara Pakai untuk Project Baru

### Tambah Menu Sidebar
Buka `resources/views/components/partials/sidebar-desktop.blade.php` dan `sidebar-mobile.blade.php`, tambah di bagian yang sudah ada catatannya:
```html
<li class="relative px-6 py-3">
    <a class="inline-flex items-center w-full text-sm font-semibold..."
        href="{{ route('nama.route') }}">
        <svg ...></svg>
        <span class="ml-4">Nama Menu</span>
    </a>
</li>
```

### Tambah Route
Tambah di file route sesuai role (`routes/user.php`, `routes/admin.php`, `routes/superadmin.php`):
```php
Route::get('/halaman', [NamaController::class, 'index'])->name('nama');
```

### Tambah Permission Baru
Tambah di `database/seeders/RoleSeeder.php` lalu jalankan:
```bash
php artisan db:seed --class=RoleSeeder
```

### Cek Role di Blade
```html
@role('admin')
    <p>Hanya admin yang lihat ini</p>
@endrole

@can('manage-users')
    <p>Hanya yang punya permission ini</p>
@endcan
```

### Cek Role di Controller
```php
if (auth()->user()->hasRole('admin')) {
    // logic khusus admin
}

if (auth()->user()->can('manage-users')) {
    // logic khusus permission
}
```

---

## Troubleshooting

**Error: `php artisan` tidak dikenal**
→ Pastikan PHP sudah terinstall dan ada di PATH sistem

**Error: `composer` tidak dikenal**
→ Download dan install Composer dari https://getcomposer.org

**Error: Class not found setelah clone**
→ Jalankan `composer install` dan `php artisan config:clear`

**Halaman putih / error 500**
→ Pastikan file `.env` sudah ada dan `php artisan key:generate` sudah dijalankan

**Asset tidak muncul (CSS/JS)**
→ Pastikan `npm install` dan `npm run dev` sudah dijalankan

---

## Lisensi
MIT License - bebas digunakan dan dimodifikasi.
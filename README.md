# Laravel Boilerplate

Stack:
- Laravel 12
- Breeze (Auth)
- Spatie Permission
- Tailwind CSS v4
- Windmill Dashboard

## Setup
1. git clone https://github.com/Aandikei/laravel-boilerplate
2. cd laravel-boilerplate
3. cp .env.example .env
4. composer install
5. npm install
6. php artisan key:generate
7. php artisan migrate --seed
8. npm run dev

## Default Accounts
- Super Admin: superadmin@app.com / password
- Admin: admin@app.com / password
- User: user@app.com / password

## Roles
- super_admin → kelola role & permission
- admin → kelola konten/user
- user → akses fitur standar
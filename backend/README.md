# Backend (PHP)

## Chay thu
```
cd backend
composer install
cp .env.example .env
php -S localhost:8000 -t public
```
Mo http://localhost:8000 thay `{"status":"ok"...}` la chay duoc.

## Cau truc
Moi module nam trong `src/Modules/<Ten>/` gom Controllers, Services, Models va `routes.php`.

# Chạy WebAdmin và các module bằng Docker

Từ thư mục gốc repo, chạy:

```powershell
docker compose --env-file .env.docker.example up --build
```

Website mặc định mở Trung tâm Admin:

- WebAdmin: http://localhost:8082
- Cổng Thợ May: http://localhost:8082/index.php?module=lookbook&tab=tailor
- Lookbook Khách Hàng: http://localhost:8082/index.php?module=lookbook&tab=lookbook
- Backend PHP: http://localhost:8002
- MySQL từ máy host: `127.0.0.1:3308`

Lần đầu tạo volume database, Compose nạp schema gốc, migration Lookbook/affiliate và dữ liệu khởi đầu. Các lần chạy sau dùng lại volume `mysql_data`.

Dừng bằng `Ctrl+C` hoặc `docker compose down`. `docker compose down -v` sẽ xóa volume database.
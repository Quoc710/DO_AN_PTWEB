# Chạy WebAdmin và các module bằng Docker

Từ thư mục gốc repo, chạy:

```powershell
docker compose --env-file .env.docker.example up --build
```

Để gửi email kích hoạt thật qua Gmail:

1. Sao chép `.env.smtp.example` thành `.env.smtp.local`.
2. Điền `MAIL_USER` là Gmail gửi thư và `MAIL_PASS` là **Google App Password** (không phải mật khẩu đăng nhập Gmail). Có thể đặt `MAIL_FROM` bằng chính địa chỉ Gmail đó.
3. Khởi động lại bằng lệnh:

```powershell
docker compose --env-file .env.docker.example --env-file .env.smtp.local up -d --build
```

Nếu chưa cấu hình SMTP hoặc gửi thất bại, ứng dụng lưu liên kết kích hoạt trong `backend/storage/mail_logs/` và trang đăng ký sẽ báo rõ email chưa được gửi. Đổi `APP_URL` nếu trang web không chạy tại `http://localhost:8082`.

Website mặc định mở Trung tâm Admin:

- WebAdmin: http://localhost:8082
- Cổng Thợ May: http://localhost:8082/index.php?module=lookbook&tab=tailor
- Lookbook Khách Hàng: http://localhost:8082/index.php?module=lookbook&tab=lookbook
- Backend PHP: http://localhost:8002
- MySQL từ máy host: `127.0.0.1:3308`

Lần đầu tạo volume database, Compose nạp schema gốc, migration Lookbook/affiliate và dữ liệu khởi đầu. Các lần chạy sau dùng lại volume `mysql_data`.

Đơn hàng online có thể được admin chuyển sang trạng thái đang giao. Khách hàng hoặc admin xác nhận giao thành công; nếu chưa ai xác nhận, container `order-scheduler` tự hoàn tất sau thời hạn tối đa: Tiết kiệm 6 ngày, Tiêu chuẩn 4 ngày, Hỏa tốc 2 ngày kể từ lúc admin bắt đầu giao. Khách có thể hủy khi đơn đang giao; đơn hủy không được tự xác nhận hoàn tất.

Dừng bằng `Ctrl+C` hoặc `docker compose down`. `docker compose down -v` sẽ xóa volume database.

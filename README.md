# Forum Dã Ngoại – Web-App-Dev

Website diễn đàn chia sẻ kiến thức và kinh nghiệm dành cho những người yêu thích dã ngoại (cắm trại, trekking, phượt, ...). Đây là đồ án nhóm môn phát triển ứng dụng web.

## Mục tiêu và yêu cầu

- Giao diện thiết kế bằng **Bootstrap**.
- Backend viết theo **hướng đối tượng (OOP)**, tổ chức theo mô hình **MVC**.
- Tích hợp **AJAX / Web service** (chỉ sử dụng service có sẵn), dữ liệu truyền qua **JSON/XML**.
- Phần quản trị ở mức cơ bản: thêm danh mục, bài viết, ...
- Làm việc nhóm có minh chứng: shared host cho database, GitHub quản lý theo branch, Teams để theo dõi tiến độ.

## Công nghệ

| Thành phần | Công nghệ |
|---|---|
| Backend | PHP (OOP + MVC) |
| Frontend | HTML, CSS, JavaScript, Bootstrap |
| Giao tiếp bất đồng bộ | AJAX (fetch/XHR) + JSON |
| Cơ sở dữ liệu | MariaDB (XAMPP), truy vấn qua stored procedures |
| Môi trường chạy | XAMPP (Apache + MariaDB + phpMyAdmin) |
| Quản lý mã nguồn | Git + GitHub (làm việc theo branch) |
| Mạng chung cho nhóm | Tailscale (LAN ảo để truy cập DB của máy host) |

## Cấu trúc thư mục

```
Web-App-Dev/
├── config/            Cấu hình ứng dụng và kết nối CSDL
├── database/
│   ├── schema.sql     Tạo database, bảng, khóa chính/khóa ngoại
│   ├── routines/      Stored procedures
│   └── seeds/         Dữ liệu mẫu
├── app/
│   ├── core/          App, Router, Controller, Model, Database
│   ├── controllers/   Xử lý request (có thư mục admin/)
│   ├── models/        Làm việc với dữ liệu
│   ├── views/         Giao diện (layouts, partials, từng trang)
│   ├── helpers/       Session, Validator, Response (trả JSON)
│   └── middleware/    Kiểm tra đăng nhập, quyền admin
├── api/               Endpoint phục vụ AJAX
├── public/            Điểm vào (index.php), .htaccess, assets, uploads
├── docs/
│   ├── evidence/      Ảnh chụp minh chứng cho báo cáo
│   └── diagrams/      Sơ đồ nghiệp vụ (BA)
├── tests/
├── CODEOWNERS
├── Plan-Forum.md
├── phan-cong-theo-tuan.md
└── README.md
```

## Hướng dẫn cài đặt

### 1. Chuẩn bị
- Cài [XAMPP](https://www.apachefriends.org/) và bật **Apache** + **MySQL (MariaDB)**.
- Cài Git.

### 2. Lấy mã nguồn
Đặt dự án vào thư mục `htdocs` của XAMPP:

```bash
cd C:/xampp/htdocs
git clone <URL-repo-GitHub> Web-App-Dev
```

### 3. Tạo database
1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Tạo database mới (ví dụ `forum_dangoai`, bảng mã `utf8mb4_unicode_ci`).
3. Import `database/schema.sql`, sau đó các file trong `database/routines/` và (tuỳ chọn) `database/seeds/seed.sql`.

### 4. Cấu hình kết nối
Mở `config/database.php` và điền thông tin kết nối:

```php
<?php
return [
    'host' => 'localhost',   // hoặc IP Tailscale của máy host
    'port' => 3306,
    'name' => 'forum_dangoai',
    'user' => 'root',
    'pass' => '',
];
```

> `config/database.php` nằm trong `.gitignore`, mỗi thành viên tự cấu hình trên máy mình, không commit lên GitHub.

### 5. Chạy dự án
Truy cập: http://localhost/Web-App-Dev/public/

### Xác thực và vai trò
- Import `database/schema.sql` vào database mới để tạo `roles` (`member`, `admin`) và liên kết người dùng qua `users.role_id`.
- Nếu đang nâng cấp database cũ có `users.role` dạng ENUM, sao lưu trước rồi chạy `database/migrations/001_normalize_user_roles.sql` một lần thay vì import lại schema.
- Mở `/register` để tạo tài khoản thành viên; mật khẩu được băm bằng `password_hash`.
- Dùng `/login`, `/profile`; quản trị viên truy cập `/admin`; đăng xuất được gửi bằng POST có CSRF token.
- Router hỗ trợ khai báo middleware cho từng route. Dùng `AuthMiddleware` để yêu cầu đăng nhập và `AdminMiddleware` để giới hạn vai trò `admin`.
- Tài khoản seed hiện có mật khẩu placeholder, không dùng để đăng nhập; hãy đăng ký tài khoản mới hoặc thiết lập mật khẩu admin bằng `password_hash`.

## Dùng chung database trong nhóm

Một thành viên làm máy host (chạy XAMPP chứa database), các bạn còn lại kết nối vào:

1. Cài Tailscale, đăng nhập và cho các thành viên vào cùng một mạng (tailnet).
2. Cấu hình XAMPP/phpMyAdmin cho phép truy cập từ mạng LAN ảo.
3. Tạo user MariaDB riêng cho thành viên, không dùng chung `root`.
4. Trong `config/database.php`, đặt `host` là IP Tailscale của máy host.

## Quy trình làm việc với Git

- Nhánh chính: `main` (chỉ merge khi đã review).
- Mỗi người làm trên nhánh riêng, đặt tên theo mẫu `feature/<tên-chức-năng>`, ví dụ `feature/login`, `feature/post-crud`.
- Tạo Pull Request vào `main`, thành viên khác review rồi mới merge (xem `CODEOWNERS`).
- Commit message ngắn gọn, nói rõ việc đã làm.
- Công việc theo tuần được ghi trong `phan-cong-theo-tuan.md`, kế hoạch tổng thể trong `Plan-Forum.md`.

## Thành viên nhóm (4 người)

| STT | Họ tên | Vai trò | Phụ trách |
|---|---|---|---|
| 1 | _(điền)_ | Nhóm trưởng | _(điền)_ |
| 2 | _(điền)_ | Thành viên | _(điền)_ |
| 3 | _(điền)_ | Thành viên | _(điền)_ |
| 4 | _(điền)_ | Thành viên | _(điền)_ |

Cơ sở dữ liệu được chia đều: mỗi thành viên thiết kế 2 bảng (tổng 8 bảng).

## Minh chứng cho báo cáo

Ảnh chụp màn hình (GitHub branch/PR, GitHub Project, Teams, phpMyAdmin, shared host) lưu trong `docs/evidence/`.

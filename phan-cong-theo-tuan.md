# Phân công chi tiết theo tuần: Forum dã ngoại

> Nhóm 4 người: **Leader**, **Khang**, **Phúc**, **Vương** (thay bằng tên thật khi dùng).
> Giả định 8 tuần. Mã FR tham chiếu file `lo-trinh-forum-da-ngoai.md`.

## Bảng tóm tắt vai trò

| Người | Mảng phụ trách | Chức năng (mã FR) |
|---|---|---|
| **Leader** | BA, kiến trúc, DB, deploy, review | FR11 + khung `core/`, `database/`, admin layout |
| **Khang** | Người dùng, bảo mật, RSS | FR01, FR02, FR14, FR10 |
| **Phúc** | Bài viết (lõi forum) | FR03, FR04, FR05, FR13 |
| **Vương** | Tương tác, web service, thiết bị | FR06, FR07, FR08, FR09, FR12 |

## Tổng quan theo tuần (ai làm gì)

| Tuần | Leader | Khang | Phúc | Vương |
|---|---|---|---|---|
| 1 | BA, ERD, wireframe, repo, board, DB chung | Cài môi trường, view đăng nhập/đăng ký tĩnh | Cài môi trường, view danh sách/chi tiết bài tĩnh | Cài môi trường, view thiết bị tĩnh |
| 2 | `core/`, layout, schema bản đầu | FR01 đăng ký, đăng nhập, CSRF | Model Post + routine | Model Product/Comment/Like + routine |
| 3 | FR11 admin danh mục, khung admin | FR02 hồ sơ, đổi mật khẩu | FR05 đăng/sửa/xóa bài, upload | FR09 thiết bị, FR12 admin sản phẩm |
| 4 | Review, seed dữ liệu mẫu | FR14 admin người dùng, AJAX check email | FR03 danh sách + phân trang, FR13 admin bài | FR07 bình luận, FR08 thích bài (AJAX) |
| 5 | Rà tích hợp, tối ưu index | FR10 RSS (XML) | FR04 tìm kiếm + gợi ý (AJAX) | FR06 bản đồ + thời tiết |
| 6 | Test tích hợp, rà bảo mật | Hoàn thiện UI, test chéo Phúc | Hoàn thiện UI, test chéo Vương | Hoàn thiện UI, test chéo Khang |
| 7 | Tối ưu, deploy thử, README | Sửa bug được giao | Sửa bug được giao | Sửa bug được giao |
| 8 | Deploy chính thức, demo | Viết báo cáo phần mình | Viết báo cáo phần mình | Viết báo cáo phần mình |

---

## Tuần 1: BA và nền tảng

**Leader**
- [ ] Viết bảng yêu cầu, use case, sitemap; chốt quy tắc nghiệp vụ (trạng thái bài viết, duyệt bài).
- [ ] Vẽ wireframe (trang chủ, danh sách, chi tiết, đăng bài, đăng nhập, thiết bị, admin) và ERD.
- [ ] Tạo cấu trúc thư mục, `.gitignore`, `README`, `config.example.php`.
- [ ] GitHub: bảo vệ `main`, tạo `develop`, PR/issue template, nhãn, milestone Sprint 1 đến 8; tạo toàn bộ issue theo mã FR và gán người.
- [ ] Chọn host DB chung, thử tạo một stored procedure mẫu và một request cURL ra ngoài.
- [ ] Tạo nhóm Teams và lịch họp.

**Khang**: cài XAMPP (PHP 8.x, bật `mod_rewrite`), clone repo, thử tạo nhánh và PR nhỏ; dựng view đăng nhập, đăng ký, hồ sơ dạng HTML + Bootstrap tĩnh (nhánh `feature/FR01-views`).

**Phúc**: cài XAMPP, clone repo, thử PR nhỏ; dựng view trang chủ, danh sách bài, chi tiết bài, form đăng bài tĩnh (nhánh `feature/FR03-views`).

**Vương**: cài XAMPP, clone repo, thử PR nhỏ; dựng view danh sách thiết bị, chi tiết bài phần bình luận/like tĩnh (nhánh `feature/FR09-views`).

**Bàn giao:** SRS, ERD, wireframe, backlog đầy đủ, DB chung kết nối được, mọi người chạy được project trống.
**Minh chứng:** ảnh repo, board, DB chung, buổi họp đầu, mỗi người có ít nhất một PR đã merge.

---

## Tuần 2: Khung MVC và đăng nhập

**Leader** (nhánh `feature/core-mvc`, `feature/schema-001`)
- [ ] `Router`, `Database` (PDO singleton), `BaseController`, `BaseModel`, `Response`.
- [ ] Layout Bootstrap chung (`layouts/main.php`, navbar, footer).
- [ ] `schema/001`: bảng `users`, `roles`, `categories`, `posts`.
- [ ] Làm một luồng mẫu đầu cuối (ví dụ danh sách danh mục) để cả nhóm theo mẫu.

**Khang** (nhánh `feature/FR01-auth`)
- [ ] `AuthController`, `User` model, routine `sp_user_register`, `sp_user_get_by_email`.
- [ ] Đăng ký (băm mật khẩu), đăng nhập, đăng xuất, session.
- [ ] `Auth.php` (kiểm tra vai trò), `Csrf.php` (token cho form).

**Phúc** (nhánh `feature/post-model`)
- [ ] `Post` model, routine `sp_post_create`, `sp_post_get`, `sp_post_list` (bản đầu, chưa lọc).
- [ ] Gửi file SQL cho Leader duyệt qua PR.

**Vương** (nhánh `feature/interact-models`)
- [ ] `Product`, `Comment`, `Like` model; routine `sp_product_*`, `sp_comment_add/list`, `sp_post_toggle_like`.
- [ ] Gửi file SQL cho Leader duyệt qua PR.

**Bàn giao:** đăng ký/đăng nhập chạy, trang chủ dùng layout chung, một luồng MVC mẫu chạy đầu cuối.

---

## Tuần 3: CRUD chính

**Leader** (nhánh `feature/FR11-admin-category`)
- [ ] `layouts/admin.php`, kiểm tra quyền admin cho toàn bộ khu `/admin`.
- [ ] FR11: thêm, sửa, xóa danh mục (có xác nhận khi xóa).

**Khang** (nhánh `feature/FR02-profile`)
- [ ] `ProfileController`: xem, sửa hồ sơ, upload avatar (kiểm tra loại và kích thước).
- [ ] Đổi mật khẩu (xác nhận mật khẩu cũ).

**Phúc** (nhánh `feature/FR05-post-crud`)
- [ ] `PostController`: đăng bài (tiêu đề, nội dung, chuyên mục, ảnh bìa, địa điểm, lat/lng), sửa, xóa; chỉ tác giả hoặc admin.
- [ ] Upload ảnh an toàn; trạng thái bài theo quy tắc đã chốt.

**Vương** (nhánh `feature/FR09-FR12-products`)
- [ ] FR09: `ProductController`, trang thiết bị theo danh mục.
- [ ] FR12: admin thêm, sửa, xóa sản phẩm (ảnh, mô tả, liên kết).

**Bàn giao:** đăng và xem bài chạy được; admin thêm được danh mục và sản phẩm.

---

## Tuần 4: Tương tác

**Leader** (nhánh `feature/seed-data`)
- [ ] Review toàn bộ PR tuần 3 và tuần 4, gỡ xung đột.
- [ ] `seed.sql`: người dùng mẫu, danh mục dã ngoại, bài viết, sản phẩm để demo.
- [ ] Kiểm tra các routine đã có index hợp lý.

**Khang** (nhánh `feature/FR14-admin-users`)
- [ ] `UserAdminController`: danh sách người dùng, khóa/mở (người bị khóa không đăng nhập được).
- [ ] AJAX kiểm tra email trùng khi đăng ký (`/api/check-email`, trả JSON).

**Phúc** (nhánh `feature/FR03-FR13`)
- [ ] FR03: danh sách bài theo chuyên mục, phân trang.
- [ ] FR13: `PostAdminController` duyệt, ẩn, xóa bài.

**Vương** (nhánh `feature/FR07-FR08-ajax`)
- [ ] FR07: bình luận bằng AJAX (`CommentApi`), tải thêm bình luận, xóa bình luận của mình.
- [ ] FR08: thích/bỏ thích bằng AJAX (`LikeApi`), cập nhật số lượt ngay trên trang; token CSRF.

**Bàn giao:** forum hoàn chỉnh ở mức cơ bản (đăng nhập, đăng bài, bình luận, thích, admin).

---

## Tuần 5: Web service và AJAX nâng cao

**Leader**
- [ ] Rà lỗi tích hợp giữa các module, thống nhất định dạng JSON `{success, data, message}`.
- [ ] Tối ưu: index, kiểm tra truy vấn chậm, chuẩn bị bộ nhớ đệm chung nếu cần.

**Khang** (nhánh `feature/FR10-rss`)
- [ ] `RssService`: đọc RSS (XML) bằng cURL + SimpleXML, cache 15 đến 30 phút.
- [ ] `/api/news` trả JSON; sidebar "Tin dã ngoại" hiển thị trên trang chủ.

**Phúc** (nhánh `feature/FR04-search`)
- [ ] Tìm kiếm theo từ khóa và chuyên mục, kết hợp phân trang.
- [ ] `/api/search/suggest?q=` gợi ý khi gõ (AJAX, debounce).

**Vương** (nhánh `feature/FR06-map-weather`)
- [ ] `WeatherService` gọi Open-Meteo bằng cURL, cache 10 đến 15 phút, xử lý lỗi và timeout.
- [ ] `/api/weather?lat=&lng=` trả JSON; hiển thị thời tiết trong chi tiết bài.
- [ ] Bản đồ Leaflet + OpenStreetMap hiển thị vị trí bài viết.

**Bàn giao:** đủ các tính năng điểm cộng (AJAX, JSON, XML, web service).

---

## Tuần 6: Hoàn thiện và kiểm thử chéo

**Leader**
- [ ] Test tích hợp toàn hệ thống, tạo file test case chung và ghi bug thành issue.
- [ ] Rà bảo mật: SQL injection, XSS, CSRF, upload, phân quyền từng route.

**Khang**: hoàn thiện UI phần mình, responsive; **test chéo các chức năng của Phúc** (FR03, FR04, FR05, FR13), ghi bug vào issue.

**Phúc**: hoàn thiện UI phần mình, responsive; **test chéo các chức năng của Vương** (FR06 đến FR09, FR12).

**Vương**: hoàn thiện UI phần mình, responsive; **test chéo các chức năng của Khang** (FR01, FR02, FR10, FR14).

**Bàn giao:** bản đầy đủ chức năng, danh sách bug có người nhận.

---

## Tuần 7: Ổn định và chuẩn bị deploy

**Leader**
- [ ] Tối ưu (index, cache), triển khai thử lên host và sửa lỗi cấu hình.
- [ ] Hoàn thiện `README` hướng dẫn cài đặt, chuẩn bị bản dự phòng chạy local.

**Khang, Phúc, Vương**: mỗi người đóng các bug thuộc module của mình; tinh chỉnh giao diện cho dữ liệu mẫu thật; kiểm tra lại toàn bộ luồng trên điện thoại.

**Bàn giao:** bản ổn định, không còn bug nghiêm trọng.

---

## Tuần 8: Deploy và báo cáo

**Leader**
- [ ] Deploy chính thức, chạy checklist sau deploy, sao lưu DB.
- [ ] Quay video demo; tổng hợp báo cáo (các mục chung: giới thiệu, phân tích, thiết kế, quản lý nhóm).

**Khang, Phúc, Vương**: mỗi người viết phần "chức năng do tôi phụ trách" (mô tả, ảnh chụp, ví dụ mã nguồn Controller/Model/View, minh chứng commit và PR của chính mình).

**Bàn giao:** sản phẩm chạy online, báo cáo, video demo.

---

## Quy ước chung cho cả 8 tuần

- Họp đầu tuần (phân task), họp giữa tuần 15 phút (gỡ vướng), cuối tuần demo ngắn và ghi biên bản.
- Mỗi task là một issue gắn mã FR, một nhánh `feature/...`, một PR có ít nhất một review; Leader là người merge vào `develop`.
- Mỗi người chỉ sửa file thuộc module mình; cần đổi file chung (`core/`, `layouts/`, `schema`) thì tạo issue cho Leader.
- Cuối mỗi tuần mỗi người chụp ảnh minh chứng công việc của mình vào `docs/report-evidence/tuần-N/`.

## Nếu một người bị trễ

| Tình huống | Xử lý |
|---|---|
| Vương trễ FR06 | Giữ phần thời tiết, cắt bản đồ; hoặc Khang hỗ trợ sau khi xong FR10 |
| Khang trễ FR14 hoặc avatar | Cắt FR14 và avatar trước, giữ FR01 và FR10 |
| Phúc trễ FR04 | Giữ tìm kiếm cơ bản, bỏ gợi ý khi gõ |
| Leader quá tải | Chuyển việc kiểm thử tích hợp cho thành viên xong sớm nhất |

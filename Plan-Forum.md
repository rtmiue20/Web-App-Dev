# Lộ trình thực hiện: Forum chia sẻ kiến thức, kinh nghiệm dã ngoại

> Phạm vi: đồ án môn Website AppDev, nhóm 4 người (1 leader + 3 thành viên).
> Giả định thời gian: **8 tuần**. Nếu hạn nộp khác, co giãn các tuần ở mục 8 nhưng giữ nguyên thứ tự.
> Công nghệ: PHP 8 (OOP, MVC tự dựng) + XAMPP + MariaDB + Bootstrap 5 + AJAX (JSON) + web service ngoài (JSON/XML).

---

## 1. Mục tiêu và tiêu chí chấm

| Tiêu chí đề bài | Cách đáp ứng trong dự án |
|---|---|
| Dùng Bootstrap | Toàn bộ giao diện dùng Bootstrap 5, responsive |
| Backend hướng đối tượng | Model, Controller, Service là các lớp; có lớp cơ sở và kế thừa |
| Điểm cao: OOP + MVC | Router + Controller + Model + View tách lớp rõ ràng |
| Điểm cao: AJAX/Webservice | AJAX cho like, bình luận, gợi ý tìm kiếm; sử dụng service ngoài (thời tiết, RSS, bản đồ) |
| Dữ liệu qua XML/JSON | API nội bộ trả JSON; đọc RSS (XML) |
| Admin mức cơ bản | Thêm/sửa/xóa danh mục, sản phẩm, bài viết (và khóa người dùng) |
| CSDL tùy chọn | MariaDB, dùng stored procedure |
| Làm việc nhóm có minh chứng | GitHub theo nhánh, shared DB, Teams/công cụ tương tự, ảnh chụp đưa vào báo cáo |

Nguyên tắc: **chỉ sử dụng** web service có sẵn, không tự tạo service cho bên khác dùng.

---

## 2. Phân tích nghiệp vụ (BA)

### 2.1 Tác nhân
- **Khách**: xem bài, tìm kiếm, xem thiết bị.
- **Thành viên**: khách + đăng bài, bình luận, thích bài, sửa hồ sơ.
- **Quản trị viên**: quản lý danh mục, sản phẩm, bài viết, người dùng.

### 2.2 Yêu cầu chức năng

| Mã | Chức năng | Ưu tiên |
|---|---|---|
| FR01 | Đăng ký, đăng nhập, đăng xuất, đổi mật khẩu | Cao |
| FR02 | Hồ sơ cá nhân (avatar, giới thiệu) | Trung bình |
| FR03 | Danh sách bài theo chuyên mục, phân trang | Cao |
| FR04 | Tìm kiếm bài, gợi ý khi gõ (AJAX) | Cao |
| FR05 | Đăng, sửa, xóa bài của mình (ảnh, địa điểm, tọa độ) | Cao |
| FR06 | Chi tiết bài kèm bản đồ và thời tiết địa điểm (web service) | Cao |
| FR07 | Bình luận (AJAX) | Cao |
| FR08 | Thích/bỏ thích bài (AJAX) | Cao |
| FR09 | Trang thiết bị/sản phẩm gợi ý theo danh mục | Trung bình |
| FR10 | Sidebar "Tin dã ngoại" đọc từ RSS (XML) | Trung bình |
| FR11 | Admin: CRUD danh mục | Cao |
| FR12 | Admin: CRUD sản phẩm | Cao |
| FR13 | Admin: quản lý bài viết (duyệt, ẩn, xóa) | Cao |
| FR14 | Admin: quản lý người dùng (khóa, mở) | Thấp |

### 2.3 Quy tắc nghiệp vụ (cần chốt ở tuần 1)
- Trạng thái bài viết: `pending` (chờ duyệt), `published`, `hidden`. Chốt: thành viên mới có cần duyệt hay đăng ngay.
- Chỉ tác giả và admin được sửa/xóa bài.
- Người dùng bị khóa không đăng nhập được.
- Mỗi người chỉ thích một bài một lần.
- Bình luận thuộc về đúng một bài; chỉ tác giả bình luận và admin được xóa.

### 2.4 Phi chức năng
- Responsive, hiển thị tốt trên điện thoại.
- Tiếng Việt có dấu (`utf8mb4`).
- Mật khẩu băm; chống SQL injection, XSS, CSRF.
- Trang tải nhanh với dữ liệu mẫu (mục tiêu dưới khoảng 2 giây).

### 2.5 Sản phẩm BA cần nộp trong tuần 1
- Bảng yêu cầu (mục 2.2), sơ đồ use case, sitemap.
- Wireframe các trang chính: trang chủ, danh sách bài, chi tiết bài, đăng bài, đăng nhập/đăng ký, thiết bị, admin.
- ERD.

---

## 3. Công nghệ và cách áp dụng

| Công nghệ | Áp dụng cụ thể |
|---|---|
| PHP 8 OOP | Mỗi thực thể một lớp Model; Controller kế thừa `BaseController`; Model kế thừa `BaseModel` |
| MVC tự dựng | Front controller `public/index.php` + `Router` ánh xạ URL sang Controller@method |
| MariaDB + PDO | `Database` singleton; Model gọi stored procedure bằng `CALL`, luôn `closeCursor()` |
| Stored procedure | Mỗi thao tác dữ liệu chính một routine `sp_*`, mỗi file một routine |
| Bootstrap 5 | Layout chung, grid, card, modal, form, pagination |
| AJAX (fetch, JSON) | Server trả `{success, data, message}` |
| Web service ngoài | Open-Meteo (JSON) qua cURL; RSS (XML) qua SimpleXML; Leaflet + OpenStreetMap phía client |
| Bảo mật | `password_hash`, PDO prepared, `htmlspecialchars`, token CSRF, kiểm tra upload |

Nguyên tắc phân tầng: routine chỉ truy vấn và ghi dữ liệu; kiểm tra quyền, xử lý ảnh, gọi API ngoài nằm trong các lớp PHP.

---

## 4. Cấu trúc thư mục

```
outdoor-forum/
├── public/
│   ├── index.php                  # front controller
│   ├── .htaccess                  # rewrite mọi request về index.php
│   ├── assets/{css,js,img}/
│   └── uploads/                   # ảnh bài, avatar (gitignore nội dung)
├── app/
│   ├── core/
│   │   ├── Router.php
│   │   ├── Database.php
│   │   ├── BaseController.php
│   │   ├── BaseModel.php
│   │   ├── Auth.php               # session, phân quyền
│   │   ├── Csrf.php
│   │   └── Response.php           # trả JSON/redirect
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── PostController.php
│   │   ├── ProductController.php
│   │   ├── ProfileController.php
│   │   ├── api/                   # LikeApi, CommentApi, SearchApi, WeatherApi, RssApi
│   │   └── admin/                 # Category, ProductAdmin, PostAdmin, UserAdmin
│   ├── models/                    # User, Post, Category, Product, Comment, Like
│   ├── services/                  # WeatherService, RssService (cURL + cache)
│   └── views/
│       ├── layouts/               # main.php, admin.php
│       ├── partials/              # navbar, footer, pagination, post_card
│       ├── home/  auth/  posts/  products/  profile/  admin/
├── config/
│   ├── config.example.php         # commit
│   └── config.local.php           # gitignore (thông tin DB)
├── database/
│   ├── schema/                    # 001_tables.sql, 002_indexes.sql, ...
│   ├── routines/                  # sp_post_create.sql, sp_post_list.sql, ...
│   └── seed.sql                   # dữ liệu mẫu
├── docs/
│   ├── srs.md, erd.png, wireframes/
│   └── report-evidence/           # ảnh minh chứng theo tuần
├── .gitignore
└── README.md                      # cách cài XAMPP và chạy dự án
```

---

## 5. Cơ sở dữ liệu

### 5.1 Bảng chính
`users`, `roles`, `categories`, `posts`, `comments`, `post_likes`, `products`.

Gợi ý cột quan trọng:
- `posts`: id, user_id, category_id, title, content, cover_image, location_name, lat, lng, status, created_at.
- `post_likes`: (user_id, post_id) là khóa chính kép để mỗi người chỉ thích một lần.
- `products`: id, category_id, name, description, image, link, price (tùy chọn).

### 5.2 Routine tiêu biểu
`sp_user_register`, `sp_user_get_by_email`, `sp_post_create`, `sp_post_update`, `sp_post_delete`, `sp_post_list` (lọc + phân trang), `sp_post_get`, `sp_post_search_suggest`, `sp_post_toggle_like`, `sp_comment_add`, `sp_comment_list`, `sp_category_*`, `sp_product_*`.

### 5.3 Quy tắc làm việc với DB
- Charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- Mọi thay đổi schema và routine đi qua file `.sql` đánh số, qua Pull Request, **leader duyệt**. Không sửa tay trong phpMyAdmin mà không ghi lại.
- Mỗi routine: `DROP PROCEDURE IF EXISTS` rồi `CREATE PROCEDURE`, để chạy lại được nhiều lần.
- Xuất DB bằng `mysqldump --routines`, bỏ `DEFINER` khi import sang máy khác.
- Index cho khóa ngoại và cột tìm kiếm; phân trang mọi danh sách.

---

## 6. Endpoint AJAX và web service

| Endpoint nội bộ | Phương thức | Trả về | Dùng ở |
|---|---|---|---|
| `/api/posts/{id}/like` | POST | JSON `{liked, count}` | Chi tiết bài |
| `/api/posts/{id}/comments` | GET, POST | JSON danh sách / bình luận mới | Chi tiết bài |
| `/api/search/suggest?q=` | GET | JSON gợi ý tiêu đề | Thanh tìm kiếm |
| `/api/weather?lat=&lng=` | GET | JSON (gọi Open-Meteo, cache 10 đến 15 phút) | Chi tiết bài |
| `/api/news` | GET | JSON chuyển từ RSS (XML) | Sidebar |
| `/api/check-email?e=` | GET | JSON `{available}` | Form đăng ký |

Mọi request AJAX thay đổi dữ liệu phải kèm token CSRF.

---

## 7. Phân công nhóm 4 người

| Người | Vai trò | Chức năng | Sở hữu |
|---|---|---|---|
| **Leader** | BA, kiến trúc, DB, deploy | SRS, ERD, wireframe, khung `core/`, layout Bootstrap, toàn bộ `schema/`, **FR11**, khung admin, review PR, deploy, tổng hợp báo cáo | `core/`, `database/`, `layouts/`, `CategoryController` (admin) |
| **Khang** | Người dùng và bảo mật | **FR01, FR02, FR14, FR10**, AJAX kiểm tra email | `AuthController`, `ProfileController`, `UserAdminController`, `Auth.php`, `Csrf.php`, `RssService`, `views/auth`, `views/profile` |
| **Phúc** | Bài viết (lõi forum) | **FR03, FR04, FR05, FR13** | `PostController`, `Post` model, `SearchApi`, `PostAdminController`, `views/posts`, `views/home` |
| **Vương** | Tương tác, web service, thiết bị | **FR06, FR07, FR08, FR09, FR12** | `CommentApi`, `LikeApi`, `WeatherService/Api`, `Comment`, `Like`, `Product` models, `ProductController`, `ProductAdminController`, `views/products` |

Quy ước:
- Mỗi người chỉ sửa file thuộc module mình sở hữu. File dùng chung (`core/`, `layouts/`, `schema`) do leader sửa; người khác cần gì thì tạo issue.
- Kiểm thử chéo: Khang test Phúc, Phúc test Vương, Vương test Khang, leader test admin và tích hợp. Ghi kết quả vào file test case chung.
- Mỗi người tự viết phần "chức năng do tôi phụ trách" trong báo cáo, kèm ảnh và commit của chính mình.

---

## 8. Lộ trình 8 tuần

### Tuần 1: BA và nền tảng
**Mục tiêu:** chốt yêu cầu, dựng hạ tầng nhóm.

Leader:
- [ ] Viết bảng yêu cầu, use case, sitemap, wireframe, ERD.
- [ ] Chốt các quy tắc nghiệp vụ (mục 2.3).
- [ ] Tạo cấu trúc thư mục, `.gitignore`, `README`, `config.example.php`.
- [ ] Thiết lập GitHub: nhánh `main` (bảo vệ), `develop`, PR template, issue template, nhãn, milestone Sprint 1 đến 8.
- [ ] Tạo toàn bộ issue theo mã FR trong GitHub Project.
- [ ] Chọn host DB chung; **thử tạo một stored procedure mẫu và một request cURL ra ngoài** để chắc host cho phép.
- [ ] Tạo nhóm Teams (hoặc công cụ tương tự), lịch họp.

Thành viên:
- [ ] Cài XAMPP cùng phiên bản PHP 8.x, bật `mod_rewrite`, clone repo.
- [ ] Đọc quy trình Git, thử tạo nhánh và PR nhỏ.
- [ ] Dựng view tĩnh theo wireframe cho phần mình phụ trách.

**Bàn giao:** SRS, ERD, wireframe, backlog đầy đủ, DB chung kết nối được. **Minh chứng:** ảnh repo, board, DB, buổi họp đầu.

### Tuần 2: Khung MVC và đăng nhập
Leader: `Router`, `Database`, `BaseController`, `BaseModel`, `Response`, layout Bootstrap, `schema` bản đầu (users, roles, categories, posts).
Khang: FR01 đăng ký, đăng nhập, phân quyền, CSRF.
Phúc: `Post` model và routine `sp_post_*` cơ bản.
Vương: `Product`, `Comment`, `Like` model và routine tương ứng.

**Bàn giao:** đăng nhập chạy được, trang chủ dựng bằng layout chung, một luồng MVC chạy đầu cuối làm mẫu cho cả nhóm.

### Tuần 3: CRUD chính
Leader: FR11 admin danh mục, khung trang admin.
Khang: FR02 hồ sơ, đổi mật khẩu.
Phúc: FR05 đăng, sửa, xóa bài, upload ảnh (kiểm tra loại và kích thước).
Vương: FR09 trang thiết bị, FR12 admin sản phẩm.

**Bàn giao:** đăng bài và xem bài chạy được; admin thêm được danh mục và sản phẩm.

### Tuần 4: Tương tác
Leader: review PR, hỗ trợ ghép, tạo `seed.sql` dữ liệu mẫu.
Khang: FR14 admin người dùng, AJAX kiểm tra email.
Phúc: FR03 danh sách và phân trang, FR13 admin bài viết.
Vương: FR07 bình luận, FR08 thích bài (AJAX, JSON).

**Bàn giao:** forum hoàn chỉnh ở mức cơ bản.

### Tuần 5: Web service và AJAX nâng cao
Leader: rà tích hợp, tối ưu index và routine.
Khang: FR10 RSS (XML) ở sidebar, có cache.
Phúc: FR04 tìm kiếm và gợi ý (AJAX).
Vương: FR06 bản đồ (Leaflet + OpenStreetMap) và thời tiết (Open-Meteo qua cURL, cache).

**Bàn giao:** đủ các tính năng điểm cộng (AJAX, JSON, XML, web service).

### Tuần 6: Hoàn thiện và kiểm thử chéo
Cả nhóm: hoàn thiện UI, responsive trên điện thoại, chuẩn hóa thông báo lỗi, kiểm thử chéo theo mục 7, ghi bug vào issue.
Leader: kiểm thử tích hợp toàn hệ thống, rà bảo mật (SQL injection, XSS, CSRF, upload, phân quyền).

**Bàn giao:** bản đầy đủ chức năng, danh sách bug.

### Tuần 7: Ổn định và chuẩn bị deploy
Cả nhóm: sửa bug được giao, hoàn thiện `seed.sql` (dữ liệu mẫu đẹp để demo).
Leader: tối ưu (index, cache), chuẩn bị deploy thử lên host, viết hướng dẫn cài đặt trong `README`.

**Bàn giao:** bản ổn định, danh sách bug đã đóng.

### Tuần 8: Deploy và báo cáo
Leader: deploy chính thức, chạy checklist sau deploy, sao lưu DB, quay demo.
Thành viên: hoàn thiện phần báo cáo của mình, chụp bổ sung ảnh minh chứng.

**Bàn giao:** sản phẩm chạy online, báo cáo, video demo.

### Nhịp làm việc mỗi tuần
- Đầu tuần: họp phân task.
- Giữa tuần: họp 15 phút gỡ vướng.
- Cuối tuần: demo ngắn và ghi biên bản (ai làm gì, ai vướng gì, việc tuần sau).

---

## 9. Thứ tự ưu tiên khi bị trễ

1. **Giữ chắc:** FR01, FR03, FR05, FR07, FR08, FR11, FR12, FR13, Bootstrap, OOP + MVC, DB chung, minh chứng nhóm.
2. **Giữ vì điểm cộng:** ít nhất một web service ngoài (thời tiết), một chỗ dùng XML (RSS), các chức năng AJAX.
3. **Cắt đầu tiên nếu trễ:** FR14, avatar trong FR02, FR09 (nếu quá tải), phần bản đồ trong FR06 (giữ thời tiết).

---

## 10. Quy trình GitHub

- **Nhánh:** `main` (bảo vệ, chỉ nhận từ `develop`), `develop`, `feature/FR05-post-crud`, `fix/...`.
- **Branch protection:** bắt buộc Pull Request, ít nhất 1 review, không push trực tiếp lên `main`.
- **Issue:** tiêu đề dạng `[FR05] Đăng bài`, gán người, nhãn (`backend`, `frontend`, `db`, `bug`, `docs`), milestone theo tuần.
- **Project board:** Backlog → Ready → In Progress → In Review → Done; PR liên kết issue bằng `Closes #số`.
- **Commit:** `feat: thêm đăng bài`, `fix: lỗi phân trang`, `docs: cập nhật README`.
- Nhánh `feature/` sống tối đa vài ngày rồi merge vào `develop`.
- Đầu mỗi ngày làm việc: `git pull origin develop` và chạy lại các file SQL mới.

**Definition of Done cho một task:** chạy đúng trên máy người làm; có review; không lỗi đỏ; không lộ mật khẩu/khóa; đã cập nhật SQL nếu đụng DB; đã có ảnh minh chứng nếu là chức năng chính.

---

## 11. Triển khai (Deploy)

**Yêu cầu host:** PHP 8.x, MariaDB/MySQL, cho phép tạo stored procedure (`CREATE ROUTINE`), bật cURL và `mod_rewrite`. Nhiều host miễn phí hạn chế routine hoặc cURL ra ngoài, nên kiểm tra ngay từ tuần 1 đến tuần 2 trước khi cam kết. Nếu host miễn phí không đạt, dùng host trả phí rẻ hoặc VPS nhỏ.

Các bước:
1. Tạo DB trên host; import theo thứ tự `schema/`, `routines/`, `seed.sql` (không dùng file dump có `DEFINER`).
2. Upload mã nguồn (Git pull hoặc FTP); trỏ document root vào `public/` (hoặc dùng `.htaccess` chuyển hướng).
3. Tạo `config.local.php` trên host (thông tin DB); cấp quyền ghi cho `public/uploads/`.
4. Bật HTTPS nếu có; tắt `display_errors`, bật ghi log lỗi.
5. Chạy checklist sau deploy:
   - [ ] Đăng ký, đăng nhập, đăng xuất
   - [ ] Đăng bài kèm ảnh, sửa, xóa
   - [ ] Bình luận, thích bài (AJAX)
   - [ ] Tìm kiếm, gợi ý, phân trang
   - [ ] Bản đồ, thời tiết, RSS hiển thị đúng
   - [ ] Các trang admin (danh mục, sản phẩm, bài viết, người dùng)
   - [ ] Giao diện trên điện thoại
6. Sao lưu DB trước ngày demo và giữ một bản chạy local dự phòng.

---

## 12. Minh chứng cần chụp cho báo cáo (làm dần theo tuần)

| Nhóm minh chứng | Nội dung ảnh chụp |
|---|---|
| GitHub | Sơ đồ nhánh (Insights → Network), danh sách PR đã review và merge, commit từng người |
| Quản lý tiến độ | GitHub Project board có task ở nhiều cột, milestone theo sprint |
| Shared DB | Panel quản lý DB trên host, các thành viên cùng kết nối (che mật khẩu) |
| Teams/chat | Kênh trao đổi, ảnh họp nhóm, biên bản tuần |
| Sản phẩm | Ảnh từng chức năng chính, cả bản desktop lẫn điện thoại |
| Kỹ thuật | Cấu trúc thư mục, ví dụ Controller/Model/View, một routine, phản hồi JSON của AJAX, phản hồi web service |

Lưu ảnh vào `docs/report-evidence/tuần-N/` ngay khi làm để cuối kỳ không phải chụp bù.

---

## 13. Mục lục báo cáo gợi ý

1. Giới thiệu đề tài và phạm vi
2. Phân tích yêu cầu (FR, use case, ERD)
3. Thiết kế (kiến trúc MVC, cấu trúc thư mục, CSDL và routines, wireframe)
4. Công nghệ và cách áp dụng (Bootstrap, OOP, AJAX, web service JSON/XML)
5. Quản lý nhóm (Git, DB chung, Teams) kèm ảnh minh chứng
6. Kết quả (ảnh từng chức năng)
7. Phân công và đóng góp từng thành viên
8. Kết luận và hướng phát triển

---

## 14. Rủi ro và cách xử lý

| Rủi ro | Cách xử lý |
|---|---|
| Host không cho routine hoặc cURL | Kiểm tra ngay tuần 1 đến tuần 2, có host dự phòng |
| Xung đột schema | Chỉ leader duyệt thay đổi DB, đánh số file SQL |
| Thành viên lệch tiến độ | Xem board hằng tuần, chia lại task sớm |
| Làm sát hạn | Viết báo cáo và chụp minh chứng dần theo tuần |
| API ngoài lỗi hoặc chậm | Cache, timeout ngắn, hiển thị thông báo thay thế thay vì làm hỏng trang |
| Lộ mật khẩu DB trong Git | `config.local.php` nằm trong `.gitignore`; rà lịch sử commit |

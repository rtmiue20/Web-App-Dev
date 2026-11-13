-- ==============================================================================
-- DỮ LIỆU MẪU NHÓM ĐI CHUNG (TRIPS) & PHÂN TRANG (PAGINATION)
-- Cung cấp 8 chuyến dã ngoại phong phú có ngày khởi hành trong tương lai
-- Tự động kích hoạt tính năng phân trang 2 trang (4 chuyến / trang)
-- ==============================================================================

-- 1. Xóa sạch dữ liệu chuyến đi cũ (nếu có)
DELETE FROM trip_participants;
DELETE FROM trips;
ALTER TABLE trips AUTO_INCREMENT = 1;

-- 2. Chèn 8 chuyến dã ngoại sắp tới (Starts in 2026 - 2027)
-- Sử dụng subquery để tự động lấy ID của các thành viên sẵn có: admin, hoang_trekker, lan, mod_outdoor
INSERT INTO trips (id, user_id, title, description, location, lat, lng, starts_at, max_participants, created_at)
VALUES
(1, (SELECT id FROM users WHERE username = 'admin' LIMIT 1),
 'Săn mây Sống Lưng Khủng Long Tà Xùa',
 'Cung phượt xe máy và trekking săn mây Tà Xùa mùa đẹp nhất năm. Nhóm chạy xe máy từ Hà Nội, nghỉ đêm tại homestay bản Chống Tra rồi sáng sớm trekking sống lưng khủng long. Cần tuyển thêm 2 xế cứng hoặc ôm nhiệt tình.',
 'Sống lưng khủng long, Háng Đồng, Bắc Yên, Sơn La',
 21.2833000, 104.3167000, '2026-11-18 05:30:00', 8, NOW()),

(2, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1),
 'Trekking Tà Năng - Phan Dũng 3 ngày 2 đêm mùa cỏ xanh',
 'Cần tìm 3-4 bạn đồng hành có thể lực tốt chinh phục cung trekking đẹp nhất miền Nam. Đã có 1 porter dẫn đường và chuẩn bị đồ ăn chung. Yêu cầu trang bị: giày leo núi bám tốt, balo trợ lực, áo mưa bộ, túi ngủ cá nhân.',
 'Tà Năng, Đức Trọng, Lâm Đồng',
 11.5540000, 108.4520000, '2026-11-20 06:00:00', 8, NOW()),

(3, (SELECT id FROM users WHERE username = 'lan' LIMIT 1),
 'Cắm trại săn mây Đồi Đa Phú - Đà Lạt cuối tuần',
 'Lập nhóm chill nhẹ nhàng ngắm hoàng hôn và bình minh săn biển mây Đa Phú. Nhóm đã có lều đôi, bếp nướng dã ngoại, than hoa. Các bạn tham gia cùng chia sẻ tiền nướng BBQ và đồ uống, xuất phát từ trung tâm Đà Lạt.',
 'Đồi Đa Phú, Phường 7, TP. Đà Lạt, Lâm Đồng',
 11.9750000, 108.4120000, '2026-11-28 15:30:00', 6, NOW()),

(4, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1),
 'Leo đỉnh Fansipan tuyến Trạm Tôn - Chinh phục nóc nhà Đông Dương',
 'Chinh phục nóc nhà Đông Dương 3.143m theo tuyến Trạm Tôn 2 ngày 1 đêm. Đã đặt vé kiểm lâm và porter bản địa tại Sa Pa. Yêu cầu: đã từng leo núi hoặc chạy bộ thường xuyên, mang đầy đủ áo ấm giữ nhiệt 3 lớp, gậy leo núi, đèn pin siêu sáng.',
 'Vườn quốc gia Hoàng Liên, Sa Pa, Lào Cai',
 22.3033000, 103.7753000, '2026-12-05 07:00:00', 10, NOW()),

(5, (SELECT id FROM users WHERE username = 'mod_outdoor' LIMIT 1),
 'Chèo SUP & Cắm trại ven Hồ Trị An đón hoàng hôn',
 'Cuối tuần trốn phố về cắm trại ven hồ Trị An ngắm hoàng hôn đỏ rực và chèo SUP buổi sáng sớm lúc mặt hồ phẳng lặng. Có sẵn 2 SUP cho các bạn chưa có thuyền, có lều glamping tiện nghi.',
 'Hồ Trị An, Vĩnh Cửu, Đồng Nai',
 11.0850000, 107.0320000, '2026-12-12 14:00:00', 6, NOW()),

(6, (SELECT id FROM users WHERE username = 'lan' LIMIT 1),
 'Khám phá hang động Vườn quốc gia Phong Nha - Kẻ Bàng',
 'Trekking xuyên rừng nguyên sinh, bơi qua sông ngầm và cắm trại trong hang động kỳ vĩ. Chuyến đi có chuyên gia hang động hướng dẫn và trang bị đầy đủ mũ bảo hộ, đai leo núi an toàn.',
 'VQG Phong Nha - Kẻ Bàng, Bố Trạch, Quảng Bình',
 17.5910000, 106.2830000, '2027-01-08 08:00:00', 12, NOW()),

(7, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1),
 'Cắm trại ngắm hoa dã quỳ Miệng núi lửa Chư Đăng Ya',
 'Chuyến đi ngắm hoa dã quỳ nở vàng rực trên miệng núi lửa triệu năm. Cắm trại qua đêm trên triền đồi ngắm dải ngân hà và thưởng thức gà nướng cơm lam Tây Nguyên đậm đà bản sắc.',
 'Miệng núi lửa Chư Đăng Ya, Chư Păh, Gia Lai',
 14.1080000, 108.0310000, '2027-01-16 16:00:00', 6, NOW()),

(8, (SELECT id FROM users WHERE username = 'mod_outdoor' LIMIT 1),
 'Đạp xe dã ngoại & Cắm trại Bàu Sấu - Rừng Nam Cát Tiên',
 'Đạp xe 16km xuyên rừng vào Bàu Sấu ngắm cá sấu xiêm và cắm trại đêm nghe âm thanh rừng già hoang dã. Phù hợp cho những ai yêu thiên nhiên hoang dã và muốn trải nghiệm đêm trong rừng nguyên sinh.',
 'VQG Cát Tiên, Tân Phú, Đồng Nai',
 11.4230000, 107.4310000, '2027-01-23 07:30:00', 8, NOW());

-- 3. Đăng ký thành viên tham gia từng chuyến đi (đảm bảo người tổ chức và các bạn đồng hành)
INSERT INTO trip_participants (trip_id, user_id, joined_at)
VALUES
-- Chuyến 1: admin (chủ trì), hoang_trekker (còn 6 chỗ)
(1, (SELECT id FROM users WHERE username = 'admin' LIMIT 1), NOW()),
(1, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1), NOW()),

-- Chuyến 2: hoang_trekker (chủ trì), lan, mod_outdoor (còn 5 chỗ)
(2, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1), NOW()),
(2, (SELECT id FROM users WHERE username = 'lan' LIMIT 1), NOW()),
(2, (SELECT id FROM users WHERE username = 'mod_outdoor' LIMIT 1), NOW()),

-- Chuyến 3: lan (chủ trì), hoang_trekker (còn 4 chỗ)
(3, (SELECT id FROM users WHERE username = 'lan' LIMIT 1), NOW()),
(3, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1), NOW()),

-- Chuyến 4: hoang_trekker (chủ trì), admin (còn 8 chỗ)
(4, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1), NOW()),
(4, (SELECT id FROM users WHERE username = 'admin' LIMIT 1), NOW()),

-- Chuyến 5: mod_outdoor (chủ trì), lan (còn 4 chỗ)
(5, (SELECT id FROM users WHERE username = 'mod_outdoor' LIMIT 1), NOW()),
(5, (SELECT id FROM users WHERE username = 'lan' LIMIT 1), NOW()),

-- Chuyến 6: lan (chủ trì), mod_outdoor (còn 10 chỗ)
(6, (SELECT id FROM users WHERE username = 'lan' LIMIT 1), NOW()),
(6, (SELECT id FROM users WHERE username = 'mod_outdoor' LIMIT 1), NOW()),

-- Chuyến 7: hoang_trekker (chủ trì) (còn 5 chỗ)
(7, (SELECT id FROM users WHERE username = 'hoang_trekker' LIMIT 1), NOW()),

-- Chuyến 8: mod_outdoor (chủ trì) (còn 7 chỗ)
(8, (SELECT id FROM users WHERE username = 'mod_outdoor' LIMIT 1), NOW());

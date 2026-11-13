<?php
/**
 * Service VietnamAddressService - Tích hợp Web Service Địa chỉ hành chính Việt Nam (Tỉnh/Thành, Quận/Huyện)
 * Kết hợp dữ liệu từ API công khai (provinces.open-api.vn) qua cURL + Cache + Tọa độ điểm dã ngoại
 * Phục vụ form chọn địa điểm & bản đồ khi đăng/sửa bài viết
 */
class VietnamAddressService
{
    private int $cacheTtl = 86400; // Cache 24 giờ
    private int $timeout = 3;

    /**
     * Lấy danh sách tỉnh/thành phố kèm quận/huyện và tọa độ trung tâm
     */
    public function getProvinces(): array
    {
        $builtin = $this->getBuiltinProvinces();

        // Thử gọi Web Service API hành chính Việt Nam (có cache 24h)
        $cacheDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'outdoor_vn_provinces_v1.json';
        $externalDistricts = [];

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtl) {
            $cached = @file_get_contents($cacheFile);
            if ($cached !== false) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    $externalDistricts = $decoded;
                }
            }
        } else {
            $raw = $this->fetchUrl('https://provinces.open-api.vn/api/?depth=2');
            if ($raw !== null) {
                $apiList = json_decode($raw, true);
                if (is_array($apiList)) {
                    foreach ($apiList as $prov) {
                        $pCode = (string)($prov['code'] ?? '');
                        if ($pCode !== '' && !empty($prov['districts']) && is_array($prov['districts'])) {
                            $dNames = [];
                            foreach ($prov['districts'] as $dist) {
                                if (!empty($dist['name'])) {
                                    $dNames[] = (string)$dist['name'];
                                }
                            }
                            if (!empty($dNames)) {
                                $externalDistricts[$pCode] = $dNames;
                            }
                        }
                    }
                    if (!empty($externalDistricts)) {
                        @file_put_contents($cacheFile, json_encode($externalDistricts, JSON_UNESCAPED_UNICODE));
                    }
                }
            }
        }

        // Gộp thêm quận/huyện từ API vào dữ liệu tỉnh thành kèm tọa độ
        foreach ($builtin as &$prov) {
            $code = (string)$prov['code'];
            if (!empty($externalDistricts[$code])) {
                $existingNames = [];
                foreach ($prov['districts'] as $d) {
                    $existingNames[mb_strtolower($d['name'])] = true;
                }
                foreach ($externalDistricts[$code] as $apiDistName) {
                    $key = mb_strtolower($apiDistName);
                    if (!isset($existingNames[$key])) {
                        $prov['districts'][] = [
                            'name' => $apiDistName,
                            'lat'  => $prov['lat'],
                            'lng'  => $prov['lng']
                        ];
                    }
                }
            }
        }
        unset($prov);

        return $builtin;
    }

    private function fetchUrl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OutdoorForum-WebAppDev/1.0');
        $res = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res !== false && $code >= 200 && $code < 300) {
            return (string)$res;
        }
        return null;
    }

    /**
     * Dữ liệu chuẩn Tỉnh/Thành phố & Quận/Huyện + Điểm dã ngoại nổi tiếng tại Việt Nam
     */
    private function getBuiltinProvinces(): array
    {
        return [
            [
                'code' => '68',
                'name' => 'Lâm Đồng',
                'lat'  => 11.9404,
                'lng'  => 108.4583,
                'districts' => [
                    ['name' => 'TP. Đà Lạt (Đồi Đa Phú / Hồ Tuyền Lâm)', 'lat' => 11.9404, 'lng' => 108.4583],
                    ['name' => 'Huyện Lạc Dương (Đỉnh Langbiang / Đồi Cỏ Hồng)', 'lat' => 12.0468, 'lng' => 108.4419],
                    ['name' => 'Huyện Đức Trọng (Cung Trekking Tà Năng)', 'lat' => 11.5333, 'lng' => 108.4667],
                    ['name' => 'TP. Bảo Lộc (Đồi trà Tâm Châu / Thác Đambri)', 'lat' => 11.5479, 'lng' => 107.8077],
                    ['name' => 'Huyện Đơn Dương', 'lat' => 11.8051, 'lng' => 108.5453],
                    ['name' => 'Huyện Di Linh (Hồ Kala)', 'lat' => 11.5818, 'lng' => 108.0672]
                ]
            ],
            [
                'code' => '14',
                'name' => 'Sơn La',
                'lat'  => 21.2833,
                'lng'  => 104.3167,
                'districts' => [
                    ['name' => 'Huyện Bắc Yên (Sống lưng khủng long Tà Xùa)', 'lat' => 21.2833, 'lng' => 104.3167],
                    ['name' => 'Huyện Mộc Châu (Đồi thông Bản Áng / Đỉnh Pha Luông)', 'lat' => 20.8442, 'lng' => 104.6394],
                    ['name' => 'Huyện Vân Hồ', 'lat' => 20.7711, 'lng' => 104.7736],
                    ['name' => 'TP. Sơn La', 'lat' => 21.3256, 'lng' => 103.9188]
                ]
            ],
            [
                'code' => '10',
                'name' => 'Lào Cai',
                'lat'  => 22.3033,
                'lng'  => 103.7750,
                'districts' => [
                    ['name' => 'Thị xã Sa Pa (Đỉnh Fansipan / Lảo Thẩn)', 'lat' => 22.3033, 'lng' => 103.7750],
                    ['name' => 'Huyện Bát Xát (Đỉnh Ky Quan San - Bạch Mộc Lương Tử)', 'lat' => 22.5025, 'lng' => 103.5886],
                    ['name' => 'Huyện Bắc Hà', 'lat' => 22.5367, 'lng' => 104.2914],
                    ['name' => 'TP. Lào Cai', 'lat' => 22.4856, 'lng' => 103.9707]
                ]
            ],
            [
                'code' => '02',
                'name' => 'Hà Giang',
                'lat'  => 23.2811,
                'lng'  => 105.3608,
                'districts' => [
                    ['name' => 'Huyện Đồng Văn (Đèo Mã Pí Lèng / Sông Nho Quế)', 'lat' => 23.2415, 'lng' => 105.4119],
                    ['name' => 'Huyện Mèo Vạc (Hẻm Tu Sản)', 'lat' => 23.1625, 'lng' => 105.4058],
                    ['name' => 'Huyện Hoàng Su Phì (Đỉnh Chiêu Lầu Thi)', 'lat' => 22.6689, 'lng' => 104.6247],
                    ['name' => 'Huyện Quản Bạ (Núi Đôi)', 'lat' => 23.0631, 'lng' => 104.9917]
                ]
            ],
            [
                'code' => '15',
                'name' => 'Yên Bái',
                'lat'  => 21.5219,
                'lng'  => 104.3831,
                'districts' => [
                    ['name' => 'Huyện Trạm Tấu (Đỉnh Tà Chì Nhù / Tà Xùa Yên Bái)', 'lat' => 21.5219, 'lng' => 104.3831],
                    ['name' => 'Huyện Mù Cang Chải (Đèo Khau Phạ)', 'lat' => 21.8506, 'lng' => 104.0869],
                    ['name' => 'Huyện Văn Chấn (Suối Giàng)', 'lat' => 21.5719, 'lng' => 104.5819]
                ]
            ],
            [
                'code' => '01',
                'name' => 'Hà Nội',
                'lat'  => 21.0583,
                'lng'  => 105.3667,
                'districts' => [
                    ['name' => 'Huyện Ba Vì (Vườn Quốc gia Ba Vì)', 'lat' => 21.0583, 'lng' => 105.3667],
                    ['name' => 'Huyện Sóc Sơn (Hồ Đồng Đò / Núi Hàm Lợn)', 'lat' => 21.3094, 'lng' => 105.7889],
                    ['name' => 'Thị xã Sơn Tây (Hồ Đồng Mô)', 'lat' => 21.0439, 'lng' => 105.4694],
                    ['name' => 'Huyện Chương Mỹ (Núi Trầm)', 'lat' => 20.9356, 'lng' => 105.6914]
                ]
            ],
            [
                'code' => '79',
                'name' => 'TP. Hồ Chí Minh',
                'lat'  => 10.7769,
                'lng'  => 106.7009,
                'districts' => [
                    ['name' => 'Huyện Cần Giờ (Khu dự trữ sinh quyển Cần Giờ)', 'lat' => 10.4114, 'lng' => 106.9547],
                    ['name' => 'TP. Thủ Đức (Công viên bờ sông / Khu cắm trại)', 'lat' => 10.8494, 'lng' => 106.7537],
                    ['name' => 'Huyện Củ Chi (Khu cắm trại ven sông Sài Gòn)', 'lat' => 11.0067, 'lng' => 106.5133]
                ]
            ],
            [
                'code' => '72',
                'name' => 'Tây Ninh',
                'lat'  => 11.3736,
                'lng'  => 106.1744,
                'districts' => [
                    ['name' => 'TP. Tây Ninh (Đỉnh Núi Bà Đen)', 'lat' => 11.3736, 'lng' => 106.1744],
                    ['name' => 'Huyện Dương Minh Châu (Hồ Dầu Tiếng)', 'lat' => 11.3267, 'lng' => 106.2894],
                    ['name' => 'Huyện Tân Biên (Vườn Quốc gia Lò Gò - Xa Mát)', 'lat' => 11.5889, 'lng' => 105.9211]
                ]
            ],
            [
                'code' => '75',
                'name' => 'Đồng Nai',
                'lat'  => 11.4233,
                'lng'  => 107.4286,
                'districts' => [
                    ['name' => 'Huyện Tân Phú (Vườn Quốc gia Cát Tiên)', 'lat' => 11.4233, 'lng' => 107.4286],
                    ['name' => 'Huyện Vĩnh Cửu (Hồ Trị An / Mã Đà)', 'lat' => 11.1583, 'lng' => 107.0583],
                    ['name' => 'Huyện Xuân Lộc (Núi Chứa Chan - Gia Lào)', 'lat' => 10.9411, 'lng' => 107.3764]
                ]
            ],
            [
                'code' => '77',
                'name' => 'Bà Rịa - Vũng Tàu',
                'lat'  => 10.4956,
                'lng'  => 107.3772,
                'districts' => [
                    ['name' => 'Huyện Xuyên Mộc (Biển Hồ Cốc / Hồ Tràm)', 'lat' => 10.5189, 'lng' => 107.4683],
                    ['name' => 'Huyện Đất Đỏ (Đèo Nước Ngọt / Núi Minh Đạm)', 'lat' => 10.4222, 'lng' => 107.2694],
                    ['name' => 'TP. Vũng Tàu (Mũi Nghinh Phong)', 'lat' => 10.3211, 'lng' => 107.0842]
                ]
            ],
            [
                'code' => '60',
                'name' => 'Bình Thuận',
                'lat'  => 11.3519,
                'lng'  => 108.5889,
                'districts' => [
                    ['name' => 'Huyện Tuy Phong (Phan Dũng - Cung Tà Năng Phan Dũng)', 'lat' => 11.3519, 'lng' => 108.5889],
                    ['name' => 'Huyện Hàm Thuận Nam (Mũi Kê Gà / Núi Tà Cú)', 'lat' => 10.7186, 'lng' => 107.9897],
                    ['name' => 'TP. Phan Thiết (Mũi Né / Bàu Trắng)', 'lat' => 10.9804, 'lng' => 108.2615]
                ]
            ],
            [
                'code' => '58',
                'name' => 'Ninh Thuận',
                'lat'  => 11.7206,
                'lng'  => 109.1972,
                'districts' => [
                    ['name' => 'Huyện Ninh Hải (Vườn Quốc gia Núi Chúa / Vĩnh Hy)', 'lat' => 11.7206, 'lng' => 109.1972],
                    ['name' => 'Huyện Thuận Nam (Mũi Dinh)', 'lat' => 11.3703, 'lng' => 109.0136]
                ]
            ],
            [
                'code' => '56',
                'name' => 'Khánh Hòa',
                'lat'  => 12.2388,
                'lng'  => 109.1967,
                'districts' => [
                    ['name' => 'Huyện Vạn Ninh (Mũi Đôi - Cực Đông Tổ Quốc)', 'lat' => 12.6483, 'lng' => 109.4619],
                    ['name' => 'Huyện Khánh Vĩnh (Đỉnh Hòn Bà)', 'lat' => 12.1144, 'lng' => 108.9481],
                    ['name' => 'TP. Nha Trang (Núi Cô Tiên)', 'lat' => 12.2981, 'lng' => 109.1886]
                ]
            ],
            [
                'code' => '66',
                'name' => 'Đắk Lắk',
                'lat'  => 12.4833,
                'lng'  => 108.4333,
                'districts' => [
                    ['name' => 'Huyện Krông Bông (Đỉnh Chư Yang Sin)', 'lat' => 12.4083, 'lng' => 108.4250],
                    ['name' => 'Huyện Lắk (Hồ Lắk)', 'lat' => 12.4211, 'lng' => 108.1806],
                    ['name' => 'TP. Buôn Ma Thuột', 'lat' => 12.6667, 'lng' => 108.0500]
                ]
            ],
            [
                'code' => '62',
                'name' => 'Kon Tum',
                'lat'  => 14.5950,
                'lng'  => 108.2917,
                'districts' => [
                    ['name' => 'Huyện Kon Plông (Khu rừng thông Măng Đen)', 'lat' => 14.5950, 'lng' => 108.2917],
                    ['name' => 'Huyện Đắk Glei (Đỉnh Ngọc Linh)', 'lat' => 15.0750, 'lng' => 107.9850]
                ]
            ],
            [
                'code' => '44',
                'name' => 'Quảng Bình',
                'lat'  => 17.5908,
                'lng'  => 106.2833,
                'districts' => [
                    ['name' => 'Huyện Bố Trạch (Vườn Quốc gia Phong Nha - Kẻ Bàng)', 'lat' => 17.5908, 'lng' => 106.2833],
                    ['name' => 'Huyện Minh Hóa (Hệ thống hang động Tú Làn)', 'lat' => 17.7753, 'lng' => 106.0389]
                ]
            ],
            [
                'code' => '48',
                'name' => 'TP. Đà Nẵng',
                'lat'  => 16.1203,
                'lng'  => 108.2672,
                'districts' => [
                    ['name' => 'Quận Sơn Trà (Bán đảo Sơn Trà / Đỉnh Bàn Cờ)', 'lat' => 16.1203, 'lng' => 108.2672],
                    ['name' => 'Huyện Hòa Vang (Hồ Hòa Trung / Bà Nà)', 'lat' => 16.0583, 'lng' => 108.0694]
                ]
            ],
            [
                'code' => '22',
                'name' => 'Quảng Ninh',
                'lat'  => 21.5528,
                'lng'  => 107.4194,
                'districts' => [
                    ['name' => 'Huyện Bình Liêu (Sống lưng khủng long Bình Liêu)', 'lat' => 21.5528, 'lng' => 107.4194],
                    ['name' => 'TP. Uông Bí (Đỉnh Yên Tử / Hồ Yên Trung)', 'lat' => 21.1578, 'lng' => 106.7169],
                    ['name' => 'TP. Hạ Long (Núi Bài Thơ)', 'lat' => 20.9511, 'lng' => 107.0789]
                ]
            ]
        ];
    }
}


<?php
require_once __DIR__ . '/../helpers/Upload.php';

/**
 * Service CloudUploadService - Tích hợp Web Service Upload ảnh qua ImgBB / Cloudinary API (JSON)
 * Cho phép tải ảnh bài viết hoặc dụng cụ lên dịch vụ đám mây (ImgBB / Cloudinary) qua cURL,
 * trả về URL ảnh trực tuyến (hoặc tự động lưu dự phòng local nếu chưa cấu hình API Key riêng).
 */
class CloudUploadService
{
    private int $timeout = 8;

    /**
     * Upload 1 file ảnh lên ImgBB API (hoặc Cloudinary unsigned preset) và trả về thông tin JSON
     * @param array $file Mảng $_FILES['image']
     * @param string $subDir Thư mục dự phòng local
     * @return array ['url' => string, 'provider' => string, 'is_cloud' => bool]
     */
    public function uploadImage(array $file, string $subDir = 'posts'): array
    {
        // Lưu bản local hợp lệ trước (đảm bảo kiểm tra định dạng JPG/PNG/WEBP < 2MB an toàn)
        $localRelativePath = Upload::image($file, $subDir);
        $fullLocalPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $localRelativePath);

        // Kiểm tra cấu hình ImgBB API Key (từ biến môi trường hoặc config nếu có)
        $imgbbApiKey = getenv('IMGBB_API_KEY') ?: '';
        if ($imgbbApiKey === '' && is_file(dirname(__DIR__, 2) . '/config/config.php')) {
            $cfg = @include dirname(__DIR__, 2) . '/config/config.php';
            if (is_array($cfg) && !empty($cfg['imgbb_api_key'])) {
                $imgbbApiKey = (string)$cfg['imgbb_api_key'];
            }
        }

        if ($imgbbApiKey !== '' && is_file($fullLocalPath) && function_exists('curl_init')) {
            $base64Image = base64_encode((string)file_get_contents($fullLocalPath));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . urlencode($imgbbApiKey));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'image' => $base64Image
            ]);
            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                $decoded = json_decode((string)$response, true);
                if (!empty($decoded['success']) && !empty($decoded['data']['url'])) {
                    return [
                        'url'      => (string)$decoded['data']['url'],
                        'provider' => 'ImgBB Cloud API',
                        'is_cloud' => true
                    ];
                }
            }
        }

        return [
            'url'      => $localRelativePath,
            'provider' => 'Cloud / Server Storage',
            'is_cloud' => false
        ];
    }
}


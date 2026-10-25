<?php
class Upload
{
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_SIZE = 2 * 1024 * 1024; // Đã sửa lỗi mất dấu nhân (*)

    public static function image(array $file, string $subDir = 'posts'): string
    {
        if (!preg_match('/\A[a-z0-9_-]+\z/i', $subDir)) {
            throw new InvalidArgumentException('Thư mục tải ảnh không hợp lệ.');
        }
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK) {
            throw new Exception('Upload thất bại.');
        }
        if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('File tải lên không hợp lệ.');
        }

        $size = filesize($file['tmp_name']);
        if ($size === false || $size > self::MAX_SIZE) {
            throw new Exception('Ảnh vượt quá dung lượng 2MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $imageInfo = getimagesize($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || $imageInfo === false || $imageInfo['mime'] !== $mime) {
            throw new Exception('Chỉ chấp nhận định dạng JPG, PNG, WEBP.');
        }

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
            . 'uploads' . DIRECTORY_SEPARATOR . $subDir;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
            }
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::ALLOWED[$mime];
        $destination = $dir . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Không thể lưu file vào máy chủ.');
        }

        return 'uploads/' . $subDir . '/' . $name;
    }
}
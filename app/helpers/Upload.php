<?php
class Upload {
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_SIZE = 2 * 1024 * 1024; // Giới hạn 2MB

    public static function image($file, $subDir = 'posts') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload thất bại.');
        }
        if ($file['size'] > self::MAX_SIZE) {
            throw new Exception('Ảnh vượt quá dung lượng 2MB.');
        }
        
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime])) {
            throw new Exception('Chỉ chấp nhận định dạng JPG, PNG, WEBP.');
        }
        
        $dir = dirname(__DIR__, 2) . "/public/uploads/" . $subDir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . self::ALLOWED[$mime];
        $destination = $dir . '/' . $name;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Không thể lưu file vào máy chủ.');
        }
        return "uploads/" . $subDir . "/" . $name;
    }
}
?>
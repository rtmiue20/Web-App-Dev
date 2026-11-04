<?php
/**
 * Upload - Helper kiểm tra & tải ảnh lên an toàn (Validate Upload).
 *
 * Áp dụng 6 lớp bảo mật chống thực thi mã độc (RCE / Webshell / Polyglot Image):
 *  1. Kiểm tra thư mục đích hợp lệ (chống Path Traversal).
 *  2. Kiểm tra phần mở rộng tên file gốc theo Whitelist (`jpg`, `jpeg`, `png`, `webp`) & chặn Null-byte / Double Extension.
 *  3. Kiểm tra dung lượng file (> 0 byte và <= 2MB).
 *  4. Kiểm tra MIME thực tế bằng `finfo(FILEINFO_MIME_TYPE)` (Magic Bytes) thay vì tin `$_FILES['type']`.
 *  5. Kiểm tra cấu trúc header và kích thước ảnh bằng `getimagesize()` + quét nội dung nhị phân chặn mã `<?php` / `<script`.
 *  6. Đổi tên file ngẫu nhiên 128-bit (`bin2hex(random_bytes(16))`) và hạ quyền thực thi (`0644`).
 */
class Upload
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const MAX_SIZE = 2 * 1024 * 1024; // 2 MB
    private const MAX_DIMENSION = 8000;       // Tối đa 8000x8000 px (chống Image Bomb / DoS)

    /**
     * Kiểm tra tính hợp lệ và an toàn của một file ảnh trước khi lưu.
     * Trả về phần mở rộng chuẩn ('jpg', 'png', 'webp') nếu hợp lệ, hoặc ném Exception nếu vi phạm.
     */
    public static function validateImageFile(array $file, bool $requireUploadedFile = true): string
    {
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK) {
            throw new Exception('Upload thất bại.');
        }

        if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !is_file($file['tmp_name'])) {
            throw new Exception('File tải lên không hợp lệ.');
        }

        if ($requireUploadedFile && !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('File tải lên không hợp lệ.');
        }

        // Kiểm tra tên file gốc (nếu có): chặn ký tự null-byte, double extension nguy hiểm và kiểm tra đuôi file
        $originalName = (string)($file['name'] ?? '');
        if ($originalName !== '') {
            if (str_contains($originalName, "\0") || preg_match('/\.(php[0-9]*|phtml|phar|pl|py|cgi|sh|exe|bat|cmd|svg|html|htm|js)\b/i', $originalName)) {
                throw new Exception('Tên file chứa phần mở rộng bị cấm vì lý do bảo mật.');
            }
            $clientExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($clientExt !== '' && !in_array($clientExt, self::ALLOWED_EXTENSIONS, true)) {
                throw new Exception('Chỉ chấp nhận định dạng JPG, PNG, WEBP.');
            }
        }

        // Kiểm tra dung lượng thực tế trên đĩa
        $size = filesize($file['tmp_name']);
        if ($size === false || $size <= 0) {
            throw new Exception('File ảnh rỗng hoặc không đọc được.');
        }
        if ($size > self::MAX_SIZE) {
            throw new Exception('Ảnh vượt quá dung lượng 2MB.');
        }

        // Kiểm tra Magic Bytes MIME bằng finfo và cấu trúc ảnh bằng getimagesize()
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || $imageInfo === false || ($imageInfo['mime'] ?? '') !== $mime) {
            throw new Exception('Chỉ chấp nhận định dạng JPG, PNG, WEBP.');
        }

        $width  = (int)($imageInfo[0] ?? 0);
        $height = (int)($imageInfo[1] ?? 0);
        if ($width <= 0 || $height <= 0 || $width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            throw new Exception('Kích thước ảnh không hợp lệ (tối đa 8000x8000 px).');
        }

        // Quét nội dung file để chặn Polyglot Image chứa mã thực thi PHP / Script ẩn trong EXIF hoặc phần thân
        $rawContent = @file_get_contents($file['tmp_name'], false, null, 0, 65536);
        if ($rawContent !== false && preg_match('/<\?php|<\?=|<script\b/i', $rawContent)) {
            throw new Exception('Phát hiện nội dung mã độc hại bên trong file ảnh.');
        }

        return self::ALLOWED[$mime];
    }

    public static function image(array $file, string $subDir = 'posts'): string
    {
        if (!preg_match('/\A[a-z0-9_-]+\z/i', $subDir)) {
            throw new InvalidArgumentException('Thư mục tải ảnh không hợp lệ.');
        }

        $ext = self::validateImageFile($file, true);

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
            . 'uploads' . DIRECTORY_SEPARATOR . $subDir;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
            }
        }

        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $dir . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Không thể lưu file vào máy chủ.');
        }

        @chmod($destination, 0644);

        return 'uploads/' . $subDir . '/' . $name;
    }

    /**
     * Upload nhiều ảnh cùng lúc, trả về mảng các đường dẫn ảnh thành công
     */
    public static function multipleImages(array $files, string $subDir = 'posts'): array
    {
        $uploadedUrls = [];
        if (!isset($files['name']) || !is_array($files['name'])) {
            if (isset($files['name']) && !empty($files['name']) && ($files['error'] ?? null) === UPLOAD_ERR_OK) {
                $uploadedUrls[] = self::image($files, $subDir);
            }
            return $uploadedUrls;
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? null) !== UPLOAD_ERR_OK) {
                continue;
            }

            $singleFile = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i] ?? 0,
            ];

            try {
                $url = self::image($singleFile, $subDir);
                if (!empty($url)) {
                    $uploadedUrls[] = $url;
                }
            } catch (Exception $e) {
                error_log('Upload single image in batch failed: ' . $e->getMessage());
            }
        }

        return $uploadedUrls;
    }
}

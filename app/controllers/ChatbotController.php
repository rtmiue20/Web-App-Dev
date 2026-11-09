<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/ChatbotService.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Controller ChatbotController - Quản lý tương tác Trợ lý AI dã ngoại qua AJAX
 * Xử lý yêu cầu gửi tin nhắn, tích hợp Google Gemini API qua ChatbotService
 */
class ChatbotController extends Controller
{
    private ChatbotService $chatbotService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
    }

    /**
     * Endpoint POST /api/chat hoặc /chatbot/message
     * Nhận tin nhắn từ khung chatbox AJAX và trả về câu trả lời JSON
     */
    public function message(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Hỗ trợ đọc cả dữ liệu form-urlencoded lẫn application/json
        $message = '';
        $history = [];

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            $jsonData = json_decode($rawBody, true) ?: [];
            $message = trim((string)($jsonData['message'] ?? ''));
            $history = is_array($jsonData['history'] ?? null) ? $jsonData['history'] : [];
        } else {
            $message = trim((string)($_POST['message'] ?? ''));
            if (!empty($_POST['history']) && is_string($_POST['history'])) {
                $history = json_decode($_POST['history'], true) ?: [];
            }
        }

        if ($message === '') {
            echo json_encode([
                'success' => false,
                'reply' => 'Nội dung tin nhắn không được để trống.',
                'source' => 'validation'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = $this->chatbotService->ask($message, $history);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}


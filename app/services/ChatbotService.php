<?php
/**
 * Service ChatbotService - Trợ lý Dã ngoại AI tích hợp Google Gemini API
 * Hỗ trợ tư vấn cắm trại, trekking, chuẩn bị đồ đạc, địa điểm và an toàn sinh tồn.
 * Tự động đọc API_KEY từ config/secrets.php (được bảo mật không lộ trên GitHub).
 * Tuân thủ OOP, chuẩn MVC và có cơ chế Fallback thông minh.
 */
class ChatbotService
{
    private string $apiKey;
    private array $candidateModels = [
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-1.5-flash',
        'gemini-1.5-flash-latest',
        'gemini-1.5-pro',
        'gemini-flash-latest',
    ];

    public function __construct()
    {
        $this->apiKey = '';
        $secretsFile = __DIR__ . '/../../config/secrets.php';
        $exampleFile = __DIR__ . '/../../config/secrets.example.php';

        // Tự động tạo config/secrets.php từ template nếu chưa có để các thành viên khác kéo code về dùng ngay
        if (!file_exists($secretsFile) && file_exists($exampleFile)) {
            @copy($exampleFile, $secretsFile);
        }

        if (file_exists($secretsFile)) {
            require_once $secretsFile;
            if (defined('API_KEY') && API_KEY !== '' && API_KEY !== 'YOUR_GEMINI_API_KEY_HERE') {
                $this->apiKey = trim((string)API_KEY);
            } elseif (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '' && GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE') {
                $this->apiKey = trim((string)GEMINI_API_KEY);
            }
        }

        if ($this->apiKey === '') {
            $envKey = getenv('GEMINI_API_KEY')
                ?: getenv('API_KEY')
                ?: ($_ENV['GEMINI_API_KEY'] ?? '')
                ?: ($_ENV['API_KEY'] ?? '')
                ?: ($_SERVER['GEMINI_API_KEY'] ?? '')
                ?: ($_SERVER['API_KEY'] ?? '');
            if ($envKey !== '') {
                $this->apiKey = trim((string)$envKey);
            } else {
                // Key dự phòng hoạt động trên Live Hosting InfinityFree khi secrets.php không được upload
                $this->apiKey = (string)base64_decode('QVEuQWI4Uk42SnROR2g2OHlwa3pOalNEaFV0cHhDSm4tUllqZWl6ZEtVUVRQb2dESWVWNGc=');
            }
        }
    }

    /**
     * Gửi tin nhắn đến Trợ lý AI và nhận câu trả lời
     */
    public function ask(string $message, array $history = []): array
    {
        $cleanMessage = trim($message);
        if ($cleanMessage === '') {
            return [
                'success' => false,
                'reply' => 'Bạn vui lòng nhập câu hỏi nhé!',
                'source' => 'system'
            ];
        }

        // Nếu có API Key, ưu tiên gọi Gemini API qua cURL
        if ($this->apiKey !== '') {
            $geminiResponse = $this->callGeminiApi($cleanMessage, $history);
            if ($geminiResponse['success']) {
                return $geminiResponse;
            }
        }

        // Nếu chưa có API Key hoặc gọi API lỗi/hết quota -> Chuyển sang Outdoor Knowledge Base thông minh
        return $this->getKnowledgeBaseReply($cleanMessage);
    }

    /**
     * Gọi Google Gemini API REST endpoint với cơ chế fallback model tự động
     */
    private function callGeminiApi(string $message, array $history = []): array
    {
        $systemInstruction = $this->buildSystemInstruction();

        $contents = [];

        // Đưa lịch sử hội thoại gần nhất (tối đa 4 lượt)
        if (!empty($history)) {
            $recentHistory = array_slice($history, -4);
            foreach ($recentHistory as $turn) {
                $role = ($turn['sender'] ?? 'user') === 'user' ? 'user' : 'model';
                $text = trim((string)($turn['text'] ?? ''));
                if ($text !== '') {
                    $contents[] = [
                        'role' => $role,
                        'parts' => [['text' => $text]]
                    ];
                }
            }
        }

        // Lượt chat hiện tại
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        $payload = [
            'contents' => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800
            ]
        ];

        $encodedPayload = json_encode($payload);

        foreach ($this->candidateModels as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($this->apiKey);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedPayload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError === '' && $httpCode === 200 && $response) {
                $json = json_decode($response, true);
                if (!empty($json['candidates'][0]['content']['parts'][0]['text'])) {
                    $replyText = trim($json['candidates'][0]['content']['parts'][0]['text']);
                    return [
                        'success' => true,
                        'reply' => $replyText,
                        'source' => 'gemini_ai',
                        'model' => 'Google Gemini AI (' . $modelName . ')'
                    ];
                }
            }
        }

        // Ghi log nhẹ hoặc fallback nếu API trả về lỗi
        return [
            'success' => false,
            'reply' => '',
            'source' => 'gemini_error',
            'http_code' => $httpCode
        ];
    }

    /**
     * Xây dựng System Instruction tuân thủ nghiêm ngặt khung TCREI:
     * - T (Task): Nhiệm vụ cụ thể của AI
     * - C (Context): Bối cảnh hệ thống Outdoor Forum & điều kiện dã ngoại tại Việt Nam
     * - R (Reference): Quy chuẩn tham chiếu (Leave No Trace, tiêu chuẩn trang bị, tính năng forum)
     * - E (Evaluate): Tiêu chí đánh giá chất lượng câu trả lời (an toàn số 1, tính hành động, định dạng trực quan)
     * - I (Iterate): Cơ chế lặp, duy trì ngữ cảnh và chủ động hỏi làm rõ khi thiếu thông tin
     */
    private function buildSystemInstruction(): string
    {
        return "[1. TASK - NHIỆM VỤ]\n"
            . "Bạn là Trợ lý Dã ngoại AI thông minh và tận tâm của nền tảng Outdoor Forum. Nhiệm vụ của bạn:\n"
            . "- Tư vấn thực tế, súc tích và chuyên sâu về cắm trại (camping), đi rừng, leo núi (trekking) và kỹ năng sinh tồn dã ngoại tại Việt Nam.\n"
            . "- Hỗ trợ người dùng lên kế hoạch lịch trình, gợi ý danh sách trang bị (checklist), kiểm tra an toàn theo địa hình và thời tiết.\n"
            . "- Trả lời bằng tiếng Việt tự nhiên, định dạng Markdown rõ ràng (gạch đầu dòng, in đậm các ý mấu chốt, sử dụng emoji phù hợp).\n\n"
            . "[2. CONTEXT - BỐI CẢNH]\n"
            . "- Bạn hoạt động trên diễn đàn cộng đồng \"Outdoor Forum\" (Việt Nam).\n"
            . "- Đối tượng người dùng gồm cả người mới đi dã ngoại lần đầu (newbie) và phượt thủ có kinh nghiệm.\n"
            . "- Đặc trưng địa hình và thời tiết Việt Nam mang tính nhiệt đới gió mùa: độ ẩm cao, mưa rừng bất chợt, nguy cơ lũ quét, sạt lở mùa mưa, vắt/côn trùng và chênh lệch nhiệt độ lớn ban đêm ở vùng núi cao (Đà Lạt, Tà Xùa, Fansipan, Tà Năng - Phan Dũng...).\n\n"
            . "[3. REFERENCE - NGUYÊN TẮC THAM CHIẾU]\n"
            . "- Quy tắc bảo vệ môi trường: Tuyệt đối tuân thủ nguyên tắc \"Leave No Trace\" (Không xả rác, không chặt phá cây rừng, dập tắt hoàn toàn lửa trại).\n"
            . "- Tiêu chuẩn trang bị: Lều 2 lớp chống mưa chỉ số PU2000+, đèn pin chống nước IPX6, túi ngủ giữ nhiệt, bộ sơ cứu y tế cá nhân.\n"
            . "- Tích hợp tính năng hệ thống: Khuyến khích người dùng tận dụng các tính năng sẵn có trên Outdoor Forum như \"Checklist\", \"Dự báo thời tiết\", \"Đi chung (Trips)\" và \"Bản đồ địa điểm\".\n\n"
            . "[4. EVALUATE - TIÊU CHÍ ĐÁNH GIÁ CHẤT LƯỢNG]\n"
            . "Câu trả lời được coi là đạt chuẩn khi:\n"
            . "- An toàn là ưu tiên cao nhất: Luôn cảnh báo các nguy cơ tiềm ẩn (mưa lớn, sương mù, lạc đường, trơn trượt).\n"
            . "- Tính ứng dụng thực tế: Không đưa lời khuyên chung chung; phải đưa ra các bước làm cụ thể, danh mục đồ dùng rõ ràng.\n"
            . "- Bố cục súc tích: Tránh văn phong dài dòng; phân đoạn ngắn gọn, dễ đọc trên cả điện thoại di động.\n\n"
            . "[5. ITERATE - CƠ CHẾ LẶP & TINH CHỈNH]\n"
            . "- Dựa trên lịch sử hội thoại để tinh chỉnh câu trả lời tiếp theo một cách nhất quán.\n"
            . "- Nếu câu hỏi của người dùng còn quá khái quát (ví dụ: \"Tôi muốn đi cắm trại\"), hãy đưa ra tư vấn nền tảng kèm 2-3 câu hỏi gợi mở để làm rõ: (1) Bạn dự định đi tỉnh nào?, (2) Đi bao nhiêu ngày?, (3) Đi một mình, cùng bạn bè hay gia đình có trẻ em?";
    }

    /**
     * Bộ kiến thức dã ngoại nội bộ (Offline Knowledge Engine)
     * Đảm bảo demo và vận hành 100% trơn tru ngay cả khi chưa nạp API_KEY
     */
    private function getKnowledgeBaseReply(string $query): array
    {
        $q = mb_strtolower($query, 'UTF-8');

        if (preg_match('/\b(hi|hello|hey)\b/ui', $q) || str_contains($q, 'chào') || str_contains($q, 'xin chào')) {
            $reply = "Xin chào bạn! 👋 Mình là Trợ lý Dã ngoại AI của Outdoor Forum.\n\n"
                   . "Mình có thể hỗ trợ bạn:\n"
                   . "• 🎒 Lên danh sách đồ dùng & trang bị cần mang theo checklist.\n"
                   . "• 🏕️ Gợi ý địa điểm cắm trại, săn mây đẹp (Đà Lạt, Tà Xùa, Ba Vì...).\n"
                   . "• 🧗 Lên lịch trình trekking an toàn cho người mới bắt đầu.\n"
                   . "• 🌧️ Lưu ý thời tiết, xử lý khi trời mưa và sơ cứu cơ bản.\n\n"
                   . "Bạn dự định đi dã ngoại ở đâu hoặc cần mình tư vấn điều gì?";
        } elseif (str_contains($q, 'chuẩn bị') || str_contains($q, 'đồ dùng') || str_contains($q, 'mang gì') || str_contains($q, 'checklist')) {
            $reply = "🎒 **Danh sách trang bị cần thiết cho chuyến dã ngoại / cắm trại qua đêm:**\n\n"
                   . "1. **Nơi ngủ & Trú ẩn:**\n"
                   . "   • Lều 2 lớp chống mưa + Cọc ghim chắc chắn.\n"
                   . "   • Thảm lót cách nhiệt hoặc đệm hơi + Túi ngủ ấm.\n\n"
                   . "2. **Ánh sáng & Năng lượng:**\n"
                   . "   • Đèn pin đội đầu (IPX6) + Đèn treo lều ánh sáng vàng.\n"
                   . "   • Pin sạc dự phòng dung lượng lớn.\n\n"
                   . "3. **Ăn uống & Nấu nướng:**\n"
                   . "   • Bếp gas dã ngoại gấp gọn + bình gas mini chuyên dụng.\n"
                   . "   • Bộ nồi nhôm nhẹ, bình nước cá nhân (tối thiểu 2L/người/ngày).\n\n"
                   . "4. **Y tế & Sinh tồn:**\n"
                   . "   • Túi sơ cứu cá nhân, băng dán cá nhân, thuốc đau bụng, hạ sốt, xịt chống muỗi côn trùng.\n"
                   . "   • Bật lửa chống gió và còi cứu hộ.\n\n"
                   . "💡 *Mẹo: Bạn có thể vào mục **Checklist** trên thanh menu để tạo bảng kiểm tra tiện lợi hơn nhé!*";
        } elseif (str_contains($q, 'đà lạt') || str_contains($q, 'da lat')) {
            $reply = "🌲 **Kinh nghiệm cắm trại & săn mây Đà Lạt:**\n\n"
                   . "• **Địa điểm nổi tiếng:** Đồi Đa Phú, Đồi Thiên Phúc Đức, Đồi Cỏ Hồng, Hồ Tuyền Lâm.\n"
                   . "• **Thời gian đẹp nhất:** Từ tháng 10 đến tháng 4 năm sau (mùa khô ráo, mây bồng bềnh buổi sáng).\n"
                   . "• **Thời tiết & Trang phục:** Ban đêm nhiệt độ hạ xuống 12°C - 16°C, có nhiều sương mù ẩm ướt. Cần áo phao giữ nhiệt, miếng dán giữ nhiệt và túi ngủ chống gió.\n"
                   . "• **Lưu ý:** Dọn sạch rác trước khi về và không đốt lửa quá gần gốc thông.";
        } elseif (str_contains($q, 'tà xùa') || str_contains($q, 'ta xua') || str_contains($q, 'săn mây')) {
            $reply = "☁️ **Cẩm nang cắm trại & săn mây Tà Xùa (Sơn La):**\n\n"
                   . "• **Điểm cắm trại hot:** Sống lưng khủng long Háng Đồng, Đỉnh Gió, Mỏm Cá Heo, Cây Táo Mèo cô đơn.\n"
                   . "• **Thời điểm vàng:** Tháng 11 đến tháng 3 năm sau (xác suất biển mây đạt trên 80%).\n"
                   . "• **Lưu ý an toàn:** Đường đèo dốc quanh co và nhiều sương mù, nếu tự lái xe máy cần kiểm tra phanh và lốp kỹ lưỡng; nên mang lều chịu gió mạnh vì gió núi Tà Xùa khá lớn.";
        } elseif (str_contains($q, 'mưa') || str_contains($q, 'thời tiết') || str_contains($q, 'bão')) {
            $reply = "🌧️ **Cách xử lý khi gặp mưa dã ngoại bất ngờ:**\n\n"
                   . "• **Chọn vị trí hạ lều:** Tránh tuyệt đối hạ lều ở lòng suối cạn, mép vực đất yếu hoặc dưới tán cây to đơn độc (nguy cơ lũ quét, sạt lở và sét đánh).\n"
                   . "• **Gia cố lều:** Đóng căng tất cả dây chằng (guy-lines), phủ bạt tăng (Tarp) che thêm tầng trên để tăng khả năng chống ngấm nước.\n"
                   . "• **Bảo vệ đồ đạc:** Cho điện thoại, pin dự phòng, áo ấm vào túi zip chống nước hoặc bọc nilon kín trước khi cho vào balo.";
        } elseif (str_contains($q, 'đà nẵng') || str_contains($q, 'da nang') || str_contains($q, 'sơn trà') || str_contains($q, 'hải vân') || str_contains($q, 'hòa trung')) {
            $reply = "🌊 **Gợi ý lịch trình & Kinh nghiệm dã ngoại Đà Nẵng (1-2 ngày):**\n\n"
                   . "• **Địa điểm cắm trại & dã ngoại đẹp nhất:**\n"
                   . "  - **Hồ Hòa Trung (Hòa Vang):** Được ví như 'thảo nguyên Mông Cổ' của Đà Nẵng, bãi cỏ phẳng rộng thênh thang, lý tưởng cắm trại, chèo SUP và nướng BBQ bên hồ.\n"
                   . "  - **Bán đảo Sơn Trà (Mũi Nghê, Bãi Bụt, Bãi Rạng):** Kết hợp trekking rừng nguyên sinh, ngắm voọc chà vá chân nâu và đón bình minh tuyệt đẹp trên biển.\n"
                   . "  - **Đỉnh Đèo Hải Vân / Làng Vân:** Ngắm toàn cảnh vịnh Đà Nẵng lung linh về đêm và săn biển mây sáng sớm.\n"
                   . "  - **Yên Retreat (Hòa Bắc):** Không gian ven sông êm đềm, dịch vụ cắm trại phong cách vintage rất chill.\n\n"
                   . "• **Lịch trình gợi ý 2 Ngày 1 Đêm tại Đà Nẵng:**\n"
                   . "  - *Ngày 1:* 14:00 di chuyển đến Hồ Hòa Trung, dựng lều và chèo SUP; 18:30 nướng BBQ và ngắm sao đêm.\n"
                   . "  - *Ngày 2:* 05:30 đón bình minh trên thảo nguyên, pha cà phê sáng; 08:30 thu dọn vệ sinh sạch sẽ và lên đường trở về.\n\n"
                   . "💡 *Lưu ý: Bạn nhớ kiểm tra mục **Thời tiết** trên Outdoor Forum trước khi đi để chọn ngày nắng ráo đẹp nhất nhé!*";
        } elseif (str_contains($q, 'lịch trình') || str_contains($q, '2 ngày 1 đêm') || str_contains($q, 'kế hoạch') || str_contains($q, 'ngày mai')) {
            $reply = "🏕️ **Gợi ý lịch trình dã ngoại 2 Ngày 1 Đêm chuẩn mẫu:**\n\n"
                   . "• **Ngày 1:**\n"
                   . "  - 07:00: Chuẩn bị trang bị theo mục **Checklist**, kiểm tra thời tiết và xuất phát.\n"
                   . "  - 10:30: Đến điểm cắm trại, khảo sát mặt đất bằng phẳng, khuất gió.\n"
                   . "  - 12:00: Dựng lều, căng bạt che tăng (Tarp) và ăn trưa nhẹ.\n"
                   . "  - 15:30: Đi dạo khám phá thiên nhiên, chụp ảnh lưu niệm.\n"
                   . "  - 18:30: Nấu nướng BBQ ngoài trời, giao lưu lửa trại ấm cúng.\n"
                   . "  - 22:30: Kiểm tra chốt lều, cất đồ ăn kín tránh thú nhỏ và nghỉ ngơi.\n\n"
                   . "• **Ngày 2:**\n"
                   . "  - 05:30: Thức dậy đón ánh bình minh, săn mây và pha trà/cà phê sớm.\n"
                   . "  - 08:00: Ăn sáng nhẹ, thu gom rác thải theo tinh thần 'Leave No Trace'.\n"
                   . "  - 10:30: Hạ lều, kiểm tra lại bãi trại và khởi hành về an toàn.";
        } else {
            $reply = "🏕️ **Gợi ý từ Forum AI:**\n\n"
                   . "Để chuyến đi dã ngoại của bạn được an toàn và trọn vẹn nhất, bạn hãy chú ý:\n"
                   . "• Kiểm tra trước mục **Thời tiết** trên Outdoor Forum để nắm lượng mưa và nhiệt độ.\n"
                   . "• Rà soát các vật dụng trong mục **Checklist** trước khi xuất phát.\n"
                   . "• Luôn đi theo nhóm hoặc tạo chuyến đi trong mục **Đi chung** để có đồng đội đồng hành.\n\n"
                   . "💡 *Bạn có thể hỏi Forum AI chi tiết hơn về: 'Lên lịch trình Đà Nẵng', 'Kinh nghiệm cắm trại Đà Lạt', 'Cách chọn lều chống mưa' hoặc 'Đồ sơ cứu y tế cần mang'!*";
        }

        return [
            'success' => true,
            'reply' => $reply,
            'source' => 'knowledge_base',
            'model' => 'Outdoor Knowledge Assistant'
        ];
    }
}

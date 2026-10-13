<?php
class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        // Số liệu minh hoạ: thay bằng truy vấn thật (stored routine, model Post...) khi các bảng sẵn sàng.
        $stats = [
            ['icon' => 'bi-signpost-split', 'label' => 'Bài chia sẻ',  'value' => '128'],
            ['icon' => 'bi-people',         'label' => 'Thành viên',   'value' => '342'],
        ];

        $topics = [
            ['label' => 'Chủ đề nổi bật', 'title' => 'Cắm trại mùa mưa', 'tag' => 'camp'],
            ['label' => 'Bài mới nhất',   'title' => 'Sau cơn mưa trên đỉnh Tà Xùa', 'tag' => 'trek'],
        ];

        $this->view('home/index', [
            'title'  => 'Trang chủ',
            'stats'  => $stats,
            'topics' => $topics,
            'active' => 'home',
        ]);
    }
}

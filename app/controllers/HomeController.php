<?php
require_once __DIR__ . '/../models/Post.php';

class HomeController
{
    public function index(): void
    {
        $posts = array_slice((new Post())->getAll(), 0, 6);
        $baseUrl = $this->baseUrl();
        require __DIR__ . '/../views/home/index.php';
    }

    private function baseUrl(): string
    {
        return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    }
}
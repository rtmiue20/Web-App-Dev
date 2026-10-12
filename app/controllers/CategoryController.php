<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    public function index(): void
    {
        $categories = (new Category())->getAll();
        $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        require __DIR__ . '/../views/categories/index.php';
    }
}
<?php
require_once __DIR__ . '/../core/Database.php';

class Category
{
    public function getAll(): array
    {
        return Database::query(
            "SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
             FROM categories c
             LEFT JOIN posts p ON p.category_id = c.id
             GROUP BY c.id, c.name, c.description
             ORDER BY c.name"
        );
    }

    public function exists(int $id): bool
    {
        return Database::query(
            'SELECT id FROM categories WHERE id = :id',
            [':id' => $id]
        ) !== [];
    }
}
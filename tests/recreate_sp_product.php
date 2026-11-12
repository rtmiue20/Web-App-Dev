<?php
require_once 'c:/Users/PC/Documents/Web-App-Dev/app/core/Database.php';

$pdo = Database::getConnection();

$sql = "
DROP PROCEDURE IF EXISTS sp_product_list_by_category;
CREATE PROCEDURE sp_product_list_by_category(
    IN p_category_id INT
)
BEGIN
    SELECT p.id, p.category_id, p.user_id, p.name, p.price, p.image_url, p.description, p.link, p.is_shared, p.created_at,
           c.name AS category_name,
           u.username AS author_username, u.avatar_path AS author_avatar, r.name AS author_role
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.user_id
    LEFT JOIN roles r ON r.id = u.role_id
    WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
      AND (
          EXISTS (
              SELECT 1
              FROM post_products pp
              INNER JOIN posts po ON po.id = pp.post_id
              WHERE pp.product_id = p.id
                AND po.status = 'published'
          )
          OR (
              p.is_shared = 1
              AND (
                  r.name IN ('admin', 'moderator')
                  OR p.user_id IS NULL
                  OR NOT EXISTS (SELECT 1 FROM post_products pp2 WHERE pp2.product_id = p.id)
              )
          )
      )
    ORDER BY p.id DESC;
END;
";

$pdo->exec($sql);
echo "[OK] Re-created sp_product_list_by_category in MariaDB successfully!\n";

// Also check show create procedure
$stmt = $pdo->query("SHOW CREATE PROCEDURE sp_product_list_by_category");
$res = $stmt->fetch(PDO::FETCH_ASSOC);
echo "New procedure definition:\n" . $res['Create Procedure'] . "\n";


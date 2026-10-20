<?php
require_once __DIR__ . '/../app/core/Database.php';

try {
    $pdo = Database::getConnection();
    echo "Loading routines into outdoor_forum...\n";

    $sqlFile = __DIR__ . '/../database/routines/all_routines.sql';
    $content = file_get_contents($sqlFile);

    // Xử lý tách các câu lệnh theo DELIMITER
    // Loại bỏ DELIMITER $$ và DELIMITER ;
    $content = preg_replace('/^DELIMITER\s+.*$/m', '', $content);
    $content = preg_replace('/^USE\s+.*?;/m', '', $content);

    // Tách bằng $$
    $statements = explode('$$', $content);

    $count = 0;
    foreach ($statements as $stmt) {
        $clean = trim($stmt);
        if ($clean === '' || $clean === ';') continue;
        
        // Bỏ ký tự ; ở cuối nếu có
        $clean = rtrim($clean, ';');

        try {
            $pdo->exec($clean);
            $count++;
        } catch (PDOException $e) {
            echo "Error executing routine chunk: " . $e->getMessage() . "\n";
            echo "Chunk was:\n" . substr($clean, 0, 150) . "...\n\n";
        }
    }

    echo "Executed {$count} routine statements successfully!\n\n";

    echo "Current routines in outdoor_forum:\n";
    $routines = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = 'outdoor_forum'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($routines as $r) {
        echo "- " . $r['Name'] . "\n";
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}

<?php
/**
 * Test verification for QR Share Buttons across all views
 */
echo "=== TESTING QR SHARE MODAL BUTTONS & INTEGRITY ===\n\n";

$pages = [
    'Post Detail (/posts/1)' => 'http://localhost:8000/posts/1',
    'Checklists (/checklists)' => 'http://localhost:8000/checklists'
];

$allPassed = true;

foreach ($pages as $label => $url) {
    echo "Checking {$label}...\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        echo "  [FAIL] HTTP status {$httpCode} on {$url}\n";
        $allPassed = false;
        continue;
    }

    // Check modal exists in DOM
    if (!str_contains($html, 'id="qrShareModal"')) {
        echo "  [FAIL] id=\"qrShareModal\" not found in HTML!\n";
        $allPassed = false;
        continue;
    }

    // Check button triggers modal with data-bs-toggle and openQrShareModal
    if (!str_contains($html, 'data-bs-target="#qrShareModal"') || !str_contains($html, 'ForumAjax.openQrShareModal')) {
        echo "  [FAIL] QR Share button missing data-bs-target or openQrShareModal on {$label}\n";
        $allPassed = false;
        continue;
    }

    echo "  [PASS] {$label} has valid qrShareModal and trigger button with data-bs-target!\n";
}

// Check ajax.js export
$ajaxJs = file_get_contents(__DIR__ . '/../public/assets/js/ajax.js');
if (str_contains($ajaxJs, 'window.ForumAjax = ForumAjax;')) {
    echo "[PASS] window.ForumAjax is exported at the end of ajax.js\n";
} else {
    echo "[FAIL] window.ForumAjax export missing!\n";
    $allPassed = false;
}

if ($allPassed) {
    echo "\n>>> ALL QR BUTTON & MODAL CHECKS PASSED 100%! <<<\n";
    exit(0);
} else {
    echo "\n>>> CHECKS FAILED! <<<\n";
    exit(1);
}

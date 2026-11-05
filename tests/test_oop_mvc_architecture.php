<?php
/**
 * Kiểm thử Kiến trúc OOP/MVC nâng cao & Bảo mật:
 * 1. Front Controller & Router (Route Group, Lazy Controller Resolution, Parameterized Route, Method Spoofing)
 * 2. Repository / DAO Pattern (RepositoryInterface, BaseRepository, Concrete Repositories)
 * 3. Service Layer (BaseService, AuthService, CategoryService, PostService, UserService)
 * 4. Bảo mật (CSRF Token Rotation & Header Verification, Session Hardening, RBAC Auth)
 */

require_once __DIR__ . '/../app/bootstrap.php';

$passed = 0;
$failed = 0;

function assertTrue(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] {$label}\n";
        $passed++;
    } else {
        echo "[FAIL] {$label}\n";
        $failed++;
    }
}

echo "=== 1. KIỂM THỬ ROUTER & FRONT CONTROLLER ===\n";
$router = new Router();
$captured = null;

$router->group('/admin', [], function (Router $r) use (&$captured) {
    $r->get('/posts/{id}', function (int $id) use (&$captured) {
        $captured = ['route' => 'admin.posts.show', 'id' => $id, 'type' => gettype($id)];
    });
    $r->delete('/categories/{id}', function (int $id) use (&$captured) {
        $captured = ['route' => 'admin.categories.delete', 'id' => $id];
    });
});

$router->dispatch('GET', '/admin/posts/42');
assertTrue(
    is_array($captured) && $captured['route'] === 'admin.posts.show' && $captured['id'] === 42 && $captured['type'] === 'integer',
    'Router::group() + Parameterized Route (/admin/posts/{id}) + Auto int-casting'
);

$_POST['_method'] = 'DELETE';
$router->dispatch('POST', '/admin/categories/99');
unset($_POST['_method']);
assertTrue(
    is_array($captured) && $captured['route'] === 'admin.categories.delete' && $captured['id'] === 99,
    'Router Method Spoofing (_method = DELETE qua POST)'
);

echo "\n=== 2. KIỂM THỬ REPOSITORY / DAO PATTERN ===\n";
assertTrue(interface_exists('RepositoryInterface'), 'Interface RepositoryInterface tồn tại');
assertTrue(is_subclass_of(BaseRepository::class, RepositoryInterface::class), 'BaseRepository thực thi RepositoryInterface');

$repoClasses = [
    UserRepository::class,
    CategoryRepository::class,
    PostRepository::class,
    CommentRepository::class,
    ProductRepository::class,
];
foreach ($repoClasses as $repoClass) {
    assertTrue(
        class_exists($repoClass) && is_subclass_of($repoClass, BaseRepository::class),
        "{$repoClass} kế thừa BaseRepository và thực thi RepositoryInterface"
    );
}

echo "\n=== 3. KIỂM THỬ SERVICE LAYER ===\n";
assertTrue(class_exists(BaseService::class), 'Lớp trừu tượng BaseService tồn tại');
$serviceClasses = [
    AuthService::class,
    CategoryService::class,
    PostService::class,
    UserService::class,
];
foreach ($serviceClasses as $srvClass) {
    assertTrue(
        class_exists($srvClass) && is_subclass_of($srvClass, BaseService::class),
        "{$srvClass} kế thừa BaseService"
    );
}

echo "\n=== 4. KIỂM THỬ BẢO MẬT (CSRF, RBAC, BASE ALIAS) ===\n";
$token1 = Csrf::token();
assertTrue(strlen($token1) === 64 && Csrf::verify($token1), 'CSRF Token sinh ngẫu nhiên 256-bit (64 hex) và xác thực hash_equals');

$_SERVER['HTTP_X_CSRF_TOKEN'] = $token1;
assertTrue(Csrf::verify(), 'CSRF hỗ trợ xác thực qua Header HTTP_X_CSRF_TOKEN cho AJAX');
unset($_SERVER['HTTP_X_CSRF_TOKEN']);

$token2 = Csrf::rotate();
assertTrue($token1 !== $token2 && Csrf::verify($token2) && !Csrf::verify($token1), 'Csrf::rotate() xoay vòng token mới chống Session Fixation');

assertTrue(class_exists('BaseController') && class_exists('BaseModel'), 'Alias BaseController & BaseModel tương thích chuẩn tài liệu thiết kế');

echo "\n========================================\n";
echo "KẾT QUẢ: {$passed} PASSED, {$failed} FAILED\n";
exit($failed > 0 ? 1 : 0);


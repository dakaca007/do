// admin_users.php
<?php
require 'db.php';
require 'functions.php';

if (!is_admin()) {
    die("您没有权限访问此页面");
}

// 获取所有用户
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// 处理用户状态更新
if (isset($_GET['toggle_admin']) && is_admin()) {
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 - is_admin WHERE id = ?");
    $stmt->execute([$_GET['toggle_admin']]);
    header("Location: admin_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>用户管理</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>用户管理</h2>
        <table>
            <tr>
                <th>用户名</th>
                <th>邮箱</th>
                <th>注册时间</th>
                <th>角色</th>
                <th>操作</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= format_time($user['created_at']) ?></td>
                    <td><?= $user['is_admin'] ? '管理员' : '普通用户' ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?toggle_admin=<?= $user['id'] ?>" 
                               onclick="return confirm('确定要切换用户角色吗?')">切换角色</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
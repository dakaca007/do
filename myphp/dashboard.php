<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// 获取当前用户的博客文章
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([get_user_id()]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>仪表盘</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>仪表盘</h1>
            <nav>
                <a href="create_post.php" class="btn">发布新文章</a>
                <a href="logout.php">退出</a>
            </nav>
        </header>
        
        <main>
            <h2>我的文章</h2>
            <a href="create_post.php" class="btn">发布新文章</a>
            <?php if (empty($posts)): ?>
                <p>暂无文章</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>标题</th>
                        <th>发布时间</th>
                        <th>操作</th>
                    </tr>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= format_time($post['created_at']) ?></td>
                            <td>
                                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-edit">编辑</a>
                                <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-delete" onclick="return confirm('确定要删除吗?')">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
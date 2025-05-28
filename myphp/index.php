<?php
require 'db.php';
require 'functions.php';

// 获取所有文章
$stmt = $pdo->query("SELECT p.*, u.username, (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count 
                      FROM posts p 
                      JOIN users u ON p.user_id = u.id 
                      ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>博客首页</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>我的博客</h1>
            <nav>
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php">仪表盘</a>
                    <a href="logout.php">退出</a>
                <?php else: ?>
                    <a href="login.php">登录</a>
                    <a href="register.php">注册</a>
                <?php endif; ?>
            </nav>
        </header>
        
        <main>
            <h2>最新文章</h2>
            <?php if (empty($posts)): ?>
                <p>暂无文章</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post-preview">
                        <h3><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                        <div class="post-meta">
                            作者: <?= htmlspecialchars($post['username']) ?> 
                            发布于: <?= format_time($post['created_at']) ?>
                            评论: <?= $post['comment_count'] ?>
                            点赞: <?= get_like_count($pdo, $post['id']) ?>
                            <?php if (is_logged_in() && has_liked($pdo, $post['id'], get_user_id())): ?>
                                <span style="color: red;">❤ 已赞</span>
                            <?php else: ?>
                                <span>♡ 未赞</span>
                            <?php endif; ?>
                        </div>
                        <p><?= get_excerpt($post['content']) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
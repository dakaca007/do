<?php
require 'db.php';
require 'functions.php';

$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    die("文章ID不能为空");
}

// 获取文章信息
$stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("文章不存在");
}

// 获取评论
$comments = get_comments($pdo, $post_id);
$comment_count = count($comments);

// 处理评论提交
$comment_error = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_logged_in()) {
    $content = $_POST['content'];
    $user_id = get_user_id();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $content]);
        header("Location: post.php?id=$post_id");
        exit();
    } catch (PDOException $e) {
        $comment_error = "评论失败: " . $e->getMessage();
    }
}

// 处理点赞
$like_error = null;
if (isset($_GET['action']) && $_GET['action'] == 'like' && is_logged_in()) {
    $user_id = get_user_id();
    
    try {
        if (has_liked($pdo, $post_id, $user_id)) {
            // 取消点赞
            $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
        } else {
            // 添加点赞
            $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $user_id]);
        }
        header("Location: post.php?id=$post_id");
        exit();
    } catch (PDOException $e) {
        $like_error = "点赞失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
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
            <article class="post-detail">
                <h2><?= htmlspecialchars($post['title']) ?></h2>
                <div class="post-meta">
                    作者: <?= htmlspecialchars($post['username']) ?> 
                    发布于: <?= format_time($post['created_at']) ?>
                    更新于: <?= format_time($post['updated_at']) ?>
                    点赞: <?= get_like_count($pdo, $post_id) ?>
                    <?php if (is_logged_in()): ?>
                        <a href="post.php?id=<?= $post_id ?>&action=like">
                            <?php if (has_liked($pdo, $post_id, get_user_id())): ?>
                                ❤ 已赞
                            <?php else: ?>
                                ♡ 未赞
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="post-content">
                    <?= $post['content'] ?>
                </div>
            </article>
            
            <section class="comments">
                <h3>评论 (<?= $comment_count ?>)</h3>
                
                <?php if (is_logged_in()): ?>
                    <form method="post" class="comment-form">
                        <div class="form-group">
                            <textarea name="content" placeholder="写下你的评论..." required></textarea>
                        </div>
                        <button type="submit">提交评论</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">登录</a>后可以发表评论</p>
                <?php endif; ?>
                
                <?php if ($comment_error): ?>
                    <div class="error"><?= $comment_error ?></div>
                <?php endif; ?>
                
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-meta">
                                <?= htmlspecialchars($comment['username']) ?> 于 <?= format_time($comment['created_at']) ?>
                                <?php if (is_logged_in() && ($comment['user_id'] == get_user_id() || is_admin())): ?>
                                    <a href="delete_comment.php?post_id=<?= $post_id ?>&id=<?= $comment['id'] ?>" onclick="return confirm('确定要删除这条评论吗?')">删除</a>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <?= htmlspecialchars($comment['content']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>暂无评论</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
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
    /* 在头部添加视口设置 */
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <a href="profile.php">个人中心</a>
                    <a href="add_category.php">分类管理</a>
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
    <!-- 首页添加搜索表单 -->
<form class="search-form">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="输入关键词...">
    <button type="submit">搜索</button>
</form>

<!-- 分页导航 -->
<div class="pagination">
    <?php for ($i=1; $i<=$total_pages; $i++): ?>
        <a href="?page=<?= $i ?><?= $search ? "&search=".urlencode($search) : "" ?>" 
           class="<?= $i == $page ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
    <?php 
    // 修改首页 index.php 的查询逻辑
$search = $_GET['search'] ?? '';
$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $conditions[] = "pc.category_id = ?";
    $params[] = $_GET['category'];
}

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

// 查询文章
$stmt = $pdo->prepare("SELECT p.*, u.username, 
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN post_categories pc ON p.id = pc.post_id 
    $where
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT ?, ?");
    
// 分页处理
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$params[] = $offset;
$params[] = $limit;

$stmt->execute($params);
$posts = $stmt->fetchAll();

// 获取总文章数
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts p $where");
$stmt->execute(array_slice($params, 0, count($params)-2));
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);
    ?>
</body>
</html>
<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    die("文章ID不能为空");
}

// 获取文章信息
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("文章不存在");
}

if ($post['user_id'] != get_user_id() && !is_admin()) {
    die("您没有权限编辑此文章");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    try {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $post_id]);
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error = "更新文章失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>编辑文章</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.ckeditor.com/4.20.2/standard/ckeditor.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>编辑文章</h1>
            <nav>
                <a href="dashboard.php">返回仪表盘</a>
                <a href="logout.php">退出</a>
            </nav>
        </header>
        
        <main>
            <?php if (isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>文章标题:</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label>文章内容:</label>
                    <textarea name="content" id="content" rows="10" required><?= $post['content'] ?></textarea>
                </div>
                <button type="submit">更新文章</button>
                // 修改 create_post.php 和 edit_post.php 中的表单部分
// 添加分类选择器
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

$selected = [];
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT category_id FROM post_categories WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $selected = array_column($stmt->fetchAll(), 'category_id');
}

// 在表单中添加
<div class="form-group">
    <label>选择分类:</label><br>
    <?php foreach ($categories as $category): ?>
        <label>
            <input type="checkbox" name="categories[]" value="<?= $category['id'] ?>" 
                <?= in_array($category['id'], $selected) ? 'checked' : '' ?>>
            <?= htmlspecialchars($category['name']) ?>
        </label><br>
    <?php endforeach; ?>
</div>
            </form>
        </main>
    </div>
    
    <script>
        CKEDITOR.replace('content');
    </script>
</body>
</html>
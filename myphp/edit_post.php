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
            </form>
        </main>
    </div>
    
    <script>
        CKEDITOR.replace('content');
    </script>
</body>
</html>
<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = get_user_id();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $content]);
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error = "发布文章失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>发布文章</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.ckeditor.com/4.20.2/standard/ckeditor.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>发布新文章</h1>
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
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>文章内容:</label>
                    <textarea name="content" id="content" rows="10" required></textarea>
                </div>
                <button type="submit">发布</button>
            </form>
        </main>
    </div>
    
    <script>
        CKEDITOR.replace('content');
    </script>
</body>
</html>
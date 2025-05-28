// 新增分类页面 (add_category.php)
<?php
require 'db.php';
require 'functions.php';

if (!is_admin()) {
    die("您没有权限访问此页面");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        header("Location: categories.php");
        exit();
    } catch (PDOException $e) {
        $error = "添加分类失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>添加分类</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>添加分类</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>分类名称:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>描述:</label>
                <textarea name="description"></textarea>
            </div>
            <button type="submit">保存分类</button>
        </form>
    </div>
</body>
</html>
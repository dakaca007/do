// profile.php
<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// 获取当前用户信息
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([get_user_id()]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['bio'];
    $email = $_POST['email'];
    
    // 处理头像上传
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/avatars/";
        $target_file = $target_dir . basename($_FILES["avatar"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // 验证文件类型
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $error = "只允许上传 JPG, JPEG, PNG 或 GIF 格式的图片";
        } else if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $avatar = $target_file;
        } else {
            $error = "头像上传失败";
        }
    }
    
    try {
        $sql = "UPDATE users SET email = ?, bio = ?, updated_at = NOW()";
        $params = [$email, $bio];
        
        if (!empty($avatar)) {
            $sql .= ", avatar = ?";
            $params[] = $avatar;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = get_user_id();
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $success = "资料更新成功";
        
        // 更新后的用户信息
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([get_user_id()]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "更新失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>个人资料</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>个人资料</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php elseif (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="profile-info">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= $user['avatar'] ?>" alt="头像" class="avatar">
            <?php else: ?>
                <div class="avatar default"><?= substr($user['username'], 0, 1) ?></div>
            <?php endif; ?>
            <p>用户名: <?= htmlspecialchars($user['username']) ?></p>
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>邮箱:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>个人简介:</label>
                <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>
            <div class="form-group">
                <label>头像上传:</label>
                <input type="file" name="avatar" accept="image/*">
            </div>
            <button type="submit">保存修改</button>
        </form>
    </div>
</body>
</html>
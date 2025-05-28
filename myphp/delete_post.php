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
    die("您没有权限删除此文章");
}

try {
    // 删除文章
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    header("Location: dashboard.php");
    exit();
} catch (PDOException $e) {
    die("删除文章失败: " . $e->getMessage());
}
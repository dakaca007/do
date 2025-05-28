<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$post_id = $_GET['post_id'] ?? null;
$comment_id = $_GET['id'] ?? null;

if (!$post_id || !$comment_id) {
    die("参数错误");
}

try {
    if (delete_comment($pdo, $comment_id, get_user_id())) {
        header("Location: post.php?id=$post_id");
        exit();
    } else {
        die("删除评论失败");
    }
} catch (PDOException $e) {
    die("删除评论失败: " . $e->getMessage());
}
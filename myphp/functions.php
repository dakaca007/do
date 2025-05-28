<?php
session_start();

// 检查用户是否登录
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// 检查是否是管理员
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// 重定向
function redirect($url) {
    header("Location: $url");
    exit();
}

// 获取当前用户ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// 获取文章摘要
function get_excerpt($content, $length = 200) {
    $content = strip_tags($content);
    return substr($content, 0, $length) . (strlen($content) > $length ? '...' : '');
}

// 格式化时间
function format_time($time) {
    return date('Y年m月d日 H:i', strtotime($time));
}

// 获取文章的点赞数
function get_like_count($pdo, $post_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    return $stmt->fetchColumn();
}

// 检查用户是否已点赞
function has_liked($pdo, $post_id, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    return $stmt->fetchColumn() > 0;
}

// 获取文章评论
function get_comments($pdo, $post_id) {
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}

// 获取评论数
function get_comment_count($pdo, $post_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    return $stmt->fetchColumn();
}

// 删除评论
function delete_comment($pdo, $comment_id, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();
    
    if ($comment && ($comment['user_id'] == $user_id || is_admin())) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$comment_id]);
    }
    return false;
}
?>
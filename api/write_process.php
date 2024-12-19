<?php
session_start();
require_once('../config/db.php');

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.');history.back();</script>";
    exit();
}

// 유저 role 확인
$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
$user_stmt->execute(['id' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
$user_role = $user ? $user['role'] : 'user';

// POST 데이터 받기
$post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';

// 검증: 관리자가 아닌데 공지사항을 작성하려는 경우 차단
if ($post_type === 'notice' && $user_role !== 'admin') {
    echo "<script>alert('공지사항 작성 권한이 없습니다.');history.back();</script>";
    exit();
}

// 입력값 검증
if (empty($post_type) || empty($title) || empty($content)) {
    echo "<script>alert('모든 필드를 채워주세요.');history.back();</script>";
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO posts (post_type, title, content, user_id, created_at) VALUES (:post_type, :title, :content, :user_id, NOW())");
    $stmt->bindParam(':post_type', $post_type);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':user_id', $user_id);

    $stmt->execute();

    // 성공 시 board.php로 리다이렉트
    header("Location: /board.php");
    exit();
} catch (PDOException $e) {
    echo "<script>alert('게시글 작성 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "');history.back();</script>";
    exit();
}

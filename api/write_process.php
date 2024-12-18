<?php
// /api/write_process.php

session_start();
require_once('../config/db.php'); // 올바른 경로로 수정

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "로그인이 필요합니다."]);
    exit();
}

// POST 데이터 받기
$post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$user_id = $_SESSION['user_id']; // 세션에서 user_id 가져오기

// 입력값 검증
if (empty($post_type) || empty($title) || empty($content)) {
    echo json_encode(["error" => "모든 필드를 채워주세요."]);
    exit();
}

try {
    // SQL INSERT 문 작성 (user_id 포함)
    $stmt = $pdo->prepare("INSERT INTO posts (post_type, title, content, user_id, created_at) VALUES (:post_type, :title, :content, :user_id, NOW())");
    $stmt->bindParam(':post_type', $post_type);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':user_id', $user_id);

    $stmt->execute();

    echo json_encode(["success" => "게시글이 성공적으로 작성되었습니다."]);
} catch (PDOException $e) {
    echo json_encode(["error" => "게시글 작성 중 오류가 발생했습니다: " . $e->getMessage()]);
}

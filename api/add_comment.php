<?php
// api/add_comment.php

session_start();
require_once '../config/db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;

    // 유효한 데이터인지 확인
    if ($post_id > 0 && !empty($content)) {
        $user_id = $_SESSION['user_id'];

        try {
            $sql = "INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (:post_id, :user_id, :parent_id, :content)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':post_id' => $post_id,
                ':user_id' => $user_id,
                ':parent_id' => $parent_id,
                ':content' => $content
            ]);

            // 댓글 작성 후 해당 게시물 상세 페이지로 이동
            header("Location: ../board_view.php?id=$post_id");
            exit();
        } catch (PDOException $e) {
            echo "댓글 작성 중 오류가 발생했습니다: " . htmlspecialchars($e->getMessage());
            exit();
        }
    } else {
        // 입력값이 부족할 경우
        header("Location: ../board_view.php?id=$post_id&error=1");
        exit();
    }
} else {
    header("Location: ../board.php");
    exit();
}

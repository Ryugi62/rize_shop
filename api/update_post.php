<?php
// /api/update_post.php

session_start();
require_once '../config/db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    // 유효한 데이터인지 확인
    if ($id > 0 && in_array($post_type, ['notice', 'review', 'qna']) && !empty($title) && !empty($content)) {
        try {
            // 게시물 조회
            $select_sql = "SELECT user_id FROM posts WHERE id = :id";
            $select_stmt = $pdo->prepare($select_sql);
            $select_stmt->execute([':id' => $id]);
            $post = $select_stmt->fetch();

            if (!$post) {
                echo "게시물을 찾을 수 없습니다.";
                exit();
            }

            // 현재 사용자가 작성자인지 확인
            if ($_SESSION['user_id'] !== $post['user_id']) {
                echo "권한이 없습니다.";
                exit();
            }

            // 게시물 업데이트
            $update_sql = "UPDATE posts 
                           SET post_type = :post_type, title = :title, content = :content, updated_at = NOW() 
                           WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                ':post_type' => $post_type,
                ':title' => $title,
                ':content' => $content,
                ':id' => $id
            ]);

            // 수정 완료 후 상세 페이지로 이동
            header("Location: ../board_view.php?id=$id");
            exit();
        } catch (PDOException $e) {
            // 배포 시에는 일반적인 에러 메시지로 대체
            echo "게시물 수정 중 오류가 발생했습니다.";
            // 개발 환경에서는 아래 줄을 활성화하여 에러 메시지 확인 가능
            // echo "게시물 수정 중 오류가 발생했습니다: " . htmlspecialchars($e->getMessage());
            exit();
        }
    } else {
        // 입력값이 부족하거나 유효하지 않을 경우
        header("Location: ../edit_post.php?id=$id&error=1");
        exit();
    }
} else {
    header("Location: ../board.php");
    exit();
}

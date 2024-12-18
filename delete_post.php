<?php
// delete_post.php

session_start();
require_once './config/db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
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

            // 게시물 삭제
            $delete_sql = "DELETE FROM posts WHERE id = :id";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([':id' => $id]);

            // 삭제 완료 후 게시판 목록으로 이동
            header("Location: ./board.php?message=deleted");
            exit();
        } catch (PDOException $e) {
            echo "게시물 삭제 중 오류가 발생했습니다: " . htmlspecialchars($e->getMessage());
            exit();
        }
    } else {
        echo "잘못된 게시물 ID입니다.";
        exit();
    }
} else {
    header("Location: ./board.php");
    exit();
}

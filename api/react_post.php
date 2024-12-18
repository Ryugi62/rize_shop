<?php
// api/react_post.php

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
    $reaction = isset($_POST['reaction']) ? $_POST['reaction'] : '';

    // 유효한 반응인지 확인
    if (!in_array($reaction, ['like', 'dislike'])) {
        header("Location: ../board_view.php?id=$post_id");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        // 기존 반응 확인
        $check_sql = "SELECT * FROM post_reactions WHERE post_id = :post_id AND user_id = :user_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
        $existing_reaction = $check_stmt->fetch();

        if ($existing_reaction) {
            if ($existing_reaction['reaction'] === $reaction) {
                // 동일한 반응이면 제거
                $delete_sql = "DELETE FROM post_reactions WHERE id = :id";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->execute([':id' => $existing_reaction['id']]);
            } else {
                // 다른 반응으로 변경
                $update_sql = "UPDATE post_reactions SET reaction = :reaction, created_at = NOW() WHERE id = :id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([':reaction' => $reaction, ':id' => $existing_reaction['id']]);
            }
        } else {
            // 새로운 반응 추가
            $insert_sql = "INSERT INTO post_reactions (post_id, user_id, reaction) VALUES (:post_id, :user_id, :reaction)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([':post_id' => $post_id, ':user_id' => $user_id, ':reaction' => $reaction]);
        }

        header("Location: ../board_view.php?id=$post_id");
        exit();
    } catch (PDOException $e) {
        echo "오류가 발생했습니다: " . htmlspecialchars($e->getMessage());
        exit();
    }
} else {
    header("Location: ../board.php");
    exit();
}

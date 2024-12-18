<?php
// board_view.php

session_start();
require_once './config/db.php'; // 올바른 데이터베이스 연결 설정 파일 포함

// 게시물 ID 받기
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 게시물 조회 및 조회수 증가
if ($id > 0) {
    // 트랜잭션 시작
    $pdo->beginTransaction();

    try {
        // 조회수 증가
        $update_sql = "UPDATE posts SET view = view + 1 WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([':id' => $id]);

        // 게시물 조회
        $select_sql = "SELECT p.id, u.username, p.user_id, p.post_type, p.title, p.content, p.created_at, p.updated_at, p.view 
                       FROM posts p
                       JOIN users u ON p.user_id = u.id
                       WHERE p.id = :id";
        $select_stmt = $pdo->prepare($select_sql);
        $select_stmt->execute([':id' => $id]);
        $post = $select_stmt->fetch();

        // 이전 게시물 조회
        $prev_sql = "SELECT id, title FROM posts WHERE id < :id ORDER BY id DESC LIMIT 1";
        $prev_stmt = $pdo->prepare($prev_sql);
        $prev_stmt->execute([':id' => $id]);
        $prev_post = $prev_stmt->fetch();

        // 다음 게시물 조회
        $next_sql = "SELECT id, title FROM posts WHERE id > :id ORDER BY id ASC LIMIT 1";
        $next_stmt = $pdo->prepare($next_sql);
        $next_stmt->execute([':id' => $id]);
        $next_post = $next_stmt->fetch();

        // 트랜잭션 커밋
        $pdo->commit();

        if (!$post) {
            // 게시물이 없을 경우
            echo "게시물을 찾을 수 없습니다.";
            exit();
        }
    } catch (PDOException $e) {
        // 오류 발생 시 롤백
        $pdo->rollBack();
        echo "게시물 조회 중 오류가 발생했습니다: " . htmlspecialchars($e->getMessage());
        exit();
    }
} else {
    // 잘못된 ID일 경우
    echo "잘못된 게시물 ID입니다.";
    exit();
}

// 좋아요/싫어요 카운트 조회
$reaction_sql = "SELECT reaction, COUNT(*) as count FROM post_reactions WHERE post_id = :post_id GROUP BY reaction";
$reaction_stmt = $pdo->prepare($reaction_sql);
$reaction_stmt->execute([':post_id' => $id]);
$reactions = $reaction_stmt->fetchAll();

// 좋아요/싫어요 수
$likes = 0;
$dislikes = 0;
foreach ($reactions as $reaction) {
    if ($reaction['reaction'] === 'like') {
        $likes = $reaction['count'];
    } elseif ($reaction['reaction'] === 'dislike') {
        $dislikes = $reaction['count'];
    }
}

// 댓글 조회 (계층적 구조)
function get_comments($pdo, $post_id, $parent_id = NULL, $level = 0)
{
    $sql = "SELECT c.id, c.user_id, u.username, c.content, c.created_at 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = :post_id AND ";
    if ($parent_id === NULL) {
        $sql .= "c.parent_id IS NULL";
    } else {
        $sql .= "c.parent_id = :parent_id";
    }
    $sql .= " ORDER BY c.created_at ASC";

    $stmt = $pdo->prepare($sql);
    if ($parent_id === NULL) {
        $stmt->execute([':post_id' => $post_id]);
    } else {
        $stmt->execute([':post_id' => $post_id, ':parent_id' => $parent_id]);
    }

    $comments = $stmt->fetchAll();

    foreach ($comments as $comment) {
        echo '<div class="comment">';
        echo '<div class="comment-header">';
        echo '<strong>' . htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8') . '</strong> ';
        echo '<span>' . htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') . '</span>';
        echo '</div>';
        echo '<div class="comment-content">' . nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) . '</div>';
        echo '<a href="#" class="reply-button" data-comment-id="' . $comment['id'] . '">답글 달기</a>';

        // 대댓글 재귀 호출
        echo '<div class="reply-form-container" id="reply-form-' . $comment['id'] . '">';
        if (isset($_SESSION['user_id'])) {
            echo '<form action="./api/add_comment.php" method="POST" class="reply_form">';
            echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post_id, ENT_QUOTES, 'UTF-8') . '">';
            echo '<input type="hidden" name="parent_id" value="' . htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8') . '">';
            echo '<textarea name="content" rows="3" placeholder="답글을 작성하세요" required></textarea>';
            echo '<button type="submit">답글 작성</button>';
            echo '</form>';
        } else {
            echo '<p>답글을 작성하려면 <a href="./login.php">로그인</a>하세요.</p>';
        }
        echo '</div>';

        // 대댓글 출력 (재귀)
        get_comments($pdo, $post_id, $comment['id'], $level + 1);
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?> - RISZE</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        /* board_view.php 전용 스타일 추가 */

        .view_post {
            background-color: var(--light-black);
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 800px;
            color: var(--white);
            margin: 40px auto;
        }

        .view_post h1 {
            font-size: 2em;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--gray);
            padding-bottom: 10px;
        }

        .post_info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            margin-bottom: 20px;
            color: var(--gray);
        }

        .post_content {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 30px;
            color: var(--white);
        }

        .post_reactions {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .post_reactions button {
            display: flex;
            align-items: center;
            gap: 5px;
            background-color: var(--black);
            color: var(--white);
            border: 1px solid var(--light-gray);
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .post_reactions button:hover {
            background-color: var(--black-hover);
        }

        .navigation_links {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .navigation_links a {
            color: var(--main);
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s;
        }

        .navigation_links a:hover {
            color: var(--main-hover);
        }

        .comments_section {
            margin-top: 40px;
            color: var(--white);
        }

        .comments_section h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--gray);
            padding-bottom: 10px;
        }

        .comment_form textarea,
        .reply_form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gray);
            border-radius: 8px;
            background-color: var(--black);
            color: var(--white);
            resize: vertical;
            margin-bottom: 10px;
        }

        .comment_form button,
        .reply_form button {
            background-color: var(--main);
            color: var(--black);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .comment_form button:hover,
        .reply_form button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        .comment {
            border-bottom: 1px solid var(--gray);
            padding: 15px 0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-header {
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .comment-content {
            font-size: 1em;
            margin-bottom: 10px;
        }

        .reply-button {
            font-size: 0.8em;
            color: var(--main);
            text-decoration: none;
            cursor: pointer;
        }

        .reply-button:hover {
            color: var(--main-hover);
        }

        .reply-form-container {
            margin-top: 10px;
            padding-left: 20px;
        }

        /* 수정 및 삭제 버튼 스타일 */
        .edit-delete-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .edit-delete-buttons a,
        .edit-delete-buttons form {
            display: inline;
        }

        .edit-button,
        .delete-button {
            background-color: var(--main);
            color: var(--black);
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: background-color 0.3s, color 0.3s;
        }

        .edit-button:hover,
        .delete-button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        .delete-button {
            background-color: var(--red);
            color: var(--white);
        }

        .delete-button:hover {
            background-color: var(--red-hover);
            color: var(--white-active);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 댓글 답글 버튼 클릭 시 답글 폼 표시
            document.querySelectorAll('.reply-button').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const commentId = this.getAttribute('data-comment-id');
                    const replyForm = document.getElementById('reply-form-' + commentId);
                    if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                        replyForm.style.display = 'block';
                    } else {
                        replyForm.style.display = 'none';
                    }
                });
            });

            // 삭제 버튼 클릭 시 확인 창 표시
            document.querySelectorAll('.delete-button').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    if (!confirm('정말로 이 게시물을 삭제하시겠습니까?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="view view_post">
            <div class="edit-delete-buttons">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $post['user_id']): ?>
                    <a href="./edit_post.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>" class="edit-button">수정</a>
                    <form action="./delete_post.php" method="POST" onsubmit="return confirm('정말로 이 게시물을 삭제하시겠습니까?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="delete-button">삭제</button>
                    </form>
                <?php endif; ?>
            </div>

            <h1><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="post_info">
                <div>
                    <span>작성자: <?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
                        <span> | 수정일: <?= htmlspecialchars($post['updated_at'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <span> | 작성일: <?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <span>조회수: <?= htmlspecialchars($post['view'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="post_content">
                <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
            <div class="post_reactions">
                <!-- 좋아요 버튼 -->
                <form action="./api/react_post.php" method="POST" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="reaction" value="like">
                    <button type="submit">
                        👍 좋아요
                        <span class="count"><?= htmlspecialchars($likes, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                </form>
                <!-- 싫어요 버튼 -->
                <form action="./api/react_post.php" method="POST" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="reaction" value="dislike">
                    <button type="submit">
                        👎 싫어요
                        <span class="count"><?= htmlspecialchars($dislikes, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                </form>
            </div>
            <div class="navigation_links">
                <?php if ($prev_post): ?>
                    <a href="./board_view.php?id=<?= htmlspecialchars($prev_post['id'], ENT_QUOTES, 'UTF-8') ?>" title="이전 게시물">&laquo; <?= htmlspecialchars($prev_post['title'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <?php if ($next_post): ?>
                    <a href="./board_view.php?id=<?= htmlspecialchars($next_post['id'], ENT_QUOTES, 'UTF-8') ?>" title="다음 게시물"><?= htmlspecialchars($next_post['title'], ENT_QUOTES, 'UTF-8') ?> &raquo;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
            <div class="comments_section">
                <h2>댓글</h2>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment_form">
                        <form action="./api/add_comment.php" method="POST">
                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <textarea name="content" rows="4" placeholder="댓글을 작성하세요" required></textarea>
                            <button type="submit">댓글 작성</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>댓글을 작성하려면 <a href="./login.php">로그인</a>하세요.</p>
                <?php endif; ?>

                <?php
                // 댓글 출력
                get_comments($pdo, $post['id']);
                ?>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
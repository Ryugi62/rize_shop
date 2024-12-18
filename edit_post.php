<?php
// edit_post.php

session_start();
require_once './config/db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// 게시물 ID 받기
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // 게시물 조회
        $select_sql = "SELECT * FROM posts WHERE id = :id";
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
    } catch (PDOException $e) {
        // 배포 시에는 일반적인 에러 메시지로 대체
        echo "게시물 조회 중 오류가 발생했습니다.";
        // 개발 환경에서는 아래 줄을 활성화하여 에러 메시지 확인 가능
        // echo "게시물 조회 중 오류가 발생했습니다: " . htmlspecialchars($e->getMessage());
        exit();
    }
} else {
    echo "잘못된 게시물 ID입니다.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>게시물 수정 - RISZE</title>
    <link rel="stylesheet" href="./style.css">
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .edit_view {
            margin-top: 60px;
            width: 100%;
            max-width: 800px;
            background-color: var(--light-black);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: var(--white);
            margin-left: auto;
            margin-right: auto;
        }

        .edit_view h2 {
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
            color: var(--white);
        }

        .edit_form {
            display: flex;
            flex-direction: column;
        }

        .edit_form label {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 18px;
        }

        .edit_form input,
        .edit_form select {
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: var(--black);
            color: var(--white);
            font-size: 16px;
        }

        .edit_form textarea {
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: var(--black);
            color: var(--white);
            font-size: 16px;
            resize: vertical;
            min-height: 300px;
        }

        .edit_form button {
            align-self: flex-end;
            padding: 12px 24px;
            background-color: var(--main);
            color: var(--black);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s, color 0.3s;
        }

        .edit_form button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        .edit_form button:active {
            background-color: var(--main-active);
            color: var(--black-active);
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="edit_view view">
            <h2>게시물 수정</h2>
            <form class="edit_form" action="./api/update_post.php" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">

                <label for="post_type">카테고리</label>
                <select id="post_type" name="post_type" required>
                    <option value="">선택하세요</option>
                    <option value="notice" <?= ($post['post_type'] === 'notice') ? 'selected' : '' ?>>공지사항</option>
                    <option value="review" <?= ($post['post_type'] === 'review') ? 'selected' : '' ?>>리뷰</option>
                    <option value="qna" <?= ($post['post_type'] === 'qna') ? 'selected' : '' ?>>Q & A</option>
                </select>

                <label for="title">제목</label>
                <input type="text" id="title" name="title" required placeholder="제목을 입력하세요" value="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>">

                <label for="content">내용</label>
                <textarea id="content" name="content" required placeholder="내용을 작성하세요"><?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>

                <button type="submit">수정 완료</button>
            </form>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <!-- CKEditor 5 초기화 스크립트 -->
    <script>
        ClassicEditor
            .create(document.querySelector('#content'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                        'insertTable', 'mediaEmbed', 'undo', 'redo'
                    ]
                },
                language: 'ko',
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells'
                    ]
                },
                licenseKey: '', // 무료 사용 시 비워두세요.
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</body>

</html>
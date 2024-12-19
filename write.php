<?php
session_start();
require_once './config/db.php';

// 유저 정보 조회 (role 확인)
$user_id = $_SESSION['user_id'] ?? null;
$user_role = 'user'; // 기본 user
if ($user_id) {
    $user_stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $user_stmt->execute(['id' => $user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['role'] === 'admin') {
        $user_role = 'admin';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>글쓰기 - RISZE</title>
    <link rel="stylesheet" href="./style.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .write_view {
            height: auto;
            width: 100%;
            color: var(--white);
            padding: 40px;
            margin-left: auto;
            margin-top: 60px;
            margin-right: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            background-color: var(--light-black);
        }

        .write_view h2 {
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
            color: var(--white);
        }

        .write_form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .write_form label {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 18px;
        }

        .write_form input,
        .write_form select {
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: var(--black);
            color: var(--white);
            font-size: 16px;
            width: 100%;
        }

        .write_form textarea {
            /* display: none; 삭제 */
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

        .write_form button {
            margin-top: 10px;
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

        .write_form button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        .write_form button:active {
            background-color: var(--main-active);
            color: var(--black-active);
        }

        .write_form textarea {
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: var(--black);
            color: var(--white);
            font-size: 16px;
            resize: vertical;
            min-height: 500px;
            /* 변경된 부분 */
        }

        .ck-editor__editable {
            min-height: 500px;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="write_view view">
            <h2>글쓰기</h2>
            <form class="write_form" action="/api/write_process.php" method="POST" id="writeForm">
                <label for="post_type">카테고리</label>
                <select id="post_type" name="post_type" required>
                    <option value="">선택하세요</option>
                    <?php if ($user_role === 'admin'): ?>
                        <option value="notice">공지사항</option>
                    <?php endif; ?>
                    <option value="review">리뷰</option>
                    <option value="qna">Q & A</option>
                </select>

                <label for="title">제목</label>
                <input type="text" id="title" name="title" required placeholder="제목을 입력하세요">

                <label for="content">내용</label>
                <textarea id="content" name="content" placeholder="내용을 작성하세요"></textarea>

                <button type="submit">등록</button>
            </form>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <script>
        let editorInstance;
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
            })
            .then(editor => {
                editorInstance = editor;
            })
            .catch(error => {
                console.error(error);
            });

        document.getElementById('writeForm').addEventListener('submit', function(event) {
            const data = editorInstance.getData().trim();
            const div = document.createElement("div");
            div.innerHTML = data;
            const text = div.textContent || div.innerText || "";
            if (text.trim() === "") {
                event.preventDefault();
                alert('내용을 작성하세요.');
                editorInstance.editing.view.focus();
            }
        });
    </script>
</body>

</html>
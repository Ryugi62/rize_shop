<?php
session_start();
require_once './config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_role = 'user';
if ($user_id) {
    $user_stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $user_stmt->execute(['id' => $user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['role'] === 'admin') {
        $user_role = 'admin';
    }
}

// post_type, product_id GET으로 받기
$post_type = $_GET['post_type'] ?? '';
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// post_type에 따른 접근 제한
if ($post_type === 'review') {
    if (!$user_id) {
        echo "<script>alert('로그인이 필요합니다.');history.back();</script>";
        exit();
    }
    // 구매 이력 체크: 해당 유저가 구매한 모든 상품 목록 불러오기
    $purchase_check = $pdo->prepare("
        SELECT DISTINCT p.id, p.product_name 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.user_id = :uid 
        AND o.status IN ('pending','processing','shipped','delivered')
        ORDER BY p.product_name ASC
    ");
    $purchase_check->execute(['uid' => $user_id]);
    $purchased_products = $purchase_check->fetchAll(PDO::FETCH_ASSOC);

    if (count($purchased_products) === 0) {
        echo "<script>alert('구매한 상품이 없습니다. 리뷰를 작성할 수 없습니다.');history.back();</script>";
        exit();
    }
} elseif ($post_type === 'qna') {
    // Q&A는 누구나 가능
    // 모든 상품 노출
    $products_stmt = $pdo->query("SELECT id, product_name FROM products ORDER BY product_name ASC");
    $all_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($post_type === 'notice') {
    // 관리자만 가능
    if ($user_role !== 'admin') {
        echo "<script>alert('공지사항 작성 권한이 없습니다.');history.back();</script>";
        exit();
    }
    // notice도 product_id를 선택하게 유지하려면 전체 상품 노출
    $products_stmt = $pdo->query("SELECT id, product_name FROM products ORDER BY product_name ASC");
    $all_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // 유효하지 않은 post_type
    echo "<script>alert('유효한 게시물 유형이 아닙니다.');history.back();</script>";
    exit();
}

$page_title = "글쓰기 - RIZZ";
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
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
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: var(--black);
            color: var(--white);
            font-size: 16px;
            resize: vertical;
            min-height: 500px;
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
            width: 100%;
        }

        .write_form button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        .write_form button:active {
            background-color: var(--main-active);
            color: var(--black-active);
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
                        <option value="notice" <?php if ($post_type === 'notice') echo 'selected'; ?>>공지사항</option>
                    <?php endif; ?>
                    <option value="review" <?php if ($post_type === 'review') echo 'selected'; ?>>리뷰</option>
                    <option value="qna" <?php if ($post_type === 'qna') echo 'selected'; ?>>Q & A</option>
                </select>

                <label for="product_id">상품 선택</label>
                <?php
                if ($post_type === 'review') {
                    // 리뷰: 구매한 상품만 표시
                    echo '<select id="product_id" name="product_id" required>';
                    echo '<option value="">상품 선택</option>';
                    foreach ($purchased_products as $pprd) {
                        echo '<option value="' . $pprd['id'] . '"' . ($pprd['id'] === $product_id ? 'selected' : '') . '>' . htmlspecialchars($pprd['product_name']) . '</option>';
                    }
                    echo '</select>';
                } elseif ($post_type === 'qna') {
                    // Q&A: 모든 상품 표시
                    echo '<select id="product_id" name="product_id" required>';
                    echo '<option value="">상품 선택</option>';
                    foreach ($all_products as $prd) {
                        echo '<option value="' . $prd['id'] . '"' . ($prd['id'] === $product_id ? 'selected' : '') . '>' . htmlspecialchars($prd['product_name']) . '</option>';
                    }
                    echo '</select>';
                } elseif ($post_type === 'notice') {
                    // notice: 관리자라면 모든 상품 표시 (또는 필요에 따라 변형 가능)
                    echo '<select id="product_id" name="product_id" required>';
                    echo '<option value="">상품 선택</option>';
                    foreach ($all_products as $prd) {
                        echo '<option value="' . $prd['id'] . '"' . ($prd['id'] === $product_id ? 'selected' : '') . '>' . htmlspecialchars($prd['product_name']) . '</option>';
                    }
                    echo '</select>';
                }
                ?>

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
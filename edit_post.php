<?php
// edit_post.php

session_start();
require_once './config/db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

        // post_type과 product_id 추출
        $post_type = $post['post_type'];
        $post_product_id = (int)$post['product_id'];

        // 유저 role 확인
        $user_stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
        $user_stmt->execute(['id' => $user_id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        $user_role = $user ? $user['role'] : 'user';

        if ($post_type === 'review') {
            // 리뷰 수정 시 구매한 상품만 표시
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
                echo "<script>alert('구매한 상품이 없습니다. 이 리뷰를 수정할 수 없습니다.');history.back();</script>";
                exit();
            }

            // 수정하는 게시물의 product_id가 현재 구매한 상품 리스트에 있는지 체크
            $valid_product = false;
            foreach ($purchased_products as $pprd) {
                if ($pprd['id'] === $post_product_id) {
                    $valid_product = true;
                    break;
                }
            }

            if (!$valid_product) {
                echo "<script>alert('이 리뷰에 해당하는 상품은 더 이상 구매 기록이 없습니다. 수정 불가.');history.back();</script>";
                exit();
            }
        } elseif ($post_type === 'qna') {
            // Q&A는 누구나 작성, 모든 상품 표시
            $products_stmt = $pdo->query("SELECT id, product_name FROM products ORDER BY product_name ASC");
            $all_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

            // 게시물의 product_id가 실제 상품 목록에 있는지 체크 (안해도 되나 안정성 위해)
            $valid_product = false;
            foreach ($all_products as $prd) {
                if ((int)$prd['id'] === $post_product_id) {
                    $valid_product = true;
                    break;
                }
            }
            if (!$valid_product) {
                echo "<script>alert('해당 Q&A 게시물의 상품을 찾을 수 없습니다.');history.back();</script>";
                exit();
            }
        } elseif ($post_type === 'notice') {
            // notice는 admin만
            if ($user_role !== 'admin') {
                echo "권한이 없습니다.";
                exit();
            }
            // notice도 모든 상품 표시(필요시)
            $products_stmt = $pdo->query("SELECT id, product_name FROM products ORDER BY product_name ASC");
            $all_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

            // product_id 유효성 체크
            $valid_product = false;
            foreach ($all_products as $prd) {
                if ((int)$prd['id'] === $post_product_id) {
                    $valid_product = true;
                    break;
                }
            }
            if (!$valid_product) {
                echo "<script>alert('해당 공지사항 게시물의 상품을 찾을 수 없습니다.');history.back();</script>";
                exit();
            }
        } else {
            // 유효하지 않은 post_type
            echo "<script>alert('유효한 게시물 유형이 아닙니다.');history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "게시물 조회 중 오류가 발생했습니다.";
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
            background-color: var(--light-black);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: var(--white);
            margin-left: auto;
            margin-right: auto;
            height: auto;
        }

        .edit_view h2 {
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
            color: var(--white);
        }

        .edit_form {
            width: 100%;
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
            margin-top: 10px;
            width: 100%;
        }

        .edit_form button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        .edit_form button:active {
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
        <div class="edit_view view">
            <h2>게시물 수정</h2>
            <form class="edit_form" action="./api/update_post.php" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">

                <label for="post_type">카테고리</label>
                <select id="post_type" name="post_type" required>
                    <option value="">선택하세요</option>
                    <?php if ($user_role === 'admin'): ?>
                        <option value="notice" <?= ($post_type === 'notice') ? 'selected' : '' ?>>공지사항</option>
                    <?php endif; ?>
                    <option value="review" <?= ($post_type === 'review') ? 'selected' : '' ?>>리뷰</option>
                    <option value="qna" <?= ($post_type === 'qna') ? 'selected' : '' ?>>Q & A</option>
                </select>

                <?php
                // product_id select box
                echo '<label for="product_id">상품 선택</label>';
                if ($post_type === 'review') {
                    // 리뷰: 구매한 상품(purchased_products)에서 product_id 선택
                    echo '<select id="product_id" name="product_id" required>';
                    echo '<option value="">상품 선택</option>';
                    foreach ($purchased_products as $pprd) {
                        echo '<option value="' . $pprd['id'] . '"' . ($pprd['id'] === $post_product_id ? 'selected' : '') . '>' . htmlspecialchars($pprd['product_name']) . '</option>';
                    }
                    echo '</select>';
                } elseif ($post_type === 'qna') {
                    // QnA: all_products에서 product_id 선택
                    echo '<select id="product_id" name="product_id" required>';
                    echo '<option value="">상품 선택</option>';
                    foreach ($all_products as $prd) {
                        echo '<option value="' . $prd['id'] . '"' . ($prd['id'] === $post_product_id ? 'selected' : '') . '>' . htmlspecialchars($prd['product_name']) . '</option>';
                    }
                    echo '</select>';
                } elseif ($post_type === 'notice') {
                    // notice: all_products에서 product_id 선택 (admin)
                    echo '<select id="product_id" name="product_id" required>';
                    echo '<option value="">상품 선택</option>';
                    foreach ($all_products as $prd) {
                        echo '<option value="' . $prd['id'] . '"' . ($prd['id'] === $post_product_id ? 'selected' : '') . '>' . htmlspecialchars($prd['product_name']) . '</option>';
                    }
                    echo '</select>';
                }
                ?>

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
                licenseKey: '',
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</body>

</html>
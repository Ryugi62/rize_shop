<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

// 상품 추가 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price_str = trim($_POST['price']);
    $description = $_POST['description']; // CKEditor로 입력한 HTML
    $stock_str = trim($_POST['stock']);

    // 할인금액 추가 부분
    $discount_str = trim($_POST['discount_amount']);

    $price = (int)preg_replace('/[^0-9]/', '', $price_str);
    $stock = (int)preg_replace('/[^0-9]/', '', $stock_str);
    $discount_amount = (int)preg_replace('/[^0-9]/', '', $discount_str);

    $upload_dir = './assets/images/';
    $default_image = './assets/images/default.png';
    $image_path = $default_image;

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image_file']['tmp_name'];
        $original_name = basename($_FILES['image_file']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($ext, $allowed_ext)) {
            $new_name = uniqid('product_') . '.' . $ext;
            $target_path = $upload_dir . $new_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $image_path = $target_path;
            } else {
                $error = "이미지 업로드 중 오류가 발생했습니다.";
            }
        } else {
            $error = "허용되지 않은 이미지 형식입니다. (jpg, jpeg, png, gif만 가능)";
        }
    }

    if (empty($error)) {
        if ($name === '' || $price === 0 || $stock < 0) {
            $error = "모든 필드를 올바르게 입력해주세요 (가격은 숫자, 재고는 0 이상의 숫자).";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO products (product_name, product_image, price, description, rating, reviews, stock, discount_amount) 
                VALUES (:product_name, :product_image, :price, :description, 0, 0, :stock, :discount_amount)
            ");
            $success_insert = $stmt->execute([
                'product_name' => $name,
                'product_image' => $image_path,
                'price' => $price,
                'description' => $description,
                'stock' => $stock,
                'discount_amount' => $discount_amount // 할인금액 추가
            ]);

            if ($success_insert) {
                $success = "상품이 성공적으로 추가되었습니다.";
            } else {
                $error = "상품 추가 중 오류가 발생하였습니다.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>상품 추가 - RIZZ 쇼핑몰 관리자</title>
    <style>
        .add_product h3 {
            font-size: 24px;
            margin-bottom: 24px;
            text-align: center;
            color: var(--white);
            border-bottom: 1px solid var(--gray);
            padding-bottom: 12px;
        }

        .add_product p {
            margin: 10px 0;
        }

        .form_group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }

        .form_group label {
            margin-bottom: 8px;
            color: var(--white);
            font-weight: bold;
        }

        .form_group input[type="text"],
        .form_group input[type="number"],
        .form_group textarea,
        .form_group input[type="file"] {
            background-color: var(--black);
            color: var(--white);
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            padding: 0.5rem;
            font-size: 16px;
        }

        .form_group textarea {
            min-height: 300px;
        }

        button[name="add_product"] {
            margin-top: 20px;
            width: 100%;
            background-color: var(--main);
            color: var(--black);
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[name="add_product"]:hover {
            background-color: var(--main-hover);
        }

        .error_msg {
            color: var(--red);
        }

        .success_msg {
            color: var(--green);
        }
    </style>
</head>

<body>
    <div class="add_product">
        <h3>상품 추가</h3>

        <?php if (isset($error)): ?>
            <p class="error_msg"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="success_msg"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form action="admin.php?mode=add_product" method="POST" enctype="multipart/form-data">
            <div class="form_group">
                <label for="name">상품 이름:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form_group">
                <label for="price">가격 (숫자만 입력):</label>
                <input type="text" id="price" name="price" required>
            </div>
            <div class="form_group">
                <label for="stock">재고 수량:</label>
                <input type="number" id="stock" name="stock" required min="0" value="0">
            </div>
            <div class="form_group">
                <label for="discount_amount">할인 금액 (0이면 할인 없음):</label>
                <input type="number" id="discount_amount" name="discount_amount" required min="0" value="0">
            </div>
            <div class="form_group">
                <label for="image_file">상품 이미지 첨부:</label>
                <input type="file" id="image_file" name="image_file" accept="image/*">
            </div>
            <div class="form_group">
                <label for="description">상품 설명:</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            <button type="submit" name="add_product">상품 추가</button>
        </form>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .then(editor => {})
            .catch(error => {
                console.error(error);
            });
    </script>
</body>

</html>
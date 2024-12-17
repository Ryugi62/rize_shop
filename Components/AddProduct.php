<?php
// AddProduct.php

// session_start(); // 제거

// 더미 데이터 초기화 (상품 관리와 공유)
if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = array(
        array('id' => 1, 'image' => './assets/images/hoodie1.png', 'name' => '더미 후디1', 'price' => '₩50,000', 'description' => '<p>이것은 더미 후디1의 설명입니다.</p>'),
        array('id' => 2, 'image' => './assets/images/hoodie2.png', 'name' => '더미 후디2', 'price' => '₩60,000', 'description' => '<p>이것은 더미 후디2의 설명입니다.</p>'),
        array('id' => 3, 'image' => './assets/images/hoodie3.png', 'name' => '더미 후디3', 'price' => '₩55,000', 'description' => '<p>이것은 더미 후디3의 설명입니다.</p>'),
    );
}

// 상품 추가 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // 입력값 검증 및 정리
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $image = trim($_POST['image']) !== '' ? trim($_POST['image']) : './assets/images/default.png';
    $description = $_POST['description']; // CKEditor의 HTML을 저장

    if ($name === '' || $price === '') {
        $error = "모든 필드를 입력해주세요.";
    } else {
        $new_id = count($_SESSION['products']) > 0 ? end($_SESSION['products'])['id'] + 1 : 1;
        $new_product = array(
            'id' => $new_id,
            'image' => htmlspecialchars($image, ENT_QUOTES, 'UTF-8'),
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'price' => htmlspecialchars($price, ENT_QUOTES, 'UTF-8'),
            'description' => $description // 보안을 위해 추후 필터링 필요
        );

        $_SESSION['products'][] = $new_product;
        $success = "상품이 성공적으로 추가되었습니다.";
    }
}
?>

<div class="add_product">
    <h3>상품 추가</h3>

    <?php if (isset($error)): ?>
        <p style="color: var(--red);"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p style="color: var(--green);"><?php echo $success; ?></p>
    <?php endif; ?>

    <!-- 상품 추가 폼 -->
    <form action="admin.php?mode=add_product" method="POST">
        <div class="form_group">
            <label for="name">상품 이름:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form_group">
            <label for="price">가격:</label>
            <input type="text" id="price" name="price" required>
        </div>
        <div class="form_group">
            <label for="image">이미지 URL:</label>
            <input type="text" id="image" name="image" placeholder="예: ./assets/images/new_image.png">
        </div>
        <div class="form_group">
            <label for="description">상품 설명:</label>
            <textarea id="description" name="description" rows="5" required></textarea>
            <script src="https://cdn.ckeditor.com/4.20.2/standard/ckeditor.js"></script>
            <script>
                CKEDITOR.replace('description');
            </script>
        </div>
        <button type="submit" name="add_product">상품 추가</button>
    </form>
</div>
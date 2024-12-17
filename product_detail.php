<?php
session_start();

// 상품 데이터 설정 (rating, reviews, stock 추가)
if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = array(
        array(
            'id' => 1,
            'image' => './assets/images/hoodie1.png',
            'name' => '데일리 특양면 기모 후드티',
            'price' => '₩50,000',
            'rating' => '4.8',
            'reviews' => 125,
            'stock' => 10
        ),
        array(
            'id' => 2,
            'image' => './assets/images/hoodie2.png',
            'name' => '남녀공용 오버핏 후드티',
            'price' => '₩60,000',
            'rating' => '4.6',
            'reviews' => 96,
            'stock' => 15
        ),
        array(
            'id' => 3,
            'image' => './assets/images/hoodie3.png',
            'name' => '클래식 후디',
            'price' => '₩70,000',
            'rating' => '4.9',
            'reviews' => 200,
            'stock' => 5
        )
    );
}

// GET 요청으로 상품 ID 받기
if (!isset($_GET['id'])) {
    header("Location: index.php?error=상품_ID가_지정되지_않았습니다.");
    exit;
}

$product_id = intval($_GET['id']);
$product = null;

// 상품 검색
foreach ($_SESSION['products'] as $p) {
    if ($p['id'] === $product_id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header("Location: index.php?error=상품을_찾을_수_없습니다.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - RIZZ 쇼핑몰</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        body {
            color: var(--white);
        }

        .product-container {
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            margin: auto;
            padding: 16px;
        }

        .product-header {
            display: flex;
            gap: 40px;
        }

        .product-header img {
            max-width: 400px;
            border-radius: 8px;
        }

        .product-info h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .product-info .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--main);
            margin: 10px 0;
        }

        .stock {
            margin: 10px 0;
            color: var(--red);
        }

        .rating {
            font-size: 1rem;
            margin: 5px 0;
        }

        .review-section {
            margin-top: 40px;
        }

        .review {
            background: var(--light-black);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .related-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 40px;
        }

        .related-product-item {
            text-align: center;
            cursor: pointer;
        }

        .related-product-item img {
            width: 100%;
            max-width: 200px;
            border-radius: 8px;
        }

        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: var(--main);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        footer {
            margin-top: 40px;
            text-align: center;
            color: var(--gray);
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <div class="product-container">
        <div class="product-header">
            <!-- 상품 이미지 -->
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">

            <!-- 상품 정보 -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="price"><?php echo htmlspecialchars($product['price']); ?></p>
                <p class="rating">평점: ★ <?php echo isset($product['rating']) ? $product['rating'] : 'N/A'; ?> (<?php echo isset($product['reviews']) ? $product['reviews'] : 0; ?>개 리뷰)</p>
                <p class="stock">남은 재고: <?php echo isset($product['stock']) ? $product['stock'] : 0; ?>개</p>
                <p>이 상품은 고품질 소재를 사용하여 제작되었으며 편안한 착용감과 세련된 디자인을 자랑합니다.</p>
            </div>
        </div>

        <!-- 리뷰 섹션 -->
        <div class="review-section">
            <h2>고객 리뷰</h2>
            <div class="review">
                <p><strong>홍길동:</strong> 배송이 정말 빠르고 옷도 너무 만족스러워요!</p>
            </div>
            <div class="review">
                <p><strong>김영희:</strong> 색상이 예쁘고 사이즈도 잘 맞습니다. 추천해요!</p>
            </div>
        </div>

        <!-- 관련 상품 섹션 -->
        <div class="related-products">
            <h2>관련 상품</h2>
            <div class="related-product-item" onclick="location.href='product_detail.php?id=1'">
                <img src="./assets/images/hoodie1.png" alt="후드티1">
                <p>데일리 특양면 기모 후드티</p>
                <p>₩50,000</p>
            </div>
            <div class="related-product-item" onclick="location.href='product_detail.php?id=2'">
                <img src="./assets/images/hoodie2.png" alt="후드티2">
                <p>남녀공용 오버핏 후드티</p>
                <p>₩60,000</p>
            </div>
        </div>

        <a href="index.php" class="back-link">← 상품 목록으로 돌아가기</a>
    </div>

    <!-- Footer -->
    <footer>
        <p>RIZZ | Rise to Your Style</p>
        <p>대표: 홍길동 | 사업자등록번호: 123-45-67890 | 통신판매업 신고번호: 제2024-창원-0001호</p>
        <p>주소: 경상남도 창원시 대학로 123번길 45 | 고객센터: 080-123-4567 | 이메일: support@rizz.com</p>
        <p>© 2024 RIZZ. All rights reserved.</p>
    </footer>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
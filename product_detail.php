<?php
session_start();
include('./config/db.php');

// GET 요청으로 상품 ID 받기
if (!isset($_GET['id'])) {
    header("Location: index.php?error=상품_ID가_지정되지_않았습니다.");
    exit;
}

$product_id = intval($_GET['id']);

// DB에서 상품 정보 조회
$stmt = $pdo->prepare("SELECT id, product_name, product_image, price, description, rating, reviews, stock FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php?error=상품을_찾을_수_없습니다.");
    exit;
}

// 관련 상품 조회 (임의로 최근 등록한 상품 중 상위 2개)
$related_stmt = $pdo->query("SELECT id, product_name, product_image, price FROM products ORDER BY created_at DESC LIMIT 2");
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// 리뷰 조회 (posts 테이블에서 product_id와 post_type='review' 사용)
$review_stmt = $pdo->prepare("
    SELECT p.title, p.content, u.username, p.created_at 
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.product_id = :product_id AND p.post_type = 'review'
    ORDER BY p.created_at DESC
");
$review_stmt->execute(['product_id' => $product_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = $product['product_name'] . " - RIZZ 쇼핑몰";
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="./style.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: sans-serif;
            background-color: #000;
            color: #fff;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 16px;
        }

        .product-header {
            display: flex;
            gap: 40px;
            margin-bottom: 24px;
        }

        .product-header .product-image {
            flex: 0 0 400px;
            background-color: #222;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            overflow: hidden;
        }

        .product-header .product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-header .product-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .product-header h1 {
            font-size: 2rem;
            margin: 0;
        }

        .product-header .price {
            font-size: 1.5rem;
            color: #2ef3e1;
            margin: 0;
        }

        .product-header .action-button {
            margin-top: 16px;
            background-color: #2ef3e1;
            color: #000;
            border: none;
            padding: 12px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
        }

        .product-header .action-button:hover {
            background-color: #24c7b8;
        }

        .tab-bar {
            display: flex;
            gap: 16px;
            border-bottom: 1px solid #444;
            margin-bottom: 24px;
        }

        .tab-bar a {
            color: #fff;
            text-decoration: none;
            padding: 8px 16px;
            font-weight: bold;
        }

        .tab-bar a:hover {
            background-color: #333;
            border-radius: 4px;
        }

        .main-content,
        .additional-section,
        .related-section,
        .qna-section {
            background-color: #111;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        /* 제목 스타일 */
        .main-content h2,
        .additional-section h2,
        .qna-section h2,
        .related-section h2 {
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        figure.image {
            display: inline-block;
            margin: 10px;
        }

        figure.image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .back-link {
            display: inline-block;
            color: #2ef3e1;
            text-decoration: none;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        footer {
            margin-top: 40px;
            text-align: center;
            color: #808080;
        }

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .related-product-item {
            text-align: center;
            cursor: pointer;
            background: #222;
            padding: 16px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .related-product-item:hover {
            background-color: #333;
        }

        .related-product-item img {
            width: 100%;
            max-width: 200px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .review {
            background: #222;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .review p {
            margin: 0;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>



    <div class="container">
        <a href="index.php" class="back-link">← 상품 목록으로 돌아가기</a>
        <div class="product-header">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            </div>
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p class="price"><?php echo htmlspecialchars(number_format($product['price'])); ?>원</p>
                <p>평점: ★<?php echo isset($product['rating']) ? htmlspecialchars($product['rating']) : '0'; ?> (<?php echo isset($product['reviews']) ? htmlspecialchars($product['reviews']) : 0; ?>개 리뷰)</p>
                <p>남은 재고: <?php echo isset($product['stock']) ? htmlspecialchars($product['stock']) : 0; ?>개</p>
                <button class="action-button">구매하기</button>
            </div>
        </div>

        <!-- 탭 -->
        <div class="tab-bar">
            <a href="#detail-section">상세정보</a>
            <a href="#review-section">리뷰</a>
            <a href="#qna-section">Q&A</a>
            <a href="#related-section">관련상품</a>
        </div>

        <!-- 상세정보 영역 -->
        <div id="detail-section" class="main-content">
            <h2>상품 상세정보</h2>
            <?php echo $product['description']; ?>
        </div>

        <!-- 리뷰 영역 (DB에서 가져온 리뷰) -->
        <div id="review-section" class="additional-section">
            <h2>고객 리뷰</h2>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $rv): ?>
                    <div class="review">
                        <p><strong><?php echo htmlspecialchars($rv['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($rv['content'])); ?></p>
                        <p style="font-size:0.8rem; color:#aaa;"><?php echo htmlspecialchars($rv['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>아직 등록된 리뷰가 없습니다.</p>
            <?php endif; ?>
        </div>

        <!-- Q&A 영역 (예제) -->
        <div id="qna-section" class="qna-section">
            <h2>Q&A</h2>
            <p>등록된 Q&A가 없습니다.</p>
        </div>

        <!-- 관련 상품 영역 -->
        <div id="related-section" class="related-section">
            <h2>관련 상품</h2>
            <div class="related-products-grid">
                <?php foreach ($related_products as $rp): ?>
                    <div class="related-product-item" onclick="location.href='product_detail.php?id=<?php echo $rp['id']; ?>'">
                        <img src="<?php echo htmlspecialchars($rp['product_image']); ?>" alt="<?php echo htmlspecialchars($rp['product_name']); ?>">
                        <p><?php echo htmlspecialchars($rp['product_name']); ?></p>
                        <p><?php echo htmlspecialchars(number_format($rp['price'])); ?>원</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <a href="index.php" class="back-link">← 상품 목록으로 돌아가기</a>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>q
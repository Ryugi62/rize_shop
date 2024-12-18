<?php
session_start();
include('./config/db.php');

// GET 요청으로 상품 ID 받기
if (!isset($_GET['id'])) {
    header("Location: index.php?error=상품_ID가_지정되지_않았습니다.");
    exit;
}

$product_id = intval($_GET['id']);

// 상품 정보 조회
$stmt = $pdo->prepare("SELECT id, product_name, product_image, price, description, rating, reviews, stock FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php?error=상품을_찾을_수_없습니다.");
    exit;
}

// 관련 상품 조회
$related_stmt = $pdo->query("SELECT id, product_name, product_image, price FROM products ORDER BY created_at DESC LIMIT 2");
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// 리뷰 조회
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

// 로그인 여부
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// 찜 상태 확인
$favorited = false;
if ($isLoggedIn) {
    $fav_check = $pdo->prepare("SELECT id FROM favorites WHERE user_id=:uid AND product_id=:pid");
    $fav_check->execute(['uid' => $user_id, 'pid' => $product_id]);
    $favorited = ($fav_check->rowCount() > 0);
}

// 장바구니 상태 확인
$in_cart = false;
$cart_qty = 1;
if ($isLoggedIn) {
    $cart_check = $pdo->prepare("SELECT quantity FROM cart WHERE user_id=:uid AND product_id=:pid");
    $cart_check->execute(['uid' => $user_id, 'pid' => $product_id]);
    if ($cart_check->rowCount() > 0) {
        $cart_row = $cart_check->fetch(PDO::FETCH_ASSOC);
        $in_cart = true;
        $cart_qty = $cart_row['quantity'];
    }
}

// 별점 표시 로직 (최대 5점)
$rating = floatval($product['rating'] ?? 0);
$full_stars = floor($rating);
$empty_stars = 5 - $full_stars;
$stars_html = str_repeat("★", $full_stars) . str_repeat("☆", $empty_stars);
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="./style.css">
    <style>
        :root {
            --main: #2ef3e1;
            --main-hover: #26d4c3;
            --white: #ffffff;
            --black: #000000;
            --gray: #808080;
            --light-gray: #d0d0d0;
            --light-black: #202020;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
            background: #000;
            color: #fff;
        }

        body {
            background: linear-gradient(to bottom, #000 0%, #111 100%);
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 16px;
        }

        a {
            color: var(--main);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
        }

        .product-header {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            margin-bottom: 24px;
        }

        .product-image {
            flex: 1 1 400px;
            background: #222;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-info {
            flex: 1 1 400px;
            background: #222;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .product-info h1 {
            font-size: 2rem;
            margin: 0;
            font-weight: bold;
        }

        .price {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--main);
            margin: 8px 0;
        }

        .product-stats {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 1rem;
            color: #ccc;
        }

        .product-stats p {
            margin: 0;
        }

        .action-button-group {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .action-button-group form button,
        .favorite-button {
            background-color: var(--main);
            color: #000;
            border: none;
            padding: 10px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .action-button-group form button:hover,
        .favorite-button:hover {
            background-color: var(--main-hover);
        }

        .favorited,
        .in-cart {
            background: #444;
            color: #fff !important;
        }

        .favorited:hover,
        .in-cart:hover {
            background: #555;
        }

        .heart-icon {
            color: #f66;
        }

        .cart-icon {
            color: #2ef3e1;
        }

        .tab-bar {
            display: flex;
            gap: 16px;
            border-bottom: 1px solid #444;
            margin-bottom: 24px;
        }

        .tab-bar a {
            color: #fff;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 4px 4px 0 0;
        }

        .tab-bar a:hover {
            background: #333;
        }

        .main-content,
        .additional-section,
        .related-section,
        .qna-section {
            background: #111;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        }

        .main-content h2,
        .additional-section h2,
        .qna-section h2,
        .related-section h2 {
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 8px;
            margin-bottom: 16px;
            font-size: 1.5rem;
            font-weight: bold;
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

        .review {
            background: #222;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .review p {
            margin: 0;
        }

        .review p strong {
            color: var(--main);
        }

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .related-product-item {
            text-align: center;
            background: #222;
            padding: 16px;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.2s;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        }

        .related-product-item:hover {
            background: #333;
            transform: scale(1.05);
        }

        .related-product-item img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .no-data-message {
            text-align: center;
            font-size: 18px;
            color: #aaa;
            background-color: #222;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
        }

        @media(max-width:768px) {
            .product-header {
                flex-direction: column;
            }
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
                <p class="price"><?php echo number_format($product['price']); ?>원</p>
                <div class="product-stats">
                    <p>평점: <?php echo $stars_html; ?> (<?php echo htmlspecialchars($product['reviews'] ?? 0); ?>개 리뷰)</p>
                    <p>남은 재고: <?php echo htmlspecialchars($product['stock'] ?? 0); ?>개</p>
                </div>
                <?php if ($isLoggedIn): ?>
                    <div class="action-button-group">
                        <button type="button" class="favorite-button <?php echo $favorited ? 'favorited' : ''; ?>" data-product-id="<?php echo $product_id; ?>">
                            <span class="heart-icon"><?php echo $favorited ? '♥' : '♡'; ?></span> <?php echo $favorited ? '찜완료' : '찜하기'; ?>
                        </button>

                        <form action="add_to_cart.php" method="post" style="display:flex; gap:8px;">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="number" name="quantity" value="<?php echo $cart_qty; ?>" min="1" max="<?php echo $product['stock']; ?>" style="width:60px;">
                            <?php if ($in_cart): ?>
                                <button type="submit" class="in-cart"><span class="cart-icon">🛒</span> 장바구니 담김</button>
                            <?php else: ?>
                                <button type="submit"><span class="cart-icon">🛒</span> 장바구니 담기</button>
                            <?php endif; ?>
                        </form>

                        <!-- 바로 구매하기 폼 추가 -->
                        <form action="checkout.php" method="post">
                            <input type="hidden" name="action" value="buy_direct">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit">바로 구매하기</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p style="color:#f88;">로그인 후 이용 가능합니다.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-bar">
            <a href="#detail-section">상세정보</a>
            <a href="#review-section">리뷰</a>
            <a href="#qna-section">Q&A</a>
            <a href="#related-section">관련상품</a>
        </div>

        <div id="detail-section" class="main-content">
            <h2>상품 상세정보</h2>
            <?php echo $product['description']; ?>
        </div>

        <div id="review-section" class="additional-section">
            <h2>고객 리뷰</h2>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $rv): ?>
                    <div class="review">
                        <p><strong><?php echo htmlspecialchars($rv['username']); ?></strong>: <?php echo nl2br(htmlspecialchars($rv['content'])); ?></p>
                        <p style="font-size:0.8rem; color:#aaa;"><?php echo htmlspecialchars($rv['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data-message">아직 등록된 리뷰가 없습니다.</div>
            <?php endif; ?>
        </div>

        <div id="qna-section" class="qna-section">
            <h2>Q&A</h2>
            <div class="no-data-message">등록된 Q&A가 없습니다.</div>
        </div>

        <div id="related-section" class="related-section">
            <h2>관련 상품</h2>
            <?php if (count($related_products) > 0): ?>
                <div class="related-products-grid">
                    <?php foreach ($related_products as $rp): ?>
                        <div class="related-product-item" onclick="location.href='product_detail.php?id=<?php echo $rp['id']; ?>'">
                            <img src="<?php echo htmlspecialchars($rp['product_image']); ?>" alt="<?php echo htmlspecialchars($rp['product_name']); ?>">
                            <p><?php echo htmlspecialchars($rp['product_name']); ?></p>
                            <p><?php echo number_format($rp['price']); ?>원</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data-message">관련 상품이 없습니다.</div>
            <?php endif; ?>
        </div>

        <a href="index.php" class="back-link">← 상품 목록으로 돌아가기</a>
    </div>

    <?php include("./Components/FooterComponents.php"); ?>

    <?php if ($isLoggedIn): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const favButton = document.querySelector('.favorite-button');
                if (!favButton) return;

                favButton.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    fetch('/api/toggle_favorites.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'product_id=' + productId
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'ok') {
                                if (data.favorited) {
                                    favButton.classList.add('favorited');
                                    favButton.innerHTML = '<span class="heart-icon">♥</span> 찜완료';
                                } else {
                                    favButton.classList.remove('favorited');
                                    favButton.innerHTML = '<span class="heart-icon">♡</span> 찜하기';
                                }
                            } else {
                                alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
                            }
                        })
                        .catch(e => {
                            console.error(e);
                            alert('요청 중 문제가 발생했습니다.');
                        });
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>
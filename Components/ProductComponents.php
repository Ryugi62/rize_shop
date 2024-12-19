<!-- Components/ProductComponents.php -->
<?php
// 상품 정보가 배열로 전달되었는지 확인
if (isset($product) && is_array($product)) {
    $id = htmlspecialchars($product['id'] ?? '');
    $image = htmlspecialchars($product['image'] ?? './assets/images/default_product.png'); // 기본 이미지 설정
    $name = htmlspecialchars($product['name'] ?? '알 수 없는 상품');
    $original_price = $product['price'] ?? 0;
    $discount_amount = $product['discount_amount'] ?? 0;
    $final_price_value = $product['final_price'] ?? $original_price; // final_price가 없으면 원가 그대로

    // 가격 형식 지정
    $original_price_formatted = number_format((float)$original_price) . '원';
    $final_price_formatted = number_format((float)$final_price_value) . '원';

    // 할인 여부에 따라 가격 표출 방식 결정
    if ($discount_amount > 0) {
        // 할인 적용
        $price_html = '<p class="product_price">'
            . '<span style="text-decoration: line-through; color:#888;">' . $original_price_formatted . '</span> '
            . '→ <span style="color:var(--main); font-weight:bold;">' . $final_price_formatted . '</span>'
            . '</p>';
    } else {
        // 할인 없음
        $price_html = '<p class="product_price">' . $original_price_formatted . '</p>';
    }
} else {
    // 기본값 설정
    $id = '';
    $image = './assets/images/default_product.png';
    $name = '알 수 없는 상품';
    $price_html = '<p class="product_price">0원</p>';
}

// 모드 확인
$mode = $mode ?? ''; // 기본값은 빈 문자열
?>

<div class="product_component" onclick="location.href='product_detail.php?id=<?= $id ?>'">
    <img src="<?= $image; ?>" alt="<?= $name; ?> 이미지" class="product_image">
    <h3 class="product_name"><?= $name; ?></h3>
    <?= $price_html; ?>
    <?php if ($mode === "view"): ?>
        <div class="product_button">
            <button class="product_like_button" onclick="event.stopPropagation(); likeProduct(<?= $id ?>)">찜하기</button>
            <button class="product_cart_button" onclick="event.stopPropagation(); addToCart(<?= $id ?>)">장바구니</button>
        </div>
        <button class="product_buy_button white_button" onclick="event.stopPropagation(); buyProduct(<?= $id ?>)">구매하기</button>
    <?php endif; ?>
</div>

<script>
    // 찜하기
    function likeProduct(productId) {
        alert('찜하기 기능은 아직 구현되지 않았습니다. 상품 ID: ' + productId);
    }

    // 장바구니
    function addToCart(productId) {
        alert('장바구니 기능은 아직 구현되지 않았습니다. 상품 ID: ' + productId);
    }

    // 구매하기
    function buyProduct(productId) {
        alert('구매하기 기능은 아직 구현되지 않았습니다. 상품 ID: ' + productId);
    }
</script>

<style>
    .product_component {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        transition: transform 0.3s, box-shadow 0.3s;
        background-color: var(--light-black);
        color: var(--white);
    }

    .product_component:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .product_image {
        width: 100%;
        height: 200px;
        border-radius: 8px;
        object-fit: contain;
        margin-bottom: 16px;
    }

    .product_name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
        text-align: center;
    }

    .product_price {
        font-size: 16px;
        color: var(--gray);
        text-align: center;
    }

    .product_button {
        display: flex;
        justify-content: space-between;
        margin-top: 16px;
        gap: 8px;
    }

    .product_like_button,
    .product_cart_button,
    .product_buy_button {
        flex: 1;
        padding: 10px 12px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
    }

    .product_like_button:hover {
        background-color: var(--main);
        color: var(--black);
    }

    .product_cart_button:hover {
        background-color: var(--main-hover);
        color: var(--black);
    }

    .product_buy_button {
        background-color: var(--main);
        color: var(--black);
        margin-top: 8px;
    }

    .product_buy_button:hover {
        background-color: var(--main-hover);
    }
</style>
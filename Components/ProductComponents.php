<!-- Components/ProductComponents.php -->
<?php
// $mode라는 변수가 선언되었는지 확인
if (isset($mode)) {
    $mode = $mode;
} else {
    $mode = "";
}

// 상품 정보가 배열로 전달되었는지 확인
if (isset($product)) {
    $image = htmlspecialchars($product['image']);
    $name = htmlspecialchars($product['name']);
    $price = htmlspecialchars($product['price']);
} else {
    // 기본값 설정
    $image = '';
    $name = '';
    $price = '';
}
?>

<div class="product_component product">
    <img src="<?php echo $image; ?>" alt="<?php echo $name; ?> 이미지" class="product_image">
    <h3 class="product_name"><?php echo $name; ?></h3>
    <p class="product_price"><?php echo $price; ?></p>
    <?php
    if ($mode === "view") {
        echo '<div class="product_button">';
        echo '<button class="product_like_button">찜하기</button>';
        echo '<button class="product_cart_button">장바구니</button>';
        echo '</div>';
        echo '<button class="product_buy_button white_button">구매하기</button>';
    }
    ?>

</div>


<style>
    .product_component {
        cursor: pointer;
        display: flex;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        flex-direction: column;
        justify-content: space-between;

        .product_image {
            height: 200px;
            border-radius: 8px;
            object-fit: contain;
        }

        .product_name {
            font-size: 18px;
            font-weight: bold;
            margin-top: 16px;
        }

        .product_price {
            font-size: 16px;
            margin-top: 8px;
        }

        .product_button {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
        }

        .product_like_button,
        .product_cart_button,
        .product_buy_button {
            width: 48%;
        }

        .product_buy_button {
            width: 100%;
            margin-top: 16px;
        }
    }

    .product_component:hover {
        /* border을 밖이 아닌 안에서 생성 */
        box-shadow: inset 0 0 0 1px var(--main);
    }
</style>
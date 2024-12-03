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
        echo '<button class="product_buy_button">구매하기</button>';
    }
    ?>

</div>


<style>
    .product_component {
        padding: 16px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;

        .product_image {
            height: 200px;
            border-radius: 8px;
            object-fit: cover;
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
            padding: 8px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            background-color: var(--black);
            color: var(--white);
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .product_like_button:hover,
        .product_cart_button:hover {
            background-color: var(--black-hover);
        }

        .product_like_button:active,
        .product_cart_button:active {
            background-color: var(--black-active);
        }

        .product_buy_button {
            color: var(--black);
            background-color: var(--white);
            width: 100%;
            margin-top: 16px;
        }

        .product_buy_button:hover {
            background-color: var(--white-hover);
        }

        .product_buy_button:active {
            background-color: var(--white-active);
        }
    }
</style>
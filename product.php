<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - 상품 리스트</title>

    <!-- 외부 스타일 시트 링크 -->
    <link rel="stylesheet" href="./style.css">

    <style>
        main {
            position: relative;
        }

        .index_view {
            height: auto;
        }

        .ranking_section {
            gap: 16px;
            width: 100%;
            display: flex;
            margin: 40px 0;
            align-items: center;
            flex-direction: column;
        }

        .section_header {
            width: 100%;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 16px;
            padding-bottom: 8px;
            justify-content: space-between;
        }

        .section_title {
            font-size: 24px;
            font-weight: bold;
        }

        .product_list {
            gap: 60px;
            width: 100%;
            display: grid;
            justify-items: center;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        .product_item {
            background: var(--light-black);
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease-in-out;
            cursor: pointer;
        }

        .product_item:hover {
            transform: scale(1.05);
        }

        .product_item img {
            width: 100%;
            height: auto;
        }

        .product_item h3 {
            margin: 12px 0 4px;
            font-size: 18px;
        }

        .product_item p {
            font-size: 16px;
            color: var(--gray);
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <div class="index_view view">
            <!-- 상품 리스트 섹션 -->
            <div class="ranking_section">
                <div class="section_header">
                    <h2 class="section_title">상품 리스트</h2>
                    <?php include("./Components/SearchComponents.php"); ?>
                </div>
                <div class="product_list">
                    <?php
                    $products = array(
                        array('id' => 1, 'image' => './assets/images/hoodie1.png', 'name' => '후디1', 'price' => '₩50,000'),
                        array('id' => 2, 'image' => './assets/images/hoodie1.png', 'name' => '후디2', 'price' => '₩60,000'),
                        array('id' => 3, 'image' => './assets/images/hoodie1.png', 'name' => '후디3', 'price' => '₩70,000'),
                        array('id' => 4, 'image' => './assets/images/hoodie1.png', 'name' => '후디4', 'price' => '₩80,000'),
                        array('id' => 5, 'image' => './assets/images/hoodie1.png', 'name' => '후디5', 'price' => '₩90,000'),
                        array('id' => 6, 'image' => './assets/images/hoodie1.png', 'name' => '후디6', 'price' => '₩100,000'),
                    );

                    foreach ($products as $product) {
                        echo '<div class="product_item" onclick="location.href=\'product_detail.php?id=' . $product['id'] . '\'">';
                        echo '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                        echo '<p>' . htmlspecialchars($product['price']) . '</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

</html>
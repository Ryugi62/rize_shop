<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - 상품 리스트</title>
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
    <?php include("./Components/HeaderComponents.php"); ?>

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
                    include('./config/db.php');

                    try {
                        // 상품 데이터 가져오기
                        $query = $pdo->query('SELECT id, product_name, product_image, price FROM products ORDER BY created_at DESC');
                        $products = $query->fetchAll(PDO::FETCH_ASSOC);

                        if ($products) {
                            foreach ($products as $product) {
                                echo '<div class="product_item" onclick="location.href=\'product_detail.php?id=' . $product['id'] . '\'">';
                                echo '<img src="' . htmlspecialchars($product['product_image']) . '" alt="' . htmlspecialchars($product['product_name']) . '">';
                                echo '<h3>' . htmlspecialchars($product['product_name']) . '</h3>';
                                echo '<p>' . htmlspecialchars(number_format($product['price'])) . '원</p>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>등록된 상품이 없습니다.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>데이터를 불러오는 중 문제가 발생했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
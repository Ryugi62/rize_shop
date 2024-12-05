<!-- index.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>

    <!-- 외부 스타일 시트 링크 -->
    <link rel="stylesheet" href="./style.css">

    <style>
        /* 메인 컨텐츠 */
        main {
            position: relative;

            .ranking_section {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 16px;
                margin-top: 40px;
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

            .ranking_product_list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 16px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include("./Components/LoadingComponents.php"); ?>

    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="product_view view">
            <div class="ranking_section">
                <div class="section_header">
                    <h2 class="section_title">상품 리스트</h2>
                    <?php include("./Components/SearchComponents.php"); ?>
                </div>

                <!-- 상품 리스트를 동적으로 생성 -->
                <div class="ranking_product_list">
                    <?php
                    // 예시 상품 데이터 배열
                    $products = array(
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디1',
                            'price' => '₩50,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디2',
                            'price' => '₩60,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디3',
                            'price' => '₩70,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디3',
                            'price' => '₩70,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디3',
                            'price' => '₩70,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디3',
                            'price' => '₩70,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디3',
                            'price' => '₩70,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디3',
                            'price' => '₩70,000',
                        ),
                        // 추가 상품 데이터...
                    );
                    $mode = "view";

                    // 상품 데이터를 동적으로 렌더링
                    foreach ($products as $product) {
                        include("./Components/ProductComponents.php");
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
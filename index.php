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


            .index_view {
                display: flex;
                max-width: 1200px;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                margin: 0 auto;
                padding: 0 16px;
            }

            .video_box {
                width: 100%;
                height: auto;
                position: relative;
            }

            .video_box video {
                display: block;
                margin: 0 auto;
                width: 100%;
                height: 650px;
                object-fit: cover;
            }

            .ranking_section {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 16px;
                margin-top: 40px;
            }

            .section_title {
                width: 100%;
                text-align: left;
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 16px;
                border-bottom: 1px solid var(--light-gray);
                padding-bottom: 8px;
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
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <div class="index_view">
            <div class="video_box">
                <video src="./assets/video/main_video.mp4" poster="./assets/images/main_video_poster.png" autoplay loop muted>
                    영상을 불러올 수 없습니다.
                </video>
            </div>

            <div class="ranking_section">
                <h2 class="section_title">랭킹 상품</h2>

                <!-- 상품 리스트를 동적으로 생성 -->
                <div class="ranking_product_list">
                    <?php
                    // 예시 상품 데이터 배열 using array()
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
                            'name' => '후디2',
                            'price' => '₩60,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디2',
                            'price' => '₩60,000',
                        ),
                        array(
                            'image' => './assets/images/hoodie1.png',
                            'name' => '후디2',
                            'price' => '₩60,000',
                        ),
                    );

                    // 상품 데이터를 동적으로 렌더링
                    foreach ($products as $product) {
                        include("./Components/ProductComponents.php");
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

</html>


<script>
    // 현재 JavaScript는 필요하지 않으므로 비워두었습니다.
</script>
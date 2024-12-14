<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>

    <!-- 외부 스타일 시트 링크 -->
    <link rel="stylesheet" href="./style.css">

    <style>
        main {
            position: relative;
        }

        .video_banner {
            position: relative;
            width: 100%;
            height: 900px;
            overflow: hidden;
        }

        .video_banner video {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .video_banner .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 36px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            z-index: 1;
            font-size: 3rem;
        }

        #slogan {
            white-space: pre;
            text-align: center;
            font-size: 3rem;
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

        .section_title {
            width: 100%;
            text-align: left;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 8px;
        }

        .product_list {
            display: grid;
            gap: 16px;
            width: 100%;
            justify-items: center;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <!-- 비디오 배너 -->
        <div class="video_banner">
            <video src="./assets/video/main_video.mp4" autoplay loop muted></video>
            <div class="overlay">
                <div class="slogan_section">
                    <p id="slogan"></p>
                </div>
            </div>
        </div>

        <div class="index_view view">
            <!-- 랭킹 상품 섹션 -->
            <div class="ranking_section">
                <h2 class="section_title">랭킹 상품</h2>
                <div class="product_list">
                    <?php
                    $ranking_products = array(
                        array('image' => './assets/images/hoodie1.png', 'name' => '랭킹 후디1', 'price' => '₩50,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '랭킹 후디2', 'price' => '₩60,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '랭킹 후디3', 'price' => '₩55,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '랭킹 후디4', 'price' => '₩70,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '랭킹 후디5', 'price' => '₩80,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '랭킹 후디6', 'price' => '₩90,000'),
                    );

                    foreach ($ranking_products as $product) {
                        include("./Components/ProductComponents.php");
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

<script>
    const sloganText = ['"a unique hoodie, and,', 'with me in it."'];
    const typingSpeed = 100;
    const delayBetweenLines = 1000;
    const sloganElement = document.getElementById('slogan');

    let currentLine = 0;
    let currentChar = 0;

    function typeNextCharacter() {
        if (currentChar < sloganText[currentLine].length) {
            sloganElement.textContent += sloganText[currentLine][currentChar];
            currentChar++;
            setTimeout(typeNextCharacter, typingSpeed);
        } else {
            if (currentLine < sloganText.length - 1) {
                currentLine++;
                currentChar = 0;
                setTimeout(() => {
                    sloganElement.textContent += '\n';
                    typeNextCharacter();
                }, delayBetweenLines);
            }
        }
    }

    typeNextCharacter();
</script>

</html>
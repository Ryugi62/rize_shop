<?php
session_start();
include('./config/db.php');

// 랭킹 상품 조회
$ranking_stmt = $pdo->query("SELECT id, product_name, product_image, price, discount_amount FROM products ORDER BY sold_count DESC LIMIT 8");
$ranking_products = $ranking_stmt->fetchAll(PDO::FETCH_ASSOC);

// 특가/이벤트 상품 조회
$event_stmt = $pdo->query("
    SELECT id, product_name, product_image, price, discount_amount
    FROM products
    WHERE is_event = 1 OR discount_amount > 0
    ORDER BY created_at DESC
    LIMIT 8
");
$event_products = $event_stmt->fetchAll(PDO::FETCH_ASSOC);

// 공지사항 조회
$notices_stmt = $pdo->query("SELECT id, title, content FROM posts WHERE post_type = 'notice' ORDER BY created_at DESC LIMIT 5");
$notices = $notices_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>
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
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            z-index: 1;
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
            gap: 60px;
            width: 100%;
            justify-items: center;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        .view_all_link {
            margin-top: 20px;
            text-align: center;
            width: 100%;
        }

        .view_all_link a {
            color: var(--main);
            text-decoration: none;
            font-weight: bold;
        }

        .view_all_link a:hover {
            text-decoration: underline;
        }

        /* 특가/이벤트 상품 스타일 */
        .event_section {
            gap: 16px;
            width: 100%;
            display: flex;
            margin: 40px 0;
            align-items: center;
            flex-direction: column;
        }

        /* 공지사항 스타일 */
        .notice_section {
            gap: 16px;
            width: 100%;
            display: flex;
            margin: 40px 0;
            align-items: flex-start;
            flex-direction: column;
        }

        .notice_list {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .notice_item {
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 8px;
        }

        .notice_item h3 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .notice_item p {
            margin: 4px 0 0;
            font-size: 16px;
            color: var(--gray);
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
                    foreach ($ranking_products as $rp) {
                        $original_price = $rp['price'];
                        $discount_amount = $rp['discount_amount'];
                        $final_price = $original_price - $discount_amount;
                        if ($final_price < 0) $final_price = 0;

                        $product = [
                            'id' => $rp['id'],
                            'image' => $rp['product_image'],
                            'name' => $rp['product_name'],
                            'price' => $original_price,
                            'discount_amount' => $discount_amount,
                            'final_price' => $final_price
                        ];
                        $mode = '';
                        include("./Components/ProductComponents.php");
                    }
                    ?>
                </div>
                <div class="view_all_link">
                    <a href="product.php">전체상품 보러가기 →</a>
                </div>
            </div>

            <!-- 특가/이벤트 상품 섹션 -->
            <div class="event_section">
                <h2 class="section_title">특가/이벤트 상품</h2>
                <div class="product_list">
                    <?php
                    foreach ($event_products as $ep) {
                        $original_price = $ep['price'];
                        $discount_amount = $ep['discount_amount'];
                        $final_price = $original_price - $discount_amount;
                        if ($final_price < 0) $final_price = 0;

                        $product = [
                            'id' => $ep['id'],
                            'image' => $ep['product_image'],
                            'name' => $ep['product_name'],
                            'price' => $original_price,
                            'discount_amount' => $discount_amount,
                            'final_price' => $final_price
                        ];
                        $mode = '';
                        include("./Components/ProductComponents.php");
                    }
                    ?>
                </div>
                <div class="view_all_link">
                    <a href="product.php">전체상품 보러가기 →</a>
                </div>
            </div>

            <!-- 공지사항 섹션 -->
            <div class="notice_section">
                <h2 class="section_title">공지사항</h2>
                <div class="notice_list">
                    <?php
                    if (count($notices) > 0) {
                        foreach ($notices as $notice) {
                            // HTML을 그대로 렌더링 (보안 고려 필요)
                            echo '<div class="notice_item">';
                            echo '<h3>' . htmlspecialchars($notice['title']) . '</h3>';
                            echo '<div class="notice_content">' . $notice['content'] . '</div>'; // HTML 태그 반영
                            echo '</div>';
                        }
                    } else {
                        echo '<p>등록된 공지사항이 없습니다.</p>';
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
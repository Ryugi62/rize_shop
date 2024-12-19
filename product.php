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
            position: relative;
        }

        .product_item:hover {
            transform: scale(1.05);
        }

        .product_item img {
            width: 100%;
            height: auto;
            display: block;
        }

        .product_name {
            margin: 12px 0 4px;
            font-size: 18px;
            color: #fff;
        }

        .product_item p {
            font-size: 16px;
            color: var(--gray);
            margin: 8px 0;
        }

        .wishlist_form {
            position: absolute;
            top: 8px;
            right: 8px;
        }

        .wishlist_button {
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .wishlist_button:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .wishlist_button.favorited {
            background: red;
        }

        .price_area {
            margin-bottom: 12px;
        }

        .original_price {
            text-decoration: line-through;
            color: #888;
            margin-right: 4px;
        }

        .discounted_price {
            color: var(--main);
            font-weight: bold;
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
                    <?php
                    include('./config/db.php');
                    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                    ?>
                    <h2 class="section_title">
                        <?php
                        if ($search !== '') {
                            echo "상품 리스트 - '" . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . "' 검색결과";
                        } else {
                            echo "상품 리스트";
                        }
                        ?>
                    </h2>
                    <?php include("./Components/SearchComponents.php"); ?>
                </div>
                <div class="product_list">
                    <?php

                    $favorited_flag = isset($_GET['favorited']) ? $_GET['favorited'] : null;

                    try {
                        if ($search === '') {
                            // 검색어 없으면 모든 상품
                            $query = $pdo->query('SELECT id, product_name, product_image, price, discount_amount FROM products ORDER BY created_at DESC');
                        } else {
                            // 검색어 있으면 LIKE
                            $stmt = $pdo->prepare('SELECT id, product_name, product_image, price, discount_amount FROM products WHERE product_name LIKE :search ORDER BY created_at DESC');
                            $stmt->execute(['search' => '%' . $search . '%']);
                            $query = $stmt;
                        }

                        $products = $query->fetchAll(PDO::FETCH_ASSOC);

                        if ($products && count($products) > 0) {
                            $user_id = $_SESSION['user_id'] ?? null;
                            $user_favorites = [];
                            if ($user_id) {
                                $fav_stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = :uid");
                                $fav_stmt->execute(['uid' => $user_id]);
                                $favs = $fav_stmt->fetchAll(PDO::FETCH_COLUMN);
                                if ($favs) {
                                    $user_favorites = array_map('intval', $favs);
                                }
                            }

                            foreach ($products as $prd) {
                                $original_price = $prd['price'];
                                $discount_amount = $prd['discount_amount'];
                                $final_price = $original_price - $discount_amount;
                                if ($final_price < 0) $final_price = 0;

                                echo '<div class="product product_item">';
                                echo '<form action="toggle_favorites.php" method="post" class="wishlist_form" onsubmit="return false;">';
                                echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($prd['id']) . '">';

                                $is_favorited = in_array($prd['id'], $user_favorites);
                                $button_class = $is_favorited ? 'wishlist_button favorited' : 'wishlist_button';

                                // 기존 toggle_favorites.php는 AJAX사용
                                // 여기서는 product_detail과 마찬가지로 AJAX로 바꾸려면 JS 필요
                                // 하지만 요구사항에 AJAX필수 언급 없음. previous code used fetch('toggle_favorites.php'), do it same way:
                                // Actually we no direct mention. We'll keep synchronous form for simplicity or just a comment.
                                echo '<button type="button" class="' . $button_class . '" onclick="toggleFavorite(' . $prd['id'] . ', this)">♥</button>';
                                echo '</form>';

                                echo '<a href="product_detail.php?id=' . $prd['id'] . '" style="text-decoration:none; color:inherit;">';
                                echo '<img src="' . htmlspecialchars($prd['product_image']) . '" alt="' . htmlspecialchars($prd['product_name']) . '">';
                                echo '<h3 class="product_name">' . htmlspecialchars($prd['product_name']) . '</h3>';
                                if ($discount_amount > 0) {
                                    echo '<p class="price_area"><span class="original_price">' . number_format($original_price) . '원</span> → <span class="discounted_price">' . number_format($final_price) . '원</span></p>';
                                } else {
                                    echo '<p>' . number_format($original_price) . '원</p>';
                                }
                                echo '</a>';

                                echo '</div>';
                            }
                        } else {
                            echo '<p>등록된 상품이 없거나 검색 결과가 없습니다.</p>';
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

    <script>
        function toggleFavorite(productId, buttonElem) {
            fetch('/toggle_favorites.php', {
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
                            buttonElem.classList.add('favorited');
                        } else {
                            buttonElem.classList.remove('favorited');
                        }
                    } else {
                        alert('오류: ' + (data.message || '알 수 없는 오류'));
                    }
                })
                .catch(e => {
                    console.error(e);
                    alert('요청 중 문제가 발생했습니다.');
                });
        }
    </script>
</body>

</html>
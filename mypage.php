<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

$user_id = $_SESSION['user_id'];

// 사용자 정보 가져오기
$query = $pdo->prepare('SELECT username, email, created_at FROM users WHERE id = :user_id');
$query->execute(['user_id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('사용자 정보를 찾을 수 없습니다.');
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - 마이페이지</title>
    <link rel="stylesheet" href="./style.css">

    <style>
        /* 마이페이지 배너 */
        .mypage_banner {
            position: relative;
            width: 100%;
            height: 300px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            margin-bottom: 40px;
            background-image: url('./assets/images/main_banner.jpg');
            /* 배너 이미지 추가 */
        }

        /* 마이페이지 콘텐츠 */
        .mypage_content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        /* 섹션 제목 */
        .section_title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 8px;
        }

        /* 프로필 섹션 */
        .profile_section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .profile_info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .profile_info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--main);
        }

        .profile_details p {
            margin: 4px 0;
            font-size: 18px;
        }

        .edit_profile_btn {
            align-self: flex-start;
            padding: 8px 16px;
            background-color: var(--main);
            color: var(--black);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }

        .edit_profile_btn:hover {
            background-color: var(--main-hover);
        }

        /* 주문 내역 섹션 */
        .orders_section,
        .favorites_section,
        .points_section,
        .posts_section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .orders_list,
        .favorites_list,
        .posts_list {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }

        .order_item,
        .favorite_item,
        .post_item {
            background-color: var(--light-black);
            padding: 16px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .order_item:hover,
        .favorite_item:hover,
        .post_item:hover {
            background-color: var(--light-gray);
        }

        .order_item img,
        .favorite_item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .order_details,
        .favorite_details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            text-align: center;
        }

        /* 포인트 섹션 */
        .points_section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .points_info {
            background-color: var(--light-black);
            padding: 16px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .points_details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .points_details p {
            margin: 0;
            font-size: 18px;
        }

        .points_history {
            margin-top: 16px;
        }

        .points_history table {
            width: 100%;
            border-collapse: collapse;
        }

        .points_history th,
        .points_history td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }

        .points_history th {
            background-color: var(--gray);
            color: var(--black);
        }

        /* 게시물 섹션 */
        .posts_section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .posts_tabs {
            display: flex;
            gap: 16px;
            border-bottom: 1px solid var(--light-gray);
        }

        .posts_tab {
            padding: 8px 16px;
            cursor: pointer;
            font-size: 18px;
            color: var(--white);
            background-color: var(--black);
            border: none;
            border-radius: 4px 4px 0 0;
            transition: background-color 0.3s, color 0.3s;
        }

        .posts_tab.active {
            background-color: var(--main);
            color: var(--black);
        }

        .posts_list {
            display: none;
            flex-direction: column;
            gap: 16px;
            margin-top: 16px;
        }

        .posts_list.active {
            display: flex;
        }

        .post_item {
            background-color: var(--light-black);
            padding: 16px;
            border-radius: 8px;
            display: flex;
            flex-direction: row;
            gap: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            justify-content: space-between;
            width: 100%;
        }

        .post_item:hover {
            background-color: var(--light-gray);
        }

        .post_title {
            font-size: 18px;
            font-weight: bold;
            color: var(--white);
            text-align: left;
            flex: 1;
        }

        .post_date {
            font-size: 14px;
            color: var(--gray);
            margin-left: 16px;
            white-space: nowrap;
        }

        .post_content {
            font-size: 16px;
            color: var(--white);
        }

        /* 더보기 버튼 */
        .load_more_btn {
            margin-top: 16px;
            padding: 8px 16px;
            background-color: var(--main);
            color: var(--black);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            align-self: center;
            transition: background-color 0.3s;
        }

        .load_more_btn:hover {
            background-color: var(--main-hover);
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .profile_info {
                flex-direction: column;
                align-items: flex-start;
            }

            .order_item,
            .favorite_item,
            .post_item {
                align-items: center;
            }

            .order_item img,
            .favorite_item img {
                width: 100%;
                height: auto;
            }

            .posts_tabs {
                flex-direction: column;
            }

            .posts_tab {
                width: 100%;
                text-align: center;
                border-radius: 4px;
            }

            .post_item {
                flex-direction: column;
                align-items: flex-start;
            }

            .post_date {
                margin-left: 0;
            }
        }

        /* 숨김 클래스 */
        .hidden {
            display: none;
        }
    </style>

</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <!-- 마이페이지 배너 -->
        <div class="mypage_banner">
            마이 페이지
        </div>

        <div class="mypage_content">
            <!-- 프로필 섹션 -->
            <div class="profile_section">
                <h2 class="section_title">내 프로필</h2>
                <div class="profile_info">
                    <img src="./assets/images/profile_default.png" alt="프로필 사진">
                    <div class="profile_details">
                        <p>이름: <?= htmlspecialchars($user['username']); ?></p>
                        <p>이메일: <?= htmlspecialchars($user['email']); ?></p>
                        <p>가입일: <?= htmlspecialchars($user['created_at']); ?></p>
                    </div>
                </div>
                <a href="./edit_profile.php" class="edit_profile_btn">프로필 수정</a>
            </div>

            <!-- 주문 내역 섹션 -->
            <div class="orders_section">
                <h2 class="section_title">주문 내역</h2>
                <div class="orders_list">
                    <?php
                    $query = $pdo->prepare('SELECT product_name, price, product_image, order_date FROM orders WHERE user_id = :user_id ORDER BY order_date DESC');
                    $query->execute(['user_id' => $user_id]);
                    $orders = $query->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php foreach ($orders as $order): ?>
                        <div class="order_item">
                            <img src="<?= htmlspecialchars($order['product_image']); ?>" alt="<?= htmlspecialchars($order['product_name']); ?>">
                            <div class="order_details">
                                <p><?= htmlspecialchars($order['product_name']); ?></p>
                                <p>가격: <?= htmlspecialchars(number_format($order['price'])); ?>원</p>
                                <p>주문일: <?= htmlspecialchars($order['order_date']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 찜 목록 섹션 -->
            <div class="favorites_section">
                <h2 class="section_title">찜 목록</h2>
                <div class="favorites_list">
                    <?php
                    $query = $pdo->prepare('
                        SELECT p.product_name, p.product_image, p.price 
                        FROM favorites f 
                        INNER JOIN products p ON f.product_id = p.id 
                        WHERE f.user_id = :user_id
                    ');
                    $query->execute(['user_id' => $user_id]);
                    $favorites = $query->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php foreach ($favorites as $favorite): ?>
                        <div class="favorite_item">
                            <img src="<?= htmlspecialchars($favorite['product_image']); ?>" alt="<?= htmlspecialchars($favorite['product_name']); ?>">
                            <div class="favorite_details">
                                <p><?= htmlspecialchars($favorite['product_name']); ?></p>
                                <p>가격: <?= htmlspecialchars(number_format($favorite['price'])); ?>원</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 포인트 섹션 -->
            <div class="points_section">
                <h2 class="section_title">내 포인트</h2>
                <?php
                $query = $pdo->prepare('SELECT balance FROM points WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1');
                $query->execute(['user_id' => $user_id]);
                $current_point = $query->fetchColumn() ?? 0;
                ?>
                <div class="points_info">
                    <p>현재 포인트: <strong><?= htmlspecialchars(number_format($current_point)); ?>점</strong></p>
                </div>
            </div>

            <!-- 내가 작성한 게시물 섹션 -->
            <div class="posts_section">
                <h2 class="section_title">내가 작성한 게시물</h2>
                <div class="posts_tabs">
                    <button class="posts_tab active" data-tab="all">전체</button>
                    <button class="posts_tab" data-tab="review">리뷰</button>
                    <button class="posts_tab" data-tab="qna">Q&A</button>
                </div>
                <div class="posts_list">
                    <?php
                    $query = $pdo->prepare('SELECT id, title, created_at, post_type FROM posts WHERE user_id = :user_id ORDER BY created_at DESC');
                    $query->execute(['user_id' => $user_id]);
                    $posts = $query->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php foreach ($posts as $post): ?>
                        <div class="post_item" data-post-type="<?= htmlspecialchars($post['post_type']); ?>">
                            <div class="post_title"><?= htmlspecialchars($post['title']); ?></div>
                            <div class="post_date"><?= htmlspecialchars($post['created_at']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <script>
        // 탭 필터링 및 더보기 버튼 동작 구현
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.posts_tab');
            const postItems = document.querySelectorAll('.post_item');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabType = tab.dataset.tab;

                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    postItems.forEach(post => {
                        if (tabType === 'all' || post.dataset.postType === tabType) {
                            post.style.display = '';
                        } else {
                            post.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>
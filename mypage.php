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
$query = $pdo->prepare('SELECT username, email, created_at, role FROM users WHERE id = :user_id');
$query->execute(['user_id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('사용자 정보를 찾을 수 없습니다.');
}

// 주문 내역 조회
$orderQuery = $pdo->prepare('
    SELECT id, product_name, price, product_image, order_date, product_id, status
    FROM orders
    WHERE user_id = :user_id
    ORDER BY order_date DESC
');
$orderQuery->execute(['user_id' => $user_id]);
$orders = $orderQuery->fetchAll(PDO::FETCH_ASSOC);

// 찜 목록 조회
$favQuery = $pdo->prepare('
    SELECT p.id AS product_id, p.product_name, p.product_image, p.price 
    FROM favorites f 
    INNER JOIN products p ON f.product_id = p.id 
    WHERE f.user_id = :user_id
');
$favQuery->execute(['user_id' => $user_id]);
$favorites = $favQuery->fetchAll(PDO::FETCH_ASSOC);

// 포인트 조회 (현재 포인트)
$pointQuery = $pdo->prepare('SELECT balance FROM points WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1');
$pointQuery->execute(['user_id' => $user_id]);
$current_point = $pointQuery->fetchColumn() ?? 0;

// 포인트 내역 조회
$pointHistoryQuery = $pdo->prepare('SELECT description, points, balance, created_at FROM points WHERE user_id = :user_id ORDER BY created_at DESC');
$pointHistoryQuery->execute(['user_id' => $user_id]);
$point_history = $pointHistoryQuery->fetchAll(PDO::FETCH_ASSOC);

// 내가 작성한 게시물 조회
$postQuery = $pdo->prepare('SELECT id, title, created_at, post_type FROM posts WHERE user_id = :user_id ORDER BY created_at DESC');
$postQuery->execute(['user_id' => $user_id]);
$posts = $postQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <title>RISZE - 마이페이지</title>
    <link rel="stylesheet" href="./style.css">

    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: sans-serif;
            margin: 0;
        }

        :root {
            --main: #2ef3e1;
            --main-hover: #26d4c3;
            --white: #ffffff;
            --black: #000000;
            --gray: #808080;
            --light-gray: #d0d0d0;
            --light-black: #202020;
        }

        a {
            color: var(--white);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .mypage_banner {
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
        }

        .mypage_content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        .section_title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 8px;
        }

        .section_title a {
            color: var(--white);
        }

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
            transition: background-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        .order_item:hover,
        .favorite_item:hover,
        .post_item:hover {
            background-color: var(--light-gray);
            color: var(--black);
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

        .points_info {
            background-color: var(--light-black);
            padding: 16px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        /* 포인트 히스토리 테이블 */
        .points_history {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .points_history th,
        .points_history td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #555;
        }

        .points_history th {
            background-color: #333;
        }

        .points_history td {
            background-color: #222;
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
            transition: background-color 0.3s, box-shadow 0.3s;
            justify-content: space-between;
            width: 100%;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        .post_item:hover {
            background-color: var(--light-gray);
            color: var(--black);
        }

        .post_title {
            font-size: 18px;
            font-weight: bold;
            text-align: left;
            flex: 1;
        }

        .post_date {
            font-size: 14px;
            color: var(--gray);
            margin-left: 16px;
            white-space: nowrap;
        }

        .no-data-message {
            text-align: center;
            font-size: 18px;
            color: var(--gray);
            background-color: #222;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
        }

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
    </style>

</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="mypage_banner">마이 페이지</div>
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

            <!-- 주문 내역 -->
            <a href="review_write.php" class="edit_profile_btn">리뷰 작성하기</a>
            <div class="orders_section">
                <h2 class="section_title"><a href="orders.php">주문 내역</a></h2>
                <?php if (count($orders) === 0): ?>
                    <div class="no-data-message">아직 주문 내역이 없습니다.</div>
                <?php else: ?>
                    <div class="orders_list">
                        <?php foreach ($orders as $order): ?>
                            <a class="order_item" href="order_status.php?id=<?= urlencode($order['id']); ?>">
                                <?php if ($order['product_image']): ?>
                                    <img src="<?= htmlspecialchars($order['product_image']); ?>" alt="<?= htmlspecialchars($order['product_name']); ?>">
                                <?php endif; ?>
                                <div class="order_details">
                                    <p><?= htmlspecialchars($order['product_name']); ?></p>
                                    <p>가격: <?= number_format($order['price']); ?>원</p>
                                    <p>주문일: <?= htmlspecialchars($order['order_date']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 찜 목록 -->
            <div class="favorites_section">
                <h2 class="section_title"><a href="favorites.php">찜 목록</a></h2>
                <?php if (count($favorites) === 0): ?>
                    <div class="no-data-message">아직 찜한 상품이 없습니다.</div>
                <?php else: ?>
                    <div class="favorites_list">
                        <?php foreach ($favorites as $favorite): ?>
                            <a class="favorite_item" href="product_detail.php?id=<?= urlencode($favorite['product_id']); ?>">
                                <?php if ($favorite['product_image']): ?>
                                    <img src="<?= htmlspecialchars($favorite['product_image']); ?>" alt="<?= htmlspecialchars($favorite['product_name']); ?>">
                                <?php endif; ?>
                                <div class="favorite_details">
                                    <p><?= htmlspecialchars($favorite['product_name']); ?></p>
                                    <p>가격: <?= number_format($favorite['price']); ?>원</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 포인트 -->
            <div class="points_section">
                <h2 class="section_title">내 포인트</h2>
                <div class="points_info">
                    <p>현재 포인트: <strong><?= number_format($current_point); ?>점</strong></p>
                </div>

                <!-- 포인트 사용/적립 내역 -->
                <h2 class="section_title">포인트 사용/적립 내역</h2>
                <?php if (count($point_history) === 0): ?>
                    <div class="no-data-message">포인트 사용/적립 내역이 없습니다.</div>
                <?php else: ?>
                    <table class="points_history">
                        <thead>
                            <tr>
                                <th>일자</th>
                                <th>내역</th>
                                <th>변동 포인트</th>
                                <th>잔액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($point_history as $ph): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ph['created_at']); ?></td>
                                    <td><?= htmlspecialchars($ph['description']); ?></td>
                                    <td><?= number_format($ph['points']); ?>점</td>
                                    <td><?= number_format($ph['balance']); ?>점</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- 내가 작성한 게시물 -->
            <div class="posts_section">
                <h2 class="section_title">내가 작성한 게시물</h2>
                <div class="posts_tabs">
                    <button class="posts_tab active" data-tab="all">전체</button>
                    <button class="posts_tab" data-tab="review">리뷰</button>
                    <button class="posts_tab" data-tab="qna">Q&A</button>
                    <?php if ($user['role'] === 'admin'): ?>
                        <button class="posts_tab" data-tab="notice">공지사항</button>
                    <?php endif; ?>
                </div>
                <?php if (count($posts) === 0): ?>
                    <div class="no-data-message" style="margin-top:16px;">아직 작성한 게시물이 없습니다.</div>
                <?php else: ?>
                    <div class="posts_list">
                        <?php foreach ($posts as $post): ?>
                            <a class="post_item" data-post-type="<?= htmlspecialchars($post['post_type']); ?>" href="board_view.php?id=<?= urlencode($post['id']); ?>">
                                <div class="post_title"><?= htmlspecialchars($post['title']); ?></div>
                                <div class="post_date"><?= htmlspecialchars($post['created_at']); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="no-data-message posts_no_data hidden" style="margin-top:16px;">해당 게시물이 없습니다.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <script>
        // 탭 필터 로직 동일
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.posts_tab');
            const posts = document.querySelectorAll('.post_item');
            const noDataMsg = document.querySelector('.posts_no_data');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    const filter = tab.dataset.tab;
                    let visibleCount = 0;
                    posts.forEach(post => {
                        const type = post.dataset.postType;
                        if (filter === 'all' || type === filter || (filter === 'notice' && type === 'notice')) {
                            post.style.display = 'flex';
                            visibleCount++;
                        } else {
                            post.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0) {
                        noDataMsg.classList.remove('hidden');
                    } else {
                        noDataMsg.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</body>

</html>
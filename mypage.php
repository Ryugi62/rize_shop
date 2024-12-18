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
    <!-- 스타일은 기존 코드에서 유지 -->
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
                    <img src="./assets/images/profile_default.png" alt="프로필 사진"> <!-- 기본 프로필 이미지 -->
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
                    $orders_total = count($orders);
                    $orders_display_limit = 4;
                    ?>

                    <?php foreach ($orders as $index => $order) {
                        $display_class = ($index < $orders_display_limit) ? '' : ' hidden';
                    ?>
                        <div class="order_item<?= $display_class; ?>">
                            <img src="<?= htmlspecialchars($order['product_image']); ?>" alt="<?= htmlspecialchars($order['product_name']); ?>">
                            <div class="order_details">
                                <p><?= htmlspecialchars($order['product_name']); ?></p>
                                <p>가격: <?= htmlspecialchars(number_format($order['price'])); ?>원</p>
                                <p>주문일: <?= htmlspecialchars($order['order_date']); ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($orders_total > $orders_display_limit) { ?>
                    <button class="load_more_btn" data-section="orders">더보기</button>
                <?php } ?>
            </div>

            <!-- 찜 목록 섹션 -->
            <div class="favorites_section">
                <h2 class="section_title">찜 목록</h2>
                <div class="favorites_list">
                    <?php
                    $query = $pdo->prepare('SELECT product_name, price, product_image FROM favorites WHERE user_id = :user_id');
                    $query->execute(['user_id' => $user_id]);
                    $favorites = $query->fetchAll(PDO::FETCH_ASSOC);
                    $favorites_total = count($favorites);
                    $favorites_display_limit = 4;
                    ?>

                    <?php foreach ($favorites as $index => $favorite) {
                        $display_class = ($index < $favorites_display_limit) ? '' : ' hidden';
                    ?>
                        <div class="favorite_item<?= $display_class; ?>">
                            <img src="<?= htmlspecialchars($favorite['product_image']); ?>" alt="<?= htmlspecialchars($favorite['product_name']); ?>">
                            <div class="favorite_details">
                                <p><?= htmlspecialchars($favorite['product_name']); ?></p>
                                <p>가격: <?= htmlspecialchars(number_format($favorite['price'])); ?>원</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($favorites_total > $favorites_display_limit) { ?>
                    <button class="load_more_btn" data-section="favorites">더보기</button>
                <?php } ?>
            </div>

            <!-- 포인트 섹션 -->
            <div class="points_section">
                <h2 class="section_title">내 포인트</h2>
                <?php
                // 현재 포인트 계산
                $query = $pdo->prepare('SELECT balance FROM points WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1');
                $query->execute(['user_id' => $user_id]);
                $current_point = $query->fetch(PDO::FETCH_ASSOC)['balance'] ?? 0;
                ?>
                <div class="points_info">
                    <p>현재 포인트: <strong><?= htmlspecialchars(number_format($current_point)); ?>점</strong></p>
                </div>
                <div class="points_history">
                    <h3>포인트 내역</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>날짜</th>
                                <th>내역</th>
                                <th>포인트</th>
                                <th>잔여 포인트</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = $pdo->prepare('SELECT created_at, description, points, balance FROM points WHERE user_id = :user_id ORDER BY created_at DESC');
                            $query->execute(['user_id' => $user_id]);
                            $points_history = $query->fetchAll(PDO::FETCH_ASSOC);
                            $points_total = count($points_history);
                            $points_display_limit = 4;
                            ?>

                            <?php foreach ($points_history as $index => $history) {
                                $display_class = ($index < $points_display_limit) ? '' : ' hidden';
                            ?>
                                <tr class="point_row<?= $display_class; ?>">
                                    <td><?= htmlspecialchars($history['created_at']); ?></td>
                                    <td><?= htmlspecialchars($history['description']); ?></td>
                                    <td><?= htmlspecialchars(number_format($history['points'])); ?>점</td>
                                    <td><?= htmlspecialchars(number_format($history['balance'])); ?>점</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php if ($points_total > $points_display_limit) { ?>
                        <button class="load_more_btn" data-section="points_history">더보기</button>
                    <?php } ?>
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

                <!-- 게시물 리스트 -->
                <div class="posts_list active" id="all">
                    <?php
                    $query = $pdo->prepare('SELECT id, title, created_at, content, post_type FROM posts WHERE user_id = :user_id ORDER BY created_at DESC');
                    $query->execute(['user_id' => $user_id]);
                    $posts = $query->fetchAll(PDO::FETCH_ASSOC);
                    $posts_total = count($posts);
                    $posts_display_limit = 4;
                    ?>

                    <?php foreach ($posts as $index => $post) {
                        $display_class = ($index < $posts_display_limit) ? '' : ' hidden';
                        $post_type = htmlspecialchars($post['post_type']);
                    ?>
                        <div class="post_item<?= $display_class; ?>" data-post-type="<?= $post_type; ?>" onclick="window.location='./board_view.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>'">
                            <div class="post_title"><?= htmlspecialchars($post['title']); ?></div>
                            <div class="post_date"><?= htmlspecialchars($post['created_at']); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <?php if ($posts_total > $posts_display_limit) { ?>
                    <button class="load_more_btn" data-section="posts">더보기</button>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreButtons = document.querySelectorAll('.load_more_btn');

            loadMoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const section = button.getAttribute('data-section');
                    let items, displayLimit;

                    switch (section) {
                        case 'orders':
                            items = document.querySelectorAll('.order_item.hidden');
                            displayLimit = 4;
                            break;
                        case 'favorites':
                            items = document.querySelectorAll('.favorite_item.hidden');
                            displayLimit = 4;
                            break;
                        case 'points_history':
                            items = document.querySelectorAll('.point_row.hidden');
                            displayLimit = 4;
                            break;
                        case 'posts':
                            const activeTab = document.querySelector('.posts_tab.active').getAttribute('data-tab');
                            if (activeTab === 'all') {
                                items = document.querySelectorAll('.post_item.hidden');
                            } else {
                                items = document.querySelectorAll(`.post_item.hidden[data-post-type="${activeTab}"]`);
                            }
                            displayLimit = 4;
                            break;
                        default:
                            items = [];
                    }

                    for (let i = 0; i < displayLimit && i < items.length; i++) {
                        items[i].classList.remove('hidden');
                    }

                    if (items.length <= displayLimit) {
                        button.style.display = 'none';
                    }
                });
            });

            // 게시물 탭 전환 기능
            const tabs = document.querySelectorAll('.posts_tab');
            const postsList = document.querySelector('.posts_list');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = tab.getAttribute('data-tab');

                    // 활성화된 탭과 리스트 초기화
                    tabs.forEach(t => t.classList.remove('active'));

                    // 클릭한 탭 활성화
                    tab.classList.add('active');

                    // 필터링된 게시물 표시
                    const allPosts = document.querySelectorAll('.post_item');
                    allPosts.forEach(post => {
                        if (target === 'all' || post.getAttribute('data-post-type') === target) {
                            post.classList.remove('hidden');
                        } else {
                            post.classList.add('hidden');
                        }
                    });

                    // '더보기' 버튼 표시 여부 조정
                    const totalVisible = Array.from(allPosts).filter(post => {
                        return target === 'all' || post.getAttribute('data-post-type') === target;
                    }).length;

                    const initiallyVisible = 4;
                    const loadMoreButton = document.querySelector(`.load_more_btn[data-section="posts"]`);
                    if (totalVisible > initiallyVisible) {
                        loadMoreButton.style.display = 'block';
                    } else {
                        loadMoreButton.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>
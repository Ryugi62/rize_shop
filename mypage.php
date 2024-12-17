<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
            gap: 12px;
            align-items: center;
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
            flex-direction: column;
            gap: 8px;
            cursor: pointer;
        }

        .post_item:hover {
            background-color: var(--light-gray);
        }

        .post_title {
            font-size: 18px;
            font-weight: bold;
            color: var(--white);
        }

        .post_date {
            font-size: 14px;
            color: var(--gray);
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
                flex-direction: column;
                align-items: flex-start;
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
        }

        /* 숨김 클래스 */
        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <?php
    // 리뷰와 Q&A 데이터를 미리 정의합니다.
    $reviews = array(
        array('title' => '훌륭한 후디!', 'date' => '2024-11-25', 'content' => '이 후디는 정말 편안하고 스타일리시해요. 색상도 예쁘고 품질도 좋아서 매우 만족합니다.'),
        array('title' => '배송이 빨라요', 'date' => '2024-10-30', 'content' => '주문한 지 하루 만에 상품을 받아볼 수 있어서 좋았습니다. 포장도 꼼꼼하게 되어 있었습니다.'),
        array('title' => '사이즈가 조금 작아요', 'date' => '2024-09-15', 'content' => '디자인은 마음에 들지만, 사이즈가 조금 작아서 아쉽네요. 다음 번에는 한 사이즈 크게 주문할 생각입니다.'),
        array('title' => '색상이 다양해서 좋아요', 'date' => '2024-08-10', 'content' => '다양한 색상이 있어서 선택의 폭이 넓어 좋았습니다. 앞으로도 다양한 색상 출시 부탁드립니다.'),
        array('title' => '친절한 고객 서비스', 'date' => '2024-07-05', 'content' => '문의사항에 빠르게 답변해주셔서 감사했습니다. 앞으로도 잘 부탁드립니다.'),
    );

    $qna = array(
        array('title' => '사이즈 문의', 'date' => '2024-11-10', 'content' => '이 후디의 정확한 사이즈 정보를 알고 싶습니다. S 사이즈가 어떤 치수인지요?'),
        array('title' => '색상 옵션', 'date' => '2024-10-05', 'content' => '후디의 다른 색상 옵션이 있나요? 현재 사이트에 보이지 않아서요.'),
        array('title' => '배송비 정책', 'date' => '2024-09-20', 'content' => '주문 금액이 일정 금액 이상일 경우 배송비가 무료인가요? 확인 부탁드립니다.'),
        array('title' => '교환/반품 절차', 'date' => '2024-08-15', 'content' => '상품에 이상이 있을 경우 교환이나 반품 절차는 어떻게 되나요?'),
        array('title' => '상품 재입고 문의', 'date' => '2024-07-25', 'content' => '인기 상품이 품절된 경우 재입고 예정이 있나요?'),
    );

    // 전체 게시물 합치기
    $all_posts = array_merge($reviews, $qna);
    ?>

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
                        <p>이름: 홍길동</p>
                        <p>이메일: honggildong@example.com</p>
                        <p>가입일: 2023-01-15</p>
                    </div>
                </div>
                <button class="edit_profile_btn">프로필 수정</button>
            </div>

            <!-- 주문 내역 섹션 -->
            <div class="orders_section">
                <h2 class="section_title">주문 내역</h2>
                <div class="orders_list">
                    <?php
                    // 예시 데이터 - 실제 구현 시 데이터베이스에서 불러와야 함
                    $orders = array(
                        array('image' => './assets/images/hoodie1.png', 'name' => '주문 상품1', 'price' => '₩50,000', 'date' => '2024-11-20'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '주문 상품2', 'price' => '₩60,000', 'date' => '2024-10-15'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '주문 상품3', 'price' => '₩55,000', 'date' => '2024-09-10'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '주문 상품4', 'price' => '₩70,000', 'date' => '2024-08-05'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '주문 상품5', 'price' => '₩80,000', 'date' => '2024-07-22'),
                    );

                    foreach ($orders as $index => $order) {
                        // 초기에는 4개만 보이고, 그 이후는 숨김
                        $display_class = ($index < 4) ? '' : ' hidden';
                    ?>
                        <div class="order_item<?php echo $display_class; ?>">
                            <img src="<?php echo $order['image']; ?>" alt="<?php echo htmlspecialchars($order['name']); ?>">
                            <div class="order_details">
                                <p><?php echo htmlspecialchars($order['name']); ?></p>
                                <p>가격: <?php echo htmlspecialchars($order['price']); ?></p>
                                <p>주문일: <?php echo htmlspecialchars($order['date']); ?></p>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <?php if (count($orders) > 4) { ?>
                    <button class="load_more_btn" data-section="orders">더보기</button>
                <?php } ?>
            </div>

            <!-- 찜 목록 섹션 -->
            <div class="favorites_section">
                <h2 class="section_title">찜 목록</h2>
                <div class="favorites_list">
                    <?php
                    // 예시 데이터 - 실제 구현 시 데이터베이스에서 불러와야 함
                    $favorites = array(
                        array('image' => './assets/images/hoodie1.png', 'name' => '찜 상품1', 'price' => '₩70,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '찜 상품2', 'price' => '₩80,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '찜 상품3', 'price' => '₩90,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '찜 상품4', 'price' => '₩100,000'),
                        array('image' => './assets/images/hoodie1.png', 'name' => '찜 상품5', 'price' => '₩110,000'),
                    );

                    foreach ($favorites as $index => $favorite) {
                        // 초기에는 4개만 보이고, 그 이후는 숨김
                        $display_class = ($index < 4) ? '' : ' hidden';
                    ?>
                        <div class="favorite_item<?php echo $display_class; ?>">
                            <img src="<?php echo $favorite['image']; ?>" alt="<?php echo htmlspecialchars($favorite['name']); ?>">
                            <div class="favorite_details">
                                <p><?php echo htmlspecialchars($favorite['name']); ?></p>
                                <p>가격: <?php echo htmlspecialchars($favorite['price']); ?></p>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <?php if (count($favorites) > 4) { ?>
                    <button class="load_more_btn" data-section="favorites">더보기</button>
                <?php } ?>
            </div>

            <!-- 포인트 섹션 -->
            <div class="points_section">
                <h2 class="section_title">내 포인트</h2>
                <div class="points_info">
                    <p>현재 포인트: <strong>₩10,000</strong></p>
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
                            // 예시 데이터 - 실제 구현 시 데이터베이스에서 불러와야 함
                            $points_history = array(
                                array('date' => '2024-12-01', 'description' => '상품 구매', 'points' => '+5,000', 'balance' => '10,000'),
                                array('date' => '2024-11-15', 'description' => '리뷰 작성', 'points' => '+2,000', 'balance' => '5,000'),
                                array('date' => '2024-10-20', 'description' => '가입 포인트', 'points' => '+3,000', 'balance' => '3,000'),
                                array('date' => '2024-09-25', 'description' => '이벤트 참여', 'points' => '+1,000', 'balance' => '4,000'),
                                array('date' => '2024-08-30', 'description' => '상품 구매', 'points' => '-1,000', 'balance' => '3,000'),
                            );

                            foreach ($points_history as $index => $history) {
                                // 초기에는 4개만 보이고, 그 이후는 숨김
                                $display_class = ($index < 4) ? '' : ' hidden';
                            ?>
                                <tr class="point_row<?php echo $display_class; ?>">
                                    <td><?php echo htmlspecialchars($history['date']); ?></td>
                                    <td><?php echo htmlspecialchars($history['description']); ?></td>
                                    <td><?php echo htmlspecialchars($history['points']); ?></td>
                                    <td><?php echo htmlspecialchars($history['balance']); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php if (count($points_history) > 4) { ?>
                        <button class="load_more_btn" data-section="points_history">더보기</button>
                    <?php } ?>
                </div>
            </div>

            <!-- 내가 작성한 게시물 섹션 -->
            <div class="posts_section">
                <h2 class="section_title">내가 작성한 게시물</h2>
                <div class="posts_tabs">
                    <button class="posts_tab active" data-tab="all">전체</button>
                    <button class="posts_tab" data-tab="reviews">리뷰</button>
                    <button class="posts_tab" data-tab="qna">Q&A</button>
                </div>

                <!-- 전체 게시물 섹션 -->
                <div class="posts_list active" id="all">
                    <?php
                    foreach ($all_posts as $index => $post) {
                        // 초기에는 4개만 보이고, 그 이후는 숨김
                        $display_class = ($index < 4) ? '' : ' hidden';
                    ?>
                        <div class="post_item<?php echo $display_class; ?>" onclick="window.location.href='post_detail.php?id=<?php echo $index; ?>'">
                            <div class="post_title"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div class="post_date"><?php echo htmlspecialchars($post['date']); ?></div>
                            <div class="post_content"><?php echo htmlspecialchars($post['content']); ?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <!-- 리뷰 게시물 섹션 -->
                <div class="posts_list" id="reviews">
                    <?php
                    foreach ($reviews as $index => $review) {
                        // 초기에는 4개만 보이고, 그 이후는 숨김
                        $display_class = ($index < 4) ? '' : ' hidden';
                    ?>
                        <div class="post_item<?php echo $display_class; ?>" onclick="window.location.href='post_detail.php?id=<?php echo $index; ?>'">
                            <div class="post_title"><?php echo htmlspecialchars($review['title']); ?></div>
                            <div class="post_date"><?php echo htmlspecialchars($review['date']); ?></div>
                            <div class="post_content"><?php echo htmlspecialchars($review['content']); ?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <!-- Q&A 게시물 섹션 -->
                <div class="posts_list" id="qna">
                    <?php
                    foreach ($qna as $index => $question) {
                        // 초기에는 4개만 보이고, 그 이후는 숨김
                        $display_class = ($index < 4) ? '' : ' hidden';
                    ?>
                        <div class="post_item<?php echo $display_class; ?>" onclick="window.location.href='post_detail.php?id=<?php echo $index; ?>'">
                            <div class="post_title"><?php echo htmlspecialchars($question['title']); ?></div>
                            <div class="post_date"><?php echo htmlspecialchars($question['date']); ?></div>
                            <div class="post_content"><?php echo htmlspecialchars($question['content']); ?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <!-- 각 리스트에 대한 더보기 버튼 -->
                <?php if (count($all_posts) > 4) { ?>
                    <button class="load_more_btn" data-section="all">더보기</button>
                <?php } ?>
                <?php if (count($reviews) > 4) { ?>
                    <button class="load_more_btn" data-section="reviews">더보기</button>
                <?php } ?>
                <?php if (count($qna) > 4) { ?>
                    <button class="load_more_btn" data-section="qna">더보기</button>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <script>
        // 게시물 탭 전환 스크립트
        const tabs = document.querySelectorAll('.posts_tab');
        const postsLists = document.querySelectorAll('.posts_list');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // 모든 탭에서 active 클래스 제거
                tabs.forEach(t => t.classList.remove('active'));
                // 클릭한 탭에 active 클래스 추가
                tab.classList.add('active');

                // 모든 게시물 리스트 숨기기
                postsLists.forEach(list => list.classList.remove('active'));

                // 클릭한 탭에 해당하는 게시물 리스트 표시
                const target = tab.getAttribute('data-tab');
                if (target === 'all') {
                    document.getElementById('all').classList.add('active');
                } else {
                    document.getElementById(target).classList.add('active');
                }
            });
        });

        // 더보기 버튼 스크립트
        const loadMoreButtons = document.querySelectorAll('.load_more_btn');

        loadMoreButtons.forEach(button => {
            button.addEventListener('click', () => {
                const section = button.getAttribute('data-section');

                if (section === 'points_history') {
                    const rows = document.querySelectorAll('.point_row.hidden');
                    let displayed = 0;
                    for (let i = 0; i < rows.length; i++) {
                        if (displayed >= 6) break;
                        rows[i].classList.remove('hidden');
                        displayed++;
                    }
                    // 버튼 숨기기
                    if (document.querySelectorAll('.point_row.hidden').length === 0) {
                        button.style.display = 'none';
                    }
                } else if (section === 'all' || section === 'reviews' || section === 'qna') {
                    const list = document.getElementById(section);
                    const items = list.querySelectorAll('.post_item.hidden');
                    let displayed = 0;
                    for (let i = 0; i < items.length; i++) {
                        if (displayed >= 6) break;
                        items[i].classList.remove('hidden');
                        displayed++;
                    }
                    // 버튼 숨기기
                    if (list.querySelectorAll('.post_item.hidden').length === 0) {
                        button.style.display = 'none';
                    }
                } else { // orders, favorites
                    const list = document.querySelector(`.${section}_list`);
                    const items = list.querySelectorAll(`.${section}_item.hidden`);
                    let displayed = 0;
                    for (let i = 0; i < items.length; i++) {
                        if (displayed >= 6) break;
                        items[i].classList.remove('hidden');
                        displayed++;
                    }
                    // 버튼 숨기기
                    if (list.querySelectorAll(`.${section}_item.hidden`).length === 0) {
                        button.style.display = 'none';
                    }
                }
            });
        });
    </script>

</body>

</html>
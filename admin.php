<?php
// admin.php

session_start(); // 세션 시작을 최상단에 위치

// 더미 데이터 초기화
if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = array(
        array('id' => 1, 'image' => './assets/images/hoodie1.png', 'name' => '더미 후디1', 'price' => '₩50,000', 'description' => '<p>이것은 더미 후디1의 설명입니다.</p>'),
        array('id' => 2, 'image' => './assets/images/hoodie2.png', 'name' => '더미 후디2', 'price' => '₩60,000', 'description' => '<p>이것은 더미 후디2의 설명입니다.</p>'),
        array('id' => 3, 'image' => './assets/images/hoodie3.png', 'name' => '더미 후디3', 'price' => '₩55,000', 'description' => '<p>이것은 더미 후디3의 설명입니다.</p>'),
    );
}

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = array(
        array('id' => 1, 'username' => 'user1', 'email' => 'user1@example.com'),
        array('id' => 2, 'username' => 'user2', 'email' => 'user2@example.com'),
        array('id' => 3, 'username' => 'user3', 'email' => 'user3@example.com'),
    );
}

if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = array(
        array(
            'id' => 1,
            'title' => '첫 번째 게시물',
            'content' => '<p>이것은 첫 번째 더미 게시물의 내용입니다.</p>',
            'author' => '관리자',
            'date' => '2024-04-01'
        ),
        array(
            'id' => 2,
            'title' => '두 번째 게시물',
            'content' => '<p>이것은 두 번째 더미 게시물의 내용입니다.</p>',
            'author' => '관리자',
            'date' => '2024-04-02'
        ),
        array(
            'id' => 3,
            'title' => '세 번째 게시물',
            'content' => '<p>이것은 세 번째 더미 게시물의 내용입니다.</p>',
            'author' => '관리자',
            'date' => '2024-04-03'
        ),
    );
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - 관리자 페이지</title>

    <!-- 스타일시트 불러오기 -->
    <link rel="stylesheet" href="./style.css">
    <style>
        .admin_view {
            margin-top: 40px;
            width: 100%;
            max-width: 1200px;
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

        .admin_mode {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .admin_mode a {
            flex: 1;
            text-align: center;
            padding: 15px;
            font-size: 16px;
            color: var(--white);
            border-radius: 8px;
            margin: 0 5px;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            border: 1px solid var(--white);
        }

        .admin_mode a:hover {
            background-color: var(--black-hover);
        }

        .admin_mode a.active {
            background-color: var(--white);
            color: var(--black);
            font-weight: bold;
        }

        .content_box {
            padding: 20px;
            background-color: var(--light-black);
            border-radius: 8px;
            width: 100%;
            min-height: 750px;
            color: var(--white);
        }

        .form_group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid var(--gray);
        }

        button {
            display: block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: var(--main);
            color: var(--black);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: var(--main-hover);
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="admin_view view">
            <div class="section_header">
                <h2 class="section_title">관리자 페이지</h2>
            </div>

            <div class="admin_mode">
                <a href="./admin.php?mode=product" class="<?= (!isset($_GET['mode']) || $_GET['mode'] == 'product') ? 'active' : '' ?>">상품 관리</a>
                <a href="./admin.php?mode=add_product" class="<?= (isset($_GET['mode']) && $_GET['mode'] == 'add_product') ? 'active' : '' ?>">상품 추가</a>
                <a href="./admin.php?mode=board" class="<?= (isset($_GET['mode']) && $_GET['mode'] == 'board') ? 'active' : '' ?>">게시물 관리</a>
                <a href="./admin.php?mode=user" class="<?= (isset($_GET['mode']) && $_GET['mode'] == 'user') ? 'active' : '' ?>">회원 관리</a>
            </div>

            <div class="content_box">
                <?php
                $mode = isset($_GET['mode']) ? $_GET['mode'] : 'product';

                switch ($mode) {
                    case 'product':
                        include("./Components/ProductManager.php");
                        break;
                    case 'add_product':
                        include("./Components/AddProduct.php");
                        break;
                    case 'board':
                        include("./Components/PostManager.php"); // 게시물 관리 컴포넌트 포함
                        break;
                    case 'user':
                        include("./Components/UserManager.php");
                        break;
                    default:
                        echo "<p>올바른 모드를 선택해주세요.</p>";
                        break;
                }
                ?>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
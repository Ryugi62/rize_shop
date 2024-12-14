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
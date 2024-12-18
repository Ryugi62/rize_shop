<?php
session_start();
include('./config/db.php');

// 관리자 권한 체크
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("관리자 권한이 필요합니다.");
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'product';

// 상품 삭제 처리
if ($mode === 'product' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del_stmt = $pdo->prepare("DELETE FROM products WHERE id=:id");
    if ($del_stmt->execute(['id' => $id])) {
        $delete_success = "상품이 삭제되었습니다.";
    } else {
        $delete_error = "상품 삭제 중 오류가 발생했습니다.";
    }
}

// edit_product 모드인 경우 로직을 먼저 처리 (HTML 출력 전)
if ($mode === 'edit_product' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    // 로직 처리용 파일 include
    include("./Components/EditProductLogic.php");
    // 여기서 header()를 호출할 수 있으며, 성공 시 페이지 이동 후 exit
    // 실패 시 $error 변수와 $product 변수를 남겨둔 채로 HTML 출력으로 넘어감
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - 관리자 페이지</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        .admin_view {
            margin-top: 40px;
            width: 100%;
            max-width: 1200px;
            height: auto;
            background: #111;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
            color: var(--white);
        }

        .section_header {
            width: 100%;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 16px;
            padding-bottom: 12px;
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
            width: 100%;
            gap: 10px;
        }

        .admin_mode a {
            flex: 1;
            text-align: center;
            padding: 15px;
            font-size: 16px;
            color: var(--white);
            border-radius: 8px;
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

        .success_msg {
            color: var(--green);
            margin-bottom: 16px;
        }

        .error_msg {
            color: var(--red);
            margin-bottom: 16px;
        }

        .admin_table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--gray);
            border-radius: 8px;
            overflow: hidden;
            background-color: var(--light-black);
            font-size: 14px;
            margin-top: 20px;
        }

        .admin_table th,
        .admin_table td {
            padding: 12px;
            border-bottom: 1px solid var(--black-hover);
            vertical-align: middle;
        }

        .admin_table th {
            background-color: var(--black-hover);
            font-weight: bold;
            color: var(--white);
            border-right: 1px solid var(--gray);
            text-align: left;
            white-space: nowrap;
        }

        .admin_table th:last-child {
            border-right: none;
        }

        .admin_table td {
            color: var(--white);
            border-right: 1px solid var(--gray);
        }

        .admin_table td:last-child {
            border-right: none;
        }

        .admin_table .no_data {
            text-align: center;
            color: var(--gray);
            padding: 20px;
        }

        .admin_table tr:hover td {
            background-color: var(--black-hover);
        }

        .action_btn {
            display: inline-block;
            color: var(--black);
            background-color: var(--main);
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 8px;
            transition: background-color 0.3s;
        }

        .action_btn:hover {
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
                <a href="./admin.php?mode=product" class="<?= ($mode == 'product' || $mode == '') ? 'active' : '' ?>">상품 관리</a>
                <a href="./admin.php?mode=add_product" class="<?= ($mode == 'add_product') ? 'active' : '' ?>">상품 추가</a>
                <a href="./admin.php?mode=board" class="<?= ($mode == 'board') ? 'active' : '' ?>">게시물 관리</a>
                <a href="./admin.php?mode=user" class="<?= ($mode == 'user') ? 'active' : '' ?>">회원 관리</a>
            </div>

            <div class="content_box">
                <?php
                if ($mode == 'product') {
                    include("./Components/ProductManager.php");
                } else if ($mode == 'add_product') {
                    include("./Components/AddProduct.php");
                } else if ($mode == 'board') {
                    include("./Components/PostManager.php");
                } else if ($mode == 'user') {
                    include("./Components/UserManager.php");
                } else if ($mode == 'edit_product') {
                    // 여기서는 템플릿(폼)만 출력
                    include("./Components/EditProductTemplate.php");
                } else {
                    echo "<p>올바른 모드를 선택해주세요.</p>";
                }
                ?>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
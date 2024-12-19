<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // 로그인 안된 상태에서는 접근 불가
    echo "<script>alert('로그인이 필요합니다.');location.href='login.php';</script>";
    exit();
}

// 결제가 완료된 상태에서 접근했다고 가정
$page_title = "결제 완료 - 감사 페이지";
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="./style.css">
    <style>
        :root {
            --main: #2ef3e1;
            --main-hover: #26d4c3;
            --white: #ffffff;
            --black: #000000;
            --gray: #808080;
            --light-gray: #d0d0d0;
            --light-black: #202020;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: #000;
            color: #fff;
        }

        .thankyou_container {
            max-width: 800px;
            margin: 80px auto;
            padding: 16px;
            text-align: center;
        }

        .thankyou_container h1 {
            font-size: 2.5rem;
            margin-bottom: 24px;
        }

        .thankyou_container p {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .action-btn {
            background: var(--main);
            color: #000;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .action-btn:hover {
            background: var(--main-hover);
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>
    <div class="thankyou_container">
        <h1>결제 완료</h1>
        <p>주문이 성공적으로 완료되었습니다!<br>상품이 준비되는 대로 발송될 예정입니다.<br>이용해주셔서 감사합니다.</p>
        <a href="mypage.php" class="action-btn">마이페이지로 이동</a>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
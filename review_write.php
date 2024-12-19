<?php
session_start();
require_once './config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.');location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// 구매한 상품 목록 가져오기
$purchase_check = $pdo->prepare("
    SELECT DISTINCT p.id, p.product_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.user_id = :uid 
    AND o.status IN ('pending','processing','shipped','delivered')
    ORDER BY p.product_name ASC
");
$purchase_check->execute(['uid' => $user_id]);
$purchased_products = $purchase_check->fetchAll(PDO::FETCH_ASSOC);

$page_title = "리뷰 작성 - RIZZ 쇼핑몰";

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

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
            background: #000;
            color: #fff;
        }

        body {
            background: linear-gradient(to bottom, #000 0%, #111 100%);
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 16px;
        }

        a {
            color: var(--main);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .form-container {
            background: #111;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        }

        .form-container h1 {
            font-size: 2rem;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .form-group select,
        .form-group textarea {
            background: #222;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 12px;
            color: #fff;
            font-size: 1rem;
            width: 100%;
        }

        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--main);
        }

        .form-group button {
            background-color: var(--main);
            color: #000;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 10px;
            width: 100%;
        }

        .form-group button:hover {
            background-color: var(--main-hover);
        }

        .no-data-message {
            text-align: center;
            font-size: 18px;
            color: #aaa;
            background-color: #222;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>
    <div class="container">
        <a href="mypage.php" class="back-link">← 마이페이지로 돌아가기</a>

        <?php if (count($purchased_products) === 0): ?>
            <div class="no-data-message">구매한 상품이 없습니다. 리뷰를 작성할 수 없습니다.</div>
        <?php else: ?>
            <div class="form-container">
                <h1>리뷰 작성</h1>
                <form action="review_process.php" method="POST">
                    <div class="form-group">
                        <label for="product_id">상품 선택</label>
                        <select name="product_id" id="product_id" required>
                            <option value="">상품 선택</option>
                            <?php foreach ($purchased_products as $pprd): ?>
                                <option value="<?= htmlspecialchars($pprd['id']) ?>"><?= htmlspecialchars($pprd['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rating">평점(1~5)</label>
                        <select name="rating" id="rating" required>
                            <option value="">평점 선택</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?>점</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">리뷰 내용</label>
                        <textarea name="content" id="content" rows="10" required placeholder="리뷰 내용을 입력해주세요."></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit">등록</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <a href="mypage.php" class="back-link">← 마이페이지로 돌아가기</a>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

$user_id = $_SESSION['user_id'];

// 장바구니 항목 가져오기
$stmt = $pdo->prepare("
    SELECT c.id, c.product_id, c.quantity, p.product_name, p.product_image, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 찜하기 항목 가져오기
$favStmt = $pdo->prepare("
    SELECT f.id as favorite_id, f.product_id, p.product_name, p.product_image, p.price
    FROM favorites f
    JOIN products p ON f.product_id = p.id
    WHERE f.user_id = :user_id
");
$favStmt->execute(['user_id' => $user_id]);
$fav_items = $favStmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>장바구니</title>
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
            background: #000;
            color: #fff;
            margin: 0;
            font-family: sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 16px;
            width: 100%;
            height: 100%;
        }

        h1 {
            margin-bottom: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
            margin-bottom: 24px;
        }

        th,
        td {
            padding: 8px;
            border-bottom: 1px solid #555;
            text-align: center;
        }

        th {
            background: #333;
        }

        .total_box {
            text-align: right;
            margin-top: 16px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .action-btn {
            background: var(--main);
            color: #000;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            background: var(--main-hover);
        }

        .section-title {
            font-size: 20px;
            margin-top: 32px;
            margin-bottom: 16px;
            border-bottom: 1px solid #555;
            padding-bottom: 8px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>
    <div class="container">
        <h1>장바구니</h1>

        <?php if (count($cart_items) === 0): ?>
            <p>장바구니에 상품이 없습니다.</p>
        <?php else: ?>
            <!-- 하나의 테이블에 수량 변경, 삭제 체크박스, 구매 선택 체크박스를 모두 포함 -->
            <form id="cartForm" method="post">
                <table>
                    <tr>
                        <th>구매선택</th>
                        <th>상품명</th>
                        <th>이미지</th>
                        <th>가격</th>
                        <th>수량</th>
                        <th>합계</th>
                        <th>삭제</th>
                    </tr>
                    <?php foreach ($cart_items as $item):
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td><input type="checkbox" name="cart_ids[]" value="<?php echo $item['id']; ?>"></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="" width="50"></td>
                            <td><?php echo number_format($item['price']); ?>원</td>
                            <td>
                                <input type="number" name="qty[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" style="width:60px;">
                            </td>
                            <td><?php echo number_format($subtotal); ?>원</td>
                            <td><input type="checkbox" name="del[]" value="<?php echo $item['id']; ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="total_box">
                    총합: <?php echo number_format($total); ?>원
                </div>

                <div class="btn-group">
                    <!-- 변경사항 적용 버튼: update_cart.php로 이동 -->
                    <button type="submit" class="action-btn" onclick="document.getElementById('cartForm').action='update_cart.php';">변경사항 적용</button>
                    <!-- 선택 상품 구매하기 버튼: checkout.php로 이동, action=cart_buy를 추가 -->
                    <button type="submit" class="action-btn" onclick="goToCheckout()">선택 상품 구매하기</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- 찜한 상품 목록 -->
        <h2 class="section-title">찜한 상품</h2>
        <?php if (count($fav_items) === 0): ?>
            <p>찜한 상품이 없습니다.</p>
        <?php else: ?>
            <form action="add_to_cart.php" method="post">
                <table>
                    <tr>
                        <th>선택</th>
                        <th>상품명</th>
                        <th>이미지</th>
                        <th>가격</th>
                    </tr>
                    <?php foreach ($fav_items as $fitem): ?>
                        <tr>
                            <td><input type="checkbox" name="favorite_ids[]" value="<?php echo $fitem['favorite_id']; ?>"></td>
                            <td><?php echo htmlspecialchars($fitem['product_name']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($fitem['product_image']); ?>" alt="" width="50"></td>
                            <td><?php echo number_format($fitem['price']); ?>원</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div style="margin-top:16px; text-align:right;">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="action-btn">장바구니에 담기</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>

    <script>
        function goToCheckout() {
            const form = document.getElementById('cartForm');
            form.action = 'checkout.php';
            // checkout 시 action 값을 cart_buy로 넘겨주기 위해 hidden input 추가
            const hiddenAction = document.createElement('input');
            hiddenAction.type = 'hidden';
            hiddenAction.name = 'action';
            hiddenAction.value = 'cart_buy';
            form.appendChild(hiddenAction);
        }
    </script>
</body>

</html>
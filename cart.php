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
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        th,
        td {
            padding: 8px;
            border-bottom: 1px solid #555;
        }

        th {
            background: #333;
        }

        .total_box {
            text-align: right;
            margin-top: 16px;
        }

        .action-btn {
            background: var(--main);
            color: #000;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }

        .action-btn:hover {
            background: var(--main-hover);
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
            <form action="update_cart.php" method="post">
                <table>
                    <tr>
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
                <div style="margin-top:16px; display:flex; gap:10px; justify-content:flex-end;">
                    <button type="submit" class="action-btn">변경사항 적용</button>
                    <a href="checkout.php" class="action-btn">구매하기</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
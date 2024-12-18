<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}
$user_id = $_SESSION['user_id'];

// 장바구니 확인
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    // 구매 확정 처리: orders 테이블에 삽입, cart 비우기
    foreach ($cart_items as $ci) {
        $ord = $pdo->prepare("INSERT INTO orders (user_id, product_name, product_image, price) VALUES (:user_id, :pn, :pi, :pr)");
        $ord->execute([
            'user_id' => $user_id,
            'pn' => $ci['product_name'],
            'pi' => $ci['product_image'],
            'pr' => $ci['price'] * $ci['quantity']
        ]);
        // sold_count 증가
        $updPrd = $pdo->prepare("UPDATE products SET sold_count = sold_count + :qty WHERE id=:id");
        $updPrd->execute(['qty' => $ci['quantity'], 'id' => $ci['product_id']]);
    }

    // cart 비우기
    $delC = $pdo->prepare("DELETE FROM cart WHERE user_id=:uid");
    $delC->execute(['uid' => $user_id]);

    header("Location: mypage.php?message=구매가 완료되었습니다.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>결제 페이지</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 16px;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid #555;
            padding: 8px;
        }

        th {
            background: #333;
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
        <h1>결제 페이지</h1>
        <?php if (count($cart_items) === 0): ?>
            <p>장바구니에 상품이 없습니다. <a href="product.php">상품 보러가기</a></p>
        <?php else: ?>
            <table>
                <tr>
                    <th>상품명</th>
                    <th>이미지</th>
                    <th>가격</th>
                    <th>수량</th>
                    <th>합계</th>
                </tr>
                <?php foreach ($cart_items as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($item['product_image']); ?>" width="50"></td>
                        <td><?php echo number_format($item['price']); ?>원</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($subtotal); ?>원</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div style="text-align:right; margin-top:16px;">총합: <?php echo number_format($total); ?>원</div>
            <form method="post" style="text-align:right; margin-top:16px;">
                <input type="hidden" name="action" value="buy">
                <button type="submit" class="action-btn">구매 확정</button>
            </form>
        <?php endif; ?>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
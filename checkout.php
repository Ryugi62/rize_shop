<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}
$user_id = $_SESSION['user_id'];

// 사용자 정보 가져오기
$user_stmt = $pdo->prepare("SELECT name, address, address_detail, phone FROM users WHERE id = :uid");
$user_stmt->execute(['uid' => $user_id]);
$user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_info) {
    die('사용자 정보를 찾을 수 없습니다.');
}

// 액션 파라미터에 따라 동작 변경
$action = $_POST['action'] ?? '';
$direct_buy_flag = isset($_POST['direct_buy']) ? (int)$_POST['direct_buy'] : 0;
$is_direct_buy = ($direct_buy_flag === 1);

$cart_items = [];
$total = 0;
$direct_product = null; // 바로 구매 상품 정보 저장할 변수

if ($is_direct_buy && $action === 'direct_preview') {
    // 상품 상세 페이지에서 "바로 구매하기"를 눌러 결제 미리보기 화면으로 진입
    $direct_product_id = intval($_POST['product_id'] ?? 0);
    $direct_quantity = intval($_POST['quantity'] ?? 1);

    // 상품 정보 조회
    $pstmt = $pdo->prepare("SELECT id, product_name, product_image, price, stock FROM products WHERE id = :id");
    $pstmt->execute(['id' => $direct_product_id]);
    $direct_product = $pstmt->fetch(PDO::FETCH_ASSOC);

    if (!$direct_product) {
        die('상품을 찾을 수 없습니다.');
    }

    // 수량 검증
    if ($direct_product['stock'] < $direct_quantity) {
        die('재고가 부족합니다.');
    }

    $direct_product['quantity'] = $direct_quantity;
    $total = $direct_product['price'] * $direct_product['quantity'];
} elseif ($is_direct_buy && $action === 'buy_direct') {
    // "결제하기" 버튼 누른 후 실제 결제 처리
    // 필요한 정보 재확인
    $direct_product_id = intval($_POST['product_id'] ?? 0);
    $direct_quantity = intval($_POST['quantity'] ?? 1);

    $pstmt = $pdo->prepare("SELECT id, product_name, product_image, price, stock FROM products WHERE id = :id");
    $pstmt->execute(['id' => $direct_product_id]);
    $direct_product = $pstmt->fetch(PDO::FETCH_ASSOC);

    if (!$direct_product) {
        die('상품을 찾을 수 없습니다.');
    }

    if ($direct_product['stock'] < $direct_quantity) {
        die('재고가 부족합니다.');
    }

    $direct_product['quantity'] = $direct_quantity;
    $total = $direct_product['price'] * $direct_product['quantity'];

    // 포인트 사용 확인
    $pointStmt = $pdo->prepare("SELECT balance FROM points WHERE user_id=:uid ORDER BY created_at DESC LIMIT 1");
    $pointStmt->execute(['uid' => $user_id]);
    $available_points = (int)($pointStmt->fetchColumn() ?? 0);

    $max_point_use = floor($total * 0.07);
    $max_usable_points = min($available_points, $max_point_use);

    $used_points = isset($_POST['use_points']) ? (int)$_POST['use_points'] : 0;
    if ($used_points > $max_usable_points) {
        $used_points = $max_usable_points;
    }

    $final_price = $total - $used_points;
    if ($final_price < 0) $final_price = 0;

    $pdo->beginTransaction();
    try {
        // 주문 생성
        $ord = $pdo->prepare("INSERT INTO orders (user_id, product_name, product_image, price, product_id, status) VALUES (:user_id, :pn, :pi, :pr, :pid, 'pending')");
        $ord->execute([
            'user_id' => $user_id,
            'pn' => $direct_product['product_name'],
            'pi' => $direct_product['product_image'],
            'pr' => $direct_product['price'] * $direct_product['quantity'],
            'pid' => $direct_product['id']
        ]);

        // 재고 감소 및 판매량 증가
        $updPrd = $pdo->prepare("UPDATE products SET sold_count = sold_count + :qty, stock = stock - :qty WHERE id=:id AND stock >= :qty");
        $updPrd->execute(['qty' => $direct_product['quantity'], 'id' => $direct_product['id']]);

        // 포인트 차감 처리
        $new_balance = $available_points;
        if ($used_points > 0) {
            $new_balance = $available_points - $used_points;
            $desc = "상품 구매 포인트 사용";
            $insPoint = $pdo->prepare("INSERT INTO points (user_id, description, points, balance) VALUES (:uid, :desc, :pts, :bal)");
            $insPoint->execute(['uid' => $user_id, 'desc' => $desc, 'pts' => -$used_points, 'bal' => $new_balance]);
        }

        // 결제 성공 후 포인트 적립
        // 여기서는 결제 금액(final_price)의 1%를 포인트로 적립하는 예제
        $earned_points = floor($final_price * 0.01); // 1% 적립
        if ($earned_points > 0) {
            $new_balance = $new_balance + $earned_points;
            $desc = "상품 구매 적립";
            $insPoint = $pdo->prepare("INSERT INTO points (user_id, description, points, balance) VALUES (:uid, :desc, :pts, :bal)");
            $insPoint->execute(['uid' => $user_id, 'desc' => $desc, 'pts' => $earned_points, 'bal' => $new_balance]);
        }

        $pdo->commit();
        header("Location: thank_you.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('결제 처리 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "');history.back();</script>";
        exit();
    }
} elseif (!$is_direct_buy && $action === 'cart_buy') {
    // 장바구니에서 선택한 상품 구매 처리 로직
    $selected_cart_ids = $_POST['cart_ids'] ?? [];
    if (empty($selected_cart_ids)) {
        die('선택된 상품이 없습니다.');
    }

    $inClause = str_repeat('?,', count($selected_cart_ids) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.product_id, c.quantity, p.product_name, p.product_image, p.price, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id IN ($inClause) AND c.user_id = ?
    ");
    $stmt->execute(array_merge($selected_cart_ids, [$user_id]));
    $cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cart_products) {
        die('선택한 장바구니 상품을 찾을 수 없습니다.');
    }

    $total = 0;
    foreach ($cart_products as $cp) {
        if ($cp['stock'] < $cp['quantity']) {
            die('재고가 부족한 상품이 있습니다: ' . htmlspecialchars($cp['product_name']));
        }
        $total += $cp['price'] * $cp['quantity'];
    }

    $pointStmt = $pdo->prepare("SELECT balance FROM points WHERE user_id=:uid ORDER BY created_at DESC LIMIT 1");
    $pointStmt->execute(['uid' => $user_id]);
    $available_points = (int)($pointStmt->fetchColumn() ?? 0);

    $max_point_use = floor($total * 0.07);
    $max_usable_points = min($available_points, $max_point_use);

    $used_points = isset($_POST['use_points']) ? (int)$_POST['use_points'] : 0;
    if ($used_points > $max_usable_points) {
        $used_points = $max_usable_points;
    }

    $final_price = $total - $used_points;
    if ($final_price < 0) $final_price = 0;

    $pdo->beginTransaction();
    try {
        foreach ($cart_products as $cp) {
            $ord = $pdo->prepare("INSERT INTO orders (user_id, product_name, product_image, price, product_id, status) VALUES (:user_id, :pn, :pi, :pr, :pid, 'pending')");
            $ord->execute([
                'user_id' => $user_id,
                'pn' => $cp['product_name'],
                'pi' => $cp['product_image'],
                'pr' => $cp['price'] * $cp['quantity'],
                'pid' => $cp['product_id']
            ]);

            $updPrd = $pdo->prepare("UPDATE products SET sold_count = sold_count + :qty, stock = stock - :qty WHERE id=:id AND stock >= :qty");
            $updPrd->execute(['qty' => $cp['quantity'], 'id' => $cp['product_id']]);
        }

        $new_balance = $available_points;
        if ($used_points > 0) {
            $new_balance = $available_points - $used_points;
            $desc = "상품 구매 포인트 사용";
            $insPoint = $pdo->prepare("INSERT INTO points (user_id, description, points, balance) VALUES (:uid, :desc, :pts, :bal)");
            $insPoint->execute(['uid' => $user_id, 'desc' => $desc, 'pts' => -$used_points, 'bal' => $new_balance]);
        }

        $earned_points = floor($final_price * 0.01);
        if ($earned_points > 0) {
            $new_balance = $new_balance + $earned_points;
            $desc = "상품 구매 적립";
            $insPoint = $pdo->prepare("INSERT INTO points (user_id, description, points, balance) VALUES (:uid, :desc, :pts, :bal)");
            $insPoint->execute(['uid' => $user_id, 'desc' => $desc, 'pts' => $earned_points, 'bal' => $new_balance]);
        }

        // 선택된 상품들 장바구니에서 제거 (필요 시)
        $del_stmt = $pdo->prepare("DELETE FROM cart WHERE id IN ($inClause) AND user_id=?");
        $del_stmt->execute(array_merge($selected_cart_ids, [$user_id]));

        $pdo->commit();
        header("Location: thank_you.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('결제 처리 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "');history.back();</script>";
        exit();
    }
} else {
    // 그 외 경우에는 장바구니 구매, 또는 기본적인 상황 처리
    die('유효하지 않은 접근입니다.');
}

// 배송 예정일 계산 (direct_preview인 경우 페이지 표시용)
$oneWeekLater = new DateTime('+1 week');
$weekMap = [
    'Sun' => '일',
    'Mon' => '월',
    'Tue' => '화',
    'Wed' => '수',
    'Thu' => '목',
    'Fri' => '금',
    'Sat' => '토'
];
$day = $weekMap[$oneWeekLater->format('D')];
$estimated_shipping_date = $oneWeekLater->format("m.d(" . $day . ") 발송 예정");

// 포인트 조회
$pointStmt = $pdo->prepare("SELECT balance FROM points WHERE user_id=:uid ORDER BY created_at DESC LIMIT 1");
$pointStmt->execute(['uid' => $user_id]);
$available_points = (int)($pointStmt->fetchColumn() ?? 0);

// 포인트 사용 한도 (7%)
$max_point_use = floor($total * 0.07);
$max_usable_points = min($available_points, $max_point_use);
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <title>결제 페이지</title>
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
            background-color: #000;
            color: #fff;
            margin: 0;
            font-family: sans-serif;
        }

        .checkout_container {
            width: 100%;
            margin: 40px auto;
            padding: 0 16px;
            max-width: 1200px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #555;
            padding-bottom: 12px;
        }

        .section_title {
            font-size: 20px;
            font-weight: bold;
            border-bottom: 1px solid #555;
            margin-bottom: 16px;
            padding-bottom: 8px;
        }

        .address_section,
        .request_section,
        .orders_section,
        .points_section {
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .address_info p,
        .points_info p {
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            text-align: center;
        }

        th,
        td {
            border-bottom: 1px solid #555;
            padding: 12px;
        }

        th {
            background-color: #333;
        }

        .action-btn {
            background: var(--main);
            color: #000;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .action-btn:hover {
            background: var(--main-hover);
        }

        .address_info {
            line-height: 1.8;
            margin-top: 8px;
        }

        .user_phone {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #555;
        }

        textarea {
            width: 100%;
            background: #000;
            color: #fff;
            border: 1px solid #555;
            font-size: 1rem;
            padding: 8px;
            margin-top: 8px;
        }

        select,
        input[type="number"] {
            background: #000;
            color: #fff;
            border: 1px solid #555;
            padding: 4px;
            font-size: 1rem;
        }

        .checkbox_area {
            margin-top: 24px;
            line-height: 1.8;
        }

        .checkbox_area input[type="checkbox"] {
            margin-right: 8px;
        }

        .bottom_fixed_bar {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #000;
            border-top: 1px solid #555;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price_info {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>
    <div class="checkout_container">
        <h1>주문서</h1>

        <!-- 주소 및 연락처 섹션 -->
        <div class="address_section">
            <h2 class="section_title">기본 배송지</h2>
            <div class="address_info">
                <p><strong><?= htmlspecialchars($user_info['name']) ?></strong> 님의 기본 배송지</p>
                <p><?= htmlspecialchars($user_info['address']) ?> <?= htmlspecialchars($user_info['address_detail']) ?></p>
                <div class="user_phone">
                    <p>연락처: <?= htmlspecialchars($user_info['phone']) ?></p>
                </div>
                <a href="address_change.php" class="action-btn" style="margin-top:8px;">배송지 변경</a>
            </div>
        </div>

        <!-- 배송 요청사항 -->
        <div class="request_section">
            <h2 class="section_title">배송 요청사항</h2>
            <textarea rows="3" placeholder="예: 문 앞에 놔주세요, 배송 전에 연락 주세요 등"></textarea>
        </div>

        <!-- 주문 상품 목록 (direct_preview 상태) -->
        <?php if ($is_direct_buy && $action === 'direct_preview'): ?>
            <div class="orders_section">
                <h2 class="section_title">주문 상품 1개</h2>
                <p style="margin-bottom:16px;"><?= $estimated_shipping_date ?></p>
                <table>
                    <thead>
                        <tr>
                            <th>상품명</th>
                            <th>이미지</th>
                            <th>가격</th>
                            <th>수량</th>
                            <th>합계</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subtotal = $direct_product['price'] * $direct_product['quantity'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($direct_product['product_name']); ?></td>
                            <td>
                                <?php if ($direct_product['product_image']): ?>
                                    <img src="<?= htmlspecialchars($direct_product['product_image']); ?>" width="50">
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($direct_product['price']); ?>원</td>
                            <td><?= $direct_product['quantity']; ?></td>
                            <td><?= number_format($subtotal); ?>원</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 적립금 사용 및 결제하기 버튼 -->
            <div class="points_section">
                <h2 class="section_title">보유 적립금 사용</h2>
                <div class="points_info">
                    <p>사용 한도(7%): <?= number_format($max_point_use) ?>원</p>
                    <p>보유 포인트: <?= number_format($available_points) ?>원</p>
                    <form method="post" style="margin-top:8px;">
                        <input type="hidden" name="action" value="buy_direct">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($direct_product['id']) ?>">
                        <input type="hidden" name="quantity" value="<?= htmlspecialchars($direct_product['quantity']) ?>">
                        <input type="hidden" name="direct_buy" value="1">

                        <input type="number" name="use_points" min="0" max="<?= $max_usable_points ?>" value="0" style="width:100px;">
                        <p style="font-size:12px; color:#ccc; margin-top:8px;">최대 사용 가능 포인트: <?= number_format($max_usable_points) ?>원</p>

                        <div class="checkbox_area">
                            <p><input type="checkbox" checked> 주문 내용을 확인했으며 결제에 동의합니다.</p>
                            <p><input type="checkbox" checked> 개인정보 제3자 제공동의</p>
                            <p><input type="checkbox" checked> 전자결제대행 이용 동의</p>
                        </div>

                        <div class="bottom_fixed_bar">
                            <div class="price_info"><?= number_format($total) ?>원 결제하기</div>
                            <button type="submit" class="action-btn">결제하기</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
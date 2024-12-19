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

// 액션에 따라 구매 대상 상품 결정
$action = $_POST['action'] ?? '';

$cart_items = [];
$total = 0;
$direct_product = null; // 바로 구매 상품 정보 저장할 변수
$is_direct_buy = ($action === 'buy_direct');

// 바로 구매하기인 경우, product_id와 quantity를 받아 단일 상품 정보를 불러옴
if ($is_direct_buy) {
    $direct_product_id = intval($_POST['product_id'] ?? 0);
    $direct_quantity = intval($_POST['quantity'] ?? 1);

    // 상품 정보 조회
    $pstmt = $pdo->prepare("SELECT id, product_name, product_image, price FROM products WHERE id = :id");
    $pstmt->execute(['id' => $direct_product_id]);
    $direct_product = $pstmt->fetch(PDO::FETCH_ASSOC);

    if (!$direct_product) {
        die('상품을 찾을 수 없습니다.');
    }

    $direct_product['quantity'] = $direct_quantity;
    $subtotal = $direct_product['price'] * $direct_product['quantity'];
    $total = $subtotal;
} else {
    // 장바구니 구매인 경우
    $stmt = $pdo->prepare("
        SELECT c.id, c.product_id, c.quantity, p.product_name, p.product_image, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

// 배송 예정일 계산
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

// 최종 결제 처리 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'buy') {
    // 여기서는 최종 결제 처리
    $used_points = isset($_POST['use_points']) ? (int)$_POST['use_points'] : 0;
    if ($used_points > $max_usable_points) {
        $used_points = $max_usable_points;
    }

    $final_price = $total - $used_points;
    if ($final_price < 0) $final_price = 0;

    // 주문 생성 및 재고 반영
    // 바로 구매하기인 경우 direct_product 하나만 주문
    // 장바구니 구매인 경우 cart_items 전체 주문

    $pdo->beginTransaction();
    try {
        if ($is_direct_buy) {
            $ci = $direct_product;
            $ord = $pdo->prepare("INSERT INTO orders (user_id, product_name, product_image, price, product_id, status) VALUES (:user_id, :pn, :pi, :pr, :pid, 'pending')");
            $ord->execute([
                'user_id' => $user_id,
                'pn' => $ci['product_name'],
                'pi' => $ci['product_image'],
                'pr' => $ci['price'] * $ci['quantity'],
                'pid' => $ci['id']
            ]);

            // 재고 감소 및 판매량 증가
            $updPrd = $pdo->prepare("UPDATE products SET sold_count = sold_count + :qty, stock = stock - :qty WHERE id=:id AND stock >= :qty");
            $updPrd->execute(['qty' => $ci['quantity'], 'id' => $ci['id']]);

            // 바로 구매 시 favorites 제거 로직 (요청사항 없음, 필요시 추가)
        } else {
            // 장바구니 구매 로직
            $product_ids = [];
            foreach ($cart_items as $ci) {
                $ord = $pdo->prepare("INSERT INTO orders (user_id, product_name, product_image, price, product_id, status) VALUES (:user_id, :pn, :pi, :pr, :pid, 'pending')");
                $ord->execute([
                    'user_id' => $user_id,
                    'pn' => $ci['product_name'],
                    'pi' => $ci['product_image'],
                    'pr' => $ci['price'] * $ci['quantity'],
                    'pid' => $ci['product_id']
                ]);

                // 재고 감소 및 판매량 증가
                $updPrd = $pdo->prepare("UPDATE products SET sold_count = sold_count + :qty, stock = stock - :qty WHERE id=:id AND stock >= :qty");
                $updPrd->execute(['qty' => $ci['quantity'], 'id' => $ci['product_id']]);

                $product_ids[] = $ci['product_id'];
            }

            // 장바구니 비우기
            $delC = $pdo->prepare("DELETE FROM cart WHERE user_id=:uid");
            $delC->execute(['uid' => $user_id]);

            // 찜하기 해제
            if (!empty($product_ids)) {
                $in_placeholders = implode(',', array_fill(0, count($product_ids), '?'));
                $params = $product_ids;
                $params[] = $user_id;

                $delF = $pdo->prepare("DELETE FROM favorites WHERE product_id IN ($in_placeholders) AND user_id = ?");
                $delF->execute(array_merge($product_ids, [$user_id]));
            }
        }

        // 포인트 차감 처리
        if ($used_points > 0) {
            $new_balance = $available_points - $used_points;
            $desc = "상품 구매 포인트 사용";
            $insPoint = $pdo->prepare("INSERT INTO points (user_id, description, points, balance) VALUES (:uid, :desc, :pts, :bal)");
            $insPoint->execute(['uid' => $user_id, 'desc' => $desc, 'pts' => -$used_points, 'bal' => $new_balance]);
        }

        $pdo->commit();
        header("Location: mypage.php?message=구매가 완료되었습니다.");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('결제 처리 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "');history.back();</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <title>결제 페이지</title>
    <link rel="stylesheet" href="./style.css">
    <style>
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

        <!-- 주문 상품 목록 -->
        <div class="orders_section">
            <?php if ($is_direct_buy): ?>
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
            <?php else: ?>
                <h2 class="section_title">주문 상품 <?= count($cart_items) ?>개</h2>
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
                        <?php if (count($cart_items) === 0): ?>
                            <tr>
                                <td colspan="5">장바구니에 상품이 없습니다.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cart_items as $item):
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']); ?></td>
                                    <td>
                                        <?php if ($item['product_image']): ?>
                                            <img src="<?= htmlspecialchars($item['product_image']); ?>" width="50">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($item['price']); ?>원</td>
                                    <td><?= $item['quantity']; ?></td>
                                    <td><?= number_format($subtotal); ?>원</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 적립금 사용 -->
        <div class="points_section">
            <h2 class="section_title">보유 적립금 사용</h2>
            <div class="points_info">
                <p>사용 한도(7%): <?= number_format($max_point_use) ?>원</p>
                <p>보유 포인트: <?= number_format($available_points) ?>원</p>
                <form method="post" style="margin-top:8px;">
                    <!-- 최종 결제 시 buy 액션 -->
                    <input type="hidden" name="action" value="buy">
                    <?php if ($is_direct_buy): ?>
                        <!-- 바로 구매한 상품의 정보도 다시 넘겨서 결제 시 알 수 있게 함 -->
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($direct_product['id']) ?>">
                        <input type="hidden" name="quantity" value="<?= htmlspecialchars($direct_product['quantity']) ?>">
                        <input type="hidden" name="direct_buy" value="1">
                    <?php endif; ?>

                    <input type="number" name="use_points" min="0" max="<?= $max_usable_points ?>" value="0" style="width:100px;">
                    <p style="font-size:12px; color:#ccc; margin-top:8px;">최대 사용 가능 포인트: <?= number_format($max_usable_points) ?>원</p>

                    <div class="checkbox_area">
                        <p><input type="checkbox" checked> 주문 내용을 확인했으며 결제에 동의합니다.</p>
                        <p><input type="checkbox" checked> 개인정보 제3자 제공동의</p>
                        <p><input type="checkbox" checked> 전자결제대행 이용 동의</p>
                    </div>

                    <div class="bottom_fixed_bar">
                        <?php
                        $final_price = $total; // 결제 전 표시되는 금액
                        ?>
                        <div class="price_info"><?= number_format($final_price) ?>원 결제하기</div>
                        <button type="submit" class="action-btn">결제하기</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

if (!isset($_GET['id'])) {
    die('주문 ID가 지정되지 않았습니다.');
}

$order_id = intval($_GET['id']);

// 주문 정보 조회
$stmt = $pdo->prepare('
    SELECT o.id, o.product_name, o.product_image, o.price, o.order_date, o.status, p.id AS pid
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    WHERE o.id = :order_id AND o.user_id = :user_id
');
$stmt->execute(['order_id' => $order_id, 'user_id' => $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('해당 주문을 찾을 수 없거나 접근 권한이 없습니다.');
}

// 배송 정보 조회 (가상의 shipping 테이블)
$shipping_stmt = $pdo->prepare('SELECT carrier_name, tracking_number, shipping_events FROM shipping WHERE order_id = :order_id');
$shipping_stmt->execute(['order_id' => $order_id]);
$shipping = $shipping_stmt->fetch(PDO::FETCH_ASSOC);

// status에 따른 상태 텍스트
$status_text = match ($order['status']) {
    'pending' => '주문 접수 중',
    'processing' => '상품 준비 중',
    'shipped' => '배송 중',
    'delivered' => '배송 완료',
    'canceled' => '주문 취소',
    default => '알 수 없는 상태',
};

// 배송 이벤트 파싱 (JSON 형태라고 가정)
$events = [];
if ($shipping && !empty($shipping['shipping_events'])) {
    $events = json_decode($shipping['shipping_events'], true);
    if (!is_array($events)) {
        $events = [];
    }
}

// 이벤트를 최신순으로 정렬 (필요하다면)
usort($events, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 상태 - 주문 #<?php echo htmlspecialchars($order['id']); ?></title>
    <link rel="stylesheet" href="./style.css">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: sans-serif;
            margin: 0;
        }

        .order_status_container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .order_status_header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 24px;
            border-bottom: 1px solid #555;
            padding-bottom: 12px;
        }

        .order_info {
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .order_info img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .order_details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .status_text {
            font-size: 18px;
            font-weight: bold;
            color: #2ef3e1;
        }

        .shipping_info {
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .shipping_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .shipping_header h2 {
            font-size: 20px;
            font-weight: bold;
        }

        .shipping_events {
            background-color: #111;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .shipping_event {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 14px;
        }

        .shipping_event strong {
            color: #2ef3e1;
        }

        .back_link {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background-color: #2ef3e1;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .back_link:hover {
            background-color: #26d4c3;
        }

        .info_links {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .info_links a {
            font-size: 14px;
            color: #fff;
            text-decoration: underline;
        }

        .info_links a:hover {
            color: #2ef3e1;
        }

        .notice_box {
            background-color: #333;
            border-radius: 8px;
            padding: 16px;
            font-size: 14px;
            color: #ccc;
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>
    <div class="order_status_container">
        <h1 class="order_status_header">주문 #<?php echo htmlspecialchars($order['id']); ?> 상태</h1>

        <div class="order_info">
            <?php if (!empty($order['product_image'])): ?>
                <img src="<?php echo htmlspecialchars($order['product_image']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>">
            <?php endif; ?>
            <div class="order_details">
                <p><strong>상품명:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
                <p><strong>가격:</strong> <?php echo htmlspecialchars(number_format($order['price'])); ?>원</p>
                <p><strong>주문일:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                <p class="status_text">현재 상태: <?php echo htmlspecialchars($status_text); ?></p>
            </div>
        </div>

        <?php if ($shipping): ?>
            <div class="shipping_info">
                <div class="shipping_header">
                    <h2><?php echo htmlspecialchars($shipping['carrier_name']); ?> 배송 조회</h2>
                    <div>
                        <p><strong>송장 번호:</strong> <?php echo htmlspecialchars($shipping['tracking_number']); ?></p>
                    </div>
                </div>
                <div class="info_links">
                    <a href="#" onclick="alert('CJ대한통운 고객센터로 전화합니다.'); return false;">CJ대한통운 전화하기</a>
                    <a href="#" onclick="alert('배송 기사에게 전화합니다.'); return false;">배송 기사 전화하기</a>
                </div>
                <div class="shipping_events">
                    <?php foreach ($events as $event): ?>
                        <div class="shipping_event">
                            <strong><?php echo htmlspecialchars($event['location']); ?></strong>
                            <span><?php echo htmlspecialchars($event['date']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="notice_box">
                    • 배송 추적 서비스를 통해 제공받는 정보로 실 배송 현황과 차이가 있을 수 있습니다.<br>니
                    일반 배송 상품은 언제 배송되나요?<br>
                    배송 완료 상품을 받지 못했어요.<br>
                    배송 조회는 어떻게 하나요?<br>
                    주문제작 상품은 언제 배송 되나요?
                </div>
            </div>
        <?php else: ?>
            <div class="notice_box">
                현재 배송 정보가 없습니다.
            </div>
        <?php endif; ?>

        <a href="mypage.php" class="back_link">마이페이지로 돌아가기</a>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

// 상태 필터 처리
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $valid_statuses) ? $_GET['status'] : '';

// 주문 상태 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    if (in_array($new_status, $valid_statuses)) {
        $upd = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        if ($upd->execute(['status' => $new_status, 'id' => $order_id])) {
            $update_success = "주문 #$order_id 상태가 '" . htmlspecialchars($new_status) . "'(으)로 변경되었습니다.";
        } else {
            $update_error = "주문 상태 변경 중 오류가 발생했습니다.";
        }
    } else {
        $update_error = "유효하지 않은 상태 값입니다.";
    }
}

// 주문 목록 가져오기 (필터 적용)
$sql = "SELECT o.id, o.user_id, o.product_name, o.product_image, o.price, o.order_date, o.status, u.name AS username
        FROM orders o
        JOIN users u ON o.user_id = u.id";
$params = [];

if (!empty($filter_status)) {
    $sql .= " WHERE o.status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>주문 관리</h3>

<?php if (isset($update_success)): ?>
    <p class="success_msg"><?php echo htmlspecialchars($update_success); ?></p>
<?php endif; ?>
<?php if (isset($update_error)): ?>
    <p class="error_msg"><?php echo htmlspecialchars($update_error); ?></p>
<?php endif; ?>

<div style="margin-bottom:16px;">
    <form method="get" style="display:inline-block;">
        <input type="hidden" name="mode" value="orders">
        <select name="status" onchange="this.form.submit()">
            <option value="">전체 상태</option>
            <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>주문 접수 중</option>
            <option value="processing" <?= $filter_status == 'processing' ? 'selected' : '' ?>>상품 준비 중</option>
            <option value="shipped" <?= $filter_status == 'shipped' ? 'selected' : '' ?>>배송 중</option>
            <option value="delivered" <?= $filter_status == 'delivered' ? 'selected' : '' ?>>배송 완료</option>
            <option value="canceled" <?= $filter_status == 'canceled' ? 'selected' : '' ?>>주문 취소</option>
        </select>
        <noscript><input type="submit" value="필터 적용"></noscript>
    </form>
</div>

<table class="admin_table">
    <thead>
        <tr>
            <th>ID</th>
            <th>사용자</th>
            <th>상품명</th>
            <th>가격</th>
            <th>상태</th>
            <th>주문일</th>
            <th>상태 변경</th>
            <th>상세</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?> (UID: <?php echo htmlspecialchars($order['user_id']); ?>)</td>
                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                    <td><?php echo number_format($order['price']); ?>원</td>
                    <td><?php
                        $status = match ($order['status']) {
                            'pending' => '주문 접수 중',
                            'processing' => '상품 준비 중',
                            'shipped' => '배송 중',
                            'delivered' => '배송 완료',
                            'canceled' => '주문 취소',
                            default => '알 수 없는 상태'
                        };
                        echo htmlspecialchars($status);
                        ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td>
                        <form method="post" style="display:flex; gap:4px;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="new_status">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>주문 접수 중</option>
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>상품 준비 중</option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>배송 중</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>배송 완료</option>
                                <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>주문 취소</option>
                            </select>
                            <button type="submit" class="action_btn">변경</button>
                        </form>
                    </td>
                    <td>
                        <a href="order_status.php?id=<?php echo $order['id']; ?>" class="action_btn">상세보기</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="no_data">해당 상태의 주문이 없습니다.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
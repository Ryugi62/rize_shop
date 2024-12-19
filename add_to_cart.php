<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

$user_id = $_SESSION['user_id'];

// 만약 favorite_ids가 넘어왔다면(찜 목록에서 선택한 상품들), 해당 상품들을 장바구니에 추가 후 찜 목록에서 제거
if (isset($_POST['favorite_ids']) && is_array($_POST['favorite_ids']) && count($_POST['favorite_ids']) > 0) {
    $favorite_ids = $_POST['favorite_ids'];

    // favorite_ids에 해당하는 product_id 조회
    $in_placeholders = implode(',', array_fill(0, count($favorite_ids), '?'));
    $params = $favorite_ids;

    $stmt = $pdo->prepare("
        SELECT f.id as favorite_id, f.product_id, p.product_name, p.price 
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        WHERE f.id IN ($in_placeholders) AND f.user_id = ?
    ");
    $params[] = $user_id;
    $stmt->execute($params);
    $favorite_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($favorite_products) === 0) {
        die('선택한 찜 상품을 찾을 수 없습니다.');
    }

    // 트랜잭션 시작 (장바구니 추가 + 찜 목록 제거를 하나의 트랜잭션으로 처리)
    $pdo->beginTransaction();
    try {
        // 모두 장바구니에 추가 (수량 1로 가정)
        foreach ($favorite_products as $fp) {
            $product_id = intval($fp['product_id']);
            $quantity = 1; // 필요 시 사용자 입력 받거나 수정 가능

            // 장바구니 중복 확인
            $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id=:user_id AND product_id=:product_id");
            $check->execute(['user_id' => $user_id, 'product_id' => $product_id]);
            if ($check->rowCount() > 0) {
                $row = $check->fetch(PDO::FETCH_ASSOC);
                $newQty = $row['quantity'] + $quantity;
                $update = $pdo->prepare("UPDATE cart SET quantity=:qty WHERE id=:id");
                $update->execute(['qty' => $newQty, 'id' => $row['id']]);
            } else {
                $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $insert->execute(['user_id' => $user_id, 'product_id' => $product_id, 'quantity' => $quantity]);
            }
        }

        // 찜 목록에서 제거
        $del_params = $favorite_ids;
        $del_params[] = $user_id;
        $delete_fav = $pdo->prepare("DELETE FROM favorites WHERE id IN ($in_placeholders) AND user_id = ?");
        $delete_fav->execute($del_params);

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die('오류가 발생했습니다: ' . $e->getMessage());
    }

    header("Location: cart.php?message=선택한 찜 상품이 장바구니에 담기고 찜목록에서 제거되었습니다.");
    exit;
}

// 위 로직에 해당하지 않으면 단일 상품 추가 로직 진행
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    die('상품 ID와 수량이 필요합니다.');
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if ($quantity < 1) {
    $quantity = 1;
}

// 제품 존재 여부 확인
$stmt = $pdo->prepare("SELECT id FROM products WHERE id=:id");
$stmt->execute(['id' => $product_id]);
if ($stmt->rowCount() === 0) {
    die('상품을 찾을 수 없습니다.');
}

// 장바구니 중복 확인
$check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id=:user_id AND product_id=:product_id");
$check->execute(['user_id' => $user_id, 'product_id' => $product_id]);
if ($check->rowCount() > 0) {
    // 이미 장바구니에 있으면 수량 증가
    $row = $check->fetch(PDO::FETCH_ASSOC);
    $newQty = $row['quantity'] + $quantity;
    $update = $pdo->prepare("UPDATE cart SET quantity=:qty WHERE id=:id");
    $update->execute(['qty' => $newQty, 'id' => $row['id']]);
} else {
    // 장바구니에 추가
    $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
    $insert->execute(['user_id' => $user_id, 'product_id' => $product_id, 'quantity' => $quantity]);
}

header("Location: cart.php?message=장바구니에 담겼습니다.");

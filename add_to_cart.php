<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    die('상품 ID와 수량이 필요합니다.');
}

$user_id = $_SESSION['user_id'];
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

// 장바구니에 같은 상품이 있는지 확인
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
exit;

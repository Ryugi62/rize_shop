<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

if (!isset($_POST['product_id'])) {
    die('상품 ID가 필요합니다.');
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);

// 이미 찜했는지 체크
$check = $pdo->prepare("SELECT id FROM favorites WHERE user_id=:user_id AND product_id=:product_id");
$check->execute(['user_id' => $user_id, 'product_id' => $product_id]);

if ($check->rowCount() > 0) {
    // 이미 찜한 상품이면 원래 페이지로 돌아가기
    $referer = $_SERVER['HTTP_REFERER'] ?? 'product.php';
    // favorited=already 로 알려줄 수도 있지만 여기선 메시지 생략
    header("Location: {$referer}?favorited=1");
    exit;
}

// 제품 정보 가져오기
$stmt = $pdo->prepare("SELECT product_name, product_image, price FROM products WHERE id=:id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('상품을 찾을 수 없습니다.');
}

// 찜하기 추가
$insert = $pdo->prepare("INSERT INTO favorites (user_id, product_id, product_name, product_image, price) VALUES (:user_id, :product_id, :product_name, :product_image, :price)");
$insert->execute([
    'user_id' => $user_id,
    'product_id' => $product_id,
    'product_name' => $product['product_name'],
    'product_image' => $product['product_image'],
    'price' => $product['price']
]);

// 찜 추가 완료 후 원래 페이지로 돌아가기
$referer = $_SERVER['HTTP_REFERER'] ?? 'product.php';
header("Location: {$referer}?favorited=1");
exit;

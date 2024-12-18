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
    // 이미 찜한 상품이면 찜 목록으로 리다이렉트
    header("Location: mypage.php?message=이미 찜한 상품입니다.");
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

header("Location: mypage.php?message=찜 목록에 추가되었습니다.");
exit;

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

$referer = $_SERVER['HTTP_REFERER'] ?? 'product.php';

if ($check->rowCount() > 0) {
    // 이미 찜한 상품이면 찜 해제
    $delete = $pdo->prepare("DELETE FROM favorites WHERE user_id=:user_id AND product_id=:product_id");
    $delete->execute(['user_id' => $user_id, 'product_id' => $product_id]);

    // 찜 해제 후 돌아감
    // AJAX 요청 등에 대비하려면 JSON응답 필요하지만 여기서는 리다이렉트
    // 요청사항에는 JSON응답이라 언급없으므로 이전 구현처럼 JSON응답 가정
    // 여기서는 리다이렉트 안하고 JSON응답 가정
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'favorited' => false]);
    exit;
}

// 아직 찜하지 않은 경우 제품 정보 가져오기
$stmt = $pdo->prepare("SELECT product_name, product_image, price FROM products WHERE id=:id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => '상품을 찾을 수 없습니다.']);
    exit();
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

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'favorited' => true]);
exit;

<?php
session_start();
require_once './config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.');history.back();</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$content = trim($_POST['content'] ?? '');

if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($content)) {
    echo "<script>alert('모든 필드를 올바르게 입력해주세요.');history.back();</script>";
    exit();
}

// 구매 이력 확인
$purchase_check = $pdo->prepare("
    SELECT id FROM orders 
    WHERE user_id=:uid AND product_id=:pid 
    AND status IN ('pending','processing','shipped','delivered')
");
$purchase_check->execute(['uid' => $user_id, 'pid' => $product_id]);
if ($purchase_check->rowCount() === 0) {
    echo "<script>alert('해당 상품을 구매한 이력이 없습니다.');history.back();</script>";
    exit();
}

// 리뷰 등록 (title은 임의로 설정)
$stmt = $pdo->prepare("INSERT INTO posts (user_id, product_id, post_type, title, content, rating, created_at) VALUES (:user_id, :product_id, 'review', :title, :content, :rating, NOW())");
$stmt->execute([
    'user_id' => $user_id,
    'product_id' => $product_id,
    'title' => '상품 리뷰',
    'content' => $content,
    'rating' => $rating
]);

echo "<script>alert('리뷰가 등록되었습니다.');location.href='mypage.php';</script>";
exit();

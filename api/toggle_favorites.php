<?php
session_start();
include('../config/db.php'); // 실제 경로에 맞게 수정

// 에러 표시 끄기
ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '로그인 필요']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => '상품 ID 없음']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);

try {
    // 찜 상태 확인
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id=:uid AND product_id=:pid");
    $stmt->execute(['uid' => $user_id, 'pid' => $product_id]);

    if ($stmt->rowCount() > 0) {
        // 이미 찜했으면 삭제
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id=:uid AND product_id=:pid");
        $del->execute(['uid' => $user_id, 'pid' => $product_id]);
        echo json_encode(['status' => 'ok', 'favorited' => false]);
    } else {
        // 찜 안했으면 추가
        $pstmt = $pdo->prepare("SELECT product_name, product_image, price FROM products WHERE id=:id");
        $pstmt->execute(['id' => $product_id]);
        $pinfo = $pstmt->fetch(PDO::FETCH_ASSOC);

        if ($pinfo) {
            $ins = $pdo->prepare("INSERT INTO favorites (user_id, product_id, product_name, product_image, price) VALUES (:uid,:pid,:pn,:pi,:pr)");
            $ins->execute([
                'uid' => $user_id,
                'pid' => $product_id,
                'pn' => $pinfo['product_name'],
                'pi' => $pinfo['product_image'],
                'pr' => $pinfo['price']
            ]);
            echo json_encode(['status' => 'ok', 'favorited' => true]);
        } else {
            echo json_encode(['status' => 'error', 'message' => '상품 정보 없음']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB오류: ' . $e->getMessage()]);
}

<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

$user_id = $_SESSION['user_id'];

// 수량 변경
if (isset($_POST['qty'])) {
    foreach ($_POST['qty'] as $cart_id => $q) {
        $quantity = intval($q);
        if ($quantity < 1) $quantity = 1;
        $upd = $pdo->prepare("UPDATE cart SET quantity=:quantity WHERE id=:id AND user_id=:user_id");
        $upd->execute(['quantity' => $quantity, 'id' => $cart_id, 'user_id' => $user_id]);
    }
}

// 삭제 처리
if (isset($_POST['del'])) {
    $del_ids = $_POST['del'];
    foreach ($del_ids as $did) {
        $del = $pdo->prepare("DELETE FROM cart WHERE id=:id AND user_id=:user_id");
        $del->execute(['id' => $did, 'user_id' => $user_id]);
    }
}

header("Location: cart.php");
exit;

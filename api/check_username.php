<?php
include '../confing/db.php';

if (isset($_POST['username'])) {
    $username = $_POST['username'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "이미 사용 중인 아이디입니다."]);
    } else {
        echo json_encode(["status" => "success", "message" => "사용 가능한 아이디입니다."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "아이디를 입력하세요."]);
}

<?php
include '../confing/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = $_POST['name'];
    $birthdate = $_POST['birthdate'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $interests = isset($_POST['interests']) ? implode(", ", $_POST['interests']) : "";

    // 비밀번호 확인
    if ($password !== $confirm_password) {
        die("비밀번호가 일치하지 않습니다.");
    }

    // 비밀번호 해싱
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // 회원가입 쿼리 실행
        $stmt = $pdo->prepare("INSERT INTO users (username, password, name, birthdate, email, phone, address, gender, interests)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $name, $birthdate, $email, $phone, $address, $gender, $interests]);

        echo "회원가입이 완료되었습니다!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "이미 존재하는 아이디 또는 이메일입니다.";
        } else {
            echo "회원가입 중 오류가 발생했습니다: " . $e->getMessage();
        }
    }
}

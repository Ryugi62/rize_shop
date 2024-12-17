<?php
$host = "localhost";
$dbname = "shopping_mall";
$username = "root";
$password = "dydehsqjfdl123!@#"; // 비밀번호를 설정한 값으로 변경하세요.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("데이터베이스 연결 실패: " . $e->getMessage());
}

<?php
session_start();
include '../config/db.php'; // 데이터베이스 연결

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['ID']; // 로그인 폼의 ID 필드
    $password = $_POST['password']; // 로그인 폼의 비밀번호 필드

    try {
        // 사용자 조회 쿼리
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 비밀번호 검증
            if (password_verify($password, $user['password'])) {
                // 세션 설정
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;


                // 로그인 성공
                echo "<script>
                    window.location.href = '/index.php'; // 메인 페이지로 리디렉션
                </script>";
                exit;
            } else {
                echo "<script>
                    alert('비밀번호가 일치하지 않습니다.');
                    window.history.back();
                </script>";
                exit;
            }
        } else {
            echo "<script>
                alert('존재하지 않는 아이디입니다.');
                window.history.back();
            </script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "서버 오류: " . $e->getMessage();
    }
} else {
    echo "<script>
        alert('잘못된 접근입니다.');
        window.history.back();
    </script>";
    exit;
}

<?php
session_start();
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

$user_id = $_SESSION['user_id'];

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 사용자 정보 가져오기 (address, address_detail 필드만 사용)
$stmt = $pdo->prepare("SELECT address, address_detail FROM users WHERE id=:uid");
$stmt->execute(['uid' => $user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_info) {
    die('사용자 정보를 찾을 수 없습니다.');
}

// POST 요청 시 주소 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 체크
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF 토큰 검증 실패');
    }

    $new_address = trim($_POST['address']);
    $new_address_detail = trim($_POST['address_detail']);

    if (empty($new_address)) {
        $error = "주소는 필수 입력 항목입니다.";
    } else {
        // 주소 업데이트
        $upd = $pdo->prepare("UPDATE users SET address=:address, address_detail=:address_detail WHERE id=:uid");
        $upd->execute([
            'address' => $new_address,
            'address_detail' => $new_address_detail,
            'uid' => $user_id
        ]);
        // 업데이트 후 리다이렉트
        header("Location: checkout.php?message=주소가 변경되었습니다.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <title>주소 변경</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        body {
            background: #000;
            color: #fff;
            margin: 0;
            font-family: sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 16px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #555;
            padding-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #555;
            background: #000;
            color: #fff;
        }

        .action-btn {
            background: var(--main);
            color: #000;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            background: var(--main-hover);
        }

        .error {
            color: red;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group button {
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>
    <div class="container">
        <h1>주소 변경</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="address">주소</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($user_info['address'] ?? '') ?>" readonly required>
                <button type="button" class="action-btn" onclick="findAddress()">주소찾기</button>
            </div>

            <div class="form-group">
                <label for="address_detail">상세주소</label>
                <input type="text" id="address_detail" name="address_detail" value="<?= htmlspecialchars($user_info['address_detail'] ?? '') ?>" placeholder="상세주소를 입력해주세요" required>
            </div>

            <button type="submit" class="action-btn">주소 변경</button>
        </form>
    </div>
    <?php include("./Components/FooterComponents.php"); ?>

    <!-- 카카오 주소 API 스크립트 -->
    <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script>
        function findAddress() {
            new daum.Postcode({
                oncomplete: function(data) {
                    // data.address: 사용자가 선택한 최종 주소
                    document.getElementById("address").value = data.address;
                    document.getElementById("address_detail").focus();
                }
            }).open();
        }
    </script>
</body>

</html>
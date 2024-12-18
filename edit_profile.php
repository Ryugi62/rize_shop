<?php
// edit_profile.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    die('로그인이 필요합니다.');
}

$user_id = $_SESSION['user_id'];

// 사용자 정보 가져오기
$query = $pdo->prepare('SELECT username, email, name, birthdate, phone, address, address_detail, gender, interests FROM users WHERE id = :user_id');
$query->execute(['user_id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('사용자 정보를 찾을 수 없습니다.');
}

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 입력 데이터 가져오기 및 검증
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $birthdate = trim($_POST['birthdate']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $address_detail = trim($_POST['address_detail']);
    $gender = trim($_POST['gender']);
    $interests = trim($_POST['interests']);

    // 기본적인 검증 (추가적인 검증 로직을 추가할 수 있습니다)
    $errors = [];

    if (empty($username)) {
        $errors[] = '사용자 이름을 입력해주세요.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '유효한 이메일을 입력해주세요.';
    }

    if (empty($name)) {
        $errors[] = '이름을 입력해주세요.';
    }

    if (empty($birthdate)) {
        $errors[] = '생년월일을 입력해주세요.';
    }

    if (empty($phone)) {
        $errors[] = '전화번호를 입력해주세요.';
    }

    if (empty($address)) {
        $errors[] = '주소를 입력해주세요.';
    }

    if (empty($gender) || !in_array($gender, ['male', 'female'])) {
        $errors[] = '성별을 선택해주세요.';
    }

    // 에러가 없으면 업데이트 수행
    if (empty($errors)) {
        $update_query = $pdo->prepare('UPDATE users SET username = :username, email = :email, name = :name, birthdate = :birthdate, phone = :phone, address = :address, address_detail = :address_detail, gender = :gender, interests = :interests WHERE id = :user_id');
        $result = $update_query->execute([
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'birthdate' => $birthdate,
            'phone' => $phone,
            'address' => $address,
            'address_detail' => $address_detail,
            'gender' => $gender,
            'interests' => $interests,
            'user_id' => $user_id
        ]);

        if ($result) {
            // 업데이트 성공 후 리다이렉트
            header('Location: mypage.php');
            exit;
        } else {
            $errors[] = '프로필 업데이트에 실패했습니다. 다시 시도해주세요.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로필 수정 - RISZE</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        /* 기본 스타일 유지 */

        .edit_profile_container {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: var(--light-black);
            border-radius: 8px;
            color: var(--white);
        }

        .edit_profile_container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 10px;
        }

        .edit_profile_form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .edit_profile_form label {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .edit_profile_form input,
        .edit_profile_form select,
        .edit_profile_form textarea {
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid var(--gray);
            border-radius: 4px;
            background-color: var(--dark-black);
            color: var(--white);
        }

        .edit_profile_form textarea {
            resize: vertical;
            height: 100px;
        }

        .error_messages {
            background-color: #ffdddd;
            border-left: 6px solid #f44336;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .error_messages ul {
            margin: 0;
            padding-left: 20px;
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .edit_profile_container {
                padding: 10px;
            }
        }

        .non-editable {
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid var(--gray);
            border-radius: 4px;
            background-color: var(--dark-black);
            color: var(--white);
            display: inline-block;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="edit_profile_container">
            <h2>프로필 수정</h2>

            <?php if (!empty($errors)): ?>
                <div class="error_messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="edit_profile_form" method="POST" action="edit_profile.php">
                <label for="username">아이디</label>
                <div class="form-group">
                    <span id="username" class="non-editable"><?= htmlspecialchars($user['username']); ?></span>
                </div>


                <label for="email">이메일</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

                <label for="name">이름</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>

                <label for="birthdate">생년월일</label>
                <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['birthdate']); ?>" required>

                <label for="phone">전화번호</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>

                <label for="address">주소</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address']); ?>" readonly required>
                <button type="button" onclick="findAddress()" class="white_button">주소찾기</button>

                <label for="address_detail">상세주소</label>
                <input type="text" id="address_detail" name="address_detail" value="<?= htmlspecialchars($user['address_detail']); ?>" placeholder="상세주소를 입력해주세요" required>

                <label for="gender">성별</label>
                <select id="gender" name="gender" required>
                    <option value="">선택</option>
                    <option value="male" <?= ($user['gender'] === 'male') ? 'selected' : ''; ?>>남자</option>
                    <option value="female" <?= ($user['gender'] === 'female') ? 'selected' : ''; ?>>여자</option>
                </select>

                <label for="interests">관심사</label>
                <textarea id="interests" name="interests"><?= htmlspecialchars($user['interests']); ?></textarea>

                <button type="submit" class="white_button">수정 완료</button>
                <button type="reset" class="white_button">초기화</button>
            </form>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>

    <!-- 카카오 주소 API 스크립트 -->
    <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

    <script>
        // 카카오 주소 검색 API
        function findAddress() {
            new daum.Postcode({
                oncomplete: function(data) {
                    document.getElementById("address").value = data.address;
                    document.getElementById("address_detail").focus(); // 상세주소 필드로 자동 포커스 이동
                }
            }).open();
        }
    </script>
</body>

</html>
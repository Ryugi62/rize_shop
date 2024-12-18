<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>
    <!-- import style -->
    <link rel="stylesheet" href="./style.css">

    <style>
        .register_view {
            height: 900px;
            align-items: center;
            justify-content: center;

            h2 {
                color: var(--white);
                font-size: 2rem;
                text-align: center;
                margin-bottom: 1.5rem;
            }
        }

        .form-group {
            gap: 1rem;
            display: flex;
            width: 100%;
            align-items: center;
        }

        label {
            font-size: 0.9rem;
            color: var(--white);
            min-width: 80px;
        }

        input,
        select {
            flex: 1;
            font-size: 1rem;
        }

        button {
            padding: 0.8rem 1rem;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .form-group button {
            margin-left: auto;
            flex-shrink: 0;
        }

        input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .checkbox-group {
            gap: 1rem;
            display: flex;
            align-items: center;
        }

        .view {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            padding: 2rem;
            border-radius: 8px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        select {
            width: 100%;
            max-width: 400px;
            font-size: 1rem;
            color: var(--black);
            padding: 0.5rem;
        }

        input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
        }

        button[type="submit"] {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <div class="register_view view">
            <h2>회원가입</h2>
            <form action="/api/register.php" method="post">
                <div class="form-group">
                    <label for="username">아이디</label>
                    <input type="text" id="username" name="username" required>
                    <button type="button" onclick="checkUsername()">중복확인</button>
                </div>
                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">비밀번호 확인</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="name">이름</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="birthdate">생년월일</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>
                <div class="form-group">
                    <label for="email">이메일</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">휴대폰 번호</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="address">주소</label>
                    <input type="text" id="address" name="address" readonly required>
                    <button type="button" onclick="findAddress()">주소찾기</button>
                </div>
                <div class="form-group">
                    <label for="address_detail">상세주소</label>
                    <input type="text" id="address_detail" name="address_detail" placeholder="상세주소를 입력해주세요" required>
                </div>
                <div class="form-group">
                    <label for="gender">성별</label>
                    <select id="gender" name="gender" required>
                        <option value="">선택</option>
                        <option value="male">남자</option>
                        <option value="female">여자</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="white_button">회원가입</button>
                    <button type="reset">초기화</button>
                </div>
            </form>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

<!-- 카카오 주소 API 스크립트 -->
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script>
    // 아이디 중복 확인
    function checkUsername() {
        const username = document.getElementById("username").value;

        if (!username) {
            alert("아이디를 입력하세요.");
            return;
        }

        fetch('/api/check_username.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `username=${encodeURIComponent(username)}`
            })
            .then(response => response.text())
            .then(data => {
                console.log("서버 응답:", data);
                try {
                    const jsonData = JSON.parse(data);
                    alert(jsonData.message);
                } catch (error) {
                    console.error("JSON 파싱 에러:", error, data);
                    alert("서버 응답이 잘못되었습니다. 관리자에게 문의하세요.");
                }
            })
            .catch(error => {
                console.error("에러 발생:", error);
                alert("아이디 중복 확인 중 문제가 발생했습니다.");
            });
    }

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

</html>
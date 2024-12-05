<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>

    <!-- import style -->
    <link rel="stylesheet" href="./style.css">

    <style>
        .login_view {

            * {
                /* border: 1px solid red; */
            }

            h2 {
                font-size: 2rem;
                color: var(--white);
                margin-bottom: 3rem;
                text-align: center;
            }

            form {
                display: flex;
                flex-direction: column;
                height: 100%;
                display: flex;
                align-items: center;
                padding-right: 4rem;
                justify-content: center;
            }

            .form-group {
                gap: 1rem;
                display: flex;
                flex-direction: column;
            }

            input {
                width: 300px;
                color: var(--black);
                background-color: var(--light-gray);
            }
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <div class="login_view view">


            <form action="/api/login.php" method="POST">
                <h2>로그인</h2>

                <div class="form-group">
                    <label for="ID">아이디</label>
                    <input type="text" name="ID" id="ID" required>

                    <label for="password">비밀번호</label>
                    <input type="password" name="password" id="password" required>

                    <button type="submit">Login</button>

                    <a href="register.php">Register</a>

                    <a href="forgotPassword.php">Forgot Password</a>
                </div>
            </form>


        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

</html>
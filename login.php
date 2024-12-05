<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>

    <!-- import style -->
    <link rel="stylesheet" href="./style.css">

    <style>
        main {
            flex: 1;

            * {
                /* border: 1px solid red; */
            }

            .login_view {
                height: 100%;
            }

            form {
                height: 100%;
                display: flex;
                align-items: center;
                padding-right: 150px;
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
        <div class="login_view">

            <form action="/api/login.php" method="POST">
                <div class="form-group">
                    <label for="ID">아이디</label>
                    <input type="text" name="ID" id="ID" required>

                    <label for="password">비밀번호</label>
                    <input type="password" name="password" id="password" required>

                    <button type="submit">Login</button>

                    <a href="register.php">Register</a>

                    <a href="forgot-password.php">Forgot Password</a>
                </div>
            </form>


        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

</html>
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
                border: 1px solid red;
            }

            .login_view {
                height: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <div class="login_view">
        </div>
    </main>

    <?php include("./Components/FooterComponents.php") ?>
</body>

</html>
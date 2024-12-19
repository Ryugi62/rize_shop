<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // 세션 시작
}

include('./config/db.php'); // db.php 연결 (이미 있다면 생략)

// 로그인 상태 확인
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// 장바구니 개수 조회
$cart_count = 0;
if ($isLoggedIn) {
    $user_id = $_SESSION['user_id'];
    $cart_count_stmt = $pdo->prepare("SELECT SUM(quantity) AS cnt FROM cart WHERE user_id = :uid");
    $cart_count_stmt->execute(['uid' => $user_id]);
    $row = $cart_count_stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = (int)($row['cnt'] ?? 0);
}
?>

<header class="header_component">
    <div class="header_viewer">
        <a href="/" class="logo">RIZZ</a>

        <!-- 메뉴 리스트 -->
        <ul class="header_menu">
            <li class="index_menu"><a href="/">메인</a></li>
            <li class="product_menu"><a href="/product.php">상품</a></li>
            <li class="board_menu"><a href="/board.php">게시판</a></li>
            <?php if ($isLoggedIn): ?>
                <li class="mypage_menu"><a href="/mypage.php">마이페이지</a></li>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
                <li class="admin_menu"><a href="/admin.php">관리자페이지</a></li>
            <?php endif; ?>
        </ul>


        <!-- 로그인 상태에 따라 메뉴 변경 -->
        <ul class="header_menu login">
            <?php if ($isLoggedIn): ?>
                <li><a href="/api/logout.php">로그아웃</a></li>
            <?php else: ?>
                <li><a href="/login.php">로그인</a></li>
                <li><a href="/register.php">회원가입</a></li>
            <?php endif; ?>
        </ul>

        <!-- 장바구니 아이콘 -->
        <?php if ($isLoggedIn): ?>
            <div class="cart_icon_wrapper">
                <a href="/cart.php" class="cart_link">
                    <span class="cart_icon">🛒</span>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart_count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>

<script>
    const path = window.location.pathname === "/" ? "/index.php" : window.location.pathname;
    const page = path.split("/").pop().split("?")[0];
    const active_menu = document.querySelector(`.${page.split(".")[0]}_menu`);
    if (active_menu) {
        active_menu.querySelector("a").classList.add("active");
    }
</script>

<style>
    .header_component {
        top: 0;
        z-index: 1000;
        position: sticky;
        background-color: var(--black);
        border-bottom: 1px solid var(--light-gray);
    }

    .header_viewer {
        margin: 0 auto;
        display: flex;
        padding: 16px;
        max-width: 1200px;
        align-items: center;
    }

    .logo {
        cursor: pointer;
        font-size: 24px;
        font-weight: bold;
        color: var(--main);
    }

    .header_menu {
        display: flex;
        margin-left: 16px;
        gap: 16px;
        align-items: center;
    }

    .header_menu li {
        list-style: none;
    }

    .header_menu li a {
        cursor: pointer;
        color: var(--white);
    }

    .header_menu li a:hover {
        color: var(--white-hover);
        text-decoration: underline;
    }

    .header_menu li a:active {
        color: var(--white-active);
    }

    .header_menu li a.active {
        color: var(--main);
        text-decoration: underline;
    }

    .cart_icon_wrapper {
        margin-left: 16px;
        margin-right: 16px;
        position: relative;
    }

    .cart_link {
        text-decoration: none;
        color: #fff;
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .cart_icon {
        font-size: 24px;
    }

    .cart_count {
        position: absolute;
        bottom: -2px;
        right: -8px;
        background: red;
        color: #fff;
        font-size: 12px;
        font-weight: bold;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login {
        margin-left: auto;
    }
</style>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // 세션 시작
}

// 로그인 상태 확인 (세션은 이미 시작된 상태라고 가정)
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // 관리자 여부 확인
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
        <ul class="header_menu">
            <?php if ($isLoggedIn): ?>
                <li><a href="/api/logout.php">로그아웃</a></li>
            <?php else: ?>
                <li><a href="/login.php">로그인</a></li>
                <li><a href="/register.php">회원가입</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<script>
    // 현재 페이지의 경로를 가져옵니다.
    const path = window.location.pathname === "/" ? "/index.php" : window.location.pathname;

    // 쿼리 파라미터를 제거하고, 파일 이름만 추출
    const page = path.split("/").pop().split("?")[0];

    // active 클래스를 추가할 메뉴 항목을 찾습니다.
    const active_menu = document.querySelector(`.${page.split(".")[0]}_menu`);

    // active 메뉴가 있으면 클래스를 추가하고, 없으면 기본 메뉴(메인)에 active 클래스를 추가합니다.
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
        margin-left: auto;
    }

    .header_menu li {
        margin-left: 16px;
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
</style>
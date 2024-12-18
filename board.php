<?php
// board.php

session_start();
require_once './config/db.php'; // 데이터베이스 연결 설정 포함

// 현재 모드에 따라 필터링 (전체, 공지사항, 리뷰, Q&A)
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

// 유효한 모드인지 확인
$valid_modes = ['notice', 'review', 'qna'];
if ($mode && !in_array($mode, $valid_modes)) {
    // 유효하지 않은 모드일 경우 기본값으로 설정
    $mode = '';
}

// 게시물 조회 쿼리
if ($mode) {
    $sql = "SELECT p.id, u.username, p.post_type, p.title, p.content, p.created_at, p.view 
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.post_type = :post_type
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':post_type' => $mode]);
} else {
    // 전체 게시물 조회
    $sql = "SELECT p.id, u.username, p.post_type, p.title, p.content, p.created_at, p.view 
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sql);
}

$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>게시판 - RISZE</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        /* 기존 style.css 유지 */

        .board_view {
            margin-top: 40px;
            width: 100%;
            max-width: 1200px;
        }

        .section_header {
            width: 100%;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 16px;
            padding-bottom: 8px;
            justify-content: space-between;
        }

        .section_title {
            font-size: 24px;
            font-weight: bold;
            color: var(--white);
        }

        .board_mode {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .board_mode a {
            flex: 1;
            text-align: center;
            padding: 15px;
            font-size: 16px;
            color: var(--white);
            border-radius: 8px;
            margin: 0 5px;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            border: 1px solid var(--white);
        }

        .board_mode a:hover {
            background-color: var(--black-hover);
        }

        .board_mode a.active {
            background-color: var(--white);
            color: var(--black);
            font-weight: bold;
        }

        .table_box {
            width: 100%;
            min-height: 750px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        thead {
            color: var(--black);
            background-color: var(--main);
        }

        th,
        td {
            padding: 15px;
            font-size: 16px;
            text-align: center;
            border-bottom: 1px solid var(--gray);
        }

        th {
            color: var(--black);
            font-weight: bold;
            background-color: var(--white);
        }

        tbody tr:hover {
            cursor: pointer;
            background-color: var(--black-hover);
        }

        .post_title {
            text-align: left;
            color: var(--white);
        }

        .navigation {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .navigation a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            color: var(--white);
            background-color: var(--light-black);
            border-radius: 50%;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }

        .navigation a:hover {
            background-color: var(--white);
            color: var(--black);
        }

        .navigation a.active {
            background-color: var(--white);
            color: var(--black);
            font-weight: bold;
        }

        a.write_button {
            padding: 10px 20px;
            background-color: var(--main);
            color: var(--black);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s;
            text-decoration: none;
        }

        a.write_button:hover {
            background-color: var(--main-hover);
            color: var(--black-hover);
        }

        a.write_button:active {
            background-color: var(--main-active);
            color: var(--black-active);
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="board_view view">
            <!-- 게시판의 모드를 정하는 4개의 탭: 전체, 공지사항, 리뷰, Q&A -->
            <div class="section_header">
                <h2 class="section_title">게시판</h2>
                <!-- 글쓰기 버튼 추가 -->
                <a href="./write.php" class="write_button">글쓰기</a>
            </div>
            <div class="board_mode">
                <a href="./board.php" class="<?= ($mode === '') ? 'active' : '' ?>">전체</a>
                <a href="./board.php?mode=notice" class="<?= ($mode === 'notice') ? 'active' : '' ?>">공지사항</a>
                <a href="./board.php?mode=review" class="<?= ($mode === 'review') ? 'active' : '' ?>">리뷰</a>
                <a href="./board.php?mode=qna" class="<?= ($mode === 'qna') ? 'active' : '' ?>">Q & A</a>
            </div>
            <div class="table_box">
                <table>
                    <thead>
                        <tr>
                            <th>번호</th>
                            <th>제목</th>
                            <th>작성자</th>
                            <th>작성일</th>
                            <th>조회수</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $row): ?>
                                <tr onclick="window.location='./board_view.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>'" style="cursor:pointer;">
                                    <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="post_title"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['view'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">게시물이 없습니다.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 네비게이션 (페이징 기능은 추가하지 않았습니다) -->
            <div class="navigation">
                <!-- 페이징 기능을 구현하려면 추가적인 코드가 필요합니다 -->
                <a href="#" title="처음">⏮</a>
                <a href="#" title="이전">◀</a>
                <a href="#" class="active" title="1페이지">1</a>
                <a href="#" title="2페이지">2</a>
                <a href="#" title="3페이지">3</a>
                <a href="#" title="다음">▶</a>
                <a href="#" title="끝">⏭</a>
            </div>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
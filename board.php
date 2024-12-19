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

// 페이징을 위한 변수 설정
$limit = 10; // 페이지당 게시물 수
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 게시물 조회 쿼리
if ($mode) {
    // 현재 모드에 해당하는 게시물 수
    $count_sql = "SELECT COUNT(*) FROM posts WHERE post_type = :post_type";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute([':post_type' => $mode]);
    $total_posts = $count_stmt->fetchColumn();

    // 페이지당 게시물 수 만큼 조회
    $sql = "SELECT p.id, u.username, p.post_type, p.title, p.content, p.created_at, p.view 
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.post_type = :post_type
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':post_type', $mode, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    // 전체 게시물 수
    $count_sql = "SELECT COUNT(*) FROM posts";
    $count_stmt = $pdo->query($count_sql);
    $total_posts = $count_stmt->fetchColumn();

    // 페이지당 게시물 수 만큼 조회
    $sql = "SELECT p.id, u.username, p.post_type, p.title, p.content, p.created_at, p.view 
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
}

$posts = $stmt->fetchAll();

// 전체 페이지 수 계산
$total_pages = ceil($total_posts / $limit);
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
            flex-wrap: wrap;
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

        .navigation a.active_page {
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
            width: 100%;
            text-align: center;
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
            </div>
            <div class="board_mode">
                <a href="./board.php?mode=all<?= isset($_GET['page']) ? '&page=' . htmlspecialchars($_GET['page']) : '' ?>" class="<?= ($mode === 'all' || $mode === '') ? 'active' : ' ' ?>">전체</a>
                <a href="./board.php?mode=notice<?= isset($_GET['page']) ? '&page=' . htmlspecialchars($_GET['page']) : '' ?>" class="<?= ($mode === 'notice') ? 'active' : '' ?>">공지사항</a>
                <a href="./board.php?mode=qna<?= isset($_GET['page']) ? '&page=' . htmlspecialchars($_GET['page']) : '' ?>" class="<?= ($mode === 'qna') ? 'active' : '' ?>">Q & A</a>
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

            <a href="./write.php?post_type=qna" class="write_button">글쓰기</a>


            <!-- 네비게이션 (페이징 기능) -->
            <?php if ($total_pages > 1): ?>
                <div class="navigation">
                    <!-- 처음 페이지로 -->
                    <?php if ($page > 1): ?>
                        <a href="<?= './board.php' . ($mode ? '?mode=' . htmlspecialchars($mode) . '&' : '?') . 'page=1' ?>" title="처음">⏮</a>
                    <?php else: ?>
                        <a href="#" title="처음" style="pointer-events: none; opacity: 0.5;">⏮</a>
                    <?php endif; ?>

                    <!-- 이전 페이지로 -->
                    <?php if ($page > 1): ?>
                        <a href="<?= './board.php' . ($mode ? '?mode=' . htmlspecialchars($mode) . '&' : '?') . 'page=' . ($page - 1) ?>" title="이전">◀</a>
                    <?php else: ?>
                        <a href="#" title="이전" style="pointer-events: none; opacity: 0.5;">◀</a>
                    <?php endif; ?>

                    <!-- 페이지 번호 -->
                    <?php
                    // 페이지 번호 표시 범위 설정
                    $range = 2; // 현재 페이지를 기준으로 좌우로 표시할 페이지 수
                    $start = max(1, $page - $range);
                    $end = min($total_pages, $page + $range);

                    for ($i = $start; $i <= $end; $i++): ?>
                        <?php if ($i == $page): ?>
                            <a href="#" class="active_page"><?= $i ?></a>
                        <?php else: ?>
                            <a href="<?= './board.php' . ($mode ? '?mode=' . htmlspecialchars($mode) . '&' : '?') . 'page=' . $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- 다음 페이지로 -->
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= './board.php' . ($mode ? '?mode=' . htmlspecialchars($mode) . '&' : '?') . 'page=' . ($page + 1) ?>" title="다음">▶</a>
                    <?php else: ?>
                        <a href="#" title="다음" style="pointer-events: none; opacity: 0.5;">▶</a>
                    <?php endif; ?>

                    <!-- 마지막 페이지로 -->
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= './board.php' . ($mode ? '?mode=' . htmlspecialchars($mode) . '&' : '?') . 'page=' . $total_pages ?>" title="끝">⏭</a>
                    <?php else: ?>
                        <a href="#" title="끝" style="pointer-events: none; opacity: 0.5;">⏭</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include("./Components/FooterComponents.php"); ?>
</body>

</html>
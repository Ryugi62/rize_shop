<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>
    <link rel="stylesheet" href="./style.css">
    <style>
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

        .navigation .icon {
            display: inline-block;
            width: 20px;
            height: 20px;
        }

        .navigation .icon:first-child {
            transform: rotate(180deg);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php include("./Components/HeaderComponents.php"); ?>

    <main>
        <div class="board_view view">
            <!-- 게시판의 모드를 정하는 4개의 탭: 전체, 공지사항, 리뷰, Q&A -->
            <div class="section_header">
                <h2 class="section_title">상품 리스트</h2>
                <?php include("./Components/SearchComponents.php"); ?>
            </div>
            <div class="board_mode">
                <a href="./board.php" class="<?= !isset($_GET['mode']) ? 'active' : '' ?>">전체</a>
                <a href="./board.php?mode=notice" class="<?= (isset($_GET['mode']) && $_GET['mode'] == 'notice') ? 'active' : '' ?>">공지사항</a>
                <a href="./board.php?mode=review" class="<?= (isset($_GET['mode']) && $_GET['mode'] == 'review') ? 'active' : '' ?>">리뷰</a>
                <a href="./board.php?mode=qna" class="<?= (isset($_GET['mode']) && $_GET['mode'] == 'qna') ? 'active' : '' ?>">Q & A</a>
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
                        <?php
                        // 더미 데이터 배열
                        $dummy_data = [
                            ['id' => 1, 'title' => '첫 번째 공지사항', 'writer' => '관리자', 'date' => '2024-12-01', 'view' => 123, 'mode' => 'notice'],
                            ['id' => 2, 'title' => '두 번째 리뷰', 'writer' => '사용자1', 'date' => '2024-12-02', 'view' => 456, 'mode' => 'review'],
                            ['id' => 3, 'title' => '세 번째 Q&A', 'writer' => '사용자2', 'date' => '2024-12-03', 'view' => 789, 'mode' => 'qna'],
                        ];

                        // 현재 모드에 따라 필터링
                        $mode = isset($_GET['mode']) ? $_GET['mode'] : '';
                        foreach ($dummy_data as $row) {
                            if ($mode == '' || $row['mode'] == $mode) {
                                echo "<tr onclick=\"window.location='./board_view.php?id={$row['id']}'\">
                                        <td>{$row['id']}</td>
                                        <td class='post_title'>{$row['title']}</td>
                                        <td>{$row['writer']}</td>
                                        <td>{$row['date']}</td>
                                        <td>{$row['view']}</td>
                                      </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- 네비게이션 -->
            <div class="navigation">
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
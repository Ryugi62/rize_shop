<?php
// PostManager.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

// 게시물 삭제 처리
if (isset($_GET['delete_post'])) {
    $post_id = intval($_GET['delete_post']);
    $delete_stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $delete_stmt->execute(['id' => $post_id]);
    $delete_success = "게시물이 삭제되었습니다.";
}

// 게시물 목록 불러오기
$stmt = $pdo->query("
    SELECT p.id, p.title, p.content, u.username AS author, p.created_at AS date
    FROM posts p
    INNER JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="post_manager">
    <h3>게시물 관리</h3>

    <?php if (isset($delete_success)): ?>
        <p style="color: var(--green);"><?php echo htmlspecialchars($delete_success); ?></p>
    <?php endif; ?>

    <h4>등록된 게시물 목록</h4>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>제목</th>
                <th>작성자</th>
                <th>작성일</th>
                <th>내용</th>
                <th>작업</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <tr onclick="location.href='board_view.php?id=<?php echo $post['id']; ?>'" style="cursor:pointer;">
                        <td><?php echo htmlspecialchars($post['id']); ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['author']); ?></td>
                        <td><?php echo htmlspecialchars($post['date']); ?></td>
                        <td><?php echo $post['content']; ?></td>
                        <td>
                            <!-- 삭제 버튼을 클릭할 때 행 클릭 이벤트가 작동하지 않도록 이벤트 전파 중지 -->
                            <a href="admin.php?mode=board&delete_post=<?php echo $post['id']; ?>"
                                onclick="event.stopPropagation(); return confirm('정말 삭제하시겠습니까?');"
                                style="color: var(--red); text-decoration: underline;">
                                삭제
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no_posts">등록된 게시물이 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<style>
    .post_manager {
        border-radius: 8px;
        margin-top: 40px;
    }

    .post_manager h3 {
        margin-bottom: 24px;
        font-size: 24px;
        font-weight: bold;
        color: var(--white);
        text-align: center;
        border-bottom: 1px solid var(--gray);
        padding-bottom: 12px;
    }

    .post_manager h4 {
        margin-bottom: 16px;
        font-size: 20px;
        font-weight: bold;
        color: var(--white);
        border-bottom: 1px solid var(--light-gray);
        padding-bottom: 8px;
    }

    .post_manager table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
        border: 1px solid var(--gray);
        border-radius: 8px;
        overflow: hidden;
    }

    .post_manager th,
    .post_manager td {
        padding: 12px;
        border-bottom: 1px solid var(--light-black-hover);
        vertical-align: top;
    }

    .post_manager th {
        background-color: var(--light-black-hover);
        font-weight: bold;
        color: var(--white);
        border-right: 1px solid var(--gray);
    }

    .post_manager th:last-child {
        border-right: none;
    }

    .post_manager td {
        color: var(--white);
        border-right: 1px solid var(--gray);
    }

    .post_manager td:last-child {
        border-right: none;
    }

    .post_manager a {
        color: var(--red);
        text-decoration: underline;
    }

    .post_manager a:hover {
        color: var(--red-hover);
    }

    .post_manager .no_posts {
        text-align: center;
        color: var(--gray);
        padding: 20px;
    }

    .post_manager tr:hover td {
        background-color: var(--black-hover);
    }
</style>
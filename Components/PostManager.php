<?php
// PostManager.php

// 세션 변수 'posts'가 설정되어 있는지 확인하고, 초기화
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = array(
        array(
            'id' => 1,
            'title' => '첫 번째 게시물',
            'content' => '<p>이것은 첫 번째 더미 게시물의 내용입니다.</p>',
            'author' => '관리자',
            'date' => '2024-04-01'
        ),
        array(
            'id' => 2,
            'title' => '두 번째 게시물',
            'content' => '<p>이것은 두 번째 더미 게시물의 내용입니다.</p>',
            'author' => '관리자',
            'date' => '2024-04-02'
        ),
        array(
            'id' => 3,
            'title' => '세 번째 게시물',
            'content' => '<p>이것은 세 번째 더미 게시물의 내용입니다.</p>',
            'author' => '관리자',
            'date' => '2024-04-03'
        ),
    );
}

// 게시물 삭제 처리
if (isset($_GET['delete_post'])) {
    $post_id = intval($_GET['delete_post']);
    foreach ($_SESSION['posts'] as $index => $post) {
        if ($post['id'] === $post_id) {
            unset($_SESSION['posts'][$index]);
            $_SESSION['posts'] = array_values($_SESSION['posts']); // 인덱스 재정렬
            $delete_success = "게시물이 삭제되었습니다.";
            break;
        }
    }
}
?>

<div class="post_manager">
    <h3>게시물 관리</h3>

    <?php if (isset($delete_success)): ?>
        <p style="color: var(--green);"><?php echo htmlspecialchars($delete_success); ?></p>
    <?php endif; ?>

    <!-- 게시물 목록 -->
    <h4>등록된 게시물 목록</h4>
    <table style="width:100%; border-collapse: collapse; color: var(--white);">
        <thead>
            <tr>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">ID</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">제목</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">작성자</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">작성일</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">내용</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">작업</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($_SESSION['posts']) && count($_SESSION['posts']) > 0): ?>
                <?php foreach ($_SESSION['posts'] as $post): ?>
                    <tr>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($post['id']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($post['title']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($post['author']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($post['date']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo $post['content']; ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;">
                            <a href="admin.php?mode=board&delete_post=<?php echo $post['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" style="color: var(--red); text-decoration: underline;">삭제</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="border: 1px solid var(--light-gray); padding: 8px; text-align: center;">등록된 게시물이 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
// UserManager.php

// session_start(); // 제거

// 더미 데이터 초기화
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = array(
        array('id' => 1, 'username' => 'user1', 'email' => 'user1@example.com'),
        array('id' => 2, 'username' => 'user2', 'email' => 'user2@example.com'),
        array('id' => 3, 'username' => 'user3', 'email' => 'user3@example.com'),
    );
}

// 사용자 삭제 처리
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    if (isset($_SESSION['users'])) {
        foreach ($_SESSION['users'] as $index => $user) {
            if ($user['id'] === $id) {
                unset($_SESSION['users'][$index]);
                $_SESSION['users'] = array_values($_SESSION['users']); // 인덱스 재정렬
                $delete_success = "사용자가 삭제되었습니다.";
                break;
            }
        }
    }
}
?>

<div class="user_manager">
    <h3>회원 관리</h3>

    <?php if (isset($delete_success)): ?>
        <p style="color: var(--green);"><?php echo $delete_success; ?></p>
    <?php endif; ?>

    <!-- 사용자 목록 -->
    <h4>등록된 사용자 목록</h4>
    <table style="width:100%; border-collapse: collapse; color: var(--white);">
        <thead>
            <tr>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">ID</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">사용자명</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">이메일</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">작업</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($_SESSION['users']) && count($_SESSION['users']) > 0):
                foreach ($_SESSION['users'] as $user):
            ?>
                    <tr>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;">
                            <a href="admin.php?mode=user&delete_user=<?php echo $user['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" style="color: var(--red); text-decoration: underline;">삭제</a>
                        </td>
                    </tr>
                <?php
                endforeach;
            else:
                ?>
                <tr>
                    <td colspan="4" style="border: 1px solid var(--light-gray); padding: 8px; text-align: center;">등록된 사용자가 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
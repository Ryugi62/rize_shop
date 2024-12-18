<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

// 현재 로그인한 관리자 ID (admin.php에서 관리자 체크했을 것으로 가정)
$current_admin_id = $_SESSION['user_id'] ?? null;

// 역할 변경 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    // 현재 관리자 자신은 역할 변경 불가
    if ($user_id === $current_admin_id) {
        $update_error = "자신의 권한은 변경할 수 없습니다.";
    } else {
        $update_stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
        $updated = $update_stmt->execute(['role' => $new_role, 'id' => $user_id]);
        if ($updated) {
            $update_success = "사용자 역할이 변경되었습니다.";
        } else {
            $update_error = "역할 변경 중 오류가 발생했습니다.";
        }
    }
}

// 비밀번호 초기화 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = trim($_POST['new_password']);

    // 현재 관리자 자신은 비밀번호 초기화 불가
    if ($user_id === $current_admin_id) {
        $update_error = "자신의 비밀번호는 초기화할 수 없습니다.";
    } elseif ($new_password === '') {
        $update_error = "비밀번호를 입력해주세요.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $pass_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $pass_updated = $pass_stmt->execute(['password' => $hashed_password, 'id' => $user_id]);

        if ($pass_updated) {
            $update_success = "비밀번호가 성공적으로 변경되었습니다.";
        } else {
            $update_error = "비밀번호 변경 중 오류가 발생했습니다.";
        }
    }
}

// 유저 삭제 처리
if (isset($_GET['delete_user'])) {
    $delete_user_id = intval($_GET['delete_user']);
    // 자기 자신은 삭제 불가
    if ($delete_user_id === $current_admin_id) {
        $update_error = "자신의 계정은 삭제할 수 없습니다.";
    } else {
        $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $deleted = $del_stmt->execute(['id' => $delete_user_id]);
        if ($deleted) {
            $update_success = "사용자가 삭제되었습니다.";
        } else {
            $update_error = "사용자 삭제 중 오류가 발생했습니다.";
        }
    }
}

// 사용자 목록 가져오기
$stmt = $pdo->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="user_manager">
    <h3>회원 관리</h3>
    <?php if (isset($update_success)): ?>
        <p class="success_msg"><?php echo htmlspecialchars($update_success); ?></p>
    <?php endif; ?>
    <?php if (isset($update_error)): ?>
        <p class="error_msg"><?php echo htmlspecialchars($update_error); ?></p>
    <?php endif; ?>

    <h4>등록된 사용자 목록</h4>

    <table class="admin_user_table">
        <thead>
            <tr>
                <th>ID</th>
                <th>사용자명</th>
                <th>이메일</th>
                <th>역할</th>
                <th>비밀번호 초기화</th>
                <th>작업</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <!-- 역할 변경 폼 -->
                            <?php if ($user['id'] === $current_admin_id): ?>
                                <!-- 본인 계정은 역할 변경 불가능 -->
                                <span><?php echo htmlspecialchars($user['role']); ?></span>
                            <?php else: ?>
                                <form action="admin.php?mode=user" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="role_select">
                                        <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="update_role" class="action_btn">저장</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- 비밀번호 초기화 폼 -->
                            <?php if ($user['id'] === $current_admin_id): ?>
                                <!-- 자기 자신은 비밀번호 초기화 불가 -->
                                <span style="color:#aaa;">-</span>
                            <?php else: ?>
                                <form action="admin.php?mode=user" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="text" name="new_password" placeholder="새 비밀번호" class="pass_input" />
                                    <button type="submit" name="reset_password" class="action_btn">초기화</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['id'] === $current_admin_id): ?>
                                <!-- 자기 자신은 삭제 불가 -->
                                <span style="color:#aaa;">-</span>
                            <?php else: ?>
                                <a href="admin.php?mode=user&delete_user=<?php echo $user['id']; ?>"
                                    onclick="return confirm('정말 이 사용자를 삭제하시겠습니까?');"
                                    class="delete_link">삭제</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no_users">등록된 사용자가 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<style>
    .user_manager {
        padding: 20px;
    }

    .user_manager h3 {
        font-size: 24px;
        font-weight: bold;
        color: var(--white);
        text-align: center;
        border-bottom: 1px solid var(--gray);
        padding-bottom: 12px;
        margin-bottom: 24px;
    }

    .user_manager h4 {
        font-size: 20px;
        font-weight: bold;
        color: var(--white);
        border-bottom: 1px solid var(--light-gray);
        padding-bottom: 8px;
        margin-bottom: 16px;
    }

    .success_msg {
        color: var(--green);
        margin-bottom: 16px;
    }

    .error_msg {
        color: var(--red);
        margin-bottom: 16px;
    }

    .admin_user_table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
        border: 1px solid var(--gray);
        border-radius: 8px;
        overflow: hidden;
    }

    .admin_user_table th,
    .admin_user_table td {
        padding: 12px;
        border-bottom: 1px solid var(--black-hover);
        vertical-align: middle;
    }

    .admin_user_table th {
        background-color: var(--black-hover);
        font-weight: bold;
        color: var(--white);
        border-right: 1px solid var(--gray);
        text-align: left;
        white-space: nowrap;
    }

    .admin_user_table th:last-child {
        border-right: none;
    }

    .admin_user_table td {
        color: var(--white);
        border-right: 1px solid var(--gray);
    }

    .admin_user_table td:last-child {
        border-right: none;
    }

    .admin_user_table tr:hover td {
        background-color: var(--light-black);
    }

    .no_users {
        text-align: center;
        color: var(--gray);
    }

    .role_select {
        background-color: var(--black);
        color: var(--white);
        border: 1px solid var(--light-gray);
        border-radius: 4px;
        padding: 4px 8px;
    }

    .pass_input {
        background-color: var(--black);
        color: var(--white);
        border: 1px solid var(--light-gray);
        border-radius: 4px;
        padding: 4px 8px;
        margin-right: 8px;
    }

    .action_btn {
        background-color: var(--main);
        color: var(--black);
        border: none;
        border-radius: 4px;
        padding: 6px 10px;
        margin-left: 4px;
        cursor: pointer;
        font-weight: bold;
    }

    .action_btn:hover {
        background-color: var(--main-hover);
    }

    .delete_link {
        color: var(--red);
        text-decoration: underline;
    }

    .delete_link:hover {
        color: var(--red-hover);
    }
</style>
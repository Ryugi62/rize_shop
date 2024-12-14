<h3>회원 관리</h3>
<div class="table_box">
    <table>
        <thead>
            <tr>
                <th>회원 ID</th>
                <th>이름</th>
                <th>이메일</th>
                <th>역할</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 유저 데이터를 배열로 처리
            $users = [
                ['id' => 1, 'name' => '홍길동', 'email' => 'user1@example.com', 'role' => '관리자'],
                ['id' => 2, 'name' => '김철수', 'email' => 'user2@example.com', 'role' => '회원'],
                ['id' => 3, 'name' => '이영희', 'email' => 'user3@example.com', 'role' => '회원'],
            ];

            foreach ($users as $user) {
                echo "<tr>
                        <td>{$user['id']}</td>
                        <td>{$user['name']}</td>
                        <td>{$user['email']}</td>
                        <td>
                            <form method='POST' action='./update_user.php' style='display:inline-block;'>
                                <input type='hidden' name='id' value='{$user['id']}'>
                                <select name='role' onchange='this.form.submit()'>
                                    <option value='관리자' " . ($user['role'] == '관리자' ? 'selected' : '') . ">관리자</option>
                                    <option value='회원' " . ($user['role'] == '회원' ? 'selected' : '') . ">회원</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href='./reset_password.php?id={$user['id']}' onclick='return confirm(\"정말 비밀번호를 초기화하시겠습니까?\")'>비밀번호 초기화</a> |
                            <a href='./delete_user.php?id={$user['id']}' onclick='return confirm(\"정말 삭제하시겠습니까?\")'>삭제</a>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
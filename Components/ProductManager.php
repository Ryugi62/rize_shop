<?php
// productManage.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('./config/db.php');

// 상품 삭제 처리
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del_stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    if ($del_stmt->execute(['id' => $id])) {
        $delete_success = "상품이 삭제되었습니다.";
    } else {
        $error = "상품 삭제 중 오류가 발생했습니다.";
    }
}

// 상품 목록 가져오기
$stmt = $pdo->query("SELECT id, product_name, product_image, price, description, stock, rating, reviews, created_at FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="product_manager">
    <h3>상품 관리</h3>

    <?php if (isset($delete_success)): ?>
        <p class="success_msg"><?php echo htmlspecialchars($delete_success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error_msg"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- 상품 목록 -->
    <h4>등록된 상품 목록</h4>
    <table class="admin_table">
        <thead>
            <tr>
                <th>ID</th>
                <th>이미지</th>
                <th>상품명</th>
                <th>가격</th>
                <th>재고</th>
                <th>평점</th>
                <th>리뷰수</th>
                <th>등록일</th>
                <th>설명</th>
                <th>작업</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product):
                    $desc_text = strip_tags($product['description']);
                    $short_desc = mb_substr($desc_text, 0, 50) . (mb_strlen($desc_text) > 50 ? '...' : '');
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td>
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 60px; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color:var(--gray);">이미지 없음</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo number_format($product['price']); ?>원</td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['rating'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($product['reviews'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                        <td><?php echo $short_desc; ?></td>
                        <td>
                            <a href="admin.php?mode=edit_product&id=<?php echo $product['id']; ?>" class="action_btn edit_btn">수정</a>
                            <a href="admin.php?mode=product&delete=<?php echo $product['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" class="action_btn delete_btn">삭제</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="no_data">등록된 상품이 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    .product_manager {
        margin-top: 40px;
        color: var(--white);
    }

    .product_manager h3 {
        margin-bottom: 24px;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        border-bottom: 1px solid var(--gray);
        padding-bottom: 12px;
    }

    .product_manager h4 {
        margin-bottom: 16px;
        font-size: 20px;
        font-weight: bold;
        border-bottom: 1px solid var(--light-gray);
        padding-bottom: 8px;
    }

    .success_msg {
        color: var(--green);
        margin-bottom: 16px;
    }

    .error_msg {
        color: var(--red);
        margin-bottom: 16px;
    }

    .admin_table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--gray);
        border-radius: 8px;
        overflow: hidden;
        background-color: var(--light-black);
        font-size: 14px;
    }

    .admin_table th,
    .admin_table td {
        padding: 12px;
        border-bottom: 1px solid var(--black-hover);
        vertical-align: middle;
    }

    .admin_table th {
        background-color: var(--black-hover);
        font-weight: bold;
        color: var(--white);
        border-right: 1px solid var(--gray);
        text-align: left;
        white-space: nowrap;
    }

    .admin_table th:last-child {
        border-right: none;
    }

    .admin_table td {
        color: var(--white);
        border-right: 1px solid var(--gray);
    }

    .admin_table td:last-child {
        border-right: none;
    }

    .admin_table .no_data {
        text-align: center;
        color: var(--gray);
        padding: 20px;
    }

    .admin_table tr:hover td {
        background-color: var(--black-hover);
    }

    .action_btn {
        display: inline-block;
        color: var(--black);
        background-color: var(--main);
        padding: 6px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        margin-right: 8px;
        transition: background-color 0.3s;
    }

    .action_btn:hover {
        background-color: var(--main-hover);
    }

    .edit_btn {
        color: var(--black);
    }

    .delete_btn {
        color: var(--black);
    }
</style>
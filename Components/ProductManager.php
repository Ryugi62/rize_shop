<?php
// ProductManager.php

// 상품 삭제 처리
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if (isset($_SESSION['products'])) {
        foreach ($_SESSION['products'] as $index => $product) {
            if ($product['id'] === $id) {
                unset($_SESSION['products'][$index]);
                $_SESSION['products'] = array_values($_SESSION['products']); // 인덱스 재정렬
                $delete_success = "상품이 삭제되었습니다.";
                break;
            }
        }
    }
}
?>

<div class="product_manager">
    <h3>상품 관리</h3>

    <?php if (isset($delete_success)): ?>
        <p style="color: var(--green);"><?php echo htmlspecialchars($delete_success); ?></p>
    <?php endif; ?>

    <!-- 상품 목록 -->
    <h4>등록된 상품 목록</h4>
    <table style="width:100%; border-collapse: collapse; color: var(--white);">
        <thead>
            <tr>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">ID</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">이미지</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">이름</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">가격</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">설명</th>
                <th style="border: 1px solid var(--light-gray); padding: 8px;">작업</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($_SESSION['products']) && count($_SESSION['products']) > 0):
                foreach ($_SESSION['products'] as $product):
            ?>
                    <tr>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($product['id']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100px;">
                        </td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo htmlspecialchars($product['price']); ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;"><?php echo $product['description']; ?></td>
                        <td style="border: 1px solid var(--light-gray); padding: 8px;">
                            <a href="admin.php?mode=edit_product&edit_product=<?php echo $product['id']; ?>" style="color: var(--blue); text-decoration: underline; margin-right: 10px;">수정</a>
                            <a href="admin.php?mode=product&delete=<?php echo $product['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" style="color: var(--red); text-decoration: underline;">삭제</a>
                        </td>
                    </tr>
                <?php
                endforeach;
            else:
                ?>
                <tr>
                    <td colspan="6" style="border: 1px solid var(--light-gray); padding: 8px; text-align: center;">등록된 상품이 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<h3>상품 관리</h3>
<form method="POST" action="./process_product.php">
    <h4>신규 상품 등록</h4>
    <div class="form_group">
        <label for="product_name">상품명</label>
        <input type="text" id="product_name" name="product_name" required>
    </div>
    <div class="form_group">
        <label for="product_price">가격</label>
        <input type="number" id="product_price" name="product_price" required>
    </div>
    <div class="form_group">
        <label for="product_description">설명</label>
        <textarea id="product_description" name="product_description" rows="4" required></textarea>
    </div>
    <button type="submit">상품 등록</button>
</form>

<hr>

<h4>기존 상품 관리</h4>
<div class="table_box">
    <table>
        <thead>
            <tr>
                <th>상품 ID</th>
                <th>상품명</th>
                <th>가격</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 기존 상품 데이터를 배열로 처리
            $products = [
                ['id' => 1, 'name' => '상품 A', 'price' => 10000],
                ['id' => 2, 'name' => '상품 B', 'price' => 20000],
                ['id' => 3, 'name' => '상품 C', 'price' => 30000],
            ];

            foreach ($products as $product) {
                echo "<tr>
                        <td>{$product['id']}</td>
                        <td>{$product['name']}</td>
                        <td>{$product['price']}원</td>
                        <td>
                            <a href='./edit_product.php?id={$product['id']}'>수정</a> |
                            <a href='./delete_product.php?id={$product['id']}' onclick='return confirm(\"정말 삭제하시겠습니까?\")'>삭제</a>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
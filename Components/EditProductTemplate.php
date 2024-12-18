<?php
// 이 템플릿은 $error, $product, $product_id 변수를 사용한다고 가정
?>
<style>
    .edit_product {
        padding: 20px;
    }

    .edit_product h3 {
        font-size: 24px;
        margin-bottom: 24px;
        text-align: center;
        color: var(--white);
        border-bottom: 1px solid var(--gray);
        padding-bottom: 12px;
    }

    .error_msg {
        color: var(--red);
    }

    .success_msg {
        color: var(--green);
    }

    .form_group {
        margin-bottom: 20px;
    }

    .form_group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: var(--white);
    }

    .form_group input[type="text"],
    .form_group input[type="number"],
    .form_group textarea,
    .form_group input[type="file"] {
        background-color: var(--black);
        color: var(--white);
        border: 1px solid var(--light-gray);
        border-radius: 4px;
        padding: 0.5rem;
        font-size: 16px;
    }

    .form_group textarea {
        min-height: 300px;
    }

    button[name="edit_product"] {
        margin-top: 20px;
        width: 100%;
        background-color: var(--main);
        color: var(--black);
        border: none;
        border-radius: 4px;
        padding: 12px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button[name="edit_product"]:hover {
        background-color: var(--main-hover);
    }

    .current_image {
        margin-top: 10px;
    }

    .current_image img {
        width: 100px;
        border-radius: 4px;
    }
</style>

<div class="edit_product">
    <h3>상품 수정</h3>

    <?php if (!empty($error)): ?>
        <p class="error_msg"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="admin.php?mode=edit_product&id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="form_group">
            <label for="name">상품 이름:</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>
        <div class="form_group">
            <label for="price">가격 (숫자만 입력):</label>
            <input type="text" id="price" name="price" required value="<?php echo htmlspecialchars($product['price']); ?>">
        </div>
        <div class="form_group">
            <label for="stock">재고 수량:</label>
            <input type="number" id="stock" name="stock" required min="0" value="<?php echo htmlspecialchars($product['stock']); ?>">
        </div>
        <div class="form_group">
            <label for="image_file">상품 이미지 (필요 시 변경):</label>
            <input type="file" id="image_file" name="image_file" accept="image/*">
            <?php if ($product['product_image']): ?>
                <div class="current_image">
                    <p>현재 이미지:</p>
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="현재 상품 이미지">
                </div>
            <?php endif; ?>
        </div>
        <div class="form_group">
            <label for="description">상품 설명:</label>
            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <button type="submit" name="edit_product">상품 수정</button>
    </form>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#description'))
        .then(editor => {})
        .catch(error => {
            console.error(error);
        });
</script>
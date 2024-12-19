<?php
// 상품 ID 체크
if (!isset($product_id)) {
    die('상품 ID가 지정되지 않았습니다.');
}

// 상품 조회
$stmt = $pdo->prepare("SELECT product_name, product_image, price, description, stock, discount_amount FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('상품을 찾을 수 없습니다.');
}

// 에러/성공 메시지
$error = null;

// 수정 처리 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $name = trim($_POST['name']);
    $price_str = trim($_POST['price']);
    $description = $_POST['description'];
    $stock_str = trim($_POST['stock']);
    $discount_str = trim($_POST['discount_amount']);

    $price = (int)preg_replace('/[^0-9]/', '', $price_str);
    $stock = (int)preg_replace('/[^0-9]/', '', $stock_str);
    $discount_amount = (int)preg_replace('/[^0-9]/', '', $discount_str);

    if ($price <= 0) {
        $error = "가격은 1원 이상이어야 합니다.";
    } elseif ($stock < 0) {
        $error = "재고 수는 0 이상이어야 합니다.";
    } elseif ($stock > 1000000) {
        $error = "재고 수가 너무 큽니다. 1,000,000 이하로 입력해주세요.";
    }

    $upload_dir = './assets/images/';
    $image_path = $product['product_image'];

    if (!$error && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image_file']['tmp_name'];
        $original_name = basename($_FILES['image_file']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed_ext)) {
            $new_name = uniqid('product_') . '.' . $ext;
            $target_path = $upload_dir . $new_name;
            if (!move_uploaded_file($tmp_name, $target_path)) {
                $error = "이미지 업로드 중 오류가 발생했습니다.";
            } else {
                $image_path = $target_path;
            }
        } else {
            $error = "허용되지 않은 이미지 형식입니다. (jpg, jpeg, png, gif만 가능)";
        }
    }

    if (!$error) {
        if ($name === '' || $price === 0 || $stock < 0) {
            $error = "모든 필드를 올바르게 입력해주세요 (가격은 숫자, 재고는 0 이상의 숫자).";
        } else {
            $update_stmt = $pdo->prepare("UPDATE products SET product_name=:pn, product_image=:pi, price=:pr, description=:d, stock=:s, discount_amount=:da WHERE id=:id");
            $success_update = $update_stmt->execute([
                'pn' => $name,
                'pi' => $image_path,
                'pr' => $price,
                'd' => $description,
                's' => $stock,
                'da' => $discount_amount,
                'id' => $product_id
            ]);

            if ($success_update) {
                // 성공 시 바로 리다이렉트 (HTML 전송 전이므로 오류 없음)
                header('Location: admin.php?mode=product');
                exit;
            } else {
                $error = "상품 수정 중 오류가 발생했습니다.";
            }
        }
    }
}
// 여기서 $error, $product 변수는 템플릿에서 사용 가능

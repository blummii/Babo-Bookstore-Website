<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

// Tắt báo lỗi hiển thị ra màn hình để tránh làm hỏng JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

try {
    // Lấy dữ liệu từ form
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $publish_year = intval($_POST['publish_year'] ?? date('Y'));
    $pages = intval($_POST['pages'] ?? 0);
    $weight = intval($_POST['weight'] ?? 0);
    $cover_type = $_POST['cover_type'] ?? 'hard';
    $price = floatval($_POST['price'] ?? 0);
    $discounted_price = floatval($_POST['discounted_price'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $category_id = intval($_POST['category'] ?? 0);
    $author_id = intval($_POST['author'] ?? 0);
    $new_category = trim($_POST['new_category'] ?? '');
    $new_author_name = trim($_POST['new_author'] ?? '');
    $publisher_id = intval($_POST['publisher'] ?? 0);
    $new_publisher = trim($_POST['new_publisher'] ?? '');
    $status = 'active'; 

    $errors = [];
    if(empty($title)) $errors[] = "Tên sách không được để trống.";
    if($category_id <= 0 && empty($new_category)) $errors[] = "Chọn thể loại hoặc thêm mới";
    if($author_id <= 0 && empty($new_author_name)) $errors[] = "Chọn tác giả hoặc thêm mới";

    if(!empty($errors)){
        echo json_encode(['success'=>false, 'error'=>implode(', ', $errors)]);
        exit;
    }

    // 1. Thêm category mới nếu có
    if(!empty($new_category)){
        $stmt = $ocon->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }

    // 2. Thêm author mới nếu có
    if(!empty($new_author_name)){
        $stmt = $ocon->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $new_author_name);
        $stmt->execute();
        $author_id = $stmt->insert_id;
        $stmt->close();
    }

    // 3. Thêm publisher mới nếu có
    if(!empty($new_publisher)){
        $stmt = $ocon->prepare("INSERT INTO publishers (name) VALUES (?)");
        $stmt->bind_param("s", $new_publisher);
        $stmt->execute();
        $publisher_id = $stmt->insert_id;
        $stmt->close();
    }

    // 4. Xử lý ảnh cover upload
    $cover_path = '';
    if(isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK){
        $fileTmpPath = $_FILES['cover']['tmp_name'];
        $fileName = basename($_FILES['cover']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid('book_') . '.' . $ext;
        
        // Đảm bảo thư mục tồn tại
        $uploadFileDir = 'images/';
        if (!file_exists($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)){
            $cover_path = $dest_path;
        }
    }

    // 5. Tạo slug nếu trống
    if(empty($slug)){
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
    }

    // 6. Thêm sách vào DB
    $stmt = $ocon->prepare("INSERT INTO books (title, slug, description, publish_year, pages, weight, cover_type, price, discounted_price, category_id, stock_quantity, status, publisher_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // --- ĐÃ SỬA LỖI TẠI DÒNG NÀY ---
    // Thứ tự đúng: sssiiisddiisi
    $stmt->bind_param(
        "sssiiisddiisi", 
        $title, $slug, $description, $publish_year, $pages, $weight, $cover_type, $price, $discounted_price, $category_id, $stock_quantity, $status, $publisher_id
    );
    
    if(!$stmt->execute()){
        throw new Exception("Lỗi SQL: " . $stmt->error);
    }
    $book_id = $stmt->insert_id;
    $stmt->close();

    // 7. Thêm quan hệ sách-tác giả
    if($author_id > 0){
        $stmt2 = $ocon->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $book_id, $author_id);
        $stmt2->execute();
        $stmt2->close();
    }

    // 8. Thêm ảnh vào bảng images nếu có
    if(!empty($cover_path)){
        $stmt_img = $ocon->prepare("INSERT INTO images (book_id, url) VALUES (?, ?)");
        $stmt_img->bind_param("is", $book_id, $cover_path);
        $stmt_img->execute();
        $stmt_img->close();
    }

    // 9. Lấy tên category và author để trả về
    $cat_res = $ocon->query("SELECT name FROM categories WHERE category_id=$category_id");
    $cat_name = ($cat_res && $cat_res->num_rows > 0) ? $cat_res->fetch_assoc()['name'] : '';

    $auth_res = $ocon->query("SELECT name FROM authors WHERE author_id=$author_id");
    $author_name = ($auth_res && $auth_res->num_rows > 0) ? $auth_res->fetch_assoc()['name'] : '';

    echo json_encode([
        'success' => true,
        'book' => [
            'id' => $book_id,
            'title' => $title,
            'price' => $discounted_price,
            'category_id' => $category_id,
            'cover_path' => $cover_path,
            'category_name' => $cat_name,
            'author_name' => $author_name,
            'status' => $status
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
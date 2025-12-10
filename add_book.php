<?php
session_start();
include 'connect.php';

// Nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Gán tên admin
$admin_name = $_SESSION['username'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm sách | Babo Bookstore</title>
<link rel="stylesheet" href="CSS/admin.css">
<link rel="stylesheet" href="CSS/add_book.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
    <h2>Thêm sách mới</h2>

    <form id="add-book-form" enctype="multipart/form-data">
        <label>Tên sách:</label>
        <input type="text" name="title" required>

        <label>Slug (tùy chọn):</label>
        <input type="text" name="slug">

        <label>Mô tả:</label>
        <textarea name="description" rows="4"></textarea>

        <label>Năm xuất bản:</label>
        <input type="number" name="publish_year" value="<?= date('Y') ?>" min="1900" max="<?= date('Y') ?>">

        <label>Số trang:</label>
        <input type="number" name="pages" value="0">

        <label>Trọng lượng (gram):</label>
        <input type="number" name="weight" value="0">

        <label>Loại bìa:</label>
        <select name="cover_type">
            <option value="hard">Cứng</option>
            <option value="soft">Mềm</option>
        </select>

        <label>Giá:</label>
        <input type="number" step="0.01" name="price" value="0">

        <label>Giá giảm:</label>
        <input type="number" step="0.01" name="discounted_price" value="0">

        <label>Thể loại:</label>
        <select name="category">
            <option value="">-- Chọn thể loại --</option>
            <?php
            $res = $ocon->query("SELECT * FROM categories");
            while($cat = $res->fetch_assoc()){
                echo '<option value="'.$cat['category_id'].'">'.htmlspecialchars($cat['name']).'</option>';
            }
            ?>
        </select>
        <input type="text" name="new_category" placeholder="Thêm thể loại mới nếu chưa có">

        <label>Nhà xuất bản:</label>
        <select name="publisher">
            <option value="">-- Chọn Nhà xuất bản --</option>
            <?php
            $res3 = $ocon->query("SELECT * FROM publishers");
            while($pub = $res3->fetch_assoc()){
                echo '<option value="'.$pub['publisher_id'].'">'.htmlspecialchars($pub['name']).'</option>';
            }
            ?>
        </select>
        <input type="text" name="new_publisher" placeholder="Thêm nhà xuất bản mới nếu chưa có">

        <label>Tác giả:</label>
        <select name="author">
            <option value="">-- Chọn Tác giả --</option>
            <?php
            $res2 = $ocon->query("SELECT * FROM authors");
            while($auth = $res2->fetch_assoc()){
                echo '<option value="'.$auth['author_id'].'">'.htmlspecialchars($auth['name']).'</option>';
            }
            ?>
        </select>
        <input type="text" name="new_author" placeholder="Thêm tác giả mới nếu chưa có">

        <label>Số lượng tồn:</label>
        <input type="number" name="stock_quantity" value="0">

        <label>Ảnh bìa:</label>
        <input type="file" name="cover" accept="image/*">

        <label>Trạng thái:</label>
        <select name="status">
            <option value="active">Hiển thị</option>
            <option value="hidden">Ẩn</option>
        </select>

        <button type="submit" class="btn-submit"><i class="fa fa-plus"></i> Thêm sách</button>
    </form>

    <h2>Danh sách sách</h2>
    <div id="book-list">
        <!-- Danh sách sách sẽ được load ở đây -->
    </div>
</main>

<script>
document.getElementById('add-book-form').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);

    fetch('add_book_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            const book = data.book;

            // 1. Cập nhật ngay danh sách bên dưới trang Admin (để admin thấy sách vừa thêm)
            const list = document.getElementById('book-list');
            let htmlAdmin = `
            <div class="book-item" style="border:1px solid #ddd; padding:10px; margin-bottom:10px; display:flex; gap:10px; align-items:center;">
                <img src="${book.cover_path}" style="width:50px; height:70px; object-fit:cover;">
                <div>
                    <h3 style="margin:0">${book.title}</h3>
                    <p style="margin:5px 0">Giá: ${Number(book.price).toLocaleString('vi-VN')}₫</p>
                    <p style="margin:0; font-size:0.9em; color:#666;">${book.category_name} - ${book.author_name}</p>
                </div>
            </div>`;
            
            // Thêm vào đầu danh sách
            list.insertAdjacentHTML('afterbegin', htmlAdmin);

            // 2. Thông báo thành công
            alert('Thêm sách thành công! Dữ liệu đã được cập nhật lên trang người dùng.');
            
            // 3. Reset form để nhập cuốn tiếp theo
            document.getElementById('add-book-form').reset();
        } else {
            alert('Lỗi: ' + data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối server hoặc lỗi xử lý PHP.');
    });
});
</script>

</body>
</html>

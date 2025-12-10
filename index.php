<?php
include 'connect.php'; 
session_start(); 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Set user_id an toàn


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ | Bookiee</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<?php if (!empty($_SESSION['cart_msg'])): ?>
    <div class="cart-alert">
        <?= $_SESSION['cart_msg']; ?>
    </div>
    <?php unset($_SESSION['cart_msg']); ?>
<?php endif; ?>
<?php 
include 'header.php'; 
?>

<section class="home_cont">
    <div class="main_descrip">
        <h1>Tìm cuốn sách mới <br> với giá tốt nhất</h1>
        <button onclick="window.location.href='checkout.php'">Mua Ngay <i class="fas fa-arrow-right"></i></button>
    </div>
</section>

<section class="features_section">
    <div class="feature_item">
        <i class="fas fa-truck-fast"></i>
        <div class="feature_info">
            <h3>Giao hàng nhanh</h3>
            <p>Vận chuyển trong 24h</p>
        </div>
    </div>
    <div class="feature_item">
        <i class="fas fa-shield-alt"></i>
        <div class="feature_info">
            <h3>Thanh toán an toàn</h3>
            <p>Bảo mật 100%</p>
        </div>
    </div>
    <div class="feature_item">
        <i class="fas fa-thumbs-up"></i>
        <div class="feature_info">
            <h3>Chất lượng cao</h3>
            <p>Sách chính hãng</p>
        </div>
    </div>
    <div class="feature_item">
        <i class="fas fa-headset"></i>
        <div class="feature_info">
            <h3>Hỗ trợ 24/7</h3>
            <p>Luôn sẵn sàng</p>
        </div>
    </div>
</section>

<section class="products_cont">
    <h2>
        Sách nổi bật 
        <a href="#" class="view_all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
    </h2>
    
    <div class="pro_box_cont">
        <?php
        $books = mysqli_query($ocon,
            "SELECT b.*, MIN(i.url) AS image
             FROM books b
             LEFT JOIN images i ON b.book_id = i.book_id
             WHERE b.status='active'
             GROUP BY b.book_id
             ORDER BY b.book_id DESC 
             LIMIT 8" // Sắp xếp theo ID mới nhất và chỉ lấy 8 cuốn
        );

        // 2. HIỂN THỊ SÁCH NẾU CÓ DỮ LIỆU
        if (mysqli_num_rows($books) > 0) {
            while ($b = mysqli_fetch_assoc($books)) {
        ?>
<form action="" method="post" class="pro_box">

    <span class="badge hot">Hot</span> 

    <img src="<?php echo $b['image']; ?>">

    <a href="checkout.php?id=<?php echo $b['book_id']; ?>&qty=1" class="buy_now_btn">
    MUA NGAY
    </a>

    <h3><?php echo $b['title']; ?></h3>

    <div class="price_row">
        <span class="price"><?php echo number_format($b['discounted_price'],0,',','.'); ?>₫</span>
        <input type="number" name="quantity" min="1" value="1" class="qty_input">
    </div>

    <div class="product_actions">
        <a href="book_detail.php?id=<?php echo $b['book_id']; ?>" class="product_btn detail_btn">
            <i class="fas fa-eye"></i> Chi tiết
        </a>

        <button type="submit" name="add_to_cart" class="product_btn add_to_cart_btn">
            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
        </button>
    </div>

</form>

        <?php
            }
        } else {
            // Hiển thị thông báo nếu không có sách
            echo "<p class='empty'>Chưa có sách nào đang hoạt động!</p>";
        }
        ?>
    </div>
</section>
<script>
    setTimeout(() => {
        let alertBox = document.querySelector('.cart-alert');
        if(alertBox){
            alertBox.classList.add('hide');
        }
    }, 2500);
</script>
<?php include 'footer.php'; ?>

</body>
</html>

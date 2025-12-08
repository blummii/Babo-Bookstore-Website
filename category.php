<?php
include 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$category_id = $_GET['id'] ?? null;

// Nếu không có id danh mục
if(!$category_id){
    header("Location: index.php");
    exit();
}

/* ==========================
   LẤY TÊN DANH MỤC
   ========================== */
$cat_query = mysqli_query($ocon, "SELECT name FROM categories WHERE category_id = '$category_id'");
$category = mysqli_fetch_assoc($cat_query);
$category_name = $category['name'] ?? "Danh mục";


?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title><?php echo $category_name; ?> | Babo Bookstore</title>

<link rel="stylesheet" href="CSS/style.css">
<link rel="stylesheet" href="CSS/header.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>

<?php include 'header.php'; ?>

<section class="products_cont">

    <h2>Danh mục: <?php echo $category_name; ?></h2>

    <div class="pro_box_cont">

        <?php
        $books = mysqli_query($ocon,
            "SELECT b.*, MIN(i.url) AS image
             FROM books b 
             LEFT JOIN images i ON b.book_id = i.book_id
             WHERE b.category_id = '$category_id' AND b.status = 'active'
             GROUP BY b.book_id"
        );

        if(mysqli_num_rows($books) > 0){
            while($b = mysqli_fetch_assoc($books)){
        ?>

<form method="post" class="pro_box">

    <span class="badge hot">Hot</span>

    <img src="<?php echo $b['image']; ?>" alt="<?php echo $b['title']; ?>">

    <!-- NÚT MUA NGAY TRÊN HOVER -->
    <a href="checkout.php?id=<?php echo $b['book_id']; ?>" class="buy_now_btn">
        MUA NGAY
    </a>

    <h3><?php echo $b['title']; ?></h3>

    <div class="price_row">
        <span class="price">
            <?php echo number_format($b['discounted_price'],0,",","."); ?>₫
        </span>

        <input type="number" name="quantity" min="1" value="1" class="qty_input">
    </div>

    <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">

    <div class="product_actions">
        <!-- XEM CHI TIẾT -->
        <a href="book_detail.php?id=<?php echo $b['book_id']; ?>" class="product_btn detail_btn">
            <i class="fas fa-eye"></i> Chi tiết
        </a>

        <!-- THÊM GIỎ HÀNG -->
        <?php if($user_id): ?>
            <button type="submit" name="add_to_cart" class="product_btn add_to_cart_btn">
                <i class="fas fa-shopping-cart"></i> Thêm giỏ hàng
            </button>
        <?php else: ?>
            <a href="login.php" class="product_btn login_btn">
                <i class="fas fa-lock"></i> Đăng nhập để mua
            </a>
        <?php endif; ?>
    </div>

</form>

        <?php 
            }
        } else {
            echo "<p class='empty'>Chưa có sách trong danh mục này!</p>";
        }
        ?>

    </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
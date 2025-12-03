<?php
// 1. XỬ LÝ THÔNG BÁO (MESSAGE)
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

// 2. LOGIC TÌM CART_ID 
// Đảm bảo các biến $ocon, $_SESSION đã được tạo ở file gọi (index.php)
$cart_id = 0; // Mặc định là 0
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    
    // Truy vấn tìm giỏ hàng của user hiện tại
    $cart_query = mysqli_query($ocon, "SELECT * FROM carts WHERE user_id = '$user_id'");
    
    if(mysqli_num_rows($cart_query) > 0){
        $cart = mysqli_fetch_assoc($cart_query);
        $cart_id = $cart['cart_id'];
    }else{
        // Nếu chưa có giỏ hàng thì tạo mới luôn
        mysqli_query($ocon, "INSERT INTO carts(user_id) VALUES('$user_id')");
        $cart_id = mysqli_insert_id($ocon);
    }
}
?>

<header class="user_header">

    <div class="header_1">
        <div class="user_flex">

            <a href="index.php" class="book_logo">
                <i class="fas fa-book-open"></i> Babo Bookstore
            </a>

            <nav class="navbar">
                <a href="index.php">Trang chủ</a>
                <a href="#">Cửa hàng</a>
                <a href="#">Liên hệ</a>
                <a href="#">Đơn hàng</a>
            </nav>

            <div class="icons">
                <a href="#" class="fas fa-search"></a>
                
                <?php
                    // Đếm số sách trong giỏ
                    $total_cart_items = 0;
                    if($cart_id > 0){
                        $count_cart_items = mysqli_query($ocon, "SELECT * FROM cart_items WHERE cart_id = '$cart_id'");
                        $total_cart_items = mysqli_num_rows($count_cart_items);
                    }
                ?>
                
                <a href="#">
                    <i class="fas fa-shopping-cart"></i>
                    <span>(<?php echo $total_cart_items; ?>)</span>
                </a>

                <div id="user-btn" class="fas fa-user"></div>
            </div>

        </div>
    </div>

</header>
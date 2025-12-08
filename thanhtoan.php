<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = [];


$cart_query = mysqli_query($ocon, "SELECT * FROM carts WHERE user_id = '$user_id'");
if (mysqli_num_rows($cart_query) > 0) {
    $cart = mysqli_fetch_assoc($cart_query);
    $cart_id = $cart['cart_id'];
} else {
    header("Location: index.php");
    exit();
}


$cart_items_query = mysqli_query($ocon, 
    "SELECT ci.*, b.title, b.discounted_price, i.url as image 
     FROM cart_items ci
     JOIN books b ON ci.book_id = b.book_id
     LEFT JOIN images i ON b.book_id = i.book_id
     WHERE ci.cart_id = '$cart_id'
     GROUP BY ci.cart_item_id"
);

$total_price = 0;
$cart_items = [];
while ($item = mysqli_fetch_assoc($cart_items_query)) {
    $cart_items[] = $item;
    $total_price += $item['discounted_price'] * $item['quantity'];
}


if (empty($cart_items)) {
    header("Location: index.php");
    exit();
}


$address_query = mysqli_query($ocon, 
    "SELECT * FROM addresses WHERE user_id = '$user_id' AND is_default = 1 LIMIT 1"
);
$default_address = mysqli_fetch_assoc($address_query);

$all_addresses = mysqli_query($ocon, "SELECT * FROM addresses WHERE user_id = '$user_id'");

$shipping_fee = 15000;


$discount = 0;
$coupon_code = '';
if (isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon_code']);
    
    $coupon_query = mysqli_query($ocon, 
        "SELECT * FROM coupons 
         WHERE code = '$coupon_code' 
         AND start_date <= CURDATE() 
         AND end_date >= CURDATE() 
         AND quantity > 0"
    );
    
    if (mysqli_num_rows($coupon_query) > 0) {
        $coupon = mysqli_fetch_assoc($coupon_query);
        

        if ($total_price >= $coupon['min_order_value']) {

            $used_check = mysqli_query($ocon, 
                "SELECT * FROM coupon_users 
                 WHERE coupon_id = '{$coupon['coupon_id']}' 
                 AND user_id = '$user_id' 
                 AND used_at IS NOT NULL"
            );
            
            if (mysqli_num_rows($used_check) == 0) {
                if ($coupon['discount_type'] == 'percent') {
                    $discount = ($total_price * $coupon['discount_value']) / 100;
                } else {
                    $discount = $coupon['discount_value'];
                }
                $message[] = "Áp dụng mã giảm giá thành công!";
            } else {
                $message[] = "Bạn đã sử dụng mã giảm giá này rồi!";
            }
        } else {
            $message[] = "Đơn hàng chưa đủ giá trị tối thiểu để áp dụng mã!";
        }
    } else {
        $message[] = "Mã giảm giá không hợp lệ hoặc đã hết hạn!";
    }
}

$final_total = $total_price + $shipping_fee - $discount;


if (isset($_POST['place_order'])) {
    $address_id = $_POST['address_id'];
    $payment_method = $_POST['payment_method'];
    $notes = mysqli_real_escape_string($ocon, trim($_POST['notes']));
    

    if (empty($address_id)) {
        $message[] = "Vui lòng chọn địa chỉ giao hàng!";
    } else {

        mysqli_begin_transaction($ocon);
        
        try {

            $order_query = "INSERT INTO orders (user_id, address_id, total_amount, payment_method, payment_status, shipping_fee, order_status) 
                           VALUES ('$user_id', '$address_id', '$final_total', '$payment_method', 'pending', '$shipping_fee', 'pending')";
            
            if (mysqli_query($ocon, $order_query)) {
                $order_id = mysqli_insert_id($ocon);
                

                foreach ($cart_items as $item) {
                    $book_id = $item['book_id'];
                    $quantity = $item['quantity'];
                    $price = $item['discounted_price'];
                    
                    mysqli_query($ocon, 
                        "INSERT INTO order_items (order_id, book_id, quantity, price_at_order) 
                         VALUES ('$order_id', '$book_id', '$quantity', '$price')"
                    );
                    

                    mysqli_query($ocon, 
                        "UPDATE books SET stock_quantity = stock_quantity - $quantity 
                         WHERE book_id = '$book_id'"
                    );
                }
                

                if ($discount > 0 && !empty($coupon_code)) {
                    $coupon_query = mysqli_query($ocon, "SELECT coupon_id FROM coupons WHERE code = '$coupon_code'");
                    $coupon_data = mysqli_fetch_assoc($coupon_query);
                    
                    mysqli_query($ocon, 
                        "INSERT INTO coupon_users (coupon_id, user_id, used_at) 
                         VALUES ('{$coupon_data['coupon_id']}', '$user_id', NOW())"
                    );

                    mysqli_query($ocon, 
                        "UPDATE coupons SET quantity = quantity - 1 
                         WHERE coupon_id = '{$coupon_data['coupon_id']}'"
                    );
                }
                

                $transaction_code = 'TNX_' . date('dmY') . '_' . $order_id;
                mysqli_query($ocon, 
                    "INSERT INTO payment (order_id, amount, method, transaction_code, status) 
                     VALUES ('$order_id', '$final_total', '$payment_method', '$transaction_code', 'pending')"
                );
                

                mysqli_query($ocon, "DELETE FROM cart_items WHERE cart_id = '$cart_id'");
                

                mysqli_commit($ocon);
                

                header("Location: orderthanhcong.php?order_id=$order_id");
                exit();
                
            } else {
                throw new Exception("Lỗi tạo đơn hàng!");
            }
            
        } catch (Exception $e) {
            mysqli_rollback($ocon);
            $message[] = "Đặt hàng thất bại: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="checkout-container">
    <div>

        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng</h2>
            
            <?php if (mysqli_num_rows($all_addresses) > 0): ?>
                <?php mysqli_data_seek($all_addresses, 0); ?>
                <?php while ($addr = mysqli_fetch_assoc($all_addresses)): ?>
                    <label class="address-card <?php echo $addr['is_default'] ? 'selected' : ''; ?>">
                        <input type="radio" name="selected_address" value="<?php echo $addr['address_id']; ?>" 
                               <?php echo $addr['is_default'] ? 'checked' : ''; ?>>
                        <strong><?php echo htmlspecialchars($addr['receiver_name']); ?></strong> - 
                        <?php echo htmlspecialchars($addr['receiver_phone']); ?>
                        <br>
                        <span class="address-text">
                            <?php echo htmlspecialchars($addr['specific_address'] . ', ' . $addr['ward'] . ', ' . $addr['district'] . ', ' . $addr['province']); ?>
                        </span>
                        <?php if ($addr['is_default']): ?>
                            <span class="default-badge"> ✓ Mặc định</span>
                        <?php endif; ?>
                    </label>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-address">Bạn chưa có địa chỉ giao hàng. <a href="#">Thêm địa chỉ mới</a></p>
            <?php endif; ?>
        </div>


        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-shopping-bag"></i> Sản phẩm (<?php echo count($cart_items); ?>)</h2>
            
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p>Số lượng: <strong><?php echo $item['quantity']; ?></strong></p>
                        <p class="item-price"><?php echo number_format($item['discounted_price'], 0, ',', '.'); ?>₫</p>
                    </div>
                    <div class="item-total">
                        <strong class="item-price">
                            <?php echo number_format($item['discounted_price'] * $item['quantity'], 0, ',', '.'); ?>₫
                        </strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-credit-card"></i> Phương thức thanh toán</h2>
            
            <div class="payment-method">
                <label class="payment-option selected">
                    <input type="radio" name="payment_method" value="COD" checked>
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Thanh toán khi nhận hàng (COD)</span>
                </label>
                
                <label class="payment-option disabled">
                    <input type="radio" name="payment_method" value="online" disabled>
                    <i class="fas fa-credit-card"></i>
                    <span>Thanh toán online</span>
                </label>
            </div>
        </div>


        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-sticky-note"></i> Ghi chú đơn hàng</h2>
            <textarea class="notes-textarea" name="order_notes" placeholder="Ghi chú thêm cho người bán (tùy chọn)..."></textarea>
        </div>
    </div>


    <div>
        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-file-invoice-dollar"></i> Tóm tắt đơn hàng</h2>
            

            <form method="post" class="coupon-section">
                <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá" 
                       value="<?php echo htmlspecialchars($coupon_code); ?>">
                <button type="submit" name="apply_coupon">Áp dụng</button>
            </form>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message[0], 'thành công') !== false ? 'success' : 'error'; ?>">
                    <i class="fas <?php echo strpos($message[0], 'thành công') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message[0]; ?>
                </div>
            <?php endif; ?>
            
            <div class="price-summary">
                <div class="price-row">
                    <span>Tạm tính:</span>
                    <strong><?php echo number_format($total_price, 0, ',', '.'); ?>₫</strong>
                </div>
                
                <div class="price-row">
                    <span>Phí vận chuyển:</span>
                    <strong><?php echo number_format($shipping_fee, 0, ',', '.'); ?>₫</strong>
                </div>
                
                <?php if ($discount > 0): ?>
                <div class="price-row discount">
                    <span>Giảm giá:</span>
                    <strong>-<?php echo number_format($discount, 0, ',', '.'); ?>₫</strong>
                </div>
                <?php endif; ?>
                
                <div class="price-row total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($final_total, 0, ',', '.'); ?>₫</span>
                </div>
            </div>
            
            <form method="post" id="checkout-form">
                <input type="hidden" name="address_id" id="address_id_input" 
                       value="<?php echo $default_address ? $default_address['address_id'] : ''; ?>">
                <input type="hidden" name="payment_method" id="payment_method_input" value="COD">
                <input type="hidden" name="notes" id="notes_input" value="">
                <input type="hidden" name="coupon_code" value="<?php echo htmlspecialchars($coupon_code); ?>">
                
                <button type="submit" name="place_order" class="place-order-btn">
                    <i class="fas fa-check-circle"></i> Đặt hàng ngay
                </button>
            </form>
            
            <p class="secure-text">
                <i class="fas fa-shield-alt"></i> Đảm bảo thanh toán an toàn & bảo mật
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>

document.querySelectorAll('.address-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        document.getElementById('address_id_input').value = radio.value;
    });
});


document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        if (this.querySelector('input').disabled) return;
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        document.getElementById('payment_method_input').value = radio.value;
    });
});


document.querySelector('.notes-textarea').addEventListener('change', function() {
    document.getElementById('notes_input').value = this.value;
});


document.getElementById('checkout-form').addEventListener('submit', function(e) {
    const addressId = document.getElementById('address_id_input').value;
    if (!addressId) {
        e.preventDefault();
        alert(' Vui lòng chọn địa chỉ giao hàng!');
        return false;
    }
    
    return confirm(' Xác nhận đặt hàng với tổng tiền ' + '<?php echo number_format($final_total, 0, ',', '.'); ?>₫' + '?');
});
</script>

</body>
</html>
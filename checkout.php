<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = [];

// XỬ LÝ MUA NGAY (từ nút "Mua ngay" trên trang sản phẩm)
$buy_now_items = [];
$is_buy_now = false;

if (isset($_GET['id']) && isset($_GET['qty'])) {
    // Mua ngay 1 sản phẩm
    $book_id = intval($_GET['id']);
    $quantity = intval($_GET['qty']);
    
    $book_query = mysqli_query($ocon, 
        "SELECT b.*, (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) as image
         FROM books b WHERE b.book_id = $book_id AND b.status = 'active'"
    );
    
    if (mysqli_num_rows($book_query) > 0) {
        $book = mysqli_fetch_assoc($book_query);
        $buy_now_items[] = [
            'book_id' => $book['book_id'],
            'title' => $book['title'],
            'discounted_price' => $book['discounted_price'],
            'quantity' => $quantity,
            'image' => $book['image']
        ];
        $is_buy_now = true;
    }
} 
// XỬ LÝ THANH TOÁN TỪ GIỎ HÀNG
else {
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

    while ($item = mysqli_fetch_assoc($cart_items_query)) {
        $buy_now_items[] = $item;
    }
}

// Nếu không có sản phẩm nào
if (empty($buy_now_items)) {
    header("Location: index.php");
    exit();
}

// TÍNH TỔNG TIỀN
$total_price = 0;
foreach ($buy_now_items as $item) {
    $total_price += $item['discounted_price'] * $item['quantity'];
}

$shipping_fee = 15000;

// XỬ LÝ MÃ GIẢM GIÁ
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

// XỬ LÝ ĐẶT HÀNG
if (isset($_POST['place_order'])) {
    $address_selection = isset($_POST['address_selection']) ? $_POST['address_selection'] : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'COD';
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($ocon, trim($_POST['notes'])) : '';
    
    if (empty($address_selection)) {
        $message[] = "Vui lòng chọn địa chỉ giao hàng!";
    } else {
        mysqli_begin_transaction($ocon);
        
        try {
            // Lấy hoặc tạo địa chỉ
            $address_query = mysqli_query($ocon, "SELECT * FROM addresses WHERE user_id = '$user_id' LIMIT 1");
            
            if (mysqli_num_rows($address_query) == 0) {
                // Tạo địa chỉ mặc định nếu chưa có
                if ($address_selection == 'address1') {
                    $addr_data = [
                        'name' => 'Người nhận',
                        'phone' => '0123456789',
                        'province' => 'Hà Nội',
                        'district' => 'Đống Đa',
                        'ward' => 'Chùa Bộc',
                        'specific' => '12 Chùa Bộc'
                    ];
                } else {
                    $addr_data = [
                        'name' => 'Người nhận',
                        'phone' => '0123456789',
                        'province' => 'Hà Nội',
                        'district' => 'Thị xã Sơn Tây',
                        'ward' => 'Phố Thanh Vị',
                        'specific' => 'Số 338'
                    ];
                }
                
                mysqli_query($ocon, "INSERT INTO addresses (user_id, receiver_name, receiver_phone, province, district, ward, specific_address, is_default) 
                                    VALUES ('$user_id', '{$addr_data['name']}', '{$addr_data['phone']}', '{$addr_data['province']}', '{$addr_data['district']}', '{$addr_data['ward']}', '{$addr_data['specific']}', 1)");
                $address_id = mysqli_insert_id($ocon);
            } else {
                $addr = mysqli_fetch_assoc($address_query);
                $address_id = $addr['address_id'];
            }
            
            $order_query = "INSERT INTO orders (user_id, address_id, total_amount, payment_method, payment_status, shipping_fee, order_status) 
                           VALUES ('$user_id', '$address_id', '$final_total', '$payment_method', 'pending', '$shipping_fee', 'pending')";
            
            if (mysqli_query($ocon, $order_query)) {
                $order_id = mysqli_insert_id($ocon);
                
                foreach ($buy_now_items as $item) {
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
                
                // Xóa giỏ hàng nếu không phải mua ngay
                if (!$is_buy_now && isset($cart_id)) {
                    mysqli_query($ocon, "DELETE FROM cart_items WHERE cart_id = '$cart_id'");
                }
                
                mysqli_commit($ocon);
                
                header("Location: order.php?status=pending");
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
    
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .checkout-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .address-card {
            border: 2px solid #e5e7eb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.8rem;
            cursor: pointer;
            transition: 0.3s;
            position: relative;
        }

        .address-card:hover {
            border-color: var(--primary);
        }

        .address-card.selected {
            border-color: var(--primary);
            background: #f0f9ff;
        }

        .address-card input[type="radio"] {
            margin-right: 0.8rem;
        }

        .address-text {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 80px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h4 {
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .item-price {
            color: var(--orange);
            font-weight: 600;
        }

        .item-total {
            text-align: right;
        }

        .payment-method {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .payment-option {
            border: 2px solid #e5e7eb;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .payment-option:hover {
            border-color: var(--primary);
        }

        .payment-option.selected {
            border-color: var(--primary);
            background: #f0f9ff;
        }

        .payment-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .payment-option i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .notes-textarea {
            width: 100%;
            min-height: 100px;
            padding: 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
        }

        .coupon-section {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .coupon-section input {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .coupon-section button {
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .coupon-section button:hover {
            background: var(--accent);
        }

        .message {
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .price-summary {
            margin-top: 1rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .price-row.discount {
            color: var(--orange);
        }

        .price-row.total {
            border-top: 2px solid #e5e7eb;
            border-bottom: none;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-top: 0.5rem;
        }

        .place-order-btn {
            width: 100%;
            padding: 1rem;
            background: var(--orange);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1rem;
            transition: 0.3s;
        }

        .place-order-btn:hover {
            background: #ea580c;
            transform: translateY(-2px);
        }

        .secure-text {
            text-align: center;
            color: #6b7280;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .secure-text i {
            color: #10b981;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="checkout-section">
    <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ đã lưu</h2> 
    
    <div class="address-list">
        
        <label class="address-card selected"> 
            <input type="radio" name="selected_address" value="address1" checked>
            
            <div class="address-info-group"> 
                <span class="recipient-line">
                    <strong>Nguyễn Văn A</strong> | 0987.654.321
                </span>
                
                <span class="full-address-line">
                    12 Chùa Bộc - Phường Quang Trung - Đống Đa - Hà Nội
                </span>
            </div>
            
            </label>
        
        <label class="address-card">
            <input type="radio" name="selected_address" value="address2">
            
            <div class="address-info-group">
                <span class="recipient-line">
                    <strong>Trần Thị B</strong> | 0333.444.555
                </span>
                <span class="full-address-line">
                    Số 338 - Phố Thanh Vị - Thị xã Sơn Tây - Hà Nội
                </span>
            </div>
        </label>
        <button type="button" class="add-address-btn is-active">
        <i class="fas fa-plus"></i> Thêm địa chỉ mới
    </button>
    </div>
</div>
        <!-- SẢN PHẨM -->
        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-shopping-bag"></i> Sản phẩm (<?php echo count($buy_now_items); ?>)</h2>
            
            <?php foreach ($buy_now_items as $item): ?>
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

        <!-- PHƯƠNG THỨC THANH TOÁN -->
        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-credit-card"></i> Phương thức thanh toán</h2>
            
            <div class="payment-method">
                <label class="payment-option selected">
                    <input type="radio" name="payment_method" value="COD" checked>
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Thanh toán khi nhận hàng (COD)</span>
                </label>
            </div>
        </div>

        <!-- GHI CHÚ -->
        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-sticky-note"></i> Ghi chú đơn hàng</h2>
            <textarea class="notes-textarea" name="order_notes" placeholder="Ghi chú thêm cho người bán (tùy chọn)..."></textarea>
        </div>
    </div>

    <!-- TÓM TẮT ĐƠN HÀNG -->
    <div>
        <div class="checkout-section">
            <h2 class="section-title"><i class="fas fa-file-invoice-dollar"></i> Tóm tắt đơn hàng</h2>
            
            <!-- MÃ GIẢM GIÁ -->
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
                <input type="hidden" name="address_selection" id="address_selection_input" value="address1">
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
// Xử lý chọn địa chỉ
document.querySelectorAll('.address-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        document.getElementById('address_selection_input').value = radio.value;
    });
});

// Xử lý chọn phương thức thanh toán
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

// Lưu ghi chú
document.querySelector('.notes-textarea').addEventListener('change', function() {
    document.getElementById('notes_input').value = this.value;
});

// Xác nhận đặt hàng
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    const addressSelection = document.getElementById('address_selection_input').value;
    if (!addressSelection) {
        e.preventDefault();
        alert(' Vui lòng chọn địa chỉ giao hàng!');
        return false;
    }
    
    return confirm(' Xác nhận đặt hàng với tổng tiền ' + '<?php echo number_format($final_total, 0, ',', '.'); ?>₫' + '?');
});
</script>

</body>
</html>

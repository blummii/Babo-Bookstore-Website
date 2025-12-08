-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 05, 2025 lúc 05:46 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlbansach`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_phone` varchar(20) NOT NULL,
  `province` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `specific_address` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `receiver_name`, `receiver_phone`, `province`, `district`, `ward`, `specific_address`, `is_default`) VALUES
(1, 1, 'Nguyễn Văn An', '0901234567', 'Hà Nội', 'Cầu Giấy', 'Dịch Vọng', 'số 12 Trần Quốc Hoàn', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `authors`
--

INSERT INTO `authors` (`author_id`, `name`, `bio`) VALUES
(1, 'Nguyễn Nhật Ánh', 'Nhà văn nổi tiếng Việt Nam'),
(2, 'Haruki Murakami', 'Nhà văn Nhật Bản'),
(3, 'Gosho Aoyama', 'Tác giả viết truyện nổi tiếng Nhật Bản'),
(4, 'Koyoharu Gotouge', 'Cây viết truyện nổi tiếng Nhật Bản'),
(5, 'Kohei Horikoshi', 'Cây bút viết manga nổi tiếng Nhật Bản'),
(6, 'Nam Cao', 'Nhà văn hiện thực tiêu biểu của văn học Việt Nam'),
(7, 'Ngô Tất Tố', 'Nhà văn có ảnh hưởng lớn ở Việt Nam');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `publish_year` year(4) NOT NULL,
  `pages` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `cover_type` enum('hard','soft','','') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `publisher_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','hidden','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `books`
--

INSERT INTO `books` (`book_id`, `title`, `slug`, `description`, `publish_year`, `pages`, `weight`, `cover_type`, `price`, `discounted_price`, `stock_quantity`, `category_id`, `publisher_id`, `created_at`, `updated_at`, `status`) VALUES
(1, 'Mắt biếc', 'mat-biec', 'Tiểu thuyết nổi tiếng của Nguyễn Nhật Ánh', '2015', 250, 300, 'soft', 85000.00, 70000.00, 100, 2, 1, '2025-12-01 15:47:18', '2025-12-01 15:47:18', 'active'),
(2, 'Rừng Nauy', 'rung-nauy', 'Tác phẩm của Haruki Murakami', '2010', 350, 400, 'soft', 120000.00, 95000.00, 80, 1, 2, '2025-12-01 15:47:18', '2025-12-01 15:47:18', 'active'),
(3, 'Thám tử lừng danh Conan', NULL, 'Bộ truyện tranh trinh thánh nổi tiếng khắp Châu Á', '1994', 80, 250, 'hard', 45000.00, 40000.00, 100, 10, 2, '2025-12-01 16:29:26', '2025-12-01 16:29:26', 'active'),
(4, 'Thanh gươm diệt quỷ', 'thanh-guom-diet-quy', 'Bộ truyện tranh hành động kịch tính kể về hành trình tiêu diệt loài quỷ của nhân vật chính Tanjiro', '2016', 350, 400, 'hard', 200000.00, 180000.00, 80, 9, 2, '2025-12-01 16:29:26', '2025-12-01 16:29:26', 'active'),
(5, 'Học viện siêu anh hừng', 'hoc-vien-sieu-anh-hung', 'Bộ truyện tranh hoành đống siêu kịch tính kể về hành trình trở thành người anh hùng vĩ đại nhất của nhân vật chính không có năng lực anh hùng Zuku', '2014', 500, 450, 'hard', 300000.00, 270000.00, 30, 9, 3, '2025-12-01 16:34:50', '2025-12-01 16:34:50', 'active'),
(6, 'Lão Hạc', '', 'Bộ truyện ngắn hiện thực nổi tiếng', '1943', 260, 230, 'soft', 60000.00, 55000.00, 100, 2, 3, '2025-12-01 16:34:50', '2025-12-01 16:34:50', 'active'),
(7, 'Tăt đèn', NULL, 'Tác phẩm văn học hiện thực nổi tiếng Việt Nam', '2016', 216, 264, 'soft', 55000.00, 45000.00, 145, 2, 3, '2025-12-01 16:37:08', '2025-12-01 16:37:08', 'active'),
(8, 'Chí Phèo', NULL, 'Truyện ngắn hiện thực nổi tiếng', '2022', 196, 250, 'soft', 50000.00, 45000.00, 98, 1, 2, '2025-12-01 16:48:00', '2025-12-01 16:48:00', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `book_authors`
--

CREATE TABLE `book_authors` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `book_authors`
--

INSERT INTO `book_authors` (`id`, `book_id`, `author_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 6, 6),
(4, 7, 7),
(5, 3, 3),
(6, 4, 4),
(7, 5, 5),
(8, 8, 6);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `created_at`) VALUES
(1, 1, '2025-12-01 15:48:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `book_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `parent_id`) VALUES
(1, 'Sách văn học', NULL),
(2, 'Tiểu thuyết', 1),
(3, 'Ngôn tình', 2),
(5, 'Đam mỹ', 2),
(6, 'Bách hợp', 2),
(7, 'Kinh tế', NULL),
(8, 'Kỹ năng sống', NULL),
(9, 'Truyện tranh', NULL),
(10, 'Trinh thám', 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_type` enum('percent','amount','','') NOT NULL,
  `min_order_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `quantity`) VALUES
(1, 'Sale10', 'Giảm 10%', 10.00, 'percent', 50000.00, '2025-12-01', '2025-12-17', 100);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupon_users`
--

CREATE TABLE `coupon_users` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `used_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupon_users`
--

INSERT INTO `coupon_users` (`id`, `coupon_id`, `user_id`, `used_at`) VALUES
(1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `images`
--

INSERT INTO `images` (`image_id`, `book_id`, `url`) VALUES
(1, 1, 'images\\matbiec.jpg\r\n'),
(2, 2, 'images\\rungnauy.jpg\r\n'),
(3, 6, 'images\\laohac.jpg'),
(4, 7, 'images\\tatden.jpg'),
(5, 3, 'images\\conan.jpg'),
(6, 4, 'images\\thanhguomdietquy.jpg'),
(7, 5, 'images\\hocviensieuanhhung.jpg'),
(8, 8, 'images\\chipheo.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('COD','online','','') NOT NULL,
  `payment_status` enum('pending','paid','failed','') NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','shipping','completed','canceled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `address_id`, `order_date`, `total_amount`, `payment_method`, `payment_status`, `shipping_fee`, `order_status`) VALUES
(1, 1, 1, '2025-12-01 15:50:34', 260000.00, 'COD', 'pending', 15000.00, 'pending');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('COD','online','','') NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `status` enum('pending','success','failed','') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `amount`, `method`, `transaction_code`, `status`, `created_at`) VALUES
(1, 1, 260000.00, 'COD', 'TNX_12012025', 'pending', '2025-12-01 15:52:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `publishers`
--

CREATE TABLE `publishers` (
  `publisher_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `publishers`
--

INSERT INTO `publishers` (`publisher_id`, `name`) VALUES
(1, 'NXB Kim Đồng'),
(2, 'NXB Trẻ'),
(3, 'NXB Lao Động'),
(4, 'NXB Văn Học');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `book_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 5, 'Sách rất hay!', '2025-12-01 15:53:36'),
(2, 1, 2, 4, 'Ý nghĩa và truyền cảm hứng', '2025-12-01 15:53:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shippers`
--

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hotline` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shippers`
--

INSERT INTO `shippers` (`shipper_id`, `name`, `hotline`) VALUES
(1, 'Giao hàng nhanh', '19002021'),
(2, 'Giao hàng tiết kiệm', '18006092');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_tracking`
--

CREATE TABLE `shipping_tracking` (
  `track_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `shipper_id` int(11) NOT NULL,
  `status_detail` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shipping_tracking`
--

INSERT INTO `shipping_tracking` (`track_id`, `order_id`, `shipper_id`, `status_detail`, `updated_at`) VALUES
(1, 1, 1, 'Đã tiếp nhận đơn hàng', '2025-12-01 15:56:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` enum('Nữ','Nam','Khác','') NOT NULL,
  `dob` date NOT NULL,
  `role` enum('customer','admin','','') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','banned','','') NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `gender`, `dob`, `role`, `created_at`, `status`, `reset_token`, `token_expiry`, `login_attempts`, `last_attempt`) VALUES
(1, 'Nguyễn Văn An', 'nguyenan@gmail.com', '$2y$10$C2c0l5r5P3lat8zb7MP9.eQ2pn62yW6EM/jletKD/h2GRklNWtsEe', '0901234567', 'Nam', '2015-06-23', 'customer', '2025-12-01 15:23:34', 'active', NULL, NULL, 0, NULL),
(2, 'Phạm Thanh Hồng', 'thanhhong@gmail.com', 'admin123', '0987654321', 'Nữ', '2015-10-12', 'admin', '2025-12-01 15:25:11', 'active', NULL, NULL, 0, NULL),
(4, 'Lê Phương Thảo', 'lephuongthao14072005@gmail.com', '$2y$10$.Yj/FYPtt21I.BU7GfXDlOLLdLt8FKEA/JMgGzIbL27sGjE7ihAEe', '', 'Nữ', '0000-00-00', 'customer', '2025-12-04 02:11:30', 'active', NULL, NULL, 0, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`);

--
-- Chỉ mục cho bảng `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`);

--
-- Chỉ mục cho bảng `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

--
-- Chỉ mục cho bảng `book_authors`
--
ALTER TABLE `book_authors`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`);

--
-- Chỉ mục cho bảng `coupon_users`
--
ALTER TABLE `coupon_users`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`);

--
-- Chỉ mục cho bảng `publishers`
--
ALTER TABLE `publishers`
  ADD PRIMARY KEY (`publisher_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Chỉ mục cho bảng `shippers`
--
ALTER TABLE `shippers`
  ADD PRIMARY KEY (`shipper_id`);

--
-- Chỉ mục cho bảng `shipping_tracking`
--
ALTER TABLE `shipping_tracking`
  ADD PRIMARY KEY (`track_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `book_authors`
--
ALTER TABLE `book_authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `coupon_users`
--
ALTER TABLE `coupon_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `publishers`
--
ALTER TABLE `publishers`
  MODIFY `publisher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `shippers`
--
ALTER TABLE `shippers`
  MODIFY `shipper_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `shipping_tracking`
--
ALTER TABLE `shipping_tracking`
  MODIFY `track_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

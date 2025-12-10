<?php
include 'connect.php';

// Lấy tên file hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Nếu chưa có $admin_name → set mặc định
$admin_name = $admin_name ?? "Admin";
?>

<style>
/* ========== ADMIN SIDEBAR ========== */
.admin_sidebar {
    width: 260px;
    background: #16515fff;
    color: #fff;
    padding: 2rem 1.4rem;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.admin_brand {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 2.5rem;
}

.admin_profile {
    text-align: center;
    margin-bottom: 2rem;
}

.admin_profile img {
    width: 70px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.admin_profile p {
    opacity: 0.9;
}

.admin_menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.admin_menu a {
    color: #ffffff;
    text-decoration: none;
    padding: .6rem .8rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: .25s ease;
    font-size: 16px;
}

.admin_menu a:hover,
.admin_menu a.active {
    background: #458298ff;
}

/* RESPONSIVE */
@media (max-width:768px){
    .admin_sidebar {
        display: none;
    }
}
</style>

<aside class="admin_sidebar">

    <div class="admin_brand">Quản lý Trang</div>

    <div class="admin_profile">
        <img src="images/user.png" alt="Admin">
        <p><?php echo $admin_name; ?></p>
    </div>

    <nav class="admin_menu">

        <a href="admin_dashboard.php" class="<?= ($current_page=='admin_dashboard.php')?'active':'' ?>">
            <i class="fa fa-chart-line"></i> Dashboard
        </a>

        <a href="#" class="<?= ($current_page=='#')?'active':'' ?>">
            <i class="fa fa-book"></i> Quản lý sách
        </a>

        <a href="admin_usermanagement.php" class="<?= ($current_page=='admin_usermanagement.php')?'active':'' ?>">
            <i class="fa fa-users"></i> Quản lý Người dùng
        </a>

        <a href="#" class="<?= ($current_page=='#')?'active':'' ?>">
            <i class="fa fa-shopping-cart"></i> Quản lý Đơn hàng
        </a>

        <a href="#" class="<?= ($current_page=='#')?'active':'' ?>">
            <i class="fa fa-comments"></i> Quản lý Bình luận
        </a>

        <a href="#" class="<?= ($current_page=='#')?'active':'' ?>">
            <i class="fa fa-chart-pie"></i> Thống kê
        </a>

        <a href="logout.php">
            <i class="fa fa-sign-out-alt"></i> Đăng xuất
        </a>

    </nav>

</aside>

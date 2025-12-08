<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$q = mysqli_query($ocon, "SELECT * FROM users WHERE user_id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($q);

$full_name = $user['full_name'];
$email     = $user['email'];
$phone     = $user['phone'];
$gender    = $user['gender'];
$dob       = $user['dob'];
$role      = $user['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thông tin tài khoản</title>
    <link rel="stylesheet" href="css/account.css">
     <link rel="stylesheet" href="css/review.css">
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
</head>
<body>

<?php include 'header.php'; ?>
<?php if (isset($_GET['updated'])): ?>
<div class="alert-success">
    Cập nhật thông tin tài khoản thành công!
</div>
<?php endif; ?>


<div class="account_wrapper">
    <h2>Thông tin tài khoản</h2>

    <form action="update_account.php" method="POST" class="account_form">

        <label>Họ và tên:</label>
        <input type="text" name="full_name" value="<?= $full_name ?>">

        <label>Email (không sửa):</label>
        <input type="email" value="<?= $email ?>" disabled>

        <label>Số điện thoại:</label>
        <input type="text" name="phone" value="<?= $phone ?>">

        <label>Giới tính:</label>
        <select name="gender">
            <option value="Nam" <?= $gender=="Nam"?"selected":"" ?>>Nam</option>
            <option value="Nữ" <?= $gender=="Nữ"?"selected":"" ?>>Nữ</option>
            <option value="Khác" <?= $gender=="Khác"?"selected":"" ?>>Khác</option>
        </select>

        <label>Ngày sinh:</label>
        <input type="date" name="dob" value="<?= $dob ?>">

        <label>Quyền:</label>
        <input type="text" value="<?= $role ?>" disabled>

        <button class="save_btn">Lưu thay đổi</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>

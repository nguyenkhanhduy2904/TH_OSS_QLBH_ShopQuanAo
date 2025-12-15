<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: ../../controllers/user_login.php');
    exit;
}

include '../../config/database.php';
include '../../models/User.php';

$conn = getConnection();
$userModel = new User($conn);
$userId = $_SESSION['MaNguoiDung'];

$profileSuccess = '';
$profileError = '';
$passwordSuccess = '';
$passwordError = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($fullname)) {
        $profileError = "Họ và tên không được để trống.";
    } else {
        if ($userModel->updateProfile($userId, $fullname, $phone, $address)) {
            $_SESSION['TenNguoiDung'] = $fullname;
            $profileSuccess = "Cập nhật thông tin thành công!";
        } else {
            $profileError = "Không có gì thay đổi hoặc đã xảy ra lỗi.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = "Vui lòng điền đầy đủ các trường.";
    } elseif (strlen($newPassword) < 6) {
        $passwordError = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = "Mật khẩu xác nhận không khớp.";
    } else {
        $result = $userModel->changePassword($userId, $oldPassword, $newPassword);
        if ($result) {
            $passwordSuccess = "Đổi mật khẩu thành công!";
        } else {
            $passwordError = "Mật khẩu cũ không đúng.";
        }
    }
}

$user = $userModel->getUserById($userId);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Fashion Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #5e72e4; --secondary-color: #8898aa; }
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fe; }
        .brand-font { font-family: 'Pacifico', cursive; color: var(--primary-color) !important; font-size: 1.6rem; }
        .navbar { background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .form-control:focus { box-shadow: none; border-color: var(--primary-color); }
        .btn-custom { background-color: var(--primary-color); color: white; border: none; transition: 0.3s; }
        .btn-custom:hover { background-color: #324cdd; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand brand-font" href="../../index.php"><i class="fas fa-shirt me-2"></i>Fashion Shop</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="orders.php" class="text-decoration-none text-muted fw-bold small me-3">
                <i class="fas fa-receipt me-1"></i> Đơn hàng của tôi
            </a>
            <a href="profile.php" class="text-decoration-none text-primary fw-bold small">
                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['TenNguoiDung']); ?>
            </a>
            <a href="../../controllers/logout.php" class="ms-3 text-danger" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h2 class="fw-bold text-dark mb-4"><i class="fas fa-user-edit me-2 text-primary"></i>Hồ sơ cá nhân</h2>
            
            <!-- Profile Update Form -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Thông tin tài khoản</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($profileSuccess): ?><div class="alert alert-success"><?php echo $profileSuccess; ?></div><?php endif; ?>
                    <?php if ($profileError): ?><div class="alert alert-danger"><?php echo $profileError; ?></div><?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Tên đăng nhập</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['TenDangNhap']); ?>" disabled readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['Email']); ?>" disabled readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Họ và tên</label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['TenNguoiDung']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['SoDienThoai']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Địa chỉ</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['DiaChi']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom px-4">Lưu thay đổi</button>
                    </form>
                </div>
            </div>

            <!-- Password Change Form -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Đổi mật khẩu</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($passwordSuccess): ?><div class="alert alert-success"><?php echo $passwordSuccess; ?></div><?php endif; ?>
                    <?php if ($passwordError): ?><div class="alert alert-danger"><?php echo $passwordError; ?></div><?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Mật khẩu cũ</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-custom px-4">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
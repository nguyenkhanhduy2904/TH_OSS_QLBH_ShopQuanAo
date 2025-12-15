<?php
session_start();

include "../config/database.php"; 
include "../models/Admin.php";         

$error = "";
$success = "";
$form_data = array("username" => "", "email" => "", "fullname" => "", "password" => "", "password_confirm" => "");

/*if (!isset($_SESSION["adminid"])) { 
    header("Location: admin_login.php"); 
    exit; 
}*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form_data["username"] = trim($_POST["username"] ?? "");
    $form_data["email"]    = trim($_POST["email"] ?? "");
    $form_data["fullname"] = trim($_POST["fullname"] ?? "");
    $form_data["password"] = trim($_POST["password"] ?? "");
    $form_data["password_confirm"] = trim($_POST["password_confirm"] ?? "");
    
    if (empty($form_data["username"])) { 
        $error = "Vui lòng nhập tên đăng nhập!"; 
    } elseif (empty($form_data["email"])) { 
        $error = "Vui lòng nhập Email!"; 
    } elseif (!filter_var($form_data["email"], FILTER_VALIDATE_EMAIL)) { 
        $error = "Email không hợp lệ!"; 
    } elseif (empty($form_data["fullname"])) { 
        $error = "Vui lòng nhập họ tên!"; 
    } elseif (empty($form_data["password"])) { 
        $error = "Vui lòng nhập mật khẩu!"; 
    } elseif (strlen($form_data["password"]) < 6) { 
        $error = "Mật khẩu phải có ít nhất 6 ký tự!"; 
    } elseif ($form_data["password"] !== $form_data["password_confirm"]) { 
        $error = "Mật khẩu xác nhận không khớp!"; 
    } else {
        $conn = getConnection();
        $adminModel = new Admin($conn);
        $result = $adminModel->register($form_data["username"], $form_data["password"]);

        if ($result === true) {
            $success = "Tạo tài khoản quản trị viên thành công!";
            $form_data = array("username" => "", "email" => "", "fullname" => "", "password" => "", "password_confirm" => "");
        } elseif ($result === "Username đã tồn tại.") {
            $error = "Tên đăng nhập này đã được sử dụng!";
        } else {
            $error = "Đã có lỗi xảy ra trong quá trình tạo tài khoản (Lỗi DB).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký Admin - Fashion Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); padding: 20px;">
    
    <div class="card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 500px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-user-shield" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">Thêm quản trị viên</h3>
                <p class="text-muted small">Tạo tài khoản truy cập hệ thống quản trị</p>
            </div>
            
            <div class="alert alert-warning d-flex align-items-center justify-content-center py-2 mb-4" role="alert">
                <i class="fas fa-lock me-2"></i> 
                <small>Chức năng chỉ dành cho Admin cấp cao</small>
            </div>
            
            <?php if (!empty($success)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label fw-bold text-secondary small">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($form_data["username"]); ?>" required placeholder="Nhập username">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="fullname" class="form-label fw-bold text-secondary small">Họ và tên</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                   value="<?php echo htmlspecialchars($form_data["fullname"]); ?>" required placeholder="Nhập họ tên">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-bold text-secondary small">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data["email"]); ?>" required placeholder="name@example.com">
                    </div>
                    <div class="form-text text-muted small fst-italic">* Email và Họ tên dùng để liên hệ nội bộ.</div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="password" class="form-label fw-bold text-secondary small">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="******">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirm" class="form-label fw-bold text-secondary small">Xác nhận</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-check-double"></i></span>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required placeholder="******">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold shadow-sm">
                    <i class="fas fa-user-plus me-2"></i> Tạo tài khoản
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="../views/admin/dashboard.php" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Quay về trang quản trị
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
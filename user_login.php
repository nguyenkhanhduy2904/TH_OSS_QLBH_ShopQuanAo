<?php
session_start();

include '../config/database.php';
include '../models/User.php';

$error = '';
$username = '';

if (isset($_SESSION['userid'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_SESSION['adminid'])) {
    header('Location: ../views/admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        $conn = getConnection();
        $userModel = new User($conn);

        $user = $userModel->login($username, $password);

        if ($user) {
            $_SESSION['MaNguoiDung'] = $user->userid;
            $_SESSION['TenNguoiDung'] = $user->fullname;
            $_SESSION['user_type'] = 'user';

            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Thông tin đăng nhập không chính xác!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Fashion Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fe;
            min-height: 100vh;
        }
        .brand-font {
            font-family: 'Pacifico', cursive;
            color: #5e72e4;
        }
        .card {
            border: none;
            border-radius: 20px;
        }
        .btn-custom {
            background-color: #5e72e4;
            color: white;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background-color: #324cdd;
            color: white;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #5e72e4;
        }
        .input-group-text {
            background-color: #fff;
            color: #aaa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .input-group:focus-within .input-group-text {
            border-color: #5e72e4;
            color: #5e72e4;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    
    <div class="card shadow-lg" style="width: 100%; max-width: 420px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-shirt fa-3x mb-3 text-primary opacity-75"></i>
                <h2 class="fw-bold brand-font mb-0">Fashion Shop</h2>
                <p class="text-muted small mt-2">Đăng nhập để khám phá phong cách của bạn</p>
            </div>
            
            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> 
                    <div><?php echo htmlspecialchars($error); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label fw-bold small text-secondary">Tài khoản</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username); ?>" required placeholder="Username / Email / SĐT">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold small text-secondary">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Nhập mật khẩu...">
                    </div>
                </div>

                <button type="submit" class="btn btn-custom w-100 py-2 rounded-pill shadow-sm">
                    ĐĂNG NHẬP
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">
                    Chưa có tài khoản? 
                    <a href="user_register.php" class="text-decoration-none fw-bold text-danger">Đăng ký ngay</a>
                </p>
                <div class="border-top pt-3 mt-3">
                    <a href="../index.php" class="text-decoration-none text-muted small">
                        <i class="fas fa-arrow-left me-1"></i> Quay về trang chủ
                    </a>
                </div>
                <div class="mt-2">
                     <a href="admin_login.php" class="text-decoration-none text-muted small fst-italic">
                        <i class="fas fa-user-shield me-1"></i> Quản trị viên
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
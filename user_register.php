<?php
session_start();

include '../config/database.php';
include '../models/User.php';

$error = '';
$success = '';
$form_data = [
    'username' => '',
    'email' => '',
    'fullname' => '',
    'phone' => '',
    'address' => ''
];

if (isset($_SESSION['userid'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $form_data = [
        'username' => $username,
        'email' => $email,
        'fullname' => $fullname,
        'phone' => $phone,
        'address' => $address
    ];

    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($fullname)) {
        $error = 'Vui lòng điền đầy đủ các trường bắt buộc (*)';
    } elseif (strlen($username) < 3) {
        $error = 'Tên đăng nhập phải có ít nhất 3 ký tự!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không trùng khớp!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Địa chỉ Email không đúng định dạng!';
    } else {
        $conn = getConnection();
        $userModel = new User($conn);

        try {
            $userModel->register($username, $password, $email, $fullname, $phone, $address);
            $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
            $form_data = array_fill_keys(array_keys($form_data), '');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký thành viên - Fashion Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
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
        .required-star { color: red; margin-left: 3px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    
    <div class="card w-100" style="max-width: 650px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fas fa-shirt fa-3x mb-2 text-primary opacity-75"></i>
                <h2 class="fw-bold brand-font mb-0">Đăng Ký Thành Viên</h2>
                <p class="text-muted small">Tạo tài khoản để khám phá phong cách của bạn</p>
            </div>

            <?php if (!empty($success)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <div class="mt-2"><a href="user_login.php" class="btn btn-sm btn-success fw-bold">Đăng nhập ngay</a></div>
                </div>
            <?php } ?>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <form method="POST" action="">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Tên đăng nhập <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="username" 
                                   value="<?php echo htmlspecialchars($form_data['username']); ?>" required placeholder="user123">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Họ và tên <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" name="fullname" 
                                   value="<?php echo htmlspecialchars($form_data['fullname']); ?>" required placeholder="Nguyễn Văn A">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Email <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" required placeholder="name@example.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Số điện thoại</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($form_data['phone']); ?>" placeholder="0901234567">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Địa chỉ giao hàng</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" class="form-control" name="address" 
                                   value="<?php echo htmlspecialchars($form_data['address']); ?>" placeholder="Số nhà, Đường, Quận/Huyện...">
                    </div>
                </div>
                
                <hr class="my-4 text-muted opacity-25">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Mật khẩu <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" required placeholder="******">
                        </div>
                        <small class="text-muted fst-italic" style="font-size: 0.75rem">Tối thiểu 6 ký tự</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Xác nhận mật khẩu <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                            <input type="password" class="form-control" name="confirm_password" required placeholder="******">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-custom w-100 py-2 rounded-pill shadow-sm fs-5">
                    <i class="fas fa-user-plus me-2"></i> ĐĂNG KÝ
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted small">
                    Đã có tài khoản? <a href="user_login.php" class="text-decoration-none fw-bold text-primary">Đăng nhập ngay</a>
                </p>
                <a href="../index.php" class="text-decoration-none text-muted small">
                    <i class="fas fa-home me-1"></i> Quay về trang chủ
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
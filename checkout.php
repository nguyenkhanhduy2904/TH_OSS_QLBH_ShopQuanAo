<?php
session_start();

include '../../config/database.php';
include '../../models/Order.php';
include '../../models/User.php';

if (!isset($_SESSION['MaNguoiDung'])) {
    $_SESSION['redirect_after_login'] = 'views/user/checkout.php'; 
    header('Location: ../../controllers/user_login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: ../../index.php');
    exit;
}

$conn = getConnection();
$userModel = new User($conn);
$orderModel = new Order($conn);

$currentUser = $userModel->getUserById($_SESSION['MaNguoiDung']);
$defaultName = $currentUser['TenNguoiDung'] ?? '';
$defaultPhone = $currentUser['SoDienThoai'] ?? '';
$defaultAddress = $currentUser['DiaChi'] ?? '';

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['Gia'] * $item['qty'];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);

    if (empty($name) || empty($phone) || empty($address)) {
        $error = "Vui lòng điền đầy đủ thông tin giao hàng.";
    } else {
        try {
            $conn->beginTransaction();

            $orderId = $orderModel->createOrder($_SESSION['MaNguoiDung'], $total, $address, $phone, $note);

            if ($orderId) {
                foreach ($_SESSION['cart'] as $item) {
                    $orderModel->addOrderDetail(
                        $orderId,
                        $item['MaSanPham'],
                        $item['MaKichThuoc'],
                        $item['qty'],
                        $item['Gia']
                    );
                }

                $conn->commit();
                unset($_SESSION['cart']);
                header("Location: order_confirmation.php?id=" . $orderId);
                exit;
            } else {
                throw new Exception("Không thể tạo đơn hàng.");
            }

        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #5e72e4; }
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fe; }
        .brand-font { font-family: 'Pacifico', cursive; color: var(--primary-color) !important; font-size: 1.6rem; }
        .navbar { background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
        .form-control:focus { border-color: var(--primary-color); box-shadow: none; }
        .btn-custom { background-color: var(--primary-color); color: white; border: none; font-weight: bold; transition: 0.3s; }
        .btn-custom:hover { background-color: #324cdd; color: white; transform: translateY(-2px); }
        .summary-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand brand-font" href="../../index.php"><i class="fa-solid fa-shirt me-2"></i>Fashion Shop</a>
        <div class="ms-auto">
            <a href="cart.php" class="text-decoration-none text-muted fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại giỏ hàng
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h2 class="fw-bold text-dark"><i class="fa-solid fa-credit-card me-2 text-primary"></i>Thanh toán</h2>
        </div>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <i class="fa-solid fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST" action="">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Thông tin giao hàng</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Họ và tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($defaultName); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($defaultPhone); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Địa chỉ nhận hàng <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($defaultAddress); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Ghi chú đơn hàng (Tùy chọn)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
                        </div>

                        <hr class="my-4">
                        <h5 class="fw-bold mb-3">Phương thức thanh toán</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" checked>
                            <label class="form-check-label" for="cod">
                                <i class="fa-solid fa-money-bill-wave text-success me-2"></i> Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card bg-white">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Đơn hàng của bạn</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    <?php foreach($_SESSION['cart'] as $item) { ?>
                                    <tr>
                                        <td class="ps-4">
                                            <?php 
                                                $imgUrl = !empty($item['HinhAnh']) ? '../../uploads/products/' . $item['HinhAnh'] : 'https://via.placeholder.com/50';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="summary-img border">
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-dark"><?php echo htmlspecialchars($item['TenSanPham']); ?></div>
                                            <div class="small text-muted">Size: <?php echo htmlspecialchars($item['TenKichThuoc']); ?></div>
                                        </td>
                                        <td class="text-center text-muted">x<?php echo $item['qty']; ?></td>
                                        <td class="text-end pe-4 fw-bold">
                                            <?php echo number_format($item['Gia'] * $item['qty'], 0, ',', '.'); ?>₫
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-4">
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>Tạm tính</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Phí vận chuyển</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top">
                            <span class="fw-bold fs-5">Tổng cộng</span>
                            <span class="fw-bold fs-3 text-danger"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-custom w-100 py-3 rounded-pill shadow-sm fs-5">
                            ĐẶT HÀNG NGAY
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: ../../controllers/user_login.php');
    exit;
}

include '../../config/database.php';
include '../../models/Order.php';

$conn = getConnection();
$orderModel = new Order($conn);

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
    header('Location: ../../index.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if ($orderModel->cancelOrder($orderId, $_SESSION['MaNguoiDung'])) {
        header("Location: order_confirmation.php?id=" . $orderId);
        exit;
    } else {
        $msg = "Không thể hủy đơn hàng này (Có thể đơn đã được duyệt hoặc đang giao).";
    }
}

$order = $orderModel->getOrderById($orderId);

if (!$order || $order['MaNguoiDung'] != $_SESSION['MaNguoiDung']) {
    header('Location: orders.php');
    exit;
}

$orderDetails = $orderModel->getOrderItems($orderId);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $orderId; ?> - Fashion Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #5e72e4; --dark-text: #2d3436; }
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fe; color: var(--dark-text); }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .brand-font { font-family: 'Pacifico', cursive; color: var(--primary-color); }
        .btn-custom { background-color: var(--primary-color); border: none; color: white; font-weight: 700; transition: 0.3s; }
        .btn-custom:hover { background-color: #324cdd; color: white; transform: translateY(-2px); }
        .success-icon-box { width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3); }
        .cancel-icon-box { width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 5px 15px rgba(214, 48, 49, 0.3); }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <?php if ($msg) { ?>
                    <div class="alert alert-danger rounded-3 shadow-sm mb-4 border-0 text-center">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $msg; ?>
                    </div>
                <?php } ?>

                <div class="card">
                    <div class="card-body text-center p-5 border-bottom bg-white">
                        <?php if ($order['TrangThai'] == 3) { ?>
                            <div class="rounded-circle cancel-icon-box mb-3">
                                <i class="fas fa-times"></i>
                            </div>
                            <h2 class="fw-bold mb-2 text-danger">Đơn hàng đã hủy</h2>
                            <p class="text-muted">Đơn hàng này đã được hủy theo yêu cầu của bạn.</p>
                        <?php } else { ?>
                            <div class="rounded-circle success-icon-box mb-3">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Đặt hàng thành công!</h2>
                            <p class="text-muted">Cảm ơn bạn đã mua sắm tại <span class="brand-font">Fashion Shop</span></p>
                        <?php } ?>
                        
                        <div class="d-inline-block bg-light px-4 py-2 rounded-pill mt-3 border">
                            Mã đơn hàng: <span class="fw-bold text-dark fs-5">#<?php echo str_pad($order['MaDon'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-white">
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6 border-end-md">
                                <h6 class="fw-bold text-uppercase text-secondary small border-bottom pb-2 mb-3">Thông tin nhận hàng</h6>
                                <div class="mb-2"><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['TenNguoiDung'] ?? ''); ?></div>
                                <div class="mb-2"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['SoDienThoai'] ?? ''); ?></div>
                                <div class="mb-2"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['DiaChi'] ?? ''); ?></div>
                            </div>
                            <div class="col-md-6 ps-md-4">
                                <h6 class="fw-bold text-uppercase text-secondary small border-bottom pb-2 mb-3">Thông tin đơn hàng</h6>
                                <div class="mb-2"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?></div>
                                <div class="mt-3">
                                    <span class="text-muted me-2">Trạng thái:</span>
                                    <?php
                                        $s = $order['TrangThai'];
                                        $badgeClass = 'bg-secondary';
                                        $statusText = 'Không rõ';
                                        switch($s) {
                                            case 0: $badgeClass = 'bg-warning text-dark'; $statusText = 'Chờ duyệt'; break;
                                            case 1: $badgeClass = 'bg-info text-dark'; $statusText = 'Đang giao'; break;
                                            case 2: $badgeClass = 'bg-success text-white'; $statusText = 'Hoàn thành'; break;
                                            case 3: $badgeClass = 'bg-danger text-white'; $statusText = 'Đã hủy'; break;
                                        }
                                    ?>
                                    <span class="badge rounded-pill px-3 py-2 <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded-4 p-4 mb-4">
                            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-receipt me-2 text-secondary"></i>Chi tiết đơn hàng</h5>
                            <ul class="list-group list-group-flush bg-transparent">
                                <?php foreach ($orderDetails as $detail) { 
                                    $subtotal = ($detail['DonGia'] ?? 0) * ($detail['SoLuong'] ?? 0);
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            $imgUrl = !empty($detail['HinhAnh']) ? '../../uploads/products/'.$detail['HinhAnh'] : 'https://via.placeholder.com/50';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="rounded me-3 border" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($detail['TenSanPham'] ?? 'Sản phẩm'); ?></div>
                                            <div class="text-muted small">Size: <?php echo htmlspecialchars($detail['TenKichThuoc'] ?? 'Tiêu chuẩn'); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">x<?php echo intval($detail['SoLuong'] ?? 0); ?></div>
                                        <div class="fw-bold text-dark"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</div>
                                    </div>
                                </li>
                                <?php } ?>
                            </ul>
                            <div class="d-flex justify-content-between align-items-center border-top border-secondary border-opacity-25 pt-3 mt-2">
                                <div class="fw-bold fs-5 text-dark">Tổng thanh toán</div>
                                <div class="fs-4 fw-bold text-danger"><?php echo number_format($order['TongTien'] ?? 0, 0, ',', '.'); ?>₫</div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-5">
                            <a href="../../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2">
                                <i class="fas fa-shopping-bag me-2"></i> Tiếp tục mua sắm
                            </a>
    
                            <?php if ($order['TrangThai'] == 0) { ?>
                                <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không? Hành động này không thể hoàn tác.');">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold">
                                        <i class="fas fa-times-circle me-2"></i> Hủy đơn hàng
                                    </button>
                                </form>
                            <?php } ?>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
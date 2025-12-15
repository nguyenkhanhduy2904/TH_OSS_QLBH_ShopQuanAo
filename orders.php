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

$orders = $orderModel->getOrdersByUserId($_SESSION['MaNguoiDung']);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - Fashion Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #5e72e4; --secondary-color: #8898aa; }
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fe; }
        .brand-font { font-family: 'Pacifico', cursive; color: var(--primary-color) !important; font-size: 1.6rem; }
        .navbar { background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); overflow: hidden; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #eee; color: #6c757d; font-weight: 700; }
        
        .badge-status-0 { background-color: #ffc107; color: #000; } 
        .badge-status-1 { background-color: #0dcaf0; color: #000; } 
        .badge-status-2 { background-color: #198754; color: #fff; } 
        .badge-status-3 { background-color: #dc3545; color: #fff; } 
        
        .btn-custom { background-color: var(--primary-color); color: white; border: none; transition: 0.3s; }
        .btn-custom:hover { background-color: #324cdd; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand brand-font" href="../../index.php"><i class="fas fa-shirt me-2"></i>Fashion Shop</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="orders.php" class="text-decoration-none text-primary fw-bold small me-3">
                <i class="fas fa-receipt me-1"></i> Đơn hàng của tôi
            </a>
            <a href="profile.php" class="text-decoration-none text-muted fw-bold small">
                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['TenNguoiDung']); ?>
            </a>
             <a href="../../controllers/logout.php" class="ms-3 text-danger" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2 text-primary"></i>Lịch sử đơn hàng</h2>
        <a href="../../index.php" class="btn btn-outline-primary rounded-pill btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Tiếp tục mua sắm
        </a>
    </div>

    <?php if (empty($orders)) { ?>
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <div class="mb-3 text-muted opacity-25">
                    <i class="fas fa-clipboard-list" style="font-size: 5rem;"></i>
                </div>
                <h4 class="text-muted fw-bold">Bạn chưa có đơn hàng nào</h4>
                <p class="mb-4 text-muted small">Hãy khám phá những bộ sưu tập thời trang mới nhất của chúng tôi!</p>
                <a href="../../index.php" class="btn btn-custom px-4 py-2 rounded-pill shadow-sm">
                    Bắt đầu mua sắm
                </a>
            </div>
        </div>
    <?php } else { ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="ps-4 py-3">Mã đơn</th>
                                <th scope="col" class="py-3">Ngày đặt</th>
                                <th scope="col" class="py-3">Tổng tiền</th>
                                <th scope="col" class="py-3 text-center">Trạng thái</th>
                                <th scope="col" class="text-end pe-4 py-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o) { 
                                $s = $o['TrangThai'];
                                $statusMap = [
                                    0 => 'Chờ duyệt',
                                    1 => 'Đang giao',
                                    2 => 'Hoàn thành',
                                    3 => 'Đã hủy'
                                ];
                                $statusText = $statusMap[$s] ?? 'Không rõ';
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    #<?php echo str_pad($o['MaDon'], 6, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td class="text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i', strtotime($o['NgayDat'])); ?>
                                </td>
                                <td class="fw-bold text-danger">
                                    <?php echo number_format($o['TongTien'], 0, ',', '.'); ?>₫
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-3 py-2 badge-status-<?php echo $s; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="order_confirmation.php?id=<?php echo $o['MaDon']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 border-0 text-muted small text-center">
                Hiển thị <?php echo count($orders); ?> đơn hàng gần nhất.
            </div>
        </div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
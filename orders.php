<?php
session_start();

// <<<<<<< HEAD
// if (!isset($_SESSION['MaNguoiDung'])) {
//     header('Location: ../../controllers/user_login.php');
//     exit;
// }

// include '../../config/database.php';
// include '../../models/Order.php'; 
// =======
if (!isset($_SESSION['MaAdmin'])) { 
    header('Location: ../../controllers/admin_login.php'); 
    exit; 
}

include '../../config/database.php';
include '../../models/Order.php';
>>>>>>> BackEnd

$conn = getConnection();
$orderModel = new Order($conn);

// <<<<<<< HEAD
// $orders = $orderModel->getOrdersByUserId($_SESSION['MaNguoiDung']);

// =======
$orders = $orderModel->getAll();

function getStatusBadge($status) {
    switch ($status) {
        case 0: return '<span class="badge bg-warning text-dark border border-warning"><i class="fas fa-clock me-1"></i> Chờ duyệt</span>';
        case 1: return '<span class="badge bg-info text-dark border border-info"><i class="fas fa-truck me-1"></i> Đang giao</span>';
        case 2: return '<span class="badge bg-success-subtle text-success border border-success"><i class="fas fa-check me-1"></i> Hoàn thành</span>';
        case 3: return '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="fas fa-times me-1"></i> Đã hủy</span>';
        default: return '<span class="badge bg-secondary">Không rõ</span>';
    }
}
>>>>>>> BackEnd
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
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
======= -->
    <title>Quản lý đơn hàng - Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fa; }

        .sidebar { min-height: 100vh; background: #2c3e50; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; transition: all 0.3s; border-radius: 8px; margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: #fff; transform: translateX(5px); }

        .table-custom th { background-color: #f8f9fa; font-weight: 700; color: #6c757d; border-top: none; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        tr { transition: background-color 0.2s; }

    </style>
</head>
<body>

<<<<<<< HEAD
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

=======
<div class="container-fluid">
    <div class="row flex-nowrap">
        
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-3 text-white min-vh-100">
                <a href="dashboard.php" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none border-bottom w-100">
                    <i class="fas fa-shirt fa-2x me-2 text-info"></i>
                    <span class="fs-5 d-none d-sm-inline fw-bold">Admin Panel</span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100 mt-4" id="menu">
                    <li class="nav-item w-100">
                        <a href="dashboard.php" class="nav-link align-middle">
                            <i class="fas fa-tachometer-alt"></i> <span class="ms-1 d-none d-sm-inline">Tổng quan</span>
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="products.php" class="nav-link align-middle">
                            <i class="fas fa-box-open"></i> <span class="ms-1 d-none d-sm-inline">Sản phẩm</span>
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="orders.php" class="nav-link align-middle active">
                            <i class="fas fa-shopping-cart"></i> <span class="ms-1 d-none d-sm-inline">Đơn hàng</span>
                        </a>
                    </li>
                    <li class="w-100 mt-4 border-top pt-2">
                         <a href="../../controllers/admin_register.php" class="nav-link align-middle">
                            <i class="fas fa-user-plus"></i> <span class="ms-1 d-none d-sm-inline">Tạo Admin mới</span>
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="../../controllers/logout.php" class="nav-link align-middle text-danger">
                            <i class="fas fa-sign-out-alt"></i> <span class="ms-1 d-none d-sm-inline">Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col py-4 px-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Quản lý đơn hàng</h3>
                    <p class="text-muted small">Theo dõi và xử lý các đơn đặt hàng mới</p>
                </div>
                
                <div class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Tìm theo mã đơn, tên khách...">
                    </div>
                    <select class="form-select w-auto">
                        <option>Tất cả trạng thái</option>
                        <option>Chờ duyệt</option>
                        <option>Hoàn thành</option>
                    </select>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end pe-4">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)) { ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có đơn hàng nào.</td></tr>
                                <?php } else { ?>
                                    <?php foreach($orders as $o) { ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">#<?php echo $o['MaDon']; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($o['TenNguoiDung'] ?? ''); ?></div>
                                            </td>
                                        <td class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($o['NgayDat'])); ?>
                                        </td>
                                        <td class="fw-bold">
                                            <?php echo number_format($o['TongTien'], 0, ',', '.'); ?>₫
                                        </td>
                                        <td>
                                            <?php echo getStatusBadge($o['TrangThai']); ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="order_details.php?id=<?php echo $o['MaDon']; ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                                Xem chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">Hiển thị <?php echo count($orders); ?> đơn hàng mới nhất.</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">Sau</a></li>
                        </ul>
                    </nav>
                </div>
            </div>

        </div>
    </div>
</div>
>>>>>>> BackEnd
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
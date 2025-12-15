<?php
session_start();

if (!isset($_SESSION['MaAdmin'])) {
    header('Location: ../../controllers/admin_login.php');
    exit;
}

include '../../config/database.php';

$conn = getConnection();

$orderId = $_GET['id'] ?? null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = (int)$_POST['status'];
    try {
        $stmt = $conn->prepare("UPDATE donhang SET TrangThai = :status WHERE MaDon = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $orderId]);
        $message = "Cập nhật trạng thái thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi cập nhật: " . $e->getMessage();
    }
}

$order = null;
$orderItems = [];

if ($orderId) {
    try {
        $sqlOrder = "SELECT d.*, u.TenNguoiDung, u.Email, u.SoDienThoai, u.DiaChi 
                     FROM donhang d 
                     LEFT JOIN nguoidung u ON d.MaNguoiDung = u.MaNguoiDung 
                     WHERE d.MaDon = :id";
        $stmt = $conn->prepare($sqlOrder);
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $sqlItems = "SELECT c.*, sp.TenSanPham, sp.HinhAnh, kt.TenKichThuoc 
                         FROM chitietdonhang c 
                         LEFT JOIN sanpham sp ON c.MaSanPham = sp.MaSanPham 
                         LEFT JOIN kichthuoc kt ON c.MaKichThuoc = kt.MaKichThuoc 
                         WHERE c.MaDon = :id";
            $stmtItems = $conn->prepare($sqlItems);
            $stmtItems->execute([':id' => $orderId]);
            $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error = "Lỗi kết nối CSDL: " . $e->getMessage();
    }
}
/* */
function getStatusBadge($status) {
    switch ($status) {
        case 0: return '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Chờ duyệt</span>';
        case 1: return '<span class="badge bg-info text-dark"><i class="fas fa-truck me-1"></i> Đang giao</span>';
        case 2: return '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Hoàn thành</span>';
        case 3: return '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Đã hủy</span>';
        default: return '<span class="badge bg-secondary">Không rõ</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $orderId; ?> - Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fa; }

        .sidebar { min-height: 100vh; background: #2c3e50; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; transition: all 0.3s; border-radius: 8px; margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: #fff; transform: translateX(5px); }
        
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table-custom th { background-color: #f8f9fa; font-weight: 700; color: #6c757d; }
        .product-img-sm { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
    </style>
</head>
<body>

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
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="orders.php" class="text-decoration-none">Đơn hàng</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                        </ol>
                    </nav>
                    <h3 class="fw-bold mb-0">Đơn hàng #<?php echo $orderId; ?></h3>
                </div>
                <div>
                    <a href="orders.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
                    <button onclick="window.print()" class="btn btn-secondary ms-2"><i class="fas fa-print me-2"></i>In phiếu</button>
                </div>
            </div>

            <?php if ($message) { ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php } ?>
            <?php if ($error) { ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
            <?php } ?>

            <?php if (!$order) { ?>
                <div class="alert alert-warning">Không tìm thấy thông tin đơn hàng.</div>
            <?php } else { ?>
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-secondary"></i>Danh sách sản phẩm</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Sản phẩm</th>
                                                <th class="text-center">Đơn giá</th>
                                                <th class="text-center">SL</th>
                                                <th class="text-end pe-4">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalCalc = 0;
                                            foreach($orderItems as $item) { 
                                                $price = isset($item['DonGia']) ? $item['DonGia'] : 0;
                                                $qty = $item['SoLuong'];
                                                $subtotal = $price * $qty;
                                                $totalCalc += $subtotal;
                                            ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <?php $img = !empty($item['HinhAnh']) ? '../../uploads/products/'.$item['HinhAnh'] : 'https://via.placeholder.com/50'; ?>
                                                        <img src="<?php echo htmlspecialchars($img); ?>" class="product-img-sm me-3">
                                                        <div>
                                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($item['TenSanPham']); ?></div>
                                                            <small class="text-muted bg-light border px-2 rounded">Size: <?php echo htmlspecialchars($item['TenKichThuoc']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?php echo number_format($price, 0, ',', '.'); ?>₫</td>
                                                <td class="text-center fw-bold"><?php echo $qty; ?></td>
                                                <td class="text-end pe-4 fw-bold text-dark"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold pt-3 text-uppercase text-secondary">Tổng cộng:</td>
                                                <td class="text-end pe-4 fw-bold text-danger fs-5 pt-3">
                                                    <?php echo number_format($order['TongTien'], 0, ',', '.'); ?>₫
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        
                        <div class="card mb-4 border-primary border-opacity-25">
                            <div class="card-header bg-primary bg-opacity-10 py-3 border-bottom border-primary border-opacity-25">
                                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Cập nhật trạng thái</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-bold">Hiện tại:</span>
                                    <?php echo getStatusBadge($order['TrangThai']); ?>
                                </div>
                                
                                <form method="POST" action="">
                                    <div class="input-group mb-3">
                                        <select name="status" class="form-select fw-bold">
                                            <option value="0" <?php echo $order['TrangThai'] == 0 ? 'selected' : ''; ?>>⏳ Chờ duyệt</option>
                                            <option value="1" <?php echo $order['TrangThai'] == 1 ? 'selected' : ''; ?>>🚚 Đang giao hàng</option>
                                            <option value="2" <?php echo $order['TrangThai'] == 2 ? 'selected' : ''; ?>>✅ Hoàn thành</option>
                                            <option value="3" <?php echo $order['TrangThai'] == 3 ? 'selected' : ''; ?>>❌ Đã hủy</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary fw-bold">
                                            Lưu
                                        </button>
                                    </div>
                                </form>
                                <small class="text-muted fst-italic">
                                    <i class="fas fa-info-circle me-1"></i> Thay đổi sẽ được cập nhật ngay lập tức.
                                </small>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-user me-2 text-secondary"></i>Thông tin khách hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 pb-3 border-bottom">
                                    <label class="text-secondary small text-uppercase fw-bold mb-1">Người nhận</label>
                                    <div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($order['TenNguoiDung'] ?? 'Khách vãng lai'); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($order['Email'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-secondary small text-uppercase fw-bold mb-1">Liên hệ</label>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-light p-2 rounded me-2"><i class="fas fa-phone text-success"></i></div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($order['SoDienThoai'] ?? '---'); ?></div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="bg-light p-2 rounded me-2"><i class="fas fa-map-marker-alt text-danger"></i></div>
                                        <div><?php echo htmlspecialchars($order['DiaChi'] ?? 'Tại cửa hàng'); ?></div>
                                    </div>
                                </div>
                                
                                <?php if(!empty($order['GhiChu'])) { ?>
                                <div class="alert alert-warning border-0 d-flex align-items-start small mb-0">
                                    <i class="fas fa-sticky-note me-2 mt-1"></i>
                                    <div><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['GhiChu']); ?></div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Ngày đặt hàng:</span>
                                    <span class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Thanh toán:</span>
                                    <span class="fw-bold text-success">COD (Tiền mặt)</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            <?php } ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
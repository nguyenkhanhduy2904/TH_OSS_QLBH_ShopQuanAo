<?php
session_start();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$total = 0;
if (!empty($cart)) {
    foreach($cart as $item) {
        $total += $item['Gia'] * $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #5e72e4; }
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fe; }
        .brand-font { font-family: 'Pacifico', cursive; color: var(--primary-color) !important; font-size: 1.6rem; }
        .navbar { background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .img-cart { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; }
        .btn-custom { background-color: var(--primary-color); color: white; border: none; font-weight: bold; }
        .btn-custom:hover { background-color: #324cdd; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand brand-font" href="../../index.php"><i class="fa-solid fa-shirt me-2"></i>Fashion Shop</a>
        <div class="ms-auto">
            <a href="../../index.php" class="text-decoration-none text-muted fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Tiếp tục mua sắm
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i>Giỏ hàng của bạn</h2>

    <?php if (empty($cart)) { ?>
        <div class="card border-0 shadow-sm text-center py-5 rounded-4">
            <div class="card-body">
                <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="120" class="mb-3 opacity-50" alt="Empty Cart">
                <h4 class="text-muted fw-bold">Giỏ hàng đang trống</h4>
                <p class="text-secondary mb-4">Có vẻ như bạn chưa chọn sản phẩm nào.</p>
                <a href="../../index.php" class="btn btn-custom px-4 py-2 rounded-pill shadow-sm">
                    Quay lại cửa hàng
                </a>
            </div>
        </div>

    <?php } else { ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4 py-3">Sản phẩm</th>
                                    <th scope="col" class="text-center">Đơn giá</th>
                                    <th scope="col" class="text-center">Số lượng</th>
                                    <th scope="col" class="text-end pe-4">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart as $key => $item) { ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                                $imgUrl = !empty($item['HinhAnh']) ? '../../uploads/products/' . $item['HinhAnh'] : 'https://via.placeholder.com/80';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="img-cart shadow-sm border me-3">
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($item['TenSanPham']); ?></h6>
                                                <small class="text-muted bg-light px-2 py-1 rounded border">Size: <?php echo htmlspecialchars($item['TenKichThuoc']); ?></small>
                                                
                                                <div class="d-block d-md-none mt-2">
                                                    <a href="cart_action.php?action=remove&key=<?php echo $key; ?>" class="text-danger small text-decoration-none"><i class="fa-solid fa-trash"></i> Xóa</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-muted fw-semibold">
                                        <?php echo number_format($item['Gia'], 0, ',', '.'); ?>₫
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center border rounded-pill px-2 py-1">
                                            <a href="cart_action.php?action=update&key=<?php echo $key; ?>&qty=<?php echo $item['qty']-1; ?>" class="btn btn-sm text-secondary p-0 px-2">
                                                <i class="fa-solid fa-minus small"></i>
                                            </a>
                                            
                                            <input type="text" class="form-control border-0 bg-transparent text-center p-0 fw-bold" 
                                                   value="<?php echo $item['qty']; ?>" style="width: 30px;" readonly>
                                            
                                            <a href="cart_action.php?action=update&key=<?php echo $key; ?>&qty=<?php echo $item['qty']+1; ?>" class="btn btn-sm text-secondary p-0 px-2">
                                                <i class="fa-solid fa-plus small"></i>
                                            </a>
                                        </div>
                                        <div class="mt-1 d-none d-md-block">
                                            <a href="cart_action.php?action=remove&key=<?php echo $key; ?>" class="text-danger small text-decoration-none opacity-75 hover-opacity-100">Xóa</a>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-danger fs-5">
                                        <?php echo number_format($item['Gia'] * $item['qty'], 0, ',', '.'); ?>₫
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0">Tổng đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-muted">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr class="my-3 border-secondary opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold fs-5">Thành tiền:</span>
                            <span class="fw-bold fs-3 text-danger"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-custom py-3 rounded-pill shadow-sm">
                                TIẾN HÀNH THANH TOÁN <i class="fa-solid fa-arrow-right ms-2"></i>
                            </a>
                            <a href="../../index.php" class="btn btn-outline-secondary py-2 rounded-pill border-0">
                                Mua thêm sản phẩm khác
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
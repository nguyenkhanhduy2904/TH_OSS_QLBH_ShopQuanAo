<?php
session_start();

include '../../config/database.php';
include '../../models/Product.php';

$conn = getConnection();
$productModel = new Product($conn);

$product = null;
$related_products = [];
$sizes = []; 
$error_message = '';
$success_message = '';

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    $product = $productModel->find($id);

    if ($product) {
        if (!empty($product['MaKichThuoc']) && !empty($product['TenKichThuoc'])) {
            $sizes[] = [
                'MaKichThuoc' => $product['MaKichThuoc'],
                'TenKichThuoc' => $product['TenKichThuoc']
            ];
        }

        $related_products = $productModel->getRelatedProducts($id, 4);
    } else {
        $error_message = 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.';
    }
} else {
    $error_message = 'Đường dẫn không hợp lệ.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$product) {
        $error_message = 'Lỗi dữ liệu sản phẩm.';
    } else {
        $qty = (int)$_POST['quantity'];
        $sizeId = isset($_POST['MaKichThuoc']) ? (int)$_POST['MaKichThuoc'] : $product['MaKichThuoc'];

        if ($qty < 1) {
            $error_message = 'Số lượng phải lớn hơn 0.';
        } elseif (empty($sizeId)) {
            $error_message = 'Lỗi kích thước sản phẩm.';
        } else {
            $sizeName = $product['TenKichThuoc'];

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $cartKey = $product['MaSanPham'] . '_' . $sizeId;

            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$cartKey] = [
                    'MaSanPham' => $product['MaSanPham'],
                    'TenSanPham' => $product['TenSanPham'],
                    'HinhAnh' => $product['HinhAnh'] ?? '',
                    'Gia' => $product['Gia'],
                    'MaKichThuoc' => $sizeId,
                    'TenKichThuoc' => $sizeName,
                    'qty' => $qty
                ];
            }

            $_SESSION['flash_success'] = "Đã thêm <b>" . htmlspecialchars($product['TenSanPham']) . "</b> vào giỏ hàng!";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

if (isset($_SESSION['flash_success'])) {
    $success_message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

$cartCount = !empty($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['TenSanPham']) : 'Sản phẩm'; ?> - Cửa hàng quần áo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #5e72e4; --secondary-color: #8898aa; }
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fe; color: #2d3436; }
        
        .brand-font { font-family: 'Pacifico', cursive; color: var(--primary-color) !important; font-size: 1.6rem; }
        .navbar { background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        .product-container { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .main-img { width: 100%; height: auto; object-fit: cover; border-radius: 15px; }
        
        .btn-custom { background-color: var(--primary-color); color: white; border: none; font-weight: 700; transition: 0.3s; }
        .btn-custom:hover { background-color: #324cdd; color: white; transform: translateY(-2px); }
        .btn-custom:disabled { background-color: #ccc; cursor: not-allowed; transform: none; }
        
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .price-text { color: var(--primary-color); font-weight: 800; font-size: 1.8rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand brand-font" href="../../index.php"><i class="fa-solid fa-shirt me-2"></i>Fashion Shop</a>
        
        <div class="ms-auto d-flex align-items-center">
            <a class="nav-link position-relative me-4" href="cart.php">
                <i class="fas fa-shopping-basket fa-lg text-secondary"></i>
                <?php if($cartCount > 0) { ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $cartCount;?></span>
                <?php } ?>
            </a>
            
            <?php if (!empty($_SESSION['MaNguoiDung'])) { ?>
                <a href="profile.php" class="text-decoration-none fw-bold text-dark"><i class="fas fa-user-circle"></i> Tài khoản</a>
            <?php } else { ?>
                <a href="../../controllers/user_login.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Đăng nhập</a>
            <?php } ?>
        </div>
    </div>
</nav>

<div class="container py-5">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-muted">Trang chủ</a></li>
            <li class="breadcrumb-item text-muted">Sản phẩm</li>
            <li class="breadcrumb-item active fw-bold text-dark"><?php echo $product ? htmlspecialchars($product['TenSanPham']) : 'Chi tiết'; ?></li>
        </ol>
    </nav>

    <?php if ($error_message) { ?>
        <div class="alert alert-danger rounded-3 border-0 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_message; ?></div>
    <?php } ?>
    
    <?php if ($success_message) { ?>
        <div class="alert alert-success rounded-3 border-0 shadow-sm alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <a href="cart.php" class="alert-link ms-2 fw-bold">Xem giỏ hàng <i class="fas fa-arrow-right"></i></a>
        </div>
    <?php } ?>

    <?php if ($product) { ?>
        <div class="product-container p-4 p-lg-5 mb-5">
            <div class="row g-5 align-items-center">
                <div class="col-md-6">
                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <?php 
                            $imgUrl = !empty($product['HinhAnh']) ? '../../uploads/products/' . $product['HinhAnh'] : null;
                        ?>
                        <?php if ($imgUrl) { ?>
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>" class="main-img shadow-sm w-100">
                        <?php } else { ?>
                            <i class="fa-solid fa-shirt fa-5x text-secondary opacity-25"></i>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-secondary text-white rounded-pill me-2">
                            <?php echo htmlspecialchars($product['TenKichThuoc'] ?? 'Size tiêu chuẩn'); ?>
                        </span>
                        <?php if (!empty($product['MauSac'])): ?>
                        <span class="badge text-dark rounded-pill" style="background-color: <?php echo htmlspecialchars(strtolower($product['MauSac'])); ?>; border: 1px solid #ccc;">
                            <?php echo htmlspecialchars($product['MauSac']); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="fw-bold mb-3 display-6" style="font-family: 'Nunito', sans-serif;"><?php echo htmlspecialchars($product['TenSanPham']); ?></h1>
                    
                    <div class="price-text mb-4"><?php echo number_format($product['Gia'], 0, ',', '.'); ?>₫</div>

                    <p class="text-muted mb-4 lh-lg"><?php echo nl2br(htmlspecialchars($product['MoTa'] ?? '')); ?></p>

                    <form action="" method="POST" class="mt-4">
                        <input type="hidden" name="MaSanPham" value="<?php echo htmlspecialchars($product['MaSanPham']); ?>">
                        
                        <div class="mb-4">
                            <label class="fw-bold mb-2 d-block text-secondary small text-uppercase">Kích thước</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (!empty($sizes)) { 
                                    foreach ($sizes as $size) { ?>
                                    <input type="radio" class="btn-check" name="MaKichThuoc" id="size_<?php echo $size['MaKichThuoc']; ?>" value="<?php echo $size['MaKichThuoc']; ?>" checked>
                                    <label class="btn btn-outline-primary fw-bold rounded-pill px-4 py-2" for="size_<?php echo $size['MaKichThuoc']; ?>">
                                        <?php echo htmlspecialchars($size['TenKichThuoc']); ?>
                                    </label>
                                <?php } 
                                } else { ?>
                                    <input type="hidden" name="MaKichThuoc" value="<?php echo $product['MaKichThuoc']; ?>">
                                    <span class="badge bg-light text-dark border px-3 py-2">Mặc định</span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <label class="fw-bold mb-2 d-block text-secondary small text-uppercase">Số lượng</label>
                                <div class="d-flex align-items-center bg-light rounded-pill px-2 border">
                                    <button type="button" class="btn btn-sm text-secondary" onclick="this.parentNode.querySelector('input').stepDown()"><i class="fas fa-minus"></i></button>
                                    <input type="number" name="quantity" value="1" min="1" max="50" class="form-control border-0 bg-transparent text-center fw-bold" style="width: 50px;">
                                    <button type="button" class="btn btn-sm text-secondary" onclick="this.parentNode.querySelector('input').stepUp()"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="col">
                                <button type="submit" name="add_to_cart" class="btn btn-custom w-100 py-2 rounded-pill shadow-sm fs-5">
                                    <i class="fas fa-cart-plus me-2"></i> Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($related_products)) { ?>
        <div class="mb-5">
            <h3 class="fw-bold mb-4"><i class="fas fa-heart text-danger me-2"></i>Có thể bạn thích</h3>
            <div class="row row-cols-2 row-cols-md-4 g-4">
                <?php foreach ($related_products as $related) { ?>
                    <div class="col">
                        <div class="card h-100 related-card border-0 shadow-sm">
                            <a href="product_detail.php?id=<?php echo $related['MaSanPham']; ?>" class="text-decoration-none">
                                <?php $rImg = !empty($related['HinhAnh']) ? '../../uploads/products/' . $related['HinhAnh'] : null; ?>
                                <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 180px; overflow: hidden;">
                                    <?php if($rImg) { ?>
                                        <img src="<?php echo htmlspecialchars($rImg); ?>" class="w-100 h-100 object-fit-cover">
                                    <?php } else { ?>
                                        <i class="fa-solid fa-shirt fa-3x text-secondary opacity-25"></i>
                                    <?php } ?>
                                </div>
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title text-dark fw-bold text-truncate mb-1"><?php echo htmlspecialchars($related['TenSanPham']); ?></h6>
                                    <div class="text-danger fw-bold"><?php echo number_format($related['Gia'], 0, ',', '.'); ?>₫</div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

    <?php } else { ?>
        <div class="text-center py-5">
            <h3 class="text-muted">Không tìm thấy sản phẩm!</h3>
            <a href="../../index.php" class="btn btn-custom mt-3 px-4 rounded-pill">Quay về trang chủ</a>
        </div>
    <?php } ?>
</div>

<footer class="bg-dark text-white py-4 text-center mt-auto">
    &copy; <?php echo date('Y'); ?> Nguyễn Sỹ Khiêm - DH52200892
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
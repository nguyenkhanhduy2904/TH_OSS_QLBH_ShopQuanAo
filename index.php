<?php
session_start();
include 'config/database.php';
include 'models/Product.php';

$conn = getConnection();
$product = new Product($conn);

$search = trim($_GET['search'] ?? '');
$filters = [];
$filters['min_price'] = !empty($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$filters['max_price'] = !empty($_GET['max_price']) ? floatval($_GET['max_price']) : '';
$filters['madanhmuc'] = !empty($_GET['category']) ? intval($_GET['category']) : ''; 
$filters['makichthuoc'] = !empty($_GET['size']) ? intval($_GET['size']) : '';

if (!empty($search)) {
    $filters['ten'] = $search;
}

$products = $product->getAll($filters);

$stmt = $conn->query('SELECT MaDanhMuc, TenDanhMuc FROM danhmuc');
$allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->query('SELECT MaKichThuoc, TenKichThuoc FROM kichthuoc');
$allSizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cửa hàng quần áo - Fashion Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #5e72e4;
            --secondary-color: #8898aa;
            --dark-text: #2d3436;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fe;
            color: var(--dark-text);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .brand-font {
            font-family: 'Pacifico', cursive;
            color: var(--primary-color) !important;
            font-size: 1.8rem;
        }
        .hero-banner {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            border-radius: 0 0 30px 30px;
            margin-bottom: 40px;
        }
        .product-card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(94, 114, 228, 0.2);
        }
        .img-wrapper {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
        .price-tag {
            font-weight: 800;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        .btn-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            border: none;
        }
        .btn-custom:hover {
            background-color: #324cdd;
            color: white;
        }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 3em; 
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand brand-font" href="index.php">
                <i class="fa-solid fa-shirt me-2"></i>Fashion Shop
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (!empty($_SESSION['MaNguoiDung'])) { ?>
                        <li class="nav-item me-2">
                            <a class="nav-link position-relative" href="views/user/cart.php">
                                <i class="fas fa-shopping-basket fa-lg text-secondary"></i>
                                <?php if($cartCount > 0) { ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $cartCount;?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['TenNguoiDung']); ?>&background=random" class="rounded-circle me-1" width="30">
                                <?php echo htmlspecialchars($_SESSION['TenNguoiDung']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item" href="views/user/profile.php"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                                <li><a class="dropdown-item" href="views/user/orders.php"><i class="fas fa-receipt me-2"></i>Đơn hàng</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="controllers/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item"><a class="btn btn-outline-danger rounded-pill px-4 me-2" href="controllers/user_login.php">Đăng nhập</a></li>
                        <li class="nav-item"><a class="btn btn-custom px-4" href="controllers/user_register.php">Đăng ký</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-banner d-flex align-items-center">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3 font-brand" style="font-family: 'Pacifico', cursive;">Phong cách của bạn</h1>
            <p class="lead mb-5 text-white-50">Tìm kiếm những bộ trang phục thể hiện cá tính của bạn</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form method="get" action="index.php" class="position-relative">
                        <input type="text" name="search" class="form-control form-control-lg rounded-pill px-4 shadow py-3" 
                               placeholder="Bạn muốn tìm gì hôm nay? (Ví dụ: Áo thun, quần jean...)" 
                               value="<?php echo htmlspecialchars($search);?>">
                        <button class="btn btn-custom position-absolute top-50 end-0 translate-middle-y me-2 rounded-pill" type="submit">
                            <i class="fas fa-search me-1"></i> Tìm kiếm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0 text-dark"><i class="fa-solid fa-tags me-2 text-primary"></i>Sản phẩm mới</h3>
            <button class="btn btn-outline-secondary rounded-pill btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="fas fa-filter me-1"></i> Bộ lọc & Sắp xếp
            </button>
        </div>

        <div class="collapse mb-4 <?php echo (!empty($_GET['min_price']) || !empty($_GET['category'])) ? 'show' : ''; ?>" id="filterPanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-4">
                    <form method="get" action="index.php" class="row g-3">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search);?>">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Khoảng giá</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="min_price" class="form-control" placeholder="Từ" value="<?php echo htmlspecialchars($filters['min_price']);?>">
                                <span class="input-group-text bg-white border-start-0 border-end-0">-</span>
                                <input type="number" name="max_price" class="form-control" placeholder="Đến" value="<?php echo htmlspecialchars($filters['max_price']);?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Danh mục</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach($allCategories as $c) { ?>
                                    <option value="<?php echo $c['MaDanhMuc'];?>" <?php if($filters['madanhmuc']==$c['MaDanhMuc']) echo 'selected';?>>
                                        <?php echo htmlspecialchars($c['TenDanhMuc']);?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Kích cỡ</label>
                            <select name="size" class="form-select form-select-sm">
                                <option value="">Tất cả kích cỡ</option>
                                <?php foreach($allSizes as $s) { ?>
                                    <option value="<?php echo $s['MaKichThuoc'];?>" <?php if($filters['makichthuoc']==$s['MaKichThuoc']) echo 'selected';?>>
                                        <?php echo htmlspecialchars($s['TenKichThuoc']);?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-custom w-100 btn-sm me-2">Áp dụng</button>
                            <a href="index.php" class="btn btn-light w-50 btn-sm text-muted">Xóa lọc</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($search)) { ?>
            <div class="alert alert-light border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="fas fa-info-circle text-info me-2"></i>
                <div>Tìm thấy <strong><?php echo count($products);?></strong> kết quả cho từ khóa "<?php echo htmlspecialchars($search);?>"</div>
            </div>
        <?php } ?>

        <div class="row g-4">
            <?php if (!empty($products)) { ?>
                <?php foreach($products as $p) { ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card product-card h-100">
                            <div class="img-wrapper position-relative">
                                <?php if (!empty($p['HinhAnh'])) { ?>
                                    <img src="uploads/products/<?php echo htmlspecialchars($p['HinhAnh']);?>" class="product-img" alt="<?php echo htmlspecialchars($p['TenSanPham']);?>">
                                <?php } else { ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light text-secondary">
                                        <i class="fa-solid fa-shirt fa-3x"></i>
                                    </div>
                                <?php } ?>
                                <span class="position-absolute top-0 start-0 m-3 badge bg-warning text-dark rounded-pill shadow-sm">Mới</span>
                            </div>

                            <div class="card-body d-flex flex-column text-center p-3">
                                <h6 class="card-title fw-bold text-dark mb-2 text-truncate"><?php echo htmlspecialchars($p['TenSanPham']);?></h6>
                                <p class="small text-muted text-truncate-2 mb-3"><?php echo htmlspecialchars($p['MoTa']);?></p>
                                
                                <div class="mt-auto">
                                    <div class="price-tag mb-3"><?php echo number_format($p['Gia'],0,',','.');?> ₫</div>
                                    <div class="d-grid">
                                        <a href="views/user/product_detail.php?id=<?php echo $p['MaSanPham'];?>" class="btn btn-outline-danger rounded-pill btn-sm fw-bold">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="120" alt="No product found" class="mb-3 opacity-50">
                    <h4 class="text-muted fw-bold">Không tìm thấy sản phẩm nào!</h4>
                    <p class="text-secondary">Hãy thử thay đổi từ khóa hoặc bộ lọc xem sao nhé.</p>
                    <a href="index.php" class="btn btn-custom px-4 mt-2">Xem tất cả sản phẩm</a>
                </div>
            <?php } ?>
        </div>
    </main>

    <footer class="bg-dark text-white pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 class="brand-font mb-3 text-white">Fashion Shop</h4>
                    <p class="text-white-50 small">Mang đến những phong cách thời trang hợp thời nhất.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Liên kết</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li><a href="#" class="text-white-50 text-decoration-none">Về chúng tôi</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Theo dõi</h5>
                    <div>
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-tiktok fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-white-50 small">
                &copy; <?php echo date('Y'); ?> Nguyễn Sỹ Khiêm - DH52200892
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['MaAdmin'])) {
    header('Location: ../../controllers/admin_login.php');
    exit;
}

include '../../config/database.php';
include '../../models/Product.php';

$conn = getConnection();
$productModel = new Product($conn);

$categories = $conn->query("SELECT * FROM danhmuc")->fetchAll(PDO::FETCH_ASSOC);
$sizes = $conn->query("SELECT * FROM kichthuoc")->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
$product = null;
$isEdit = false;
if ($id) {
    $product = $productModel->find($id);
    if ($product) {
        $isEdit = true;
    }
}
$name = $product['TenSanPham'] ?? '';
$catId = $product['MaDanhMuc'] ?? '';
$sizeId = $product['MaKichThuoc'] ?? '';
$price = $product['Gia'] ?? '';
$desc = $product['MoTa'] ?? '';
$image = $product['HinhAnh'] ?? '';
$mauSac = $product['MauSac'] ?? '';
$status = $product['TrangThai'] ?? 1;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $catId = $_POST['category'];
    $sizeId = $_POST['size'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $status = $_POST['status'];
    $mauSac = $_POST['mauSac'];

    $newImage = $image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../../uploads/products/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;
        
        $allowTypes = ['jpg','png','jpeg','gif'];
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $newImage = $fileName;
            } else {
                $error = "Lỗi khi tải ảnh lên.";
            }
        } else {
            $error = "Chỉ chấp nhận file ảnh JPG, JPEG, PNG, GIF.";
        }
    }

    if (empty($error)) {
        $data = [
            'TenSanPham' => $name,
            'MaDanhMuc' => $catId,
            'MaKichThuoc' => $sizeId,
            'Gia' => $price,
            'MoTa' => $desc,
            'TrangThai' => $status,
            'HinhAnh' => $newImage,
            'MauSac' => $mauSac
        ];

        if ($isEdit) {
            if ($productModel->update($id, $data)) {
                $success = "Cập nhật sản phẩm thành công!";
                $image = $newImage;
            } else {
                $error = "Lỗi khi cập nhật DB.";
            }
        } else {
            if ($productModel->create($data)) {
                $success = "Thêm sản phẩm thành công!";
                $name = $catId = $sizeId = $price = $desc = $image = '';
            } else {
                $error = "Lỗi khi thêm vào DB.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm'; ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #2c3e50; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .img-preview { max-width: 150px; border-radius: 8px; border: 1px solid #ddd; padding: 3px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-3 text-white min-vh-100">
                <a href="dashboard.php" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none border-bottom w-100">
                    <i class="fas fa-shirt fa-2x me-2 text-warning"></i>
                    <span class="fs-5 d-none d-sm-inline fw-bold">Admin Panel</span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100 mt-4" id="menu">
                    <li class="nav-item w-100">
                        <a href="dashboard.php" class="nav-link align-middle">
                            <i class="fas fa-tachometer-alt"></i> <span class="ms-1 d-none d-sm-inline">Tổng quan</span>
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="products.php" class="nav-link align-middle active">
                            <i class="fas fa-box-open"></i> <span class="ms-1 d-none d-sm-inline">Sản phẩm</span>
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="orders.php" class="nav-link align-middle">
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
                <h3 class="fw-bold"><?php echo $isEdit ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới'; ?></h3>
                <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
            </div>

            <?php if ($success) { ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
            <?php } ?>
            <?php if ($error) { ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
            <?php } ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Danh mục</label>
                                        <select name="category" class="form-select">
                                            <option value="">-- Chọn danh mục --</option>
                                            <?php foreach ($categories as $cat) { ?>
                                                <option value="<?php echo $cat['MaDanhMuc']; ?>" <?php echo $catId == $cat['MaDanhMuc'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['TenDanhMuc']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Kích cỡ</label>
                                        <select name="size" class="form-select">
                                            <option value="">-- Chọn size --</option>
                                            <?php foreach ($sizes as $s) { ?>
                                                <option value="<?php echo $s['MaKichThuoc']; ?>" <?php echo $sizeId == $s['MaKichThuoc'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($s['TenKichThuoc']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Màu sắc</label>
                                        <input type="text" name="mauSac" class="form-control" value="<?php echo htmlspecialchars($mauSac); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mô tả chi tiết</label>
                                    <textarea name="desc" class="form-control" rows="5"><?php echo htmlspecialchars($desc); ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" value="<?php echo $price; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="1" <?php echo $status == 1 ? 'selected' : ''; ?>>Còn hàng</option>
                                        <option value="0" <?php echo $status == 0 ? 'selected' : ''; ?>>Hết hàng</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Hình ảnh</label>
                                    <input type="file" name="image" class="form-control mb-2" accept="image/*" onchange="previewImage(this)">
                                    <div class="text-center bg-light p-2 rounded">
                                        <?php if ($image) { ?>
                                            <img src="../../uploads/products/<?php echo $image; ?>" id="preview" class="img-preview">
                                            
                                        <?php } else { ?>
                                            <img src="https://via.placeholder.com/150x150?text=No+Image" id="preview" class="img-preview opacity-50">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="fas fa-save me-2"></i> <?php echo $isEdit ? 'Cập nhật' : 'Thêm mới'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>
<?php
class Product {
    private $conn;
    private $table = 'sanpham';
    /* */
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($filters = []) {
        $sql = "SELECT sp.*, dm.TenDanhMuc, kt.TenKichThuoc FROM sanpham sp LEFT JOIN danhmuc dm ON sp.MaDanhMuc = dm.MaDanhMuc LEFT JOIN kichthuoc kt ON sp.MaKichThuoc = kt.MaKichThuoc WHERE 1=1";
        $params = [];
        if (!empty($filters['ten'])) {
            $sql .= " AND sp.TenSanPham LIKE :ten";
            $params[':ten'] = '%' . $filters['ten'] . '%';
        }
        if (!empty($filters['madanhmuc'])) {
            $sql .= " AND sp.MaDanhMuc = :madanhmuc";
            $params[':madanhmuc'] = $filters['madanhmuc'];
        }
        if (!empty($filters['makichthuoc'])) {
            $sql .= " AND sp.MaKichThuoc = :makichthuoc";
            $params[':makichthuoc'] = $filters['makichthuoc'];
        }
        if (!empty($filters['min_price'])) {
            $sql .= " AND sp.Gia >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND sp.Gia <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT sp.*, dm.*, kt.* FROM sanpham sp LEFT JOIN danhmuc dm ON sp.MaDanhMuc = dm.MaDanhMuc LEFT JOIN kichthuoc kt ON sp.MaKichThuoc = kt.MaKichThuoc WHERE sp.MaSanPham = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO sanpham (TenSanPham, MoTa, HinhAnh, MaDanhMuc, MaKichThuoc, Gia, MauSac, TrangThai, CreatedAt) VALUES (:ten, :mota, :hinh, :madanhmuc, :makichthuoc, :gia, :mausac, :trangthai, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':ten' => $data['TenSanPham'],
            ':mota' => $data['MoTa'] ?? '',
            ':hinh' => $data['HinhAnh'] ?? '',
            ':madanhmuc' => $data['MaDanhMuc'] ?? null,
            ':makichthuoc' => $data['MaKichThuoc'] ?? null,
            ':gia' => $data['Gia'] ?? 0,
            ':mausac' => $data['MauSac'] ?? '',
            ':trangthai' => $data['TrangThai'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE sanpham SET TenSanPham = :ten, MoTa = :mota, HinhAnh = :hinh, MaDanhMuc = :madanhmuc, MaKichThuoc = :makichthuoc, Gia = :gia, MauSac = :mausac, TrangThai = :trangthai WHERE MaSanPham = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':ten' => $data['TenSanPham'],
            ':mota' => $data['MoTa'] ?? '',
            ':hinh' => $data['HinhAnh'] ?? '',
            ':madanhmuc' => $data['MaDanhMuc'] ?? null,
            ':makichthuoc' => $data['MaKichThuoc'] ?? null,
            ':gia' => $data['Gia'] ?? 0,
            ':mausac' => $data['MauSac'] ?? '',
            ':trangthai' => $data['TrangThai'] ?? 1,
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM sanpham WHERE MaSanPham = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getRelatedProducts($currentProductId, $limit = 4) {
        $stmt = $this->conn->prepare("SELECT MaDanhMuc FROM sanpham WHERE MaSanPham = :currentProductId LIMIT 1");
        $stmt->bindParam(':currentProductId', $currentProductId, PDO::PARAM_INT);
        $stmt->execute();
        $productCategory = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$productCategory || !isset($productCategory['MaDanhMuc'])) {
            return [];
        }

        $maDanhMuc = $productCategory['MaDanhMuc'];

        $sql = "SELECT sp.*, dm.TenDanhMuc, kt.TenKichThuoc FROM sanpham sp LEFT JOIN danhmuc dm ON sp.MaDanhMuc = dm.MaDanhMuc LEFT JOIN kichthuoc kt ON sp.MaKichThuoc = kt.MaKichThuoc WHERE sp.MaDanhMuc = :maDanhMuc AND sp.MaSanPham != :currentProductId LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':maDanhMuc', $maDanhMuc, PDO::PARAM_INT);
        $stmt->bindParam(':currentProductId', $currentProductId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllSizes() {
        $stmt = $this->conn->prepare("SELECT * FROM kichthuoc ORDER BY MaKichThuoc ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
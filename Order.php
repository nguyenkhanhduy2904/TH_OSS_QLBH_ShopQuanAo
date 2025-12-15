<?php
class Order {
    private $conn;
    private $table = 'donhang';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createOrder($userId, $total, $address, $phone, $note = '') {
        $sql = "INSERT INTO donhang (MaNguoiDung, NgayDat, TongTien, TrangThai, DiaChi, SoDienThoai, GhiChu) 
                VALUES (:uid, NOW(), :total, 0, :addr, :phone, :note)";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':addr', $address);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':note', $note);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addOrderDetail($orderId, $productId, $sizeId, $qty, $price) {
        $sql = "INSERT INTO chitietdonhang (MaDon, MaSanPham, MaKichThuoc, SoLuong, DonGia) 
                VALUES (:oid, :pid, :sid, :qty, :price)";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':oid'   => $orderId,
            ':pid'   => $productId,
            ':sid'   => $sizeId,
            ':qty'   => $qty,
            ':price' => $price
        ]);
    }

    public function getOrderById($orderId) {
        $sql = "SELECT d.*, u.TenNguoiDung, u.Email 
                FROM donhang d 
                LEFT JOIN nguoidung u ON d.MaNguoiDung = u.MaNguoiDung 
                WHERE d.MaDon = :oid";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($orderId) {
    $sql = "SELECT c.*, sp.TenSanPham, sp.HinhAnh, kt.TenKichThuoc 
            FROM chitietdonhang c 
            LEFT JOIN sanpham sp ON c.MaSanPham = sp.MaSanPham 
            LEFT JOIN kichthuoc kt ON c.MaKichThuoc = kt.MaKichThuoc 
            WHERE c.MaDon = :id";
            
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':id' => $orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getAll() {
        $stmt = $this->conn->query('SELECT d.*, u.TenNguoiDung FROM donhang d LEFT JOIN nguoidung u ON d.MaNguoiDung = u.MaNguoiDung ORDER BY d.NgayDat DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($maDon, $status) {
        $stmt = $this->conn->prepare('UPDATE donhang SET TrangThai = :status WHERE MaDon = :madon');
        return $stmt->execute([':status' => $status, ':madon' => $maDon]);
    }
    
    public function find($maDon) {
        return $this->getOrderById($maDon);
    }
    public function getOrdersByUserId($userId) {
        $sql = "SELECT * FROM donhang WHERE MaNguoiDung = :uid ORDER BY NgayDat DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function cancelOrder($orderId, $userId) {
        $sql = "UPDATE donhang 
                SET TrangThai = 3 
                WHERE MaDon = :oid 
                AND MaNguoiDung = :uid 
                AND TrangThai = 0"; 
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':oid' => $orderId,
            ':uid' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }
}
?>
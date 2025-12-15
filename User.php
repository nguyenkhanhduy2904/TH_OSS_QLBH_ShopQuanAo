<?php

class User {
    private $conn;
    private $table = 'nguoidung';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $password, $email, $fullname, $phone = null, $address = null): void {
        $sql = "SELECT MaNguoiDung FROM {$this->table} WHERE TenDangNhap = :username OR Email = :email OR SoDienThoai = :phone LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('Tên đăng nhập, Email hoặc SĐT đã tồn tại!');
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO {$this->table} (TenDangNhap, TenNguoiDung, Email, MatKhau, DiaChi, SoDienThoai, CreatedAt) 
                VALUES (:username, :fullname, :email, :password, :address, :phone, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':phone', $phone);

        if (!$stmt->execute()) {
            throw new Exception('Đăng ký thất bại!');
        }
    }

    public function login($username_input, $password) {
        $sql = "SELECT MaNguoiDung, TenDangNhap, TenNguoiDung, Email, MatKhau, DiaChi, SoDienThoai 
                FROM {$this->table} 
                WHERE TenDangNhap = :u1 OR Email = :u2 OR SoDienThoai = :u3 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':u1', $username_input);
        $stmt->bindParam(':u2', $username_input);
        $stmt->bindParam(':u3', $username_input);
        
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['MatKhau'])) {
                return (object)[
                    'userid'   => $user['MaNguoiDung'],
                    'username' => $user['TenDangNhap'],
                    'fullname' => $user['TenNguoiDung'],
                    'email'    => $user['Email'],
                    'phone'    => $user['SoDienThoai'],
                    'address'  => $user['DiaChi']
                ];
            }
        }
        return false;
    }

    public function getUserById($userid) {
        $id = (int)$userid;
        $sql = "SELECT MaNguoiDung, TenDangNhap, TenNguoiDung, Email, DiaChi, SoDienThoai, CreatedAt FROM {$this->table} WHERE MaNguoiDung = :userid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':userid', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userid, $fullname, $phone, $address) {
        $id = (int)$userid;
        $sql = "UPDATE {$this->table} SET TenNguoiDung = :fullname, SoDienThoai = :phone, DiaChi = :address WHERE MaNguoiDung = :userid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':userid', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function changePassword($userid, $oldPassword, $newPassword) {
        $id = (int)$userid;
        $sql = "SELECT MatKhau FROM {$this->table} WHERE MaNguoiDung = :userid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':userid', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!password_verify($oldPassword, $user['MatKhau'])) {
            return false;
        }

        $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $update_sql = "UPDATE {$this->table} SET MatKhau = :password WHERE MaNguoiDung = :userid";
        $update_stmt = $this->conn->prepare($update_sql);
        $update_stmt->bindParam(':password', $newHashedPassword);
        $update_stmt->bindParam(':userid', $id, PDO::PARAM_INT);
        return $update_stmt->execute();
    }

    public function usernameExists($username) {
        $sql = "SELECT MaNguoiDung FROM {$this->table} WHERE TenDangNhap = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function emailExists($email) {
        $sql = "SELECT MaNguoiDung FROM {$this->table} WHERE Email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
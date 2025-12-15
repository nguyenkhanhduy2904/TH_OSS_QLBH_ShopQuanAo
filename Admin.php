<?php
class Admin {
    private $conn;
    private $table = 'quantrivien';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $password) {
        if ($this->usernameExists($username)) {
            return "Username đã tồn tại."; 
        }

        $hashed_password = password_hash(trim($password), PASSWORD_BCRYPT);

        $sql = "INSERT INTO {$this->table} (TenAdmin, MatKhau) VALUES (:username, :password)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $username = htmlspecialchars(strip_tags(trim($username)));
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                return true; 
            }
            return false; 
        } catch (PDOException $e) {
            return false;
        }
    }

    public function usernameExists($username) {
        $sql = "SELECT MaAdmin FROM {$this->table} WHERE TenAdmin = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $username = htmlspecialchars(strip_tags(trim($username)));
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function login($username, $password) {
        $sql = "SELECT MaAdmin, TenAdmin, MatKhau FROM {$this->table} WHERE TenAdmin = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $username = trim($username);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $admin['MatKhau'])) {
                
                return (object) [
                    'adminid'  => $admin['MaAdmin'],
                    'username' => $admin['TenAdmin'],
                    'fullname' => 'Administrator',
                    'email'    => ''            
                ];
            }
        }
        return false;
    }

    public function getById($id) {
        $id = (int)$id;
        $sql = "SELECT MaAdmin, TenAdmin, CreatedAt FROM {$this->table} WHERE MaAdmin = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
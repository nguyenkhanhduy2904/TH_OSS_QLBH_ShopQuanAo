<?php
include 'config/database.php';

$conn = getConnection();

$stmt = $conn->query("SELECT MaNguoiDung, MatKhau FROM nguoidung WHERE NOT MatKhau LIKE '$2y$%%'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $hashed_password = password_hash($user['MatKhau'], PASSWORD_BCRYPT);
    $update_stmt = $conn->prepare("UPDATE nguoidung SET MatKhau = :password WHERE MaNguoiDung = :id");
    $update_stmt->execute([':password' => $hashed_password, ':id' => $user['MaNguoiDung']]);
    echo "User password for ID {$user['MaNguoiDung']} has been hashed.<br>";
}

$stmt = $conn->query("SELECT MaAdmin, MatKhau FROM quantrivien WHERE NOT MatKhau LIKE '$2y$%%'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($admins as $admin) {
    $hashed_password = password_hash($admin['MatKhau'], PASSWORD_BCRYPT);
    $update_stmt = $conn->prepare("UPDATE quantrivien SET MatKhau = :password WHERE MaAdmin = :id");
    $update_stmt->execute([':password' => $hashed_password, ':id' => $admin['MaAdmin']]);
    echo "Admin password for ID {$admin['MaAdmin']} has been hashed.<br>";
}

echo "Password hashing completed.";
?>
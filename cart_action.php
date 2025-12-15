<?php
session_start();

$action = $_GET['action'] ?? '';
$key = $_GET['key'] ?? '';
$qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

if (isset($_SESSION['cart'][$key])) {
    if ($action == 'remove') {
        unset($_SESSION['cart'][$key]);
    } 
    elseif ($action == 'update') {
        if ($qty > 0) {
            $_SESSION['cart'][$key]['qty'] = $qty;
        } else {
            unset($_SESSION['cart'][$key]);
        }
    }
}

header('Location: cart.php');
exit;
?>
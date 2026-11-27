<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(array());
    exit;
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($orderId <= 0) {
    echo json_encode(array());
    exit;
}

require_once 'db.php';

// Проверка прав: пекарь может смотреть любой заказ, пользователь — только свой
$isBaker = !empty($_SESSION['is_baker']);
$userId  = (int)$_SESSION['user_id'];

if ($isBaker) {
    $stmt = $pdo->prepare("SELECT order_id FROM orders WHERE order_id = ?");
    $stmt->execute(array($orderId));
} else {
    $stmt = $pdo->prepare("SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute(array($orderId, $userId));
}

if (!$stmt->fetch()) {
    echo json_encode(array());
    exit;
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.title, p.image_path 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$stmt->execute(array($orderId));
$items = $stmt->fetchAll();

echo json_encode($items);
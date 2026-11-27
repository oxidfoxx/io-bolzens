<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($productId <= 0) {
    echo json_encode(array('success' => false, 'error' => 'Invalid product id'));
    exit;
}

require_once 'db.php';

// Проверяем, что товар существует
$stmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = ?");
$stmt->execute(array($productId));
if (!$stmt->fetch()) {
    echo json_encode(array('success' => false, 'error' => 'Product not found'));
    exit;
}

// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Добавляем или увеличиваем количество
if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]++;
} else {
    $_SESSION['cart'][$productId] = 1;
}

// Считаем общее количество
$count = 0;
foreach ($_SESSION['cart'] as $qty) {
    $count += $qty;
}

echo json_encode(array(
    'success' => true,
    'count' => $count,
    'product_id' => $productId
));
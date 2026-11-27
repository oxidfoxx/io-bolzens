<?php
// assets/includes/db_helpers.php
// Функции для работы с товарами и категориями

/**
 * Возвращает все категории, отсортированные по id.
 * @param PDO $pdo
 * @return array
 */
function getAllCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id ASC");
    return $stmt->fetchAll();
}

/**
 * Возвращает все товары, опционально — только для одной категории.
 * @param PDO $pdo
 * @param string|null $categorySlug
 * @return array
 */
function getProducts($pdo, $categorySlug = null) {
    if ($categorySlug !== null) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.slug AS category_slug, c.title AS category_title
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE c.slug = ?
            ORDER BY p.product_id ASC
        ");
        $stmt->execute(array($categorySlug));
    } else {
        $stmt = $pdo->query("
            SELECT p.*, c.slug AS category_slug, c.title AS category_title
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            ORDER BY c.category_id ASC, p.product_id ASC
        ");
    }
    return $stmt->fetchAll();
}

/**
 * Возвращает товары, сгруппированные по slug категории.
 * Результат: [ 'bread' => [товары...], 'viennoiserie' => [...], ... ]
 * @param PDO $pdo
 * @return array
 */
function getProductsGroupedByCategory($pdo) {
    $products = getProducts($pdo);
    $grouped = array();
    foreach ($products as $p) {
        $grouped[$p['category_slug']][] = $p;
    }
    return $grouped;
}

/**
 * Возвращает один товар по id.
 * @param PDO $pdo
 * @param int $id
 * @return array|false
 */
function getProductById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?
    ");
    $stmt->execute(array((int)$id));
    return $stmt->fetch();
}
?>
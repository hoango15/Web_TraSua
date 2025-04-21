<?php
function getFeaturedProducts($conn, $limit = 8)
{
    $products = [];
    $sql = "SELECT * FROM products ORDER BY id DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

function getProductsByCategory($conn, $category_id = null)
{
    $products = [];
    if ($category_id) {
        $sql = "SELECT * FROM products WHERE category_id = ? ORDER BY name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
    } else {
        $sql = "SELECT * FROM products ORDER BY name";
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

function getProductById($conn, $product_id)
{
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getRelatedProducts($conn, $category_id, $product_id, $limit = 4)
{
    $products = [];
    $sql = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $category_id, $product_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

function getAllCategories($conn)
{
    $categories = [];
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

function getAllToppings($conn)
{
    $toppings = [];
    $sql = "SELECT * FROM toppings ORDER BY name";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $toppings[] = $row;
        }
    }
    return $toppings;
}

function getNewsItems($conn, $limit = 4)
{
    $news = [];
    $sql = "SELECT * FROM news ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
    }
    return $news;
}

function getNewsById($conn, $news_id)
{
    $sql = "SELECT * FROM news WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function formatPrice($price)
{
    return number_format($price, 0, ',', '.') . 'đ';
}

function formatDate($date)
{
    return date('d/m/Y H:i', strtotime($date));
}

function calculateDiscountPercent($original_price, $discount_price)
{
    if ($original_price <= 0 || $discount_price <= 0 || $discount_price >= $original_price) {
        return 0;
    }
    return round(($original_price - $discount_price) / $original_price * 100);
}
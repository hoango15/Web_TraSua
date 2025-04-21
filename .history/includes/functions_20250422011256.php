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

function getCartItems($conn, $cart)
{
    $cart_items = [];
    $total = 0;
    if (!empty($cart)) {
        foreach ($cart as $cart_id => $item) {
            $product_id = $item['product_id'];

            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();

                $item_price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
                $topping_names = [];
                if (!empty($item['toppings'])) {
                    foreach ($item['toppings'] as $topping_id) {
                        $topping_sql = "SELECT name FROM toppings WHERE id = ?";
                        $topping_stmt = $conn->prepare($topping_sql);
                        $topping_stmt->bind_param("i", $topping_id);
                        $topping_stmt->execute();
                        $topping_result = $topping_stmt->get_result();

                        if ($topping_result->num_rows > 0) {
                            $topping = $topping_result->fetch_assoc();
                            $topping_names[] = $topping['name'];
                            $item_price += 5000; 
                        }
                    }
                }

                $item_total = $item_price * $item['quantity'];
                $total += $item_total;

                $cart_items[] = [
                    'cart_id' => $cart_id,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'sugar_level' => $item['sugar_level'],
                    'ice_level' => $item['ice_level'],
                    'toppings' => $topping_names,
                    'item_price' => $item_price,
                    'item_total' => $item_total
                ];
            }
        }
    }
    return [
        'items' => $cart_items,
        'subtotal' => $total,
        'shipping' => 20000,
        'total' => $total + 20000
    ];
}

function getUserById($conn, $user_id)
{
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getUserOrders($conn, $user_id)
{
    $orders = [];

    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    return $orders;
}

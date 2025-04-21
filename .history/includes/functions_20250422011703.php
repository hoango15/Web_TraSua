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

function getOrderById($conn, $order_id, $user_id)
{
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getOrderByIdForPayment($conn, $order_id)
{
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getOrderByIdSimple($conn, $order_id)
{
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getOrderItems($conn, $order_id)
{
    $items = [];
    $sql = "SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $toppings = [];
            $topping_sql = "SELECT t.name 
                           FROM order_item_toppings oit 
                           JOIN toppings t ON oit.topping_id = t.id 
                           WHERE oit.order_item_id = ?";
            $topping_stmt = $conn->prepare($topping_sql);
            $topping_stmt->bind_param("i", $row['id']);
            $topping_stmt->execute();
            $topping_result = $topping_stmt->get_result();
            while ($topping = $topping_result->fetch_assoc()) {
                $toppings[] = $topping['name'];
            }
            $row['toppings'] = $toppings;
            $items[] = $row;
        }
    }
    return $items;
}

function createOrder($conn, $user_id, $name, $email, $phone, $address, $subtotal, $shipping_fee, $total, $payment_method, $delivery_notes = '')
{
    $payment_status = ($payment_method === 'cod') ? 'pending' : 'unpaid';

    $sql = "INSERT INTO orders (user_id, name, email, phone, address, subtotal, shipping_fee, total, status, payment_method, payment_status, delivery_notes, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssdddss", $user_id, $name, $email, $phone, $address, $subtotal, $shipping_fee, $total, $payment_method, $payment_status, $delivery_notes);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function addOrderItem($conn, $order_id, $product_id, $quantity, $price, $sugar_level, $ice_level, $toppings = [])
{
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, sugar_level, ice_level) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiidii", $order_id, $product_id, $quantity, $price, $sugar_level, $ice_level);
    if ($stmt->execute()) {
        $order_item_id = $conn->insert_id;
        if (!empty($toppings)) {
            foreach ($toppings as $topping_name) {
                $topping_sql = "SELECT id FROM toppings WHERE name = ?";
                $topping_stmt = $conn->prepare($topping_sql);
                $topping_stmt->bind_param("s", $topping_name);
                $topping_stmt->execute();
                $topping_result = $topping_stmt->get_result();
                if ($topping_result->num_rows > 0) {
                    $topping = $topping_result->fetch_assoc();
                    $topping_id = $topping['id'];

                    $insert_sql = "INSERT INTO order_item_toppings (order_item_id, topping_id) VALUES (?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ii", $order_item_id, $topping_id);
                    $insert_stmt->execute();
                }
            }
        }
        return true;
    }
    return false;
}

function getStatusText($status)
{
    switch ($status) {
        case 'pending':
            return 'Chờ xác nhận';
        case 'processing':
            return 'Đang xử lý';
        case 'shipping':
            return 'Đang giao hàng';
        case 'completed':
            return 'Đã hoàn thành';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return 'Không xác định';
    }
}


function getPaymentMethodText($method)
{
    switch ($method) {
        case 'cod':
            return 'Thanh toán khi nhận hàng';
        case 'momo':
            return 'Thanh toán qua MoMo';
        default:
            return 'Không xác định';
    }
}

function getPaymentStatusText($status)
{
    switch ($status) {
        case 'paid':
            return 'Đã thanh toán';
        case 'unpaid':
            return 'Chưa thanh toán';
        case 'pending':
            return 'Chờ thanh toán';
        case 'refunded':
            return 'Đã hoàn tiền';
        default:
            return 'Không xác định';
    }
}

function getSliderImages($conn)
{
    $images = [];
    $sql = "SELECT * FROM slider_images WHERE active = 1 ORDER BY sort_order";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
    }
    return $images;
}

function getFooterContent($conn)
{
    $sql = "SELECT * FROM settings WHERE setting_key IN ('footer_about', 'footer_contact', 'footer_social')";
    $result = $conn->query($sql);
    $footer = [
        'about' => '',
        'contact' => '',
        'social' => []
    ];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['setting_key'] === 'footer_social') {
                $footer['social'] = json_decode($row['setting_value'], true);
            } else {
                $key = str_replace('footer_', '', $row['setting_key']);
                $footer[$key] = $row['setting_value'];
            }
        }
    }
    return $footer;
}

function checkAccountLockStatus($conn, $email)
{
    $sql = "SELECT * FROM login_attempts WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_locked = false;
    $time_remaining = 0;
    if ($result->num_rows > 0) {
        $attempt = $result->fetch_assoc();
        if ($attempt['is_locked'] == 1) {
            $lock_time = strtotime($attempt['lock_time']);
            $current_time = time();
            $lock_duration = 300;
            if ($current_time - $lock_time < $lock_duration) {
                $is_locked = true;
                $time_remaining = $lock_duration - ($current_time - $lock_time);
            } else {
                $sql = "UPDATE login_attempts SET is_locked = 0, attempts = 0 WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
            }
        }
    }
    return [
        'is_locked' => $is_locked,
        'time_remaining' => $time_remaining
    ];
}

function getFailedLoginAttempts($conn, $email)
{
    $sql = "SELECT attempts FROM login_attempts WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attempt = $result->fetch_assoc();
        return $attempt['attempts'];
    }

    return 0;
}
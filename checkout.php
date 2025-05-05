<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to complete your purchase']);
    exit();
}

// Get cart items from localStorage (passed via AJAX)
$cart = json_decode(file_get_contents('php://input'), true);

if (!$cart) {
    echo json_encode(['success' => false, 'message' => 'No items in cart']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Calculate total
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Get user's address
    $stmt_user = $pdo->prepare("SELECT address FROM users WHERE user_id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $address = $user['address'] ?? ''; // Default to empty string if no address

    // Create order with all required fields
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            total_amount, 
            status, 
            shipping_address, 
            billing_address, 
            created_at, 
            updated_at
        ) VALUES (
            ?, ?, 'pending', ?, ?, 
            CURRENT_TIMESTAMP, 
            CURRENT_TIMESTAMP
        )
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $total,
        $address,
        $address
    ]);
    
    $order_id = $pdo->lastInsertId();

    // Add order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        
        // Update product inventory
        $update_inventory = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $update_inventory->execute([$item['quantity'], $item['id']]);
    }

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id IN (SELECT cart_id FROM shopping_cart WHERE user_id = ?)");
    $stmt->execute([$_SESSION['user_id']]);

    // Commit transaction
    $pdo->commit();

    // Return success response with order details
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully', 
        'order_id' => $order_id,
        'order_details' => [
            'total_amount' => $total,
            'status' => 'pending',
            'shipping_address' => $address,
            'billing_address' => $address
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log('Checkout Error: ' . $e->getMessage()); // Log the error
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while processing your order. Please try again or contact support.'
    ]);
}
?> 
<?php
session_start();
require_once 'config.php';

// Handle CLI requests
if (php_sapi_name() === 'cli') {
    if ($argc >= 2) {
        $action = $argv[1];
        
        // Handle additional arguments
        if ($argc >= 3) {
            $_GET['id'] = $argv[2];
        }
        
        // Log CLI request
        $log_message = "CLI Request at " . date('Y-m-d H:i:s') . " - Action: $action\n";
        file_put_contents('process_debug.log', $log_message, FILE_APPEND);
        
        // Process the action
        switch ($action) {
            case 'setup_database':
                setupDatabase();
                break;
                
            case 'add_email_to_orders':
                addEmailToOrders();
                break;
                
            case 'fix_phone_column':
                fixPhoneColumn();
                break;
                
            case 'order_confirmation':
                getOrderConfirmation();
                break;
                
            default:
                echo "Invalid action specified. Available actions:\n";
                echo "- setup_database\n";
                echo "- add_email_to_orders\n";
                echo "- fix_phone_column\n";
                echo "- order_confirmation [order_id]\n";
                break;
        }
        exit;
    } else {
        echo "Usage: php process.php [action] [id]\n";
        echo "Available actions:\n";
        echo "- setup_database\n";
        echo "- add_email_to_orders\n";
        echo "- fix_phone_column\n";
        echo "- order_confirmation [order_id]\n";
        exit;
    }
}

// Set headers for JSON responses for web requests
header('Content-Type: application/json');

// Get the action from request parameters
$action = $_GET['action'] ?? '';

// If no action is specified in GET, check POST
if (empty($action)) {
    $action = $_POST['action'] ?? '';
}

// If still no action, check if it's in the JSON body
if (empty($action)) {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $action = $jsonData['action'] ?? '';
}

// Log request for debugging
$log_message = "Request at " . date('Y-m-d H:i:s') . " - Action: $action\n";
file_put_contents('process_debug.log', $log_message, FILE_APPEND);

// Process based on action
switch ($action) {
    case 'checkout':
        processCheckout();
        break;
        
    case 'delete_order':
        deleteOrder();
        break;
        
    case 'get_order_details':
        getOrderDetails();
        break;
        
    case 'update_product_stock':
        updateProductStock();
        break;
        
    case 'get_product':
        getProduct();
        break;
        
    case 'save_product':
        saveProduct();
        break;
        
    case 'delete_product':
        deleteProduct();
        break;
        
    case 'add_email_to_orders':
        addEmailToOrders();
        break;
        
    case 'fix_phone_column':
        fixPhoneColumn();
        break;
        
    case 'setup_database':
        setupDatabase();
        break;
        
    case 'order_confirmation':
        getOrderConfirmation();
        break;
        
    case 'process_order':
        processOrder();
        break;
        
    case 'update_order':
        updateOrder();
        break;
        
    case 'logout':
        logout();
        break;
        
    case 'add_path_column':
        addPathColumn();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified'
        ]);
        break;
}

/**
 * Process checkout and create a new order
 */
function processCheckout() {
    global $pdo;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please log in to complete your purchase']);
        exit();
    }

    // Get cart items from request body
    $cart = json_decode(file_get_contents('php://input'), true);
    if (isset($cart['action'])) {
        unset($cart['action']); // Remove action if it was included in the body
    }

    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'No items in cart']);
        exit();
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // First verify all products have sufficient stock before processing
        file_put_contents($log_file, "Pre-checking stock for all cart items\n", FILE_APPEND);
        
        foreach ($cart as $item) {
            $item_id = $item['id'] ?? ($item['product_id'] ?? null);
            $item_name = $item['name'] ?? 'Unknown Product';
            $item_quantity = intval($item['quantity'] ?? 1);
            
            if (!empty($item_id) && is_numeric($item_id)) {
                $check_stock = $pdo->prepare("SELECT product_id, name, stock_quantity FROM products WHERE product_id = ?");
                $check_stock->execute([intval($item_id)]);
                $product = $check_stock->fetch(PDO::FETCH_ASSOC);
                
                if ($product && $product['stock_quantity'] < $item_quantity) {
                    file_put_contents($log_file, "Error: Not enough stock for product '{$product['name']}' (ID: {$product['product_id']}). Available: {$product['stock_quantity']}, Requested: {$item_quantity}.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => "Not enough stock for '{$product['name']}'. Available: {$product['stock_quantity']}, Requested: {$item_quantity}."
                    ]);
                    exit();
                }
            } else if (!empty($item_name) && $item_name !== 'Unknown Product') {
                $check_by_name = $pdo->prepare("SELECT product_id, name, stock_quantity FROM products WHERE name = ? OR name LIKE ?");
                $check_by_name->execute([$item_name, "%$item_name%"]);
                $product = $check_by_name->fetch(PDO::FETCH_ASSOC);
                
                if ($product && $product['stock_quantity'] < $item_quantity) {
                    file_put_contents($log_file, "Error: Not enough stock for product '{$product['name']}' (ID: {$product['product_id']}). Available: {$product['stock_quantity']}, Requested: {$item_quantity}.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => "Not enough stock for '{$product['name']}'. Available: {$product['stock_quantity']}, Requested: {$item_quantity}."
                    ]);
                    exit();
                }
            }
        }
        
        file_put_contents($log_file, "All items have sufficient stock. Proceeding with order.\n", FILE_APPEND);
        
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
            $update_inventory = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $update_inventory->execute([$item['quantity'], $item['id']]);
        }

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
}

/**
 * Delete an order and its items
 */
function deleteOrder() {
    global $pdo;
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['order_id']) || empty($data['order_id'])) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit;
    }

    $orderId = $data['order_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // First delete from order_items
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        // Then delete the order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        // Commit the transaction
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Order successfully deleted']);
    } catch (PDOException $e) {
        // Rollback the transaction in case of error
        $pdo->rollBack();
        
        echo json_encode(['success' => false, 'message' => 'Error deleting order: ' . $e->getMessage()]);
    }
}

/**
 * Get order details including items
 */
function getOrderDetails() {
    global $pdo;
    
    // Temporarily disable auth for testing
    /*
    // Check if user is logged in and is admin
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    */

    // Check if order ID is provided
    if (!isset($_GET['id'])) {
        echo json_encode(['error' => 'Order ID is required']);
        exit;
    }

    $order_id = intval($_GET['id']);
    
    try {
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.* 
            FROM orders o
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['error' => 'Order not found']);
            exit;
        }
        
        // Get order items with product information
        $items_stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image_url, p.product_id 
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ");
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return order details and items
        echo json_encode([
            'order' => $order,
            'items' => $items,
            'debug_info' => [
                'order_id' => $order_id,
                'item_count' => count($items)
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log('Error fetching order details: ' . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Update product stock quantity
 */
function updateProductStock() {
    global $pdo;
    
    // For debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Log incoming data
    $debug_log = "Debug Log: " . date('Y-m-d H:i:s') . "\n";
    $debug_log .= "POST Data: " . print_r($_POST, true) . "\n";
    $debug_log .= "GET Data: " . print_r($_GET, true) . "\n";
    $debug_log .= "Raw input: " . file_get_contents('php://input') . "\n";
    file_put_contents('stock_update_debug.log', $debug_log, FILE_APPEND);
    
    // Check if user is logged in and is admin
    // Temporarily disable auth for testing
    /*
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    */

    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log decoded data
    file_put_contents('stock_update_debug.log', "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);

    // Check if product_id and quantity are provided
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required']);
        exit;
    }

    $product_id = intval($data['product_id']);
    $quantity = intval($data['quantity']);
    
    // Log processing values
    file_put_contents('stock_update_debug.log', "Processing product_id: $product_id, quantity: $quantity\n", FILE_APPEND);

    // Validate quantity
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }

    try {
        // Check if product exists and get current stock
        $stmt = $pdo->prepare("SELECT product_id, stock_quantity FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log query results
        file_put_contents('stock_update_debug.log', "Product query result: " . print_r($product, true) . "\n", FILE_APPEND);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found with ID: ' . $product_id]);
            exit;
        }
        
        // Update stock quantity
        $new_quantity = $product['stock_quantity'] + $quantity;
        
        $update_stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
        $result = $update_stmt->execute([$new_quantity, $product_id]);
        
        // Log update result
        file_put_contents('stock_update_debug.log', "Update result: " . ($result ? "Success" : "Failed") . "\n", FILE_APPEND);
        file_put_contents('stock_update_debug.log', "Rows affected: " . $update_stmt->rowCount() . "\n", FILE_APPEND);
        
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock. SQL error: ' . implode(' ', $update_stmt->errorInfo())]);
            exit;
        }
        
        // Verify the update was successful
        $verify_stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $verify_stmt->execute([$product_id]);
        $updated_product = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        file_put_contents('stock_update_debug.log', "Verification result: " . print_r($updated_product, true) . "\n", FILE_APPEND);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'previous_stock' => $product['stock_quantity'],
            'new_stock' => $new_quantity,
            'actual_new_stock' => $updated_product['stock_quantity'],
            'rows_affected' => $update_stmt->rowCount()
        ]);
        
    } catch (PDOException $e) {
        file_put_contents('stock_update_debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        error_log('Stock Update Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get product details
 */
function getProduct() {
    global $pdo;
    
    // Check if user is logged in as admin
    /*
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    */

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID is required']);
        exit;
    }

    $productId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        echo json_encode($product);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Save (create/update) a product
 */
function saveProduct() {
    global $pdo;
    
    // Check if user is admin
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Get product data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product name and price are required']);
        exit;
    }

    try {
        // Check if this is an update (product_id is provided)
        if (!empty($data['product_id'])) {
            // Update existing product
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, category = ?, 
                    image_url = ?, path = ?, stock_quantity = ?, updated_at = CURRENT_TIMESTAMP
                WHERE product_id = ?
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                floatval($data['price']),
                $data['category'] ?? '',
                $data['image_url'] ?? '',
                $data['path'] ?? '',
                intval($data['stock_quantity'] ?? 0),
                $data['product_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully',
                'product_id' => $data['product_id']
            ]);
        } else {
            // Create new product
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, category, image_url, path, stock_quantity, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                floatval($data['price']),
                $data['category'] ?? '',
                $data['image_url'] ?? '',
                $data['path'] ?? '',
                intval($data['stock_quantity'] ?? 0)
            ]);
            
            $product_id = $pdo->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $product_id
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Delete a product
 */
function deleteProduct() {
    global $pdo;
    
    // Check if user is admin
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Get product ID
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID is required']);
        exit;
    }

    try {
        // First check if the product exists
        $check_stmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = ?");
        $check_stmt->execute([$data['product_id']]);
        
        if ($check_stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }
        
        // Delete the product
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$data['product_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Add email column to orders table if it doesn't exist
 */
function addEmailToOrders() {
    global $pdo;
    
    // Change header to text for CLI output if requested via CLI
    if (php_sapi_name() === 'cli') {
        header('Content-Type: text/plain');
    }
    
    $response = ["message" => "", "success" => false];
    
    try {
        // Check if email column exists
        $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'email'");
        $column_exists = $result->rowCount() > 0;
        
        if (!$column_exists) {
            // Add email column
            $pdo->exec("ALTER TABLE orders ADD COLUMN email VARCHAR(255) AFTER phone");
            $response["message"] = "Email column added successfully!";
            $response["success"] = true;
        } else {
            $response["message"] = "Email column already exists.";
            $response["success"] = true;
        }
        
        // Show current structure
        $columns = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
        $response["columns"] = $columns;
        $response["structure"] = "Current orders table columns: " . implode(", ", $columns);
        
    } catch (PDOException $e) {
        $response["message"] = "Error: " . $e->getMessage();
        $response["success"] = false;
    }
    
    // If CLI, output text
    if (php_sapi_name() === 'cli') {
        echo $response["message"] . "\n";
        if (isset($response["structure"])) {
            echo $response["structure"] . "\n";
        }
    } else {
        // Otherwise return JSON
        echo json_encode($response);
    }
}

/**
 * Fix phone column in orders table
 */
function fixPhoneColumn() {
    global $pdo;
    
    // Change header to text for CLI output if requested via CLI
    if (php_sapi_name() === 'cli') {
        header('Content-Type: text/plain');
    }
    
    $response = ["message" => "", "success" => false];
    
    try {
        // Check if phone column exists
        $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'phone'");
        $column_exists = $result->rowCount() > 0;
        
        if (!$column_exists) {
            // Add phone column
            $pdo->exec("ALTER TABLE orders ADD COLUMN phone VARCHAR(255) AFTER email");
            $response["message"] = "Phone column added successfully!";
            $response["success"] = true;
        } else {
            $response["message"] = "Phone column already exists.";
            $response["success"] = true;
        }
        
        // Show current structure
        $columns = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
        $response["columns"] = $columns;
        $response["structure"] = "Current orders table columns: " . implode(", ", $columns);
        
    } catch (PDOException $e) {
        $response["message"] = "Error: " . $e->getMessage();
        $response["success"] = false;
    }
    
    // If CLI, output text
    if (php_sapi_name() === 'cli') {
        echo $response["message"] . "\n";
        if (isset($response["structure"])) {
            echo $response["structure"] . "\n";
        }
    } else {
        // Otherwise return JSON
        echo json_encode($response);
    }
}

/**
 * Setup database
 */
function setupDatabase() {
    global $pdo;
    
    // Change header to text for CLI output if requested via CLI
    if (php_sapi_name() === 'cli') {
        header('Content-Type: text/plain');
    }
    
    $response = ["message" => "", "success" => false];
    
    try {
        // Check if database exists
        $result = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('users', 'products', 'orders', 'order_items')");
        $tables_count = $result->fetchColumn();
        
        if ($tables_count < 4) {
            // Tables don't exist yet, create them from database.sql file
            if (file_exists('database.sql')) {
                $sql = file_get_contents('database.sql');
                $pdo->exec($sql);
                $response["message"] = "Database setup completed successfully.";
                $response["success"] = true;
            } else {
                $response["message"] = "Error: database.sql file not found.";
                $response["success"] = false;
            }
        } else {
            $response["message"] = "Database already setup.";
            $response["success"] = true;
        }
        
        // List all tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $response["tables"] = $tables;
        $response["structure"] = "Database tables: " . implode(", ", $tables);
        
    } catch (PDOException $e) {
        $response["message"] = "Error: " . $e->getMessage();
        $response["success"] = false;
    }
    
    // If CLI, output text
    if (php_sapi_name() === 'cli') {
        echo $response["message"] . "\n";
        if (isset($response["structure"])) {
            echo $response["structure"] . "\n";
        }
    } else {
        // Otherwise return JSON
        echo json_encode($response);
    }
}

/**
 * Get order confirmation
 */
function getOrderConfirmation() {
    global $pdo;
    
    // If accessed via web browser directly, render HTML
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false && !isset($_GET['format']) && php_sapi_name() !== 'cli') {
        // We're rendering HTML, change the content type
        header('Content-Type: text/html');
        
        // Check if we have an order ID
        if (!isset($_GET['order_id'])) {
            header('Location: Home.php');
            exit();
        }

        $order_id = $_GET['order_id'];

        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.* 
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            header('Location: Home.php');
            exit();
        }

        // Get customer details - use data directly from orders table
        $customerName = $order['first_name'] . ' ' . $order['last_name'];
        $customerPhone = $order['phone'];
        // Get customer email directly from the orders table
        $customerEmail = $order['email'] ?: ($_SESSION['customer_email'] ?? '');

        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Include the HTML template
        ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TRÈS CHIC | Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="./darkMode.css" rel="stylesheet" />
    <script src="./darkMode.js"></script>
    <link href="./styles.css" rel="stylesheet" />
    <script src="./javascript.js"></script>
</head>
<body class="bg-white text-black transition-colors duration-200">
    <!-- Navbar -->
    <header class="flex justify-between items-center px-4 py-3 border-b transition-colors duration-200">
        <div class="flex items-center gap-4">
            <a href="Home.php" class="text-2xl">←</a>
            <div class="text-sm">Back to Shopping</div>
        </div>
        <div class="text-3xl font-bold tracking-wider"><a href="Home.php">TRÈS CHIC</a></div>
        <div class="flex items-center gap-6 text-base">
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                <span id="darkModeIcon" class="text-lg">🌙</span>
            </button>
        </div>
    </header>

    <main class="max-w-3xl mx-auto py-12 px-4">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-semibold mb-2">Thank You for Your Order!</h1>
            <p class="text-gray-600">Order #<?php echo $order_id; ?> has been placed successfully</p>
        </div>
        
        <div class="border rounded-lg overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-xl font-medium">Order Summary</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="font-medium mb-2">Shipping Information</h3>
                        <p class="text-gray-600"><?php echo $customerName; ?></p>
                        <p class="text-gray-600"><?php echo $order['shipping_address']; ?></p>
                        <p class="text-gray-600"><?php echo $customerEmail; ?></p>
                        <p class="text-gray-600"><?php echo $customerPhone; ?></p>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-2">Order Details</h3>
                        <p class="text-gray-600">Order Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                        <p class="text-gray-600">Payment Method: Credit Card</p>
                        <p class="text-gray-600">Order Status: <?php echo isset($order['status']) ? '<span class="capitalize">' . $order['status'] . '</span>' : 'Processing'; ?></p>
                    </div>
                </div>
                
                <h3 class="font-medium mb-4 pb-2 border-b">Items Ordered</h3>
                
                <div class="space-y-4 mb-6">
                    <?php if (empty($orderItems)): ?>
                        <p class="text-gray-500">No items in this order</p>
                    <?php else: ?>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="flex items-center gap-4 py-2">
                                <img src="<?php echo $item['image_url'] ?: './Images/product-placeholder.png'; ?>" alt="<?php echo $item['name']; ?>" class="w-16 h-16 object-contain">
                                <div class="flex-1">
                                    <h3 class="font-medium"><?php echo $item['name']; ?></h3>
                                    <p class="text-sm text-gray-600">$<?php echo number_format($item['price_at_time'], 2); ?> x <?php echo $item['quantity']; ?></p>
                                </div>
                                <p class="font-medium">$<?php echo number_format($item['price_at_time'] * $item['quantity'], 2); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-between font-medium text-lg py-4 border-t">
                    <span>Total</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <p class="mb-4">A confirmation email has been sent to <?php echo $customerEmail ?: 'your email address'; ?></p>
            <a href="Home.php" class="inline-block bg-black text-white py-3 px-8 rounded-full text-center text-sm font-medium hover:bg-gray-800 transition">
                Continue Shopping
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 mt-10 py-8 px-4 text-sm">
        <div class="text-center text-xs text-gray-500">© 2025 TRÈS CHIC. Tous droits réservés.</div>
    </footer>
</body>
</html><?php
        exit();
    }

    // For API requests, return JSON data
    $order_id = $_GET['order_id'] ?? null;
    
    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit;
    }
    
    try {
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.* 
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $orderItems
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Process an order and handle stock updates
 */
function processOrder() {
    global $pdo;

    // Create a log file for this order
    $log_file = 'order_process_log_' . date('Y-m-d') . '.log';
    file_put_contents($log_file, "=== New Order Process Started at " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

    // Get the order data from the request
    $raw_input = file_get_contents('php://input');
    file_put_contents($log_file, "Raw input: $raw_input\n", FILE_APPEND);

    $orderData = json_decode($raw_input, true);
    file_put_contents($log_file, "Decoded order data: " . print_r($orderData, true) . "\n", FILE_APPEND);

    // Check if the order data is valid
    if (!$orderData || empty($orderData['cart'])) {
        $error_msg = 'Invalid order data';
        file_put_contents($log_file, "Error: $error_msg\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => $error_msg]);
        exit();
    }

    try {
        // Start transaction
        $pdo->beginTransaction();
        file_put_contents($log_file, "Transaction started\n", FILE_APPEND);

        // Extract order details
        $cart = $orderData['cart'];
        $customer = $orderData['customer'];
        
        // First verify all products have sufficient stock before processing
        file_put_contents($log_file, "Pre-checking stock for all cart items\n", FILE_APPEND);
        
        foreach ($cart as $item) {
            $item_id = $item['id'] ?? ($item['product_id'] ?? null);
            $item_name = $item['name'] ?? 'Unknown Product';
            $item_quantity = intval($item['quantity'] ?? 1);
            
            if (!empty($item_id) && is_numeric($item_id)) {
                $check_stock = $pdo->prepare("SELECT product_id, name, stock_quantity FROM products WHERE product_id = ?");
                $check_stock->execute([intval($item_id)]);
                $product = $check_stock->fetch(PDO::FETCH_ASSOC);
                
                if ($product && $product['stock_quantity'] < $item_quantity) {
                    file_put_contents($log_file, "Error: Not enough stock for product '{$product['name']}' (ID: {$product['product_id']}). Available: {$product['stock_quantity']}, Requested: {$item_quantity}.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => "Not enough stock for '{$product['name']}'. Available: {$product['stock_quantity']}, Requested: {$item_quantity}."
                    ]);
                    exit();
                }
            } else if (!empty($item_name) && $item_name !== 'Unknown Product') {
                $check_by_name = $pdo->prepare("SELECT product_id, name, stock_quantity FROM products WHERE name = ? OR name LIKE ?");
                $check_by_name->execute([$item_name, "%$item_name%"]);
                $product = $check_by_name->fetch(PDO::FETCH_ASSOC);
                
                if ($product && $product['stock_quantity'] < $item_quantity) {
                    file_put_contents($log_file, "Error: Not enough stock for product '{$product['name']}' (ID: {$product['product_id']}). Available: {$product['stock_quantity']}, Requested: {$item_quantity}.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => "Not enough stock for '{$product['name']}'. Available: {$product['stock_quantity']}, Requested: {$item_quantity}."
                    ]);
                    exit();
                }
            }
        }
        
        file_put_contents($log_file, "All items have sufficient stock. Proceeding with order.\n", FILE_APPEND);
        
        // Calculate total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        file_put_contents($log_file, "Cart items: " . count($cart) . ", Total: $total\n", FILE_APPEND);
        
        // Use address directly from the form
        $address = $customer['address'];
        
        // Get user_id from session or set default guest user
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        // Get product_id from first item in cart (if your schema requires just one product_id per order)
        $product_id = !empty($cart) ? ($cart[0]['id'] ?? ($cart[0]['product_id'] ?? 0)) : 0;
        
        file_put_contents($log_file, "User ID: $user_id, Primary product ID: $product_id\n", FILE_APPEND);
        
        // Store customer info in session
        $_SESSION['customer_name'] = $customer['firstName'] . ' ' . $customer['lastName'];
        $_SESSION['customer_email'] = $customer['email'];
        $_SESSION['customer_phone'] = $customer['phone'];
        
        try {
            // First check if all required columns exist in the orders table
            $required_columns = ['user_id', 'first_name', 'last_name', 'phone', 'email', 'total_amount', 'shipping_address', 'product_id'];
            $missing_columns = [];
            
            $columns_result = $pdo->query("DESCRIBE orders");
            $columns = $columns_result->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($required_columns as $column) {
                if (!in_array($column, $columns)) {
                    $missing_columns[] = $column;
                }
            }
            
            if (!empty($missing_columns)) {
                throw new Exception("Missing required columns in orders table: " . implode(", ", $missing_columns));
            }
            
            // Create order with updated schema
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id,
                    first_name,
                    last_name,
                    phone,
                    email,
                    total_amount, 
                    shipping_address, 
                    created_at,
                    updated_at,
                    product_id
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, 
                    NOW(), 
                    NOW(),
                    ?
                )
            ");
            
            $stmt->execute([
                $user_id,
                $customer['firstName'],
                $customer['lastName'],
                $customer['phone'],
                $customer['email'],
                $total,
                $address,
                $product_id
            ]);
            
            $order_id = $pdo->lastInsertId();
            file_put_contents($log_file, "Order created with ID: $order_id\n", FILE_APPEND);
        } catch (Exception $e) {
            // Fallback to a simpler query if the first one fails
            file_put_contents($log_file, "Error with full order insert: " . $e->getMessage() . "\nTrying simplified insert...\n", FILE_APPEND);
            
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id,
                    total_amount, 
                    shipping_address, 
                    created_at,
                    updated_at
                ) VALUES (
                    ?, ?, ?, 
                    NOW(), 
                    NOW()
                )
            ");
            
            $stmt->execute([
                $user_id,
                $total,
                $address
            ]);
            
            $order_id = $pdo->lastInsertId();
            file_put_contents($log_file, "Order created with simplified schema, ID: $order_id\n", FILE_APPEND);
        }

        // Add order items - using price_at_time instead of price
        // First check if user_id column exists in order_items table
        $check_column = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'user_id'");
        $user_id_column_exists = $check_column->rowCount() > 0;
        
        file_put_contents($log_file, "Checking if user_id column exists in order_items: " . ($user_id_column_exists ? "Yes" : "No") . "\n", FILE_APPEND);
        
        if ($user_id_column_exists) {
            $order_items_stmt = $pdo->prepare("INSERT INTO order_items (order_id, user_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?, ?)");
        } else {
            $order_items_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        }
        
        foreach ($cart as $key => $item) {
            file_put_contents($log_file, "Processing cart item #" . ($key + 1) . ": " . print_r($item, true) . "\n", FILE_APPEND);
            
            // Try to get the product ID from various sources
            $item_id = $item['id'] ?? ($item['product_id'] ?? null);
            
            // Make sure product_id exists
            if (empty($item_id)) {
                $item_name = $item['name'] ?? 'Unknown';
                file_put_contents($log_file, "Warning: Item '$item_name' has no ID, using 0 as placeholder\n", FILE_APPEND);
                $item_id = 0; // Use 0 as a fallback ID
            }
            
            // Get the item details
            $item_name = $item['name'] ?? 'Unknown Product';
            $item_quantity = intval($item['quantity'] ?? 1);
            $item_price = floatval($item['price'] ?? 0);
            
            file_put_contents($log_file, "Looking for product with ID: $item_id or name: $item_name\n", FILE_APPEND);
            
            // Try to find the product by ID first (if it's numeric)
            $product = null;
            if (is_numeric($item_id) && $item_id > 0) {
                $check_stock = $pdo->prepare("SELECT product_id, name, stock_quantity FROM products WHERE product_id = ?");
                $check_stock->execute([intval($item_id)]);
                $product = $check_stock->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    file_put_contents($log_file, "Found product by ID: " . print_r($product, true) . "\n", FILE_APPEND);
                }
            }
            
            // If not found by ID, try to find by name
            if (!$product && !empty($item_name) && $item_name !== 'Unknown Product') {
                file_put_contents($log_file, "Product not found with ID $item_id, trying to find by name\n", FILE_APPEND);
                
                $check_by_name = $pdo->prepare("SELECT product_id, name, stock_quantity FROM products WHERE name = ? OR name LIKE ?");
                $check_by_name->execute([$item_name, "%$item_name%"]);
                $product = $check_by_name->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    file_put_contents($log_file, "Found product by name: " . print_r($product, true) . "\n", FILE_APPEND);
                }
            }
            
            if (!$product) {
                file_put_contents($log_file, "Warning: Product with ID {$item_id} and name '{$item_name}' not found in database. Using provided data.\n", FILE_APPEND);
                // Instead of failing, use the data we have
                $actual_product_id = intval($item_id);
            } else {
                // Get the actual product_id from the database
                $actual_product_id = $product['product_id'];
                
                // We've already checked stock at the beginning, so we know we have enough
                
                // Update product stock - THIS IS THE IMPORTANT PART FOR REDUCING STOCK
                $update_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $result = $update_stock->execute([$item_quantity, $actual_product_id]);
                
                file_put_contents($log_file, "Stock update result: " . ($result ? "Success" : "Failed") . "\n", FILE_APPEND);
                
                // Verify the stock was actually updated
                $verify_stock = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
                $verify_stock->execute([$actual_product_id]);
                $updated_product = $verify_stock->fetch(PDO::FETCH_ASSOC);
                
                file_put_contents($log_file, "New stock quantity: " . ($updated_product ? $updated_product['stock_quantity'] : 'Unknown') . "\n", FILE_APPEND);
            }
            
            // Insert order item with the actual product_id
            try {
                if ($user_id_column_exists) {
                    $order_items_stmt->execute([
                        $order_id,
                        $user_id,
                        $actual_product_id, 
                        $item_quantity, 
                        $item_price
                    ]);
                } else {
                    $order_items_stmt->execute([
                        $order_id,
                        $actual_product_id, 
                        $item_quantity, 
                        $item_price
                    ]);
                }
                
                file_put_contents($log_file, "Order item inserted for product ID: $actual_product_id\n", FILE_APPEND);
            } catch (Exception $e) {
                file_put_contents($log_file, "Error inserting order item: " . $e->getMessage() . "\n", FILE_APPEND);
                // Continue with next item instead of failing the whole order
            }
        }

        // Commit transaction
        $pdo->commit();
        file_put_contents($log_file, "Transaction committed successfully\n", FILE_APPEND);

        // Return success response
        echo json_encode([
            'success' => true, 
            'message' => 'Order placed successfully', 
            'order_id' => $order_id
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            file_put_contents($log_file, "Transaction rolled back due to error\n", FILE_APPEND);
        }
        
        // Log the error
        $error_msg = 'Order Processing Error: ' . $e->getMessage();
        error_log($error_msg);
        file_put_contents($log_file, "Error: $error_msg\n", FILE_APPEND);
        
        // Return error response
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred while processing your order: ' . $e->getMessage()
        ]);
    }

    file_put_contents($log_file, "=== Order Process Completed at " . date('Y-m-d H:i:s') . " ===\n\n", FILE_APPEND);
}

/**
 * Update an order
 */
function updateOrder() {
    global $pdo;

    // Check if user is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['order_id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID and status are required']);
        exit;
    }

    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($data['status'], $validStatuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$data['status'], $data['order_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

/**
 * Logout function
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // For API requests, return JSON
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    } else {
        // For regular requests, redirect to login page
        header("Location: login.php");
        exit();
    }
}

/**
 * Add path column to orders table if it doesn't exist
 */
function addPathColumn() {
    global $pdo;
    
    // Change header to text for CLI output if requested via CLI
    if (php_sapi_name() === 'cli') {
        header('Content-Type: text/plain');
    }
    
    $response = ["message" => "", "success" => false];
    
    try {
        // Check if path column exists in orders table
        $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'path'");
        $column_exists_orders = $result->rowCount() > 0;
        
        if (!$column_exists_orders) {
            // Add path column to orders
            $pdo->exec("ALTER TABLE orders ADD COLUMN path VARCHAR(255) AFTER phone");
            $response["message"] = "Path column added successfully to orders table! ";
            $response["success"] = true;
        } else {
            $response["message"] = "Path column already exists in orders table. ";
            $response["success"] = true;
        }
        
        // Check if path column exists in products table
        $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'path'");
        $column_exists_products = $result->rowCount() > 0;
        
        if (!$column_exists_products) {
            // Add path column to products
            $pdo->exec("ALTER TABLE products ADD COLUMN path VARCHAR(255) AFTER image_url");
            $response["message"] .= "Path column added successfully to products table!";
            $response["success"] = true;
        } else {
            $response["message"] .= "Path column already exists in products table.";
            $response["success"] = true;
        }
        
        // Show current structure of orders table
        $columns = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
        $response["orders_columns"] = $columns;
        $response["orders_structure"] = "Current orders table columns: " . implode(", ", $columns);
        
        // Show current structure of products table
        $columns = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
        $response["products_columns"] = $columns;
        $response["products_structure"] = "Current products table columns: " . implode(", ", $columns);
        
    } catch (PDOException $e) {
        $response["message"] = "Error: " . $e->getMessage();
        $response["success"] = false;
    }
    
    // If CLI, output text
    if (php_sapi_name() === 'cli') {
        echo $response["message"] . "\n";
        if (isset($response["orders_structure"])) {
            echo $response["orders_structure"] . "\n";
        }
        if (isset($response["products_structure"])) {
            echo $response["products_structure"] . "\n";
        }
    } else {
        // Otherwise return JSON
        echo json_encode($response);
    }
}
?> 
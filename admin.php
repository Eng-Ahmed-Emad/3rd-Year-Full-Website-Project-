<?php
session_start();
require_once 'config.php'; // We'll create this file for database connection

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                // Add new product
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, image_path, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['category'],
                    $_POST['image_url'],
                    $_POST['stock_quantity']
                ]);
                break;
            
            case 'update_product':
                // Update existing product
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, image_path = ?, stock_quantity = ? WHERE product_id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['category'],
                    $_POST['image_url'],
                    $_POST['stock_quantity'],
                    $_POST['product_id']
                ]);
                break;
            
            case 'delete_product':
                // Delete product
                $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
                $stmt->execute([$_POST['product_id']]);
                break;
        }
    }
}

// Fetch data for display
$products = $pdo->query("SELECT * FROM products")->fetchAll();
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRÈS CHIC | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">TRÈS CHIC Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="#dashboard" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a>
                <a href="#products" class="block px-4 py-2 hover:bg-gray-700">Products</a>
                <a href="#orders" class="block px-4 py-2 hover:bg-gray-700">Orders</a>
                <a href="#users" class="block px-4 py-2 hover:bg-gray-700">Users</a>
                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-700">Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 p-8">
            <!-- Dashboard Section -->
            <section id="dashboard" class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold">Total Products</h3>
                        <p class="text-3xl font-bold"><?php echo count($products); ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold">Total Orders</h3>
                        <p class="text-3xl font-bold"><?php echo count($orders); ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold">Total Users</h3>
                        <p class="text-3xl font-bold"><?php echo count($users); ?></p>
                    </div>
                </div>
            </section>

            <!-- Products Section -->
            <section id="products" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Products</h2>
                    <button onclick="showAddProductModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Add Product</button>
                </div>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo $product['product_id']; ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="px-6 py-4">$<?php echo number_format($product['price'], 2); ?></td>
                                <td class="px-6 py-4"><?php echo $product['stock_quantity']; ?></td>
                                <td class="px-6 py-4">
                                    <button onclick="showEditProductModal(<?php echo $product['product_id']; ?>)" class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteProduct(<?php echo $product['product_id']; ?>)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Orders Section -->
            <section id="orders" class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Orders</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo $order['order_id']; ?></td>
                                <td class="px-6 py-4">
                                    <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                </td>
                                <td class="px-6 py-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="px-6 py-4"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="deleteOrder(<?php echo $order['order_id']; ?>)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="relative top-5 mx-auto p-3 border w-96 shadow-lg rounded-md bg-white max-h-[60vh] overflow-y-auto">
            <div class="mt-2">
                <h3 class="text-lg font-medium" id="modalTitle">Add New Product</h3>
                <form id="productForm" class="mt-2">
                    <input type="hidden" id="productId" name="product_id">
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="name">Name</label>
                        <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="description">Description</label>
                        <textarea id="description" name="description" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="price">Price</label>
                        <input type="number" step="0.01" id="price" name="price" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="category">Category</label>
                        <select id="category" name="category" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select a category</option>
                            <option value="accessories">Accessories</option>
                            <option value="fragrance">Fragrance</option>
                            <option value="tie">Tie</option>
                            <option value="suit">Suit</option>
                            <option value="shirt">Shirt</option>
                            <option value="vest">Vest</option>
                            <option value="shoes">Shoes</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="image_url">Image URL</label>
                        <input type="text" id="image_url" name="image_url" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="path">Path (for product page link)</label>
                        <input type="text" id="path" name="path" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g., shirt, tie, belt">
                        <p class="text-xs text-gray-500 mt-1">This will be used for the product page URL (./products/[path]). Do not include the .php extension.</p>
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1" for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="button" onclick="hideProductModal()" class="bg-gray-500 text-white px-3 py-1 rounded mr-2">Cancel</button>
                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-lg font-medium">Order Details <span id="orderIdDisplay"></span></h3>
                    <button onclick="hideOrderDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mt-4">
                    <div>
                        <h4 class="font-medium mb-2">Customer Information</h4>
                        <p><strong>Name:</strong> <span id="customerName"></span></p>
                        <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="customerPhone"></span></p>
                        <p><strong>Address:</strong> <span id="shippingAddress"></span></p>
                        <p><strong>Order Date:</strong> <span id="orderDate"></span></p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="font-medium mb-2">Ordered Items</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Update</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsList" class="divide-y divide-gray-200">
                                <!-- Order items will be added here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex justify-between mt-6 border-t pt-4">
                        <span class="font-medium">Order Total:</span>
                        <span id="orderTotal" class="font-medium"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Product Modal Functions
        function showAddProductModal() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productModal').classList.remove('hidden');
        }

        function showEditProductModal(productId) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            // Fetch product data and populate form
            fetch(`process.php?action=get_product&id=${productId}`)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('productId').value = product.product_id;
                    document.getElementById('name').value = product.name;
                    document.getElementById('description').value = product.description;
                    document.getElementById('price').value = product.price;
                    
                    // Set the category dropdown value
                    const categorySelect = document.getElementById('category');
                    for (let i = 0; i < categorySelect.options.length; i++) {
                        if (categorySelect.options[i].value === product.category) {
                            categorySelect.selectedIndex = i;
                            break;
                        }
                    }
                    
                    document.getElementById('image_url').value = product.image_url;
                    document.getElementById('path').value = product.path || '';
                    document.getElementById('stock_quantity').value = product.stock_quantity;
                    document.getElementById('productModal').classList.remove('hidden');
                });
        }

        function hideProductModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        // Order Details Modal Functions
        function viewOrderDetails(orderId) {
            console.log('Viewing order details for ID:', orderId);
            
            // Fetch order details
            fetch(`process.php?action=get_order_details&id=${orderId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text().then(text => {
                        try {
                            return text ? JSON.parse(text) : {};
                        } catch (error) {
                            console.error('Error parsing JSON:', error);
                            console.log('Raw response:', text);
                            return { error: 'Invalid server response' };
                        }
                    });
                })
                .then(data => {
                    console.log('Order details data:', data);
                    
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    // Populate order details
                    document.getElementById('orderIdDisplay').textContent = `#${data.order.order_id}`;
                    document.getElementById('customerName').textContent = `${data.order.first_name} ${data.order.last_name}`;
                    document.getElementById('customerEmail').textContent = data.order.email || 'Not provided';
                    document.getElementById('customerPhone').textContent = data.order.phone || 'Not provided';
                    document.getElementById('shippingAddress').textContent = data.order.shipping_address || 'Not provided';
                    document.getElementById('orderDate').textContent = new Date(data.order.created_at).toLocaleString();
                    document.getElementById('orderTotal').textContent = `$${parseFloat(data.order.total_amount).toFixed(2)}`;
                    
                    // Populate order items
                    const orderItemsList = document.getElementById('orderItemsList');
                    orderItemsList.innerHTML = '';
                    
                    if (!data.items || data.items.length === 0) {
                        orderItemsList.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">No items found for this order</td></tr>';
                    } else {
                        data.items.forEach(item => {
                            const row = document.createElement('tr');
                            const productName = item.name || 'Unknown Product';
                            const productPrice = parseFloat(item.price_at_time).toFixed(2);
                            const quantity = parseInt(item.quantity);
                            const totalPrice = (parseFloat(item.price_at_time) * quantity).toFixed(2);
                            const productId = item.product_id;
                            
                            console.log('Item details:', {
                                productId: productId,
                                name: productName,
                                price: productPrice,
                                quantity: quantity
                            });
                            
                            // Only show restock button if we have a valid product_id
                            const restockButton = productId 
                                ? `<button onclick="updateProductStock(${productId}, ${quantity})" class="px-3 py-1 bg-blue-500 text-white rounded text-xs">
                                      Restock
                                   </button>`
                                : '<span class="text-red-500 text-xs">Product not found</span>';
                            
                            row.innerHTML = `
                                <td class="px-6 py-4">${productName}</td>
                                <td class="px-6 py-4">$${productPrice}</td>
                                <td class="px-6 py-4">${quantity}</td>
                                <td class="px-6 py-4">$${totalPrice}</td>
                                <td class="px-6 py-4">${restockButton}</td>
                            `;
                            
                            orderItemsList.appendChild(row);
                        });
                    }
                    
                    // Show modal
                    document.getElementById('orderDetailsModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error: Could not load order details');
                });
        }

        function hideOrderDetailsModal() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
        }

        // Update Product Stock
        function updateProductStock(productId, quantity) {
            if (confirm('Are you sure you want to add ' + quantity + ' items back to stock?')) {
                console.log('Updating stock for product ID ' + productId + ' with quantity ' + quantity);
                
                fetch('process.php?action=update_product_stock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text().then(text => {
                        try {
                            return text ? JSON.parse(text) : {};
                        } catch (error) {
                            console.error('Error parsing JSON:', error);
                            console.log('Raw response:', text);
                            return { success: false, message: 'Invalid server response' };
                        }
                    });
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert('Stock updated successfully! Previous stock: ' + data.previous_stock + ', New stock: ' + data.actual_new_stock);
                        hideOrderDetailsModal();
                        location.reload();
                    } else {
                        alert('Error updating stock: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error: Could not connect to the server');
                });
            }
        }

        // Delete Product
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('process.php?action=delete_product', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                }).then(() => {
                    location.reload();
                });
            }
        }

        // Form Submission
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            fetch('process.php?action=save_product', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            }).then(() => {
                location.reload();
            });
        });

        // Delete Order
        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                fetch('process.php?action=delete_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order successfully deleted!');
                        location.reload();
                    } else {
                        alert('Error deleting order: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error: Could not connect to the server');
                });
            }
        }
    </script>
</body>
</html> 
<?php
session_start();
require_once 'config.php';

// Initialize variables
$cart_items = [];
$total = 0;
$error = '';

// Get cart items from localStorage via JavaScript
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TRÈS CHIC | Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="./darkMode.css" rel="stylesheet" />
    <script src="./darkMode.js"></script>
    <style>
        body {
            font-family: 'Georgia', serif;
        }
        .form-input {
            @apply border-2 border-gray-300 px-4 py-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400;
            transition: all 0.2s ease-in-out;
            background-color: #fafafa;
        }
        .form-input:hover {
            border-color: #999;
        }
        .dark .form-input {
            @apply bg-gray-800 border-gray-600 text-white focus:ring-gray-500 focus:border-gray-500;
            background-color: #2d2d2d;
        }
        .dark .form-input:hover {
            border-color: #777;
        }
        input:focus, select:focus {
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        .dark input:focus, .dark select:focus {
            box-shadow: 0 0 0 2px rgba(255,255,255,0.1);
        }
        label {
            font-weight: 500;
            color: #333;
        }
        .dark label {
            color: #e0e0e0;
        }
    </style>
</head>
<body class="bg-white text-black transition-colors duration-200">
    <!-- Navbar -->
    <header class="flex justify-between items-center px-4 py-3 border-b transition-colors duration-200">
        <div class="flex items-center gap-4">
            <a href="javascript:history.back()" class="text-2xl">←</a>
            <div class="text-sm">Back</div>
        </div>
        <div class="text-3xl font-bold tracking-wider"><a href="Home.php">TRÈS CHIC</a></div>
        <div class="flex items-center gap-6 text-base">
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                <span id="darkModeIcon" class="text-lg">🌙</span>
            </button>
        </div>
    </header>

    <main class="max-w-5xl mx-auto py-12 px-4">
        <h1 class="text-3xl font-semibold mb-8 text-center">Checkout</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Order Summary -->
            <div>
                <h2 class="text-xl font-medium mb-4 pb-2 border-b">Order Summary</h2>
                <div id="orderItems" class="space-y-4 mb-6">
                    <!-- Cart items will be inserted here by JavaScript -->
                </div>
                
                <div class="flex justify-between font-medium text-lg py-4 border-t">
                    <span>Total</span>
                    <span id="orderTotal">$0.00</span>
                </div>
            </div>
            
            <!-- Checkout Form -->
            <div>
                <h2 class="text-xl font-medium mb-4 pb-2 border-b">Shipping Information</h2>
                <form id="checkoutForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1" for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1" for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" class="form-input" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm mb-1" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm mb-1" for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm mb-1" for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-input" required>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="bg-black text-white py-3 rounded-full text-center w-full text-sm font-medium hover:bg-gray-800 transition">
                            Complete Purchase
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 mt-10 py-8 px-4 text-sm">
        <div class="text-center text-xs text-gray-500">© 2025 TRÈS CHIC. Tous droits réservés.</div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Checkout page loaded');
            
            // Load cart data from localStorage
            const cartJson = localStorage.getItem('cart');
            console.log('Cart from localStorage:', cartJson);
            
            const cart = JSON.parse(cartJson) || [];
            console.log('Parsed cart:', cart);
            
            const orderItems = document.getElementById('orderItems');
            const orderTotal = document.getElementById('orderTotal');
            
            // Calculate total and display cart items
            let total = 0;
            
            if (cart.length === 0) {
                orderItems.innerHTML = '<p class="text-gray-500">Your cart is empty</p>';
            } else {
                cart.forEach((item, index) => {
                    console.log(`Cart item #${index + 1}:`, item);
                    
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    
                    const itemElement = document.createElement('div');
                    itemElement.className = 'flex items-center gap-4 py-2';
                    itemElement.innerHTML = `
                        <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-contain">
                        <div class="flex-1">
                            <h3 class="font-medium">${item.name}</h3>
                            <p class="text-sm text-gray-600">$${item.price.toFixed(2)} x ${item.quantity}</p>
                            <p class="text-xs text-gray-500">ID: ${item.id || item.product_id || 'Unknown'}</p>
                        </div>
                        <p class="font-medium">$${itemTotal.toFixed(2)}</p>
                    `;
                    orderItems.appendChild(itemElement);
                });
            }
            
            orderTotal.textContent = `$${total.toFixed(2)}`;
            
            // Handle form submission
            const checkoutForm = document.getElementById('checkoutForm');
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Checkout form submitted');
                
                if (cart.length === 0) {
                    alert('Your cart is empty');
                    return;
                }
                
                // Get form data
                const formData = new FormData(checkoutForm);
                
                // Make sure each cart item has an id for the database
                const cartWithIds = cart.map((item, index) => {
                    // First try to use existing id or product_id
                    if (item.id) {
                        console.log(`Item #${index + 1} already has id: ${item.id}`);
                        return item;
                    } else if (item.product_id) {
                        console.log(`Item #${index + 1} using product_id as id: ${item.product_id}`);
                        return {...item, id: item.product_id};
                    }
                    
                    // If no id is present, generate one (this is a fallback)
                    const fallbackId = 'product_' + Math.floor(Math.random() * 10000);
                    console.log(`Item #${index + 1} using generated fallback id: ${fallbackId}`);
                    return {...item, id: fallbackId};
                });
                
                console.log('Cart items with IDs:', cartWithIds);
                
                const orderData = {
                    cart: cartWithIds,
                    customer: {
                        firstName: formData.get('firstName'),
                        lastName: formData.get('lastName'),
                        email: formData.get('email'),
                        phone: formData.get('phone'),
                        address: formData.get('address')
                    }
                };
                
                console.log('Sending order data:', orderData);
                
                // Show loading state
                const submitButton = checkoutForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.textContent;
                submitButton.textContent = 'Processing...';
                submitButton.disabled = true;
                
                // Send order data to server
                fetch('process.php?action=process_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => {
                    console.log('Server response status:', response.status);
                    return response.text().then(text => {
                        try {
                            // Try to parse JSON but handle text responses too
                            const data = text ? JSON.parse(text) : {};
                            console.log('Parsed server response:', data);
                            return data;
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                            console.log('Raw server response:', text);
                            throw new Error('Invalid server response: ' + text);
                        }
                    });
                })
                .then(data => {
                    console.log('Server response data:', data);
                    
                    if (data.success) {
                        // Clear cart and redirect to confirmation page
                        localStorage.removeItem('cart');
                        console.log('Cart cleared, redirecting to confirmation page');
                        window.location.href = `process.php?action=order_confirmation&order_id=${data.order_id}`;
                    } else {
                        // Reset button state
                        submitButton.textContent = originalButtonText;
                        submitButton.disabled = false;
                        
                        // Show error
                        if (data.message && data.message.includes('Not enough stock')) {
                            // Create a more user-friendly notification
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4 mb-4';
                            errorMessage.innerHTML = `
                                <strong class="font-bold">Stock Error!</strong>
                                <span class="block sm:inline"> Some items in your cart are out of stock or have insufficient quantity.</span>
                                <p class="mt-2">Please adjust your cart and try again.</p>
                            `;
                            
                            // Insert the error message at the top of the form
                            checkoutForm.insertBefore(errorMessage, checkoutForm.firstChild);
                            
                            // Scroll to the error message
                            errorMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } else {
                            alert('Error: ' + data.message);
                        }
                    }
                })
                .catch(error => {
                    // Reset button state
                    submitButton.textContent = originalButtonText;
                    submitButton.disabled = false;
                    
                    console.error('Error details:', error);
                    // Try to get more information about the error
                    alert('An error occurred while processing your order: ' + error.message + '. Check the console for more details.');
                });
            });
        });
    </script>
</body>
</html> 
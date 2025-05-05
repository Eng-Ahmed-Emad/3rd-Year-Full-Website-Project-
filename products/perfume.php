<?php
require_once '../config.php';

// Get stock quantity for perfume product
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = 4");
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);
$stock_quantity = $product ? $product['stock_quantity'] : 0;
$productId = $product ? $product['product_id'] : 4;
$productName = $product ? $product['name'] : 'Imagination';
$productPrice = $product ? $product['price'] : 330.00;
$inStock = $stock_quantity > 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TRÈS CHIC | Imagination Perfume</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="../darkMode.css" rel="stylesheet" />
    <script src="../darkMode.js"></script>
    <link href="../styles.css" rel="stylesheet" />
    <style>
        /* Custom styles not in styles.css */
    </style>
    <script src="../javascript.js"></script>
</head>
<body class="bg-white text-black transition-colors duration-200">
    <!-- Sliding Menu -->
    <div id="slidingMenu" class="sliding-menu">
        <div class="menu-content">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-bold">Menu</h2>
                <button onclick="toggleMenu()" class="text-2xl hover:text-gray-600">×</button>
            </div>
            <div class="menu-item" onclick="window.location.href='#'">
                <span class="text-gray-600">Cart</span>
            </div>
            <div class="menu-item" onclick="toggleMenu(); toggleContactPanel();">
                <span class="text-gray-600">Contact Us</span>
            </div>
            <div class="menu-item" onclick="window.location.href='../faq.php'">
                <span class="text-gray-600">FAQs</span>
            </div>
        </div>
    </div>
    <div id="menuOverlay" class="menu-overlay" onclick="toggleMenu()"></div>

    <!-- Navbar -->
    <header class="flex justify-between items-center px-4 py-3 border-b transition-colors duration-200">
        <div class="flex items-center gap-4">
            <div class="navbar-icon cursor-pointer" onclick="toggleMenu()">☰</div>
            <div class="text-sm">Menu</div>
        </div>
        <div class="text-2xl font-bold tracking-wider"><a href="../Home.php">TRÈS CHIC</a></div>
        <div class="flex items-center gap-4 text-sm">
            <div class="cursor-pointer" onclick="toggleContactPanel()">Contactez-nous</div>
            <div><a href="../login.php" onclick="handleLoginIconClick(event)">🔑</a></div>
            <div onclick="toggleCartPanel()" class="cursor-pointer">🛒</div>
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                <span id="darkModeIcon" class="text-lg">🌙</span>
            </button>
        </div>
    </header>

    <!-- Main Product Section -->
    <main class="grid grid-cols-1 md:grid-cols-2 min-h-screen">
        <!-- Video Section -->
        <div class="bg-gray-50 flex justify-center items-center p-0 relative">
            <video autoplay loop muted playsinline class="w-full h-full object-cover">
                <source src="../Images/bottlevideo.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    
        <!-- Product Info -->
        <div class="p-10 flex flex-col justify-center items-start">
            <div class="w-full max-w-md">
                <p class="text-xs tracking-widest mb-1"><?php echo $productId; ?></p>
                <h1 class="text-3xl font-semibold mb-2"><?php echo $productName; ?></h1>
                <p class="text-lg mb-6">$ <?php echo number_format($productPrice, 2); ?></p>
                
                <!-- Stock Info -->
                <div class="flex items-center gap-2 mb-4">
                    <?php if ($inStock): ?>
                    <p class="text-sm text-gray-700">In Stock - <?php echo $stock_quantity; ?> items</p>
                    <?php else: ?>
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <p class="text-sm text-gray-700">Out of Stock</p>
                    <?php endif; ?>
                </div>
        
                <!-- Bottle Engraving -->
                <div class="mb-6 p-4 border rounded-lg">
                    <div class="flex items-start gap-4">
                        <img src="../Images/bottleicon.png" alt="Bottle" class="w-12 h-12 object-contain"/>
                        <div>
                            <h3 class="font-medium text-sm">Bottle Engraving</h3>
                            <p class="text-sm text-gray-600">Complimentary</p>
                            <p class="text-sm text-gray-600">With your initials or numbers</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity Input -->
                <div class="mb-4">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <div class="flex items-center">
                        <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $stock_quantity; ?>" value="1" 
                               class="shadow-sm py-2 px-3 border border-gray-300 rounded-md w-24 text-center"
                               <?php echo !$inStock ? 'disabled' : ''; ?>>
                    </div>
                </div>
        
                <!-- Buttons -->
                <button onclick="addToCartWithQuantity('<?php echo $productId; ?>', '<?php echo $productName; ?>', <?php echo $productPrice; ?>, './Images/bottleicon.png')" 
                    class="bg-black text-white py-3 rounded-full text-center w-full text-sm font-medium hover:bg-gray-800 transition mb-2 <?php echo !$inStock ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                    <?php echo !$inStock ? 'disabled' : ''; ?>>
                    <?php echo $inStock ? 'Place in Cart' : 'Out of Stock'; ?>
                </button>
                <a href="#" onclick="toggleContactPanel(); return false;" class="text-xs underline text-center block mb-8">Order by phone</a>
        
                <!-- Description -->
                <div class="text-sm text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        Icon - Personalizable & Refillable
                    </p>
                    <p class="mb-4">
                        Complimentary Carbon Efficient Delivery or Collect-in-Store.
                    </p>
                    <p class="mb-4">
                        Available exclusively on TRÈSCHIC.com and in selected TRÈS CHIC stores.
                    </p>
                </div>
        
                <!-- Expandable Sections -->
                <div class="mt-10 text-sm border-t pt-4">
                    <details class="mb-2">
                        <summary class="cursor-pointer font-medium">Product care</summary>
                        <p class="mt-2 text-gray-600">Store in a cool, dry place away from direct sunlight. Keep the bottle tightly sealed when not in use.</p>
                    </details>
                    <details>
                        <summary class="cursor-pointer font-medium">In-Store Service</summary>
                        <p class="mt-2 text-gray-600">Visit our boutiques for a personalized fragrance consultation.</p>
                    </details>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 mt-10 py-8 px-4 text-sm">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <div>
                <h4 class="font-bold mb-2">Aide</h4>
                <ul>
                    <li>FAQs</li>
                    <li>Soins des produits</li>
                    <li>Magasins</li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-2">Services</h4>
                <ul>
                    <li>Réparations</li>
                    <li>Personnalisation</li>
                    <li>Art du Cadeau</li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-2">À propos</h4>
                <ul>
                    <li>Défilés</li>
                    <li>Culture</li>
                    <li>La Maison</li>
                </ul>
            </div>
        </div>
        <div class="text-center text-xs text-gray-500 mt-6">© 2025 TRÈS CHIC. Tous droits réservés.</div>
    </footer>

    <!-- Contact Panel -->
    <div id="contactPanel" class="fixed top-0 right-0 h-full w-[600px] bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <div class="p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl">Contact Us</h2>
                <button onclick="toggleContactPanel()" class="text-2xl hover:text-gray-600">×</button>
            </div>

            <p class="text-gray-600 mb-8">
                Wherever you are, TRÈS CHIC Client Advisors will be delighted to assist you.
            </p>

            <div class="space-y-4 mb-12">
                <a href="tel:+201013972690" class="flex items-center gap-3 text-lg">
                    <span class="text-xl">📱</span>
                    +20 10 1397 2690 3omda
                </a>
                <a href="tel:+201115573567" class="flex items-center gap-3 text-lg">
                    <span class="text-xl">📱</span>
                    +20 11 1557 3567 Marwan Sherif
                </a>
                <a href="tel:+201014967095" class="flex items-center gap-3 text-lg">
                    <span class="text-xl">📱</span>
                    +20 10 1496 7095 Abdelrahman Shweal
                </a>
                <a href="tel:+201030680370" class="flex items-center gap-3 text-lg">
                    <span class="text-xl">📱</span>
                    +20 10 3068 0370 Ahmed Sherif
                </a>
            </div>

            <div class="space-y-6">
                <h3 class="font-medium">Need Help?</h3>
                <ul class="space-y-4">
                    <li>
                        <a href="#" class="text-gray-600 hover:text-black transition">FAQ</a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-600 hover:text-black transition">Care Services</a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-600 hover:text-black transition">Find a Store</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" onclick="toggleContactPanel()" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

    <!-- Cart Panel -->
    <div id="cartPanel" class="fixed top-0 right-0 h-full w-[400px] bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50 hidden">
        <div class="p-8 h-full overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-medium">Your Cart</h2>
                <button onclick="toggleCartPanel()" class="text-2xl hover:text-gray-600">×</button>
            </div>
            <div id="cartItems" class="mb-4"></div>
            <div id="cartTotal" class="cart-total font-medium"></div>
            <button onclick="window.location.href='../checkout_confirmation.php'" class="w-full bg-black text-white py-3 rounded-full text-center text-sm font-medium hover:bg-gray-800 transition mt-4">
                Checkout
            </button>
        </div>
    </div>
    <div id="cartOverlay" onclick="toggleCartPanel()" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

    <script>
        // Set login status variables
        const isLoggedIn = <?php echo isset($_SESSION['logged_in']) ? 'true' : 'false'; ?>;
        const username = '<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>';
    </script>
</body>
</html> 




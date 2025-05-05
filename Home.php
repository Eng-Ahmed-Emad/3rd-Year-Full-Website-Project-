<?php
require_once 'config.php';

try {
    // Fetch main products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'main' OR category IS NULL ORDER BY product_id LIMIT 4");
    $stmt->execute();
    $mainProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch accessories
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'accessories' ORDER BY product_id LIMIT 4");
    $stmt->execute();
    $accessories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch fragrances
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'fragrance' ORDER BY product_id LIMIT 4");
    $stmt->execute();
    $fragrances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Initialize empty arrays if there's a database error
    $mainProducts = [];
    $accessories = [];
    $fragrances = [];
    
    // Log the error
    error_log('Database error in Home.php: ' . $e->getMessage());
}

// Helper function to get product file name
function getProductFileName($product) {
    // For all products, use the dynamic product.php with the product ID
    return 'product.php?id=' . $product['product_id'];
}

// Helper function to get product image URL
function getProductImageUrl($product) {
    // If product has a valid image_url, use it
    if (isset($product['image_url']) && !empty($product['image_url'])) {
        return $product['image_url'];
    }
    
    // Match product names to default image filenames
    $nameToImage = [
        'Custom Suit' => './Images/Black suit outer.png',
        'Premium Shirt' => './Images/White shirt outer.png',
        'Tailored Vest' => './Images/vest outter.png',
        'Italian Shoes' => './Images/shoes.png',
        'Silk Tie' => './Images/tie.png',
        'Leather Belt' => './Images/belt.png',
        'Designer Glasses' => './Images/glasses.png',
        'Signature Perfume' => './Images/perfume.png'
    ];
    
    if (isset($product['name']) && isset($nameToImage[$product['name']])) {
        return $nameToImage[$product['name']];
    }
    
    // Default fallback
    return './Images/product-placeholder.png';
}

// Helper function to format price
function formatPrice($price) {
    return '€' . number_format((float)$price, 2, '.', ',');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TRÈS CHIC | Classic Wear</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link href="./styles.css" rel="stylesheet" />
  <script src="./darkMode.js"></script>
  <script src="./javascript.js"></script>
</head>
<body class="bg-white text-black transition-colors duration-200">
  <!-- Sliding Menu -->
  <div id="slidingMenu" class="sliding-menu">
    <div class="menu-content">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-bold">Menu</h2>
        <button onclick="toggleMenu()" class="text-2xl hover:text-gray-600">×</button>
      </div>
      <div class="menu-item" onclick="toggleMenu(); toggleCartPanel();">
        <span class="text-gray-600">Cart</span>
      </div>
      <div class="menu-item" onclick="toggleMenu(); toggleContactPanel();">
        <span class="text-gray-600">Contact Us</span>
      </div>
      <div class="menu-item" onclick="window.location.href='faq.php'">
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
    <div class="text-3xl font-bold tracking-wider">TRÈS CHIC</div>
    <div class="flex items-center gap-6 text-base">
      <div class="cursor-pointer" onclick="toggleContactPanel()">Contactez-nous</div>
      <div><a href="login.php" onclick="handleLoginIconClick(event)">🔑</a></div>
      <div onclick="toggleCartPanel()" class="cursor-pointer">🛒</div>
      <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
        <span id="darkModeIcon" class="text-lg">🌙</span>
      </button>
    </div>
  </header>

  <!-- Hero Video -->
  <section class="w-full relative">
    <div class="w-full h-[500px] overflow-hidden">
      <video id="heroVideo" class="w-full h-full object-cover" autoplay loop playsinline>
        <source src="./Images/Men's Formal Wear.mp4" type="video/mp4" />
        Votre navigateur ne supporte pas les vidéos HTML5.
      </video>
      <!-- Video Controls -->
      <div class="absolute bottom-4 right-4 flex gap-3">
        <button onclick="togglePause()" class="bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded-full w-10 h-10 flex items-center justify-center transition-all">
          <span id="playPauseIcon">⏸️</span>
        </button>
        <button onclick="toggleMute()" class="bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded-full w-10 h-10 flex items-center justify-center transition-all">
          <span id="muteIcon">🔊</span>
        </button>
      </div>
    </div>
  </section>

  <!-- Product Grid Section -->
  <section class="px-6 py-16 bg-white">
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6 mb-20">
      <!-- Product Cards -->
      <?php if (count($mainProducts) > 0): ?>
        <?php foreach ($mainProducts as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <h2 class="text-2xl font-bold mb-8 text-center">Accessories</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Accessories Cards -->
      <?php if (count($accessories) > 0): ?>
        <?php foreach ($accessories as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
  
  <!-- Fragrance Section -->
  <section class="px-6 py-16 bg-gray-50">
    <h2 class="text-2xl font-bold mb-8 text-center">Fragrances</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Fragrance Cards -->
      <?php if (count($fragrances) > 0): ?>
        <?php foreach ($fragrances as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
  
  <!-- Ties Section -->
  <section class="px-6 py-16 bg-white">
    <h2 class="text-2xl font-bold mb-8 text-center">Ties Collection</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Ties Cards -->
      <?php
      try {
          // Fetch ties products
          $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'tie' ORDER BY product_id LIMIT 4");
          $stmt->execute();
          $ties = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          // If database error, use empty array
          $ties = [];
          
          // Log the error
          error_log('Database error in Home.php (ties section): ' . $e->getMessage());
      }
      ?>
      
      <?php if (count($ties) > 0): ?>
        <?php foreach ($ties as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Suits Section -->
  <section class="px-6 py-16 bg-gray-50">
    <h2 class="text-2xl font-bold mb-8 text-center">Suits Collection</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Suits Cards -->
      <?php
      try {
          // Fetch suits products
          $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'suit' ORDER BY product_id LIMIT 4");
          $stmt->execute();
          $suits = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          // If database error, use empty array
          $suits = [];
          
          // Log the error
          error_log('Database error in Home.php (suits section): ' . $e->getMessage());
      }
      ?>
      
      <?php if (count($suits) > 0): ?>
        <?php foreach ($suits as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
  
  <!-- Shirts Section -->
  <section class="px-6 py-16 bg-white">
    <h2 class="text-2xl font-bold mb-8 text-center">Shirts Collection</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Shirts Cards -->
      <?php
      try {
          // Fetch shirts products
          $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'shirt' ORDER BY product_id LIMIT 4");
          $stmt->execute();
          $shirts = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          // If database error, use empty array
          $shirts = [];
          
          // Log the error
          error_log('Database error in Home.php (shirts section): ' . $e->getMessage());
      }
      ?>
      
      <?php if (count($shirts) > 0): ?>
        <?php foreach ($shirts as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
  
  <!-- Vests Section -->
  <section class="px-6 py-16 bg-gray-50">
    <h2 class="text-2xl font-bold mb-8 text-center">Vests Collection</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Vests Cards -->
      <?php
      try {
          // Fetch vests products
          $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'vest' ORDER BY product_id LIMIT 4");
          $stmt->execute();
          $vests = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          // If database error, use empty array
          $vests = [];
          
          // Log the error
          error_log('Database error in Home.php (vests section): ' . $e->getMessage());
      }
      ?>
      
      <?php if (count($vests) > 0): ?>
        <?php foreach ($vests as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
  
  <!-- Shoes Section -->
  <section class="px-6 py-16 bg-white">
    <h2 class="text-2xl font-bold mb-8 text-center">Shoes Collection</h2>
    <div class="products-grid grid grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Shoes Cards -->
      <?php
      try {
          // Fetch shoes products
          $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'shoes' ORDER BY product_id LIMIT 4");
          $stmt->execute();
          $shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          // If database error, use empty array
          $shoes = [];
          
          // Log the error
          error_log('Database error in Home.php (shoes section): ' . $e->getMessage());
      }
      ?>
      
      <?php if (count($shoes) > 0): ?>
        <?php foreach ($shoes as $product): ?>
          <a href="products/product.php?id=<?php echo $product['product_id']; ?>" class="block text-center group cursor-pointer relative overflow-hidden">
            <div class="aspect-w-1 aspect-h-1 mb-3 overflow-hidden">
              <img src="<?php echo getProductImageUrl($product); ?>" alt="<?php echo $product['name']; ?>" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300">
            </div>
            <h3 class="text-lg font-medium"><?php echo $product['name']; ?></h3>
            <p class="text-gray-600"><?php echo formatPrice($product['price']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

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
  <div id="contactPanel" class="fixed top-0 right-0 h-full w-[600px] bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50 hidden">
    <div class="p-8 h-full overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-medium">Contact Us</h2>
        <button onclick="toggleContactPanel()" class="text-2xl hover:text-gray-600">×</button>
      </div>

      <p class="text-gray-600 mb-8">
        Wherever you are, TRÈS CHIC Client Advisors will be delighted to assist you.
      </p>

      <div class="space-y-6 mb-8">
        <a href="tel:+201013972690" class="flex items-center gap-3 text-lg hover:text-gray-600 transition-colors">
          <span class="text-xl">📱</span>
          +20 10 1397 2690 3omda
        </a>
        <a href="tel:+201115573567" class="flex items-center gap-3 text-lg hover:text-gray-600 transition-colors">
          <span class="text-xl">📱</span>
          +20 11 1557 3567 Marwan Sherif
        </a>
        <a href="tel:+201014967095" class="flex items-center gap-3 text-lg hover:text-gray-600 transition-colors">
          <span class="text-xl">📱</span>
          +20 10 1496 7095 Abdelrahman Shweal
        </a>
        <a href="tel:+201030680370" class="flex items-center gap-3 text-lg hover:text-gray-600 transition-colors">
          <span class="text-xl">📱</span>
          +20 10 3068 0370 Ahmed Sherif
        </a>
      </div>

      <div class="space-y-4">
        <h3 class="font-medium">Need Help?</h3>
        <ul class="space-y-3">
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
      <button onclick="window.location.href='checkout_confirmation.php'" class="w-full bg-black text-white py-3 rounded-full text-center text-sm font-medium hover:bg-gray-800 transition mt-4">
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
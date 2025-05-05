<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRÈS CHIC | FAQs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="./darkMode.css" rel="stylesheet">
    <script src="./darkMode.js"></script>
    <link href="./styles.css" rel="stylesheet">
    <script src="./javascript.js"></script>
</head>
<body class="bg-white text-black transition-colors duration-200">
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

    <script>
        // Set login status variables
        const isLoggedIn = <?php echo isset($_SESSION['logged_in']) ? 'true' : 'false'; ?>;
        const username = '<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>';
    </script>

    <!-- FAQ Section -->
    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <h1 class="text-4xl font-bold text-center mb-12">Frequently Asked Questions</h1>
        
        <div class="space-y-8">
            <!-- FAQ Item 1 -->
            <div class="faq-item">
                <div class="faq-question flex justify-between items-center" onclick="toggleAnswer(this)">
                    <h3 class="text-xl font-semibold">What is your return policy?</h3>
                    <span class="text-2xl transition-transform duration-300">▼</span>
                </div>
                <div class="faq-answer">
                    <p class="text-gray-600 dark:text-gray-400">
                        We offer a 30-day return policy for all items. Items must be unworn, unwashed, and in their original packaging with all tags attached. Please contact our customer service team to initiate a return.
                    </p>
                </div>
            </div>

            <!-- FAQ Item 2 -->
            <div class="faq-item">
                <div class="faq-question flex justify-between items-center" onclick="toggleAnswer(this)">
                    <h3 class="text-xl font-semibold">How do I track my order?</h3>
                    <span class="text-2xl transition-transform duration-300">▼</span>
                </div>
                <div class="faq-answer">
                    <p class="text-gray-600 dark:text-gray-400">
                        Once your order has been shipped, you will receive a tracking number via email. You can use this number to track your package on our website or through the shipping carrier's website.
                    </p>
                </div>
            </div>

            <!-- FAQ Item 3 -->
            <div class="faq-item">
                <div class="faq-question flex justify-between items-center" onclick="toggleAnswer(this)">
                    <h3 class="text-xl font-semibold">What payment methods do you accept?</h3>
                    <span class="text-2xl transition-transform duration-300">▼</span>
                </div>
                <div class="faq-answer">
                    <p class="text-gray-600 dark:text-gray-400">
                        We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. All transactions are secure and encrypted.
                    </p>
                </div>
            </div>

            <!-- FAQ Item 4 -->
            <div class="faq-item">
                <div class="faq-question flex justify-between items-center" onclick="toggleAnswer(this)">
                    <h3 class="text-xl font-semibold">How do I care for my garments?</h3>
                    <span class="text-2xl transition-transform duration-300">▼</span>
                </div>
                <div class="faq-answer">
                    <p class="text-gray-600 dark:text-gray-400">
                        Each garment comes with specific care instructions. Generally, we recommend dry cleaning for suits and formal wear, while shirts can be machine washed on a gentle cycle. Always check the care label for specific instructions.
                    </p>
                </div>
            </div>

            <!-- FAQ Item 5 -->
            <div class="faq-item">
                <div class="faq-question flex justify-between items-center" onclick="toggleAnswer(this)">
                    <h3 class="text-xl font-semibold">Do you offer international shipping?</h3>
                    <span class="text-2xl transition-transform duration-300">▼</span>
                </div>
                <div class="faq-answer">
                    <p class="text-gray-600 dark:text-gray-400">
                        Yes, we ship worldwide. Shipping costs and delivery times vary depending on the destination. You can calculate shipping costs during checkout.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
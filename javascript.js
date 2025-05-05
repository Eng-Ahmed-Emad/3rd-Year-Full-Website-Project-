// Contact panel functionality
function toggleContactPanel() {
  const panel = document.getElementById('contactPanel');
  const overlay = document.getElementById('overlay');
  
  if (panel.classList.contains('hidden')) {
    panel.classList.remove('hidden');
    overlay.classList.remove('hidden');
    setTimeout(() => {
      panel.classList.remove('translate-x-full');
    }, 10);
  } else {
    panel.classList.add('translate-x-full');
    setTimeout(() => {
      panel.classList.add('hidden');
      overlay.classList.add('hidden');
    }, 300);
  }
}

// Sliding menu functionality
function toggleMenu() {
  const menu = document.getElementById('slidingMenu');
  const overlay = document.getElementById('menuOverlay');
  menu.classList.toggle('open');
  overlay.classList.toggle('open');
  document.body.classList.toggle('overflow-hidden');
}

// Cart functionality
function toggleCartPanel() {
  const panel = document.getElementById('cartPanel');
  const overlay = document.getElementById('cartOverlay');
  
  if (panel.classList.contains('hidden')) {
    panel.classList.remove('hidden');
    overlay.classList.remove('hidden');
    setTimeout(() => {
      panel.classList.remove('translate-x-full');
    }, 10);
    updateCartDisplay();
  } else {
    panel.classList.add('translate-x-full');
    setTimeout(() => {
      panel.classList.add('hidden');
      overlay.classList.add('hidden');
    }, 300);
  }
}

function addToCart(productId, productName, productPrice, productImage) {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  const existingItem = cart.find(item => item.id === productId);
  
  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({
      id: productId,
      name: productName,
      price: productPrice,
      image: productImage,
      quantity: 1
    });
  }
  
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartDisplay();
}

function addToCartWithQuantity(productId, productName, productPrice, productImage) {
  const quantityInput = document.getElementById('quantity');
  const quantity = parseInt(quantityInput ? quantityInput.value : 1, 10);
  
  if (isNaN(quantity) || quantity < 1) {
    showNotification('Please enter a valid quantity', 'error');
    return;
  }
  
  // Check if a max attribute exists on the quantity input (set to stock_quantity)
  const maxStock = quantityInput.getAttribute('max');
  if (maxStock && quantity > parseInt(maxStock, 10)) {
    showNotification(`Sorry, only ${maxStock} items are available in stock`, 'error');
    return;
  }
  
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  const existingItem = cart.find(item => item.id === productId);
  
  if (existingItem) {
    // Check if the combined quantity would exceed available stock
    if (maxStock && (existingItem.quantity + quantity) > parseInt(maxStock, 10)) {
      showNotification(`Sorry, you already have ${existingItem.quantity} in your cart. Only ${maxStock} items are available in total.`, 'error');
      return;
    }
    existingItem.quantity += quantity;
  } else {
    cart.push({
      id: productId,
      name: productName,
      price: productPrice,
      image: productImage,
      quantity: quantity
    });
  }
  
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartDisplay();
  showNotification(`Added ${quantity} item(s) to cart`, 'success');
}

function removeFromCart(productId) {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  cart = cart.filter(item => item.id !== productId);
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartDisplay();
}

function updateCartDisplay() {
  const cartItems = document.getElementById('cartItems');
  const cartTotal = document.getElementById('cartTotal');
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  
  cartItems.innerHTML = '';
  let total = 0;
  
  cart.forEach(item => {
    const itemTotal = item.price * item.quantity;
    total += itemTotal;
    
    const cartItem = document.createElement('div');
    cartItem.className = 'cart-item';
    cartItem.innerHTML = `
      <img src="${item.image}" alt="${item.name}">
      <div class="cart-item-details">
        <h3 class="font-medium">${item.name}</h3>
        <p class="text-gray-600">$${item.price.toFixed(2)} x ${item.quantity}</p>
        <p class="font-medium">$${itemTotal.toFixed(2)}</p>
      </div>
      <div class="cart-item-actions">
        <button onclick="removeFromCart('${item.id}')">Remove</button>
      </div>
    `;
    cartItems.appendChild(cartItem);
  });
  
  cartTotal.textContent = `Total: $${total.toFixed(2)}`;
}

// Video controls functionality
document.addEventListener('DOMContentLoaded', function() {
  const video = document.getElementById('heroVideo');
  if (video) {
    const playPauseIcon = document.getElementById('playPauseIcon');
    const muteIcon = document.getElementById('muteIcon');
    
    // Start video muted by default
    video.muted = true;
    
    window.togglePause = function() {
      if (video.paused) {
        video.play();
        playPauseIcon.textContent = '⏸️';
      } else {
        video.pause();
        playPauseIcon.textContent = '▶️';
      }
    }
    
    window.toggleMute = function() {
      video.muted = !video.muted;
      muteIcon.textContent = video.muted ? '🔇' : '🔊';
    }
  }
});

// Checkout functionality 
function checkout() {
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  if (cart.length === 0) {
    showNotification('Your cart is empty', 'error');
    return;
  }

  // The URL used for checkout may vary depending on which page is calling this function
  // We will first try to determine if we're in a product page by looking at the URL path
  const isProductPage = window.location.pathname.includes('/products/');
  const checkoutUrl = isProductPage ? '../process.php?action=checkout' : 'process.php?action=checkout';

  fetch(checkoutUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(cart)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Order placed successfully! Order ID: ' + data.order_id, 'success');
      localStorage.removeItem('cart');
      updateCartDisplay();
      toggleCartPanel();
    } else {
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('An error occurred while processing your order', 'error');
  });
}

// Notification system
function showNotification(message, type = 'error') {
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-300 ease-in-out`;
  notification.innerHTML = `
      <div class="bg-white shadow-lg rounded-lg overflow-hidden w-80">
          <div class="flex items-center p-4 ${type === 'error' ? 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200' : 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200'}">
              <div class="flex-shrink-0">
                  ${type === 'error' ? 
                      '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' :
                      '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>'
                  }
              </div>
              <div class="ml-3">
                  <p class="text-sm font-medium">${message}</p>
              </div>
              <div class="ml-auto pl-3">
                  <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-500">
                      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                  </button>
              </div>
          </div>
      </div>
  `;
  document.body.appendChild(notification);
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => notification.remove(), 300);
  }, 5000);
}

// FAQ page functionality
function toggleAnswer(element) {
  const answer = element.nextElementSibling;
  const arrow = element.querySelector('span');
  
  answer.classList.toggle('active');
  arrow.classList.toggle('rotate-180');
}

// Login icon functionality
function handleLoginIconClick(event) {
  // Check if user is logged in (you'll need to set this variable in your PHP)
  if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
    event.preventDefault(); // Prevent default link behavior
    showNotification(`You are already logged in as ${username}`, 'success');
  }
  // If not logged in, the default link behavior will proceed to login.php
} 
// Check for saved dark mode preference
function initDarkMode() {
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.documentElement.classList.add('dark');
        updateDarkModeIcon(true);
    }
}

// Toggle dark mode
function toggleDarkMode() {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'disabled');
        updateDarkModeIcon(false);
    } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'enabled');
        updateDarkModeIcon(true);
    }
}

// Update the dark mode icon
function updateDarkModeIcon(isDark) {
    const darkModeIcon = document.getElementById('darkModeIcon');
    if (darkModeIcon) {
        darkModeIcon.textContent = isDark ? '☀️' : '🌙';
    }
}

// Initialize dark mode on page load
document.addEventListener('DOMContentLoaded', initDarkMode); 
// B2B Retailer Platform - JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeCarousel();
    initializeFormValidation();
    initializeQuantitySelectors();
});

// ===== Navigation Functions =====
function initializeNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.navbar-menu');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Toggle hamburger animation
            const spans = hamburger.querySelectorAll('span');
            spans[0].style.transform = navMenu.classList.contains('active') ? 'rotate(45deg) translateY(10px)' : 'none';
            spans[1].style.opacity = navMenu.classList.contains('active') ? '0' : '1';
            spans[2].style.transform = navMenu.classList.contains('active') ? 'rotate(-45deg) translateY(-10px)' : 'none';
        });
        
        // Close menu when link is clicked
        if (navMenu) {
            const navLinks = navMenu.querySelectorAll('a');
            const spans = hamburger.querySelectorAll('span');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    navMenu.classList.remove('active');
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                });
            });
        }
    }
}

// ===== Carousel Functions =====
function initializeCarousel() {
    const carousel = document.querySelector('.carousel');
    if (!carousel) return;
    
    let currentIndex = 0;
    const items = document.querySelectorAll('.carousel-item');
    const dots = document.querySelectorAll('.carousel-dot');
    
    function showSlide(index) {
        const itemsContainer = carousel.querySelector('.carousel-items');
        itemsContainer.style.transform = `translateX(-${index * 100}%)`;
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
        
        currentIndex = index;
    }
    
    // Auto-play carousel
    let autoPlayInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % items.length;
        showSlide(currentIndex);
    }, 5000);
    
    // Next button
    const nextBtn = carousel.querySelector('.carousel-nav.next');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            clearInterval(autoPlayInterval);
            currentIndex = (currentIndex + 1) % items.length;
            showSlide(currentIndex);
            autoPlayInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % items.length;
                showSlide(currentIndex);
            }, 5000);
        });
    }
    
    // Previous button
    const prevBtn = carousel.querySelector('.carousel-nav.prev');
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            clearInterval(autoPlayInterval);
            currentIndex = (currentIndex - 1 + items.length) % items.length;
            showSlide(currentIndex);
            autoPlayInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % items.length;
                showSlide(currentIndex);
            }, 5000);
        });
    }
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            clearInterval(autoPlayInterval);
            showSlide(index);
            autoPlayInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % items.length;
                showSlide(currentIndex);
            }, 5000);
        });
    });
}

// ===== Form Validation =====
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
            showError(input);
        } else {
            clearError(input);
        }
    });
    
    return isValid;
}

function validateInput(input) {
    const value = input.value.trim();
    const type = input.type;
    const name = input.name;
    
    // Check if empty
    if (!value) {
        return false;
    }
    
    // Email validation
    if (type === 'email') {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }
    
    // Phone validation
    if (name === 'phone') {
        return /^\d{10}$/.test(value.replace(/[^\d]/g, ''));
    }
    
    // Password validation
    if (name === 'password') {
        return value.length >= 8;
    }
    
    // Confirm password
    if (name === 'confirm_password') {
        const password = document.querySelector('input[name="password"]');
        return password && password.value === value;
    }
    
    return true;
}

function showError(input) {
    input.classList.add('error');
    let errorMsg = input.parentElement.querySelector('.error-message');
    
    if (!errorMsg) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        input.parentElement.appendChild(errorMsg);
    }
    
    if (input.type === 'email') {
        errorMsg.textContent = 'Please enter a valid email address';
    } else if (input.name === 'phone') {
        errorMsg.textContent = 'Please enter a valid 10-digit phone number';
    } else if (input.name === 'password') {
        errorMsg.textContent = 'Password must be at least 8 characters';
    } else if (input.name === 'confirm_password') {
        errorMsg.textContent = 'Passwords do not match';
    } else {
        errorMsg.textContent = 'This field is required';
    }
}

function clearError(input) {
    input.classList.remove('error');
    const errorMsg = input.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.textContent = '';
    }
}

// ===== Quantity Selectors =====
function initializeQuantitySelectors() {
    const selectors = document.querySelectorAll('.quantity-selector');
    
    selectors.forEach(selector => {
        const minusBtn = selector.querySelector('button:first-child');
        const input = selector.querySelector('input');
        const plusBtn = selector.querySelector('button:last-child');
        
        if (minusBtn) {
            minusBtn.addEventListener('click', () => {
                let value = parseInt(input.value) || 1;
                if (value > 1) {
                    input.value = value - 1;
                }
            });
        }
        
        if (plusBtn) {
            plusBtn.addEventListener('click', () => {
                let value = parseInt(input.value) || 1;
                input.value = value + 1;
            });
        }
    });
}

// ===== Cart Functions =====
function addToCart(productId) {
    const quantityInput = document.querySelector(`.quantity-selector input[data-product="${productId}"]`);
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
    
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart', 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function removeFromCart(itemId) {
    const formData = new FormData();
    formData.append('action', 'remove_from_cart');
    formData.append('item_id', itemId);
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    });
}

function updateQuantity(itemId, newQuantity) {
    if (newQuantity < 1) return;
    
    const formData = new FormData();
    formData.append('action', 'update_quantity');
    formData.append('item_id', itemId);
    formData.append('quantity', newQuantity);
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    });
}

// ===== Notification Functions =====
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '80px';
    notification.style.right = '20px';
    notification.style.zIndex = '3000';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease-out reverse';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ===== Modal Functions =====
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal on background click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// ===== File Upload Preview =====
function previewFile(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (input && input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// ===== Export to PDF (using window.print) =====
function printDocument() {
    window.print();
}

// ===== Utility Functions =====
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===== Search and Filter =====
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toUpperCase();
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toUpperCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// ===== Confirm Delete =====
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// ===== Export Table to CSV =====
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    const csv = [];
    
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        
        cols.forEach(col => {
            csvRow.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        
        csv.push(csvRow.join(','));
    });
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

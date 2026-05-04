// Menu Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    const searchInput = document.getElementById('searchInput');
    const menuSubtitle = document.querySelector('.menu-subtitle');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const modalProductImage = document.getElementById('modalProductImage');
    const quantityInput = document.getElementById('quantityInput');
    const minusBtn = document.querySelector('.qty-btn.minus');
    const plusBtn = document.querySelector('.qty-btn.plus');
    const sizeButtons = document.querySelectorAll('.size-btn');
    const modalProductName = document.getElementById('modalProductName');
    const modalProductDesc = document.getElementById('modalProductDesc');
    const totalPriceEl = document.getElementById('totalPrice');
    const specialInstructionsInput = document.getElementById('specialInstructions');
    
    let currentFilter = 'Coffee';
    
    // Filter functionality
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;

            if (menuSubtitle) {
                menuSubtitle.textContent = this.textContent.trim();
            }

            filterProducts();
        });
    });
    
    // Search functionality
    searchInput.addEventListener('keyup', function() {
        filterProducts();
    });
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        
        productCards.forEach(card => {
            const category = card.dataset.category;
            const productName = card.dataset.name;
            
            const matchesFilter = category === currentFilter;
            const matchesSearch = productName.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
    
    // Product modal functionality
    productCards.forEach(card => {
        card.addEventListener('click', function() {
            const stock = parseInt(card.dataset.stock) || 0;
            if (stock === 0) {
                return; // Don't open modal for out of stock items
            }
            openProductModal(card);
        });

        card.addEventListener('keydown', function(event) {
            const stock = parseInt(card.dataset.stock) || 0;
            if (stock === 0) {
                return; // Don't open modal for out of stock items
            }
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openProductModal(card);
            }
        });
        
        // Add out-of-stock styling
        const stock = parseInt(card.dataset.stock) || 0;
        if (stock === 0) {
            card.style.opacity = '0.6';
            card.style.cursor = 'not-allowed';
            card.setAttribute('aria-disabled', 'true');
        }
    });

    function openProductModal(card) {
        const productId = card.dataset.productId;
        const productName = card.dataset.productName;
        const productDesc = card.dataset.productDesc;
        const productImage = card.dataset.productImage || card.querySelector('img')?.getAttribute('src') || 'Coffee/SpanishLatte.png';
        const stock = parseInt(card.dataset.stock) || 0;
        const price16 = parsePriceValue(
            card.getAttribute('data-price-16oz') ||
            card.getAttribute('data-price-16') ||
            card.dataset.price16oz ||
            card.dataset.price16
        );
        const price22 = parsePriceValue(
            card.getAttribute('data-price-22oz') ||
            card.getAttribute('data-price-22') ||
            card.dataset.price22oz ||
            card.dataset.price22
        );

        modalProductName.textContent = productName;
        modalProductDesc.textContent = productDesc;
        if (modalProductImage) {
            modalProductImage.src = productImage;
            modalProductImage.alt = productName;
        }

        // Keep product prices in modal state to guarantee recalculation works.
        modalProductName.dataset.price16 = String(price16);
        modalProductName.dataset.price22 = String(price22);
        modalProductName.dataset.stock = String(stock);

        sizeButtons[0].dataset.price = String(price16);
        sizeButtons[1].dataset.price = String(price22);

        sizeButtons.forEach(sizeBtn => sizeBtn.classList.remove('active'));
        sizeButtons[0].classList.add('active');

        modalProductName.dataset.productId = productId;

        quantityInput.value = 1;
        specialInstructionsInput.value = '';

        // Handle out of stock status
        const stockWarning = document.getElementById('stockWarning');
        const buyNowBtn = document.getElementById('buyNowBtn');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const sizeOptions = document.querySelector('.size-options');
        const quantityControl = document.querySelector('.quantity-control');
        
        if (stock === 0) {
            stockWarning.style.display = 'block';
            buyNowBtn.disabled = true;
            addToCartBtn.disabled = true;
            sizeOptions.style.opacity = '0.5';
            sizeOptions.style.pointerEvents = 'none';
            quantityControl.style.opacity = '0.5';
            quantityControl.style.pointerEvents = 'none';
            buyNowBtn.style.opacity = '0.5';
            addToCartBtn.style.opacity = '0.5';
        } else {
            stockWarning.style.display = 'none';
            buyNowBtn.disabled = false;
            addToCartBtn.disabled = false;
            sizeOptions.style.opacity = '1';
            sizeOptions.style.pointerEvents = 'auto';
            quantityControl.style.opacity = '1';
            quantityControl.style.pointerEvents = 'auto';
            buyNowBtn.style.opacity = '1';
            addToCartBtn.style.opacity = '1';
        }

        updatePriceDisplay();
        productModal.show();
    }

    function parsePriceValue(value) {
        if (value === null || value === undefined) {
            return 0;
        }

        // Accept values like "120", "120.00", "120,00", or "120.00P".
        const cleaned = String(value)
            .replace(/[^\d.,-]/g, '')
            .replace(',', '.');

        const parsed = parseFloat(cleaned);
        return Number.isFinite(parsed) ? parsed : 0;
    }
    
    // Size selection
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            sizeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updatePriceDisplay();
        });
    });
    
    // Quantity control
    minusBtn.addEventListener('click', function() {
        const value = parseInt(quantityInput.value) || 1;
        if (value > 1) {
            quantityInput.value = value - 1;
            updatePriceDisplay();
        }
    });
    
    plusBtn.addEventListener('click', function() {
        const value = parseInt(quantityInput.value) || 1;
        quantityInput.value = value + 1;
        updatePriceDisplay();
    });
    
    quantityInput.addEventListener('change', updatePriceDisplay);
    quantityInput.addEventListener('input', updatePriceDisplay);
    
    function updatePriceDisplay() {
        const activeSize = document.querySelector('.size-btn.active');
        if (!activeSize) {
            totalPriceEl.textContent = '0.00₱';
            return;
        }

        const fallbackPrice = activeSize.dataset.size === '22oz'
            ? modalProductName.dataset.price22
            : modalProductName.dataset.price16;

        const price = parsePriceValue(activeSize.dataset.price || fallbackPrice);
        const quantity = Math.max(1, parseInt(quantityInput.value, 10) || 1);
        quantityInput.value = quantity;
        const total = price * quantity;
        
        totalPriceEl.textContent = total.toFixed(2) + '₱';
    }
    
    // Add to cart
    document.getElementById('addToCartBtn').addEventListener('click', function() {
        addToCart();
    });
    
    // Buy now
    document.getElementById('buyNowBtn').addEventListener('click', function() {
        addToCart(true);
    });
    
    function addToCart(checkout = false) {
        const productId = document.getElementById('modalProductName').dataset.productId;
        const activeSize = document.querySelector('.size-btn.active');
        const size = activeSize.dataset.size;
        const quantity = parseInt(document.getElementById('quantityInput').value) || 1;
        const specialInstructions = document.getElementById('specialInstructions').value;
        
        // Check if user is logged in
        fetch('api/cart.php?action=get_cart')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Not logged in, redirect to signin
                    window.location.href = 'signin.php';
                    return;
                }
                
                // Add to cart
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('size', size);
                formData.append('quantity', quantity);
                formData.append('special_instructions', specialInstructions);
                
                fetch('api/cart.php?action=add_to_cart', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        productModal.hide();
                        if (checkout) {
                            window.KapeNotify.toastSuccess('Proceeding to checkout...', 'Success');
                            window.location.href = 'checkout.php';
                        } else {
                            window.KapeNotify.toastSuccess('Item added to cart.');
                        }
                    } else {
                        window.KapeNotify.popup('Cart Error', data.message || 'Unable to add item to cart.', 'error');
                    }
                })
                .catch(() => {
                    window.KapeNotify.popup('Connection Error', 'Unable to connect to cart service. Please try again.', 'error');
                });
            })
            .catch(() => {
                window.KapeNotify.popup('Session Error', 'Unable to verify session. Please try again.', 'warning');
            });
    }

    filterProducts();
});

// Menu Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    const searchInput = document.getElementById('searchInput');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productBtns = document.querySelectorAll('.product-btn');
    
    let currentFilter = 'all';
    
    // Filter functionality
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter === 'all' ? 'all' : this.dataset.filter;
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
            
            const matchesFilter = currentFilter === 'all' || category === currentFilter;
            const matchesSearch = productName.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
    
    // Product modal functionality
    productBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productDesc = this.dataset.productDesc;
            const price16 = this.dataset.price16;
            const price22 = this.dataset.price22;
            
            // Set modal data
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductDesc').textContent = productDesc;
            
            // Store prices in size buttons
            const sizeButtons = document.querySelectorAll('.size-btn');
            sizeButtons[0].dataset.price = price16;
            sizeButtons[1].dataset.price = price22;
            
            // Reset size selection
            sizeButtons[0].classList.add('active');
            sizeButtons.forEach(btn => btn.classList.remove('active'));
            sizeButtons[0].classList.add('active');
            
            // Store product ID
            document.getElementById('modalProductName').dataset.productId = productId;
            
            // Reset quantity
            document.getElementById('quantityInput').value = 1;
            
            // Update price display
            updatePriceDisplay();
            
            productModal.show();
        });
    });
    
    // Size selection
    const sizeButtons = document.querySelectorAll('.size-btn');
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            sizeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updatePriceDisplay();
        });
    });
    
    // Quantity control
    const quantityInput = document.getElementById('quantityInput');
    const minusBtn = document.querySelector('.qty-btn.minus');
    const plusBtn = document.querySelector('.qty-btn.plus');
    
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
    
    function updatePriceDisplay() {
        const activeSize = document.querySelector('.size-btn.active');
        const price = parseFloat(activeSize.dataset.price) || 0;
        const quantity = parseInt(document.getElementById('quantityInput').value) || 1;
        const total = price * quantity;
        
        document.getElementById('totalPrice').textContent = total.toFixed(2) + '₽';
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
                            window.location.href = 'checkout.php';
                        } else {
                            alert('Added to cart!');
                        }
                    }
                });
            });
    }
});

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3 class="cart-title">YOUR ORDERS</h3>
        <button class="cart-close" id="cartClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="cart-items-container" id="cartItemsContainer">
        <!-- Cart items will be loaded here -->
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
        </div>
    </div>
    
    <div class="cart-summary">
        <div class="summary-row">
            <span>Subtotal</span>
            <span id="subtotal">0.00₱</span>
        </div>
        <div class="summary-row">
            <span>Shipping</span>
            <span id="shipping">0.00₱</span>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <span id="total">0.00₱</span>
        </div>
    </div>
    
    <button class="checkout-btn" id="checkoutBtn" onclick="window.location.href='checkout.php'">Check Out</button>
</div>

<!-- Cart Overlay -->
<div class="cart-overlay" id="cartOverlay"></div>

<script>
    // Load cart items on page load
    function loadCartItems() {
        fetch('api/cart.php?action=get_cart')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.cart) {
                    displayCart(data.cart, data.totals);
                } else {
                    showEmptyCart();
                }
            })
            .catch(error => console.error('Error loading cart:', error));
    }

    function displayCart(cartItems, totals) {
        const container = document.getElementById('cartItemsContainer');
        
        if (cartItems.length === 0) {
            showEmptyCart();
            return;
        }
        
        let html = '';
        cartItems.forEach(item => {
            html += `
                <div class="cart-item">
                    <div class="cart-item-image-wrapper">
                        <img src="${item.image_url || 'assets/images/placeholder.jpg'}" alt="${item.name}" class="cart-item-image">
                    </div>
                    <div class="cart-item-content">
                        <div class="cart-item-header">
                            <div>
                                <h4 class="cart-item-name">${item.name}</h4>
                                <p class="cart-item-category">${item.category}</p>
                                <p class="cart-item-size">size: ${item.size}</p>
                                ${item.special_instructions ? `<p class="cart-item-instructions">${item.special_instructions}</p>` : ''}
                            </div>
                            <button class="cart-item-remove" onclick="removeFromCart(${item.id})" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="cart-item-footer">
                            <div class="quantity-controls">
                                <button class="qty-btn" onclick="updateQuantity(${item.id}, parseInt(document.getElementById('qty-${item.id}').value) - 1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="qty-${item.id}" class="qty-input" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, Math.max(1, parseInt(this.value) || 1))" onkeypress="if(event.key==='Enter') updateQuantity(${item.id}, Math.max(1, parseInt(this.value) || 1))">
                                <button class="qty-btn" onclick="updateQuantity(${item.id}, parseInt(document.getElementById('qty-${item.id}').value) + 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-item-price">${parseFloat(item.total_price).toFixed(2)}₱</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Update totals
        document.getElementById('subtotal').textContent = parseFloat(totals.subtotal).toFixed(2) + '₱';
        document.getElementById('shipping').textContent = parseFloat(totals.shipping).toFixed(2) + '₱';
        document.getElementById('total').textContent = parseFloat(totals.total).toFixed(2) + '₱';
        // Notify other UI parts (e.g., navbar) about cart count
        try {
            document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count: cartItems.length } }));
        } catch (e) {
            // ignore
        }
    }

    function showEmptyCart() {
        const container = document.getElementById('cartItemsContainer');
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
            </div>
        `;
        document.getElementById('subtotal').textContent = '0.00₱';
        document.getElementById('shipping').textContent = '0.00₱';
        document.getElementById('total').textContent = '0.00₱';
        // Notify other UI parts (e.g., navbar) that cart is empty
        try {
            document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count: 0 } }));
        } catch (e) {
            // ignore
        }
    }

    function removeFromCart(cartId) {
        const onConfirm = () => {
            fetch('api/cart.php?action=remove_from_cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cart_id=' + cartId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    if (window.KapeNotify) {
                        window.KapeNotify.toastInfo('Item removed from cart.', 'Updated');
                    }
                }
            });
        };

        window.KapeNotify.confirm({
            title: 'Remove Item',
            text: 'Remove this item from cart?',
            icon: 'warning',
            confirmText: 'Remove',
            cancelText: 'Keep'
        }).then(function(confirmed) {
            if (confirmed) {
                onConfirm();
            }
        });
    }

    function updateQuantity(cartId, newQuantity) {
        // Don't allow quantity less than 1
        if (newQuantity < 1) {
            removeFromCart(cartId);
            return;
        }

        fetch('api/cart.php?action=update_cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cart_id=' + cartId + '&quantity=' + newQuantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCartItems();
                if (window.KapeNotify) {
                    window.KapeNotify.toastInfo('Quantity updated.', 'Updated');
                }
            }
        })
        .catch(error => console.error('Error updating quantity:', error));
    }

    // Open/close cart sidebar
    function openCart() {
        document.getElementById('cartSidebar').classList.add('open');
        document.getElementById('cartOverlay').classList.add('open');
        loadCartItems();
    }

    function closeCart() {
        document.getElementById('cartSidebar').classList.remove('open');
        document.getElementById('cartOverlay').classList.remove('open');
    }

    // Event listeners
    document.getElementById('cartClose').addEventListener('click', closeCart);
    document.getElementById('cartOverlay').addEventListener('click', closeCart);

    // Open cart when cart icon is clicked
    document.addEventListener('DOMContentLoaded', function() {
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.addEventListener('click', function(e) {
                e.preventDefault();
                openCart();
            });
        }
    });
    
    // Dispatch an initial cartUpdated event after load to inform navbar of current count
    document.addEventListener('DOMContentLoaded', function() {
        fetch('api/cart.php?action=get_cart')
            .then(response => response.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.cart)) {
                    try { document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count: data.cart.length } })); } catch (e) {}
                } else {
                    try { document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count: 0 } })); } catch (e) {}
                }
            })
            .catch(() => { try { document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count: 0 } })); } catch (e) {} });
    });
</script>

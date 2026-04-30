// Checkout Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    
    document.getElementById('checkoutBtn').addEventListener('click', submitOrder);
    document.getElementById('cartItems').addEventListener('click', handleCartAction);
});

function loadCart() {
    fetch('api/cart.php?action=get_cart')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCart(data);
            } else {
                displayEmptyCart();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayEmptyCart();
        });
}

function displayCart(data) {
    const cartItemsContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const totalEl = document.getElementById('total');
    const cartItems = Array.isArray(data.cart) ? data.cart : [];
    const totals = data.totals || { subtotal: 0, shipping: 0, total: 0 };
    
    // Clear previous items
    cartItemsContainer.innerHTML = '';
    
    // Display items
    if (cartItems.length === 0) {
        displayEmptyCart();
        return;
    }

    cartItems.forEach(item => {
        const itemTotal = parseFloat(item.total_price || 0);
        const itemPrice = parseFloat(item.price || 0);
        const quantity = Math.max(1, parseInt(item.quantity, 10) || 1);
        const instructions = item.special_instructions
            ? item.special_instructions
            : 'none';
        const itemImage = item.image_url || 'Coffee/SpanishLatte.png';
        const itemHTML = `
            <div class="cart-item" data-cart-id="${item.id}">
                <div class="cart-item-image">
                    <img src="${itemImage}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <p class="cart-item-name">${item.name}</p>
                    <p class="cart-item-info">${item.category || 'Coffee'}</p>
                    <p class="cart-item-info">size: ${item.size}</p>
                    ${instructions !== 'none' ? `<p class="cart-item-instructions">${instructions}</p>` : ''}
                    <div class="cart-item-footer">
                        <div class="cart-qty-view">
                            <button type="button" class="cart-qty-btn" data-action="decrease" title="Decrease quantity">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="cart-qty-input" value="${quantity}" min="1" data-action="input" title="Enter quantity">
                            <button type="button" class="cart-qty-btn" data-action="increase" title="Increase quantity">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <p class="cart-item-price">${itemTotal.toFixed(2)} ₱</p>
                        <button type="button" class="cart-delete-btn" data-action="delete" title="Remove item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        cartItemsContainer.innerHTML += itemHTML;
    });
    
    // Update totals
    subtotalEl.textContent = parseFloat(totals.subtotal || 0).toFixed(2) + '₱';
    shippingEl.textContent = parseFloat(totals.shipping || 0).toFixed(2) + '₱';
    totalEl.textContent = parseFloat(totals.total || 0).toFixed(2) + '₱';

    // Add event listeners for quantity inputs
    const quantityInputs = cartItemsContainer.querySelectorAll('.cart-qty-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const cartItem = e.target.closest('.cart-item');
            if (cartItem) {
                const cartId = parseInt(cartItem.dataset.cartId, 10);
                const newQty = Math.max(1, parseInt(this.value, 10) || 1);
                if (cartId && newQty !== parseInt(this.dataset.originalValue || 1, 10)) {
                    updateCartItemQuantity(cartId, newQty);
                }
            }
        });

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const cartItem = e.target.closest('.cart-item');
                if (cartItem) {
                    const cartId = parseInt(cartItem.dataset.cartId, 10);
                    const newQty = Math.max(1, parseInt(this.value, 10) || 1);
                    if (cartId) {
                        updateCartItemQuantity(cartId, newQty);
                    }
                }
            }
        });
    });
}

function displayEmptyCart() {
    const cartItemsContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const totalEl = document.getElementById('total');

    cartItemsContainer.innerHTML = '<p class="cart-item-info">Your cart is empty.</p>';
    subtotalEl.textContent = '0.00 ₱';
    shippingEl.textContent = '0.00 ₱';
    totalEl.textContent = '0.00 ₱';
}

function handleCartAction(event) {
    const actionTarget = event.target.closest('[data-action]');
    if (!actionTarget) {
        return;
    }

    // Don't handle input field value changes here - let the input handlers deal with it
    if (actionTarget.classList.contains('cart-qty-input')) {
        return;
    }

    const cartItemEl = actionTarget.closest('.cart-item');
    if (!cartItemEl) {
        return;
    }

    const cartId = parseInt(cartItemEl.dataset.cartId, 10);
    if (!cartId) {
        return;
    }

    const action = actionTarget.dataset.action;
    if (action === 'delete') {
        removeCartItem(cartId);
        return;
    }

    const quantityInput = cartItemEl.querySelector('.cart-qty-input');
    const currentQty = Math.max(1, parseInt(quantityInput ? quantityInput.value : '1', 10) || 1);
    
    let nextQty = currentQty;
    if (action === 'decrease') {
        nextQty = currentQty - 1;
    } else if (action === 'increase') {
        nextQty = currentQty + 1;
    }

    if (nextQty < 1) {
        removeCartItem(cartId);
        return;
    }

    updateCartItemQuantity(cartId, nextQty);
}

function updateCartItemQuantity(cartId, quantity) {
    fetch('api/cart.php?action=update_cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'cart_id=' + encodeURIComponent(cartId) + '&quantity=' + encodeURIComponent(quantity)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
        } else {
            window.KapeNotify.popup('Update Error', data.message || 'Unable to update quantity.', 'error');
        }
    })
    .catch(() => {
        window.KapeNotify.popup('Connection Error', 'Unable to update quantity right now.', 'error');
    });
}

function removeCartItem(cartId) {
    window.KapeNotify.confirm({
        title: 'Remove Item',
        text: 'Remove this item from order summary?',
        icon: 'warning',
        confirmText: 'Remove',
        cancelText: 'Cancel'
    }).then(function(confirmed) {
        if (!confirmed) {
            return;
        }

        fetch('api/cart.php?action=remove_from_cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'cart_id=' + encodeURIComponent(cartId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
                window.KapeNotify.toastInfo('Item removed from cart.', 'Updated');
            } else {
                window.KapeNotify.popup('Remove Error', data.message || 'Unable to remove item.', 'error');
            }
        })
        .catch(() => {
            window.KapeNotify.popup('Connection Error', 'Unable to remove item right now.', 'error');
        });
    });
}

function submitOrder() {
    // Require phone verification before placing the order
    if (!window.checkoutPhoneVerified) {
        window.KapeNotify.popup('Verification Required', 'Please verify your phone number first.', 'warning');
        return;
    }
    
    const form = document.getElementById('checkoutForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('api/cart.php?action=create_order', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.payment_redirect_url) {
                window.KapeNotify.popup('Redirecting to GCash', 'Please complete your payment in PayMongo.', 'info')
                    .then(function () {
                        window.location.href = data.payment_redirect_url;
                    });
                return;
            }

            window.KapeNotify.popup('Order Placed', 'Order placed successfully! Order ID: ' + data.order_id, 'success')
                .then(function () {
                    window.location.href = 'index.php';
                });
        } else {
            window.KapeNotify.popup('Checkout Error', data.message || 'Unable to place order.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.KapeNotify.popup('Checkout Error', 'An error occurred while placing the order.', 'error');
    });
}

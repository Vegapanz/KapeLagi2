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
        const itemHTML = `
            <div class="cart-item" data-cart-id="${item.id}">
                <div class="cart-item-image">
                    <img src="Coffee/SpanishLatte.png" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <div class="cart-item-meta">
                        <p class="cart-item-name">${item.name}</p>
                        <div class="cart-item-actions">
                            <div class="cart-qty-view" aria-label="Quantity ${quantity}">
                                <button type="button" class="cart-qty-btn" data-action="decrease" title="Decrease quantity">−</button>
                                <span class="cart-qty-count">${quantity}</span>
                                <button type="button" class="cart-qty-btn" data-action="increase" title="Increase quantity">+</button>
                            </div>
                            <button type="button" class="cart-delete-btn" data-action="delete" title="Remove item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <p class="cart-item-info">${item.category || 'Coffee'}</p>
                    <p class="cart-item-info">size: ${item.size}</p>
                    <p class="cart-item-info">Special Instructions: ${instructions}</p>
                    <p class="cart-item-info">Qty: ${quantity} x ${itemPrice.toFixed(2)}P = ${itemTotal.toFixed(2)}P</p>
                </div>
            </div>
        `;
        cartItemsContainer.innerHTML += itemHTML;
    });
    
    // Update totals
    subtotalEl.textContent = parseFloat(totals.subtotal || 0).toFixed(2) + 'P';
    shippingEl.textContent = parseFloat(totals.shipping || 0).toFixed(2) + 'P';
    totalEl.textContent = parseFloat(totals.total || 0).toFixed(2) + 'P';
}

function displayEmptyCart() {
    const cartItemsContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const totalEl = document.getElementById('total');

    cartItemsContainer.innerHTML = '<p class="cart-item-info">Your cart is empty.</p>';
    subtotalEl.textContent = '0.00P';
    shippingEl.textContent = '0.00P';
    totalEl.textContent = '0.00P';
}

function handleCartAction(event) {
    const actionTarget = event.target.closest('[data-action]');
    if (!actionTarget) {
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

    const quantityEl = cartItemEl.querySelector('.cart-qty-count');
    const currentQty = Math.max(1, parseInt(quantityEl ? quantityEl.textContent : '1', 10) || 1);
    const nextQty = action === 'decrease' ? currentQty - 1 : currentQty + 1;

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

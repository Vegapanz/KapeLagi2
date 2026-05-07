// Checkout Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    restoreSavedShipping();
    loadCart();

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', submitOrder);
    }

    const cartItems = document.getElementById('cartItems');
    if (cartItems) {
        cartItems.addEventListener('click', handleCartAction);
    }

    const deliveryInput = document.getElementById('delivery_address');
    const address2Input = document.querySelector('input[name="address_2"]');
    if (deliveryInput) {
        deliveryInput.addEventListener('input', clearSavedShippingData);
    }
    if (address2Input) {
        address2Input.addEventListener('input', clearSavedShippingData);
    }

    setAddressSearchHandlers();
});

const checkoutState = {
    subtotal: 0,
    shipping: 0,
    distanceKm: 0
};

function getCurrentAddressInfo() {
    const deliveryInput = document.getElementById('delivery_address');
    const address2Input = document.querySelector('input[name="address_2"]');
    const delivery = deliveryInput ? deliveryInput.value.trim() : '';
    const address2 = address2Input ? address2Input.value.trim() : '';
    const key = [address2, delivery].filter(Boolean).join(' | ');
    const full = [address2, delivery].filter(Boolean).join(', ');

    return {
        delivery,
        address2,
        key: key || null,
        full: full || null
    };
}

function getAddressKey() {
    const info = getCurrentAddressInfo();
    return info.key;
}

function getSavedShipping() {
    if (typeof window.localStorage === 'undefined') {
        return null;
    }

    const raw = localStorage.getItem('checkoutSavedShipping');
    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch (err) {
        localStorage.removeItem('checkoutSavedShipping');
        return null;
    }
}

function saveShippingData(distanceKm, distanceMeters, shipping) {
    const addressKey = getAddressKey();
    if (!addressKey) {
        return;
    }

    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');

    const addressInfo = getCurrentAddressInfo();
    const data = {
        address: addressKey,
        delivery_address: addressInfo.delivery,
        address_2: addressInfo.address2,
        full_address: addressInfo.full,
        distanceKm: parseFloat(distanceKm || 0),
        distanceMeters: parseFloat(distanceMeters || 0),
        shipping: parseFloat(shipping || 0),
        lat: latInput ? latInput.value : '',
        lng: lngInput ? lngInput.value : ''
    };

    localStorage.setItem('checkoutSavedShipping', JSON.stringify(data));
}

function clearSavedShippingData() {
    if (typeof window.localStorage !== 'undefined') {
        localStorage.removeItem('checkoutSavedShipping');
    }

    checkoutState.shipping = 0;
    checkoutState.distanceKm = 0;

    const shippingFeeInput = document.getElementById('shipping_fee');
    const distanceKmInput = document.getElementById('distance_km');
    if (shippingFeeInput) shippingFeeInput.value = '0';
    if (distanceKmInput) distanceKmInput.value = '0';

    updateTotalsUI();
}

function restoreSavedShipping() {
    const saved = getSavedShipping();
    if (!saved) {
        return;
    }

    const addressInfo = getCurrentAddressInfo();
    const addressMatches = saved.address === addressInfo.key
        || saved.delivery_address === addressInfo.delivery
        || saved.full_address === addressInfo.full;

    if (!addressMatches) {
        return;
    }

    checkoutState.shipping = parseFloat(saved.shipping || 0);
    checkoutState.distanceKm = parseFloat(saved.distanceKm || 0);

    const shippingFeeInput = document.getElementById('shipping_fee');
    const distanceKmInput = document.getElementById('distance_km');
    if (shippingFeeInput) shippingFeeInput.value = checkoutState.shipping.toFixed(2);
    if (distanceKmInput) distanceKmInput.value = checkoutState.distanceKm.toFixed(2);

    updateTotalsUI();

    const routeInfoEl = document.getElementById('routeInfo');
    if (routeInfoEl && checkoutState.distanceKm > 0) {
        const meters = parseFloat(saved.distanceMeters || 0);
        const metersText = meters > 0 ? ` (${Math.round(meters)} m)` : '';
        routeInfoEl.textContent = `Distance: ${checkoutState.distanceKm.toFixed(2)} km${metersText} | Shipping: ${formatPeso(checkoutState.shipping)}`;
    }
}

function formatPeso(value) {
    return '₱' + parseFloat(value || 0).toFixed(2);
}

function updateTotalsUI() {
    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const totalEl = document.getElementById('total');

    if (subtotalEl) subtotalEl.textContent = formatPeso(checkoutState.subtotal);
    if (shippingEl) shippingEl.textContent = formatPeso(checkoutState.shipping);
    if (totalEl) totalEl.textContent = formatPeso(checkoutState.subtotal + checkoutState.shipping);
}

function clearAddressSuggestions() {
    const suggestions = document.getElementById('addressSuggestions');
    if (!suggestions) return;
    suggestions.innerHTML = '';
    suggestions.classList.remove('active');
}

function renderAddressSuggestions(results) {
    const suggestions = document.getElementById('addressSuggestions');
    if (!suggestions) return;

    suggestions.innerHTML = '';
    if (!Array.isArray(results) || !results.length) {
        suggestions.classList.remove('active');
        return;
    }

    results.slice(0, 5).forEach(item => {
        const option = document.createElement('div');
        option.className = 'address-suggestion-item';
        option.textContent = item.display_name;
        option.addEventListener('click', function () {
            const addressInput = document.getElementById('delivery_address');
            const address2Input = document.querySelector('input[name="address_2"]');
            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');

            if (addressInput) addressInput.value = item.display_name;
            if (address2Input) address2Input.value = '';
            if (latInput) latInput.value = item.lat;
            if (lngInput) lngInput.value = item.lon;

            clearAddressSuggestions();

            if (window.KapeCheckout && typeof window.KapeCheckout.setMapLocation === 'function') {
                window.KapeCheckout.setMapLocation(parseFloat(item.lat), parseFloat(item.lon), item.display_name);
            }
        });
        suggestions.appendChild(option);
    });
    suggestions.classList.add('active');
}

let addressSearchTimer = null;
function searchAddressQuery(query) {
    const suggestions = document.getElementById('addressSuggestions');
    if (!query || query.trim().length < 3 || !suggestions) {
        clearAddressSuggestions();
        return;
    }

    if (addressSearchTimer) {
        clearTimeout(addressSearchTimer);
    }
    addressSearchTimer = setTimeout(() => {
        fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query)}&addressdetails=1&limit=7`) 
            .then(response => response.json())
            .then(data => renderAddressSuggestions(data))
            .catch(() => clearAddressSuggestions());
    }, 300);
}

function setAddressSearchHandlers() {
    const addressInput = document.getElementById('delivery_address');
    if (!addressInput) return;

    addressInput.addEventListener('input', function () {
        const query = this.value || '';
        if (query.trim().length >= 3) {
            searchAddressQuery(query);
        } else {
            clearAddressSuggestions();
        }
    });

    document.addEventListener('click', function (event) {
        const suggestions = document.getElementById('addressSuggestions');
        if (!suggestions) return;
        if (event.target !== suggestions && !suggestions.contains(event.target) && event.target.id !== 'delivery_address') {
            clearAddressSuggestions();
        }
    });
}

function setShippingByDistance(distanceKm, distanceMeters) {
    const km = Math.max(0, parseFloat(distanceKm || 0));
    const roundedKm = Math.ceil(km);
    let shipping = 0;

    if (km <= 0) {
        shipping = 0;
    } else {
        // Philippine-style local delivery rate:
        // base fee covers the first 2 km, then add 5 pesos per extra km.
        shipping = km <= 2 ? 49 : 49 + ((roundedKm - 2) * 5);
    }

    checkoutState.shipping = shipping;
    checkoutState.distanceKm = km;

    const shippingFeeInput = document.getElementById('shipping_fee');
    const distanceKmInput = document.getElementById('distance_km');
    if (shippingFeeInput) shippingFeeInput.value = shipping.toFixed(2);
    if (distanceKmInput) distanceKmInput.value = km.toFixed(2);

    updateTotalsUI();
    saveShippingData(km, distanceMeters, shipping);

    const routeInfoEl = document.getElementById('routeInfo');
    if (routeInfoEl && km > 0) {
        const metersText = typeof distanceMeters === 'number' && distanceMeters > 0 ? ` (${Math.round(distanceMeters)} m)` : '';
        routeInfoEl.textContent = `Distance: ${km.toFixed(2)} km${metersText} | Shipping: ${formatPeso(shipping)}`;
    }
}

window.KapeCheckout = window.KapeCheckout || {};
window.KapeCheckout.setShippingByDistance = setShippingByDistance;

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
    const cartItems = Array.isArray(data.cart) ? data.cart : [];
    const totals = data.totals || { subtotal: 0, shipping: 0, total: 0 };
    const routeShippingSelected = checkoutState.distanceKm > 0 || checkoutState.shipping > 0;
    
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
    checkoutState.subtotal = parseFloat(totals.subtotal || 0);
    if (!routeShippingSelected) {
        checkoutState.shipping = parseFloat(totals.shipping || 0);
        checkoutState.distanceKm = 0;
    }
    updateTotalsUI();

    const shippingFeeInput = document.getElementById('shipping_fee');
    const distanceKmInput = document.getElementById('distance_km');
    if (shippingFeeInput) shippingFeeInput.value = checkoutState.shipping.toFixed(2);
    if (distanceKmInput) distanceKmInput.value = checkoutState.distanceKm.toFixed(2);

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

    cartItemsContainer.innerHTML = '<p class="cart-item-info">Your cart is empty.</p>';
    checkoutState.subtotal = 0;
    checkoutState.shipping = 0;
    checkoutState.distanceKm = 0;
    updateTotalsUI();
}

function submitOrder() {
    if (!window.checkoutPhoneVerified) {
        window.KapeNotify.popup('Verification Required', 'Please verify your phone number first.', 'warning');
        return;
    }

    const form = document.getElementById('checkoutForm');
    if (!form) {
        window.KapeNotify.popup('Checkout Error', 'Unable to find checkout form.', 'error');
        return;
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethod) {
        window.KapeNotify.popup('Payment Method Required', 'Please select a payment method.', 'warning');
        return;
    }

    const formData = new FormData(form);
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Processing...';
    }

    if (checkoutState.shipping > 0) {
        saveShippingData(checkoutState.distanceKm, checkoutState.distanceKm * 1000, checkoutState.shipping);
    }

    if (paymentMethod.value === 'GCASH') {
        processGCashPayment(formData);
    } else {
        processCODPayment(formData);
    }
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

function processCODPayment(formData) {
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
    })
    .finally(() => {
        // Re-enable checkout button
        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Check Out';
    });
}

function processGCashPayment(formData) {
    // First create the order
    fetch('api/cart.php?action=create_order', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const orderId = data.order_id;
            const totalAmount = checkoutState.subtotal + checkoutState.shipping;

            // Show processing message
            window.KapeNotify.toastInfo('Redirecting to GCash payment...', 'Processing');

            // Create GCash payment and redirect
            return createGCashPayment(orderId, totalAmount);
        } else {
            throw new Error(data.message || 'Unable to create order');
        }
    })
    .catch(error => {
        console.error('GCash Payment Error:', error);
        window.KapeNotify.popup('Payment Error', error.message || 'Unable to process GCash payment.', 'error');

        // Re-enable checkout button
        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Check Out';
    });
}

function createGCashPayment(orderId, amount) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('amount', amount.toFixed(2));

    return fetch('api/payment.php?action=create_gcash_payment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to PayMongo hosted checkout
            window.location.href = data.checkout_url;
        } else {
            throw new Error(data.message || 'Failed to create GCash payment');
        }
    });
}

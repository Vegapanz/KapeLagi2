// Checkout Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    
    document.getElementById('checkoutBtn').addEventListener('click', submitOrder);
});

function loadCart() {
    fetch('api/cart.php?action=get_cart')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCart(data);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayCart(data) {
    const cartItemsContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    
    // Clear previous items
    cartItemsContainer.innerHTML = '';
    
    // Display items
    data.items.forEach(item => {
        const itemHTML = `
            <div class="cart-item">
                <div class="cart-item-image">
                    <img src="Coffee/SpanishLatte.png" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <p class="cart-item-name">${item.name}</p>
                    <p class="cart-item-info">${item.size}</p>
                    <p class="cart-item-info">Qty: ${item.quantity} × ${item.price}₽ = ${item.item_total.toFixed(2)}₽</p>
                </div>
            </div>
        `;
        cartItemsContainer.innerHTML += itemHTML;
    });
    
    // Update totals
    subtotalEl.textContent = data.total.toFixed(2) + '₽';
    totalEl.textContent = data.total.toFixed(2) + '₽';
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
            alert('Order placed successfully! Order ID: ' + data.order_id);
            window.location.href = 'index.php';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while placing the order');
    });
}

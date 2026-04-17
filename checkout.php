<?php
include 'config/session.php';
include 'config/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

$user_id = get_user_id();

// Get user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KapeLagi</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'components/navbar.php'; ?>
    
    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container-lg">
            <div class="checkout-header">
                <a href="menu.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
            
            <div class="checkout-content">
                <!-- Left: Delivery Information -->
                <div class="checkout-form">
                    <h2 class="checkout-title">Delivery Information</h2>
                    
                    <form id="checkoutForm">
                        <div class="form-row">
                            <div class="form-col">
                                <label>Name</label>
                                <input type="text" name="customer_name" class="form-input" value="<?php echo $user['name'] ?? ''; ?>" required>
                            </div>
                            <div class="form-col">
                                <label>Mobile Number</label>
                                <input type="tel" name="customer_phone" class="form-input" placeholder="eg. 09xxxxxxxxx" value="<?php echo $user['phone'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label>Email</label>
                                <input type="email" name="customer_email" class="form-input" value="<?php echo $user['email'] ?? ''; ?>" required>
                            </div>
                            <div class="form-col">
                                <label>Province</label>
                                <select name="province" class="form-input" required>
                                    <option value="">Select Province</option>
                                    <option value="Cavite" <?php echo (isset($user['province']) && $user['province'] == 'Cavite') ? 'selected' : ''; ?>>Cavite</option>
                                    <option value="Metro Manila">Metro Manila</option>
                                    <option value="Laguna">Laguna</option>
                                    <option value="Rizal">Rizal</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Address 1</label>
                            <input type="text" name="delivery_address" class="form-input" placeholder="Street, house no., etc." value="<?php echo $user['address'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Address 2 (optional)</label>
                            <input type="text" name="address_2" class="form-input" placeholder="Barangay">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label>City</label>
                                <select name="city" class="form-input" required>
                                    <option value="">Select City</option>
                                    <option value="Dasmariñas" <?php echo (isset($user['city']) && $user['city'] == 'Dasmariñas') ? 'selected' : ''; ?>>Dasmariñas</option>
                                    <option value="Silang">Silang</option>
                                    <option value="Kawit">Kawit</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    
                    <h3 class="section-title mt-5">Payment Method</h3>
                    <div class="payment-placeholder">
                        <p>Payment methods coming soon...</p>
                    </div>
                </div>
                
                <!-- Right: Order Summary -->
                <div class="order-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    
                    <div id="cartItems" class="cart-items">
                        <!-- Items will be loaded by JavaScript -->
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal">0.00₽</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="shipping">0.00₽</span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="total">0.00₽</span>
                    </div>
                    
                    <button class="checkout-btn" id="checkoutBtn">Check Out</button>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Checkout JavaScript -->
    <script src="assets/js/checkout.js"></script>
</body>
</html>

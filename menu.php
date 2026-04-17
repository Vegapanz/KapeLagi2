<?php
include 'config/session.php';
include 'config/db.php';

// Get all products
$sql = "SELECT * FROM products ORDER BY category, name";
$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get categories
$categories = ['Coffee', 'Non-Coffee', 'Fruity'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - KapeLagi</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/menu.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'components/navbar.php'; ?>
    
    <!-- Menu Section -->
    <section class="menu-section">
        <div class="container-lg">
            <h1 class="menu-title">MENU</h1>
            <p class="menu-subtitle">Coffee</p>
            
            <!-- Filters -->
            <div class="menu-filters">
                <button class="filter-btn active" data-filter="all">Coffee</button>
                <button class="filter-btn" data-filter="Non-Coffee">Non-Coffee</button>
                <button class="filter-btn" data-filter="Fruity">Fruity</button>
            </div>
            
            <!-- Search Bar -->
            <div class="search-wrapper">
                <input type="text" id="searchInput" class="search-input" placeholder="Search">
            </div>
            
            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-category="<?php echo $product['category']; ?>" data-name="<?php echo strtolower($product['name']); ?>">
                        <div class="product-image">
                            <img src="Coffee/SpanishLatte.png" alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo $product['name']; ?></h3>
                            <p class="product-sizes">
                                16oz: <?php echo $product['price_16oz']; ?>₽ | 
                                22oz: <?php echo $product['price_22oz']; ?>₽
                            </p>
                            <button class="product-btn" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo $product['name']; ?>" data-product-desc="<?php echo $product['description']; ?>" data-price-16="<?php echo $product['price_16oz']; ?>" data-price-22="<?php echo $product['price_22oz']; ?>">
                                View
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Product Detail Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content product-modal">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <div class="modal-body">
                    <div class="product-detail">
                        <div class="product-detail-image">
                            <img src="Coffee/SpanishLatte.png" id="modalProductImage" alt="Product">
                        </div>
                        <div class="product-detail-info">
                            <h2 id="modalProductName"></h2>
                            <p id="modalProductDesc" class="product-description"></p>
                            
                            <div class="size-selector">
                                <label>Size:</label>
                                <div class="size-options">
                                    <button class="size-btn active" data-size="16oz" data-price="0">16oz</button>
                                    <button class="size-btn" data-size="22oz" data-price="0">22oz</button>
                                </div>
                            </div>
                            
                            <div class="quantity-selector">
                                <label>Quantity:</label>
                                <div class="quantity-control">
                                    <button class="qty-btn minus">−</button>
                                    <input type="number" id="quantityInput" value="1" min="1">
                                    <button class="qty-btn plus">+</button>
                                </div>
                            </div>
                            
                            <div class="special-instructions">
                                <label>Special Instructions:</label>
                                <textarea id="specialInstructions" placeholder="e.g., Extra shot, no ice, etc." rows="3"></textarea>
                            </div>
                            
                            <div class="price-display">
                                <span>Price: </span>
                                <span id="totalPrice" class="price-value">0.00₽</span>
                            </div>
                            
                            <div class="modal-buttons">
                                <button class="btn-primary" id="buyNowBtn">Buy Now</button>
                                <button class="btn-secondary" id="addToCartBtn">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Sidebar -->
    <?php include 'components/cart-sidebar.php'; ?>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Menu JavaScript -->
    <script src="assets/js/menu.js"></script>
</body>
</html>

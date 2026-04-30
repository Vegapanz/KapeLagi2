<?php
include 'config/session.php';
include 'config/db.php';

function getMenuImagePath($productName, $imageUrl = null)
{
    $imageUrl = trim((string)$imageUrl);
    if ($imageUrl !== '') {
        return $imageUrl;
    }

    $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower((string)$productName));
    $fallbackImages = [
        'americano' => 'Coffee/Americano.png',
        'berrymatcha' => 'Coffee/BerryMatcha.png',
        'biscofflatte' => 'Coffee/BiscoffLatte.png',
        'blueberrymilk' => 'Coffee/Blueberry Milk.png',
        'blueberry' => 'Coffee/Blueberry.png',
        'blueberrychoco' => 'Coffee/BlueberryChoco.png',
        'blueberryespresso' => 'Coffee/BlueberryEspresso.png',
        'blueberrymatcha' => 'Coffee/BlueberryMAtcha.png',
        'caramelmacchiato' => 'Coffee/CaramelMacchiato.png',
        'chocoberry' => 'Coffee/ChocoBerry.png',
        'chocomatcha' => 'Coffee/ChocoMatcha.png',
        'dirtymatcha' => 'Coffee/Dirty Matcha.png',
        'frenchvanilla' => 'Coffee/FrenchVanilla.png',
        'greenapple' => 'Coffee/GreenApple.png',
        'hazelnutlatte' => 'Coffee/HazelnutLatte.png',
        'icychoco' => 'Coffee/IcyChoco.png',
        'lemonade' => 'Coffee/Lemonade.png',
        'lychee' => 'Coffee/Lychee.png',
        'matchalatte' => 'Coffee/MatchaLatte.png',
        'mochalatte' => 'Coffee/MochaLatte.png',
        'nutellalatte' => 'Coffee/NutellaLatte.png',
        'saltedcaramellatte' => 'Coffee/SaltedCaramelLatte.png',
        'spanishlatte' => 'Coffee/SpanishLatte.png',
        'strawberrymilk' => 'Coffee/StrawberryMilk.png',
        'vanillalatte' => 'Coffee/VanillaLatte.png',
        'vietnamese' => 'Coffee/Vietnamese.png',
    ];

    return $fallbackImages[$normalized] ?? 'Coffee/SpanishLatte.png';
}

function getMenuCatalog()
{
    return [
        'Americano' => ['category' => 'Coffee', 'image' => 'Coffee/Americano.png', 'description' => 'A classic espresso stretched with hot water for a smooth, full-bodied flavor', 'price_16oz' => 100, 'price_22oz' => 120],
        'Berry Matcha' => ['category' => 'Non-Coffee', 'image' => 'Coffee/BerryMatcha.png', 'description' => 'A bright berry and matcha blend with a smooth finish', 'price_16oz' => 120, 'price_22oz' => 140],
        'Biscoff Latte' => ['category' => 'Coffee', 'image' => 'Coffee/BiscoffLatte.png', 'description' => 'A rich latte with spiced Biscoff sweetness', 'price_16oz' => 130, 'price_22oz' => 150],
        'Blueberry Milk' => ['category' => 'Non-Coffee', 'image' => 'Coffee/Blueberry Milk.png', 'description' => 'Smooth blueberry flavor combined with creamy milk', 'price_16oz' => 110, 'price_22oz' => 130],
        'Blueberry' => ['category' => 'Fruity', 'image' => 'Coffee/Blueberry.png', 'description' => 'A fresh blueberry drink with a crisp fruity profile', 'price_16oz' => 110, 'price_22oz' => 130],
        'Blueberry Choco' => ['category' => 'Non-Coffee', 'image' => 'Coffee/BlueberryChoco.png', 'description' => 'Blueberry sweetness paired with a chocolate finish', 'price_16oz' => 115, 'price_22oz' => 135],
        'Blueberry Espresso' => ['category' => 'Coffee', 'image' => 'Coffee/BlueberryEspresso.png', 'description' => 'Fruit and espresso in a bold layered drink', 'price_16oz' => 125, 'price_22oz' => 145],
        'Blueberry Matcha' => ['category' => 'Non-Coffee', 'image' => 'Coffee/BlueberryMAtcha.png', 'description' => 'Matcha with a sweet blueberry twist', 'price_16oz' => 120, 'price_22oz' => 140],
        'Caramel Macchiato' => ['category' => 'Coffee', 'image' => 'Coffee/CaramelMacchiato.png', 'description' => 'Rich caramel layered with espresso and milk', 'price_16oz' => 120, 'price_22oz' => 140],
        'Choco Berry' => ['category' => 'Non-Coffee', 'image' => 'Coffee/ChocoBerry.png', 'description' => 'A chocolate and berry combination with a sweet tang', 'price_16oz' => 115, 'price_22oz' => 135],
        'Choco Matcha' => ['category' => 'Non-Coffee', 'image' => 'Coffee/ChocoMatcha.png', 'description' => 'Chocolate meets matcha in a creamy blended drink', 'price_16oz' => 120, 'price_22oz' => 140],
        'Dirty Matcha' => ['category' => 'Coffee', 'image' => 'Coffee/Dirty Matcha.png', 'description' => 'Matcha layered with espresso for a bolder sip', 'price_16oz' => 130, 'price_22oz' => 150],
        'French Vanilla' => ['category' => 'Coffee', 'image' => 'Coffee/FrenchVanilla.png', 'description' => 'Smooth vanilla and coffee with a soft dessert note', 'price_16oz' => 110, 'price_22oz' => 130],
        'Green Apple' => ['category' => 'Fruity', 'image' => 'Coffee/GreenApple.png', 'description' => 'Crisp green apple flavor with a bright finish', 'price_16oz' => 110, 'price_22oz' => 130],
        'Hazelnut Latte' => ['category' => 'Coffee', 'image' => 'Coffee/HazelnutLatte.png', 'description' => 'Warm hazelnut sweetness blended into a latte', 'price_16oz' => 120, 'price_22oz' => 140],
        'Icy Choco' => ['category' => 'Non-Coffee', 'image' => 'Coffee/IcyChoco.png', 'description' => 'A chilled chocolate drink with a refreshing finish', 'price_16oz' => 115, 'price_22oz' => 135],
        'Lemonade' => ['category' => 'Fruity', 'image' => 'Coffee/Lemonade.png', 'description' => 'A clean citrus drink with a bright, tangy kick', 'price_16oz' => 100, 'price_22oz' => 120],
        'Lychee' => ['category' => 'Fruity', 'image' => 'Coffee/Lychee.png', 'description' => 'Sweet lychee flavor with a fragrant fruit profile', 'price_16oz' => 110, 'price_22oz' => 130],
        'Matcha Latte' => ['category' => 'Coffee', 'image' => 'Coffee/MatchaLatte.png', 'description' => 'Vibrant green tea powder whisked with hot milk', 'price_16oz' => 130, 'price_22oz' => 150],
        'Mocha Latte' => ['category' => 'Coffee', 'image' => 'Coffee/MochaLatte.png', 'description' => 'Coffee and chocolate blended into a smooth latte', 'price_16oz' => 125, 'price_22oz' => 145],
        'Nutella Latte' => ['category' => 'Coffee', 'image' => 'Coffee/NutellaLatte.png', 'description' => 'A creamy latte with rich Nutella-inspired flavor', 'price_16oz' => 130, 'price_22oz' => 150],
        'Salted Caramel Latte' => ['category' => 'Coffee', 'image' => 'Coffee/SaltedCaramelLatte.png', 'description' => 'Sweet caramel balanced with a light salted finish', 'price_16oz' => 130, 'price_22oz' => 150],
        'Spanish Latte' => ['category' => 'Coffee', 'image' => 'Coffee/SpanishLatte.png', 'description' => 'A sweet, creamy espresso-based drink with condensed milk', 'price_16oz' => 120, 'price_22oz' => 140],
        'Strawberry Milk' => ['category' => 'Non-Coffee', 'image' => 'Coffee/StrawberryMilk.png', 'description' => 'Creamy strawberry milk with a soft dessert-like flavor', 'price_16oz' => 110, 'price_22oz' => 130],
        'Vanilla Latte' => ['category' => 'Coffee', 'image' => 'Coffee/VanillaLatte.png', 'description' => 'Smooth vanilla flavor combined with espresso and milk', 'price_16oz' => 110, 'price_22oz' => 130],
        'Vietnamese' => ['category' => 'Coffee', 'image' => 'Coffee/Vietnamese.png', 'description' => 'A bold Vietnamese-style coffee with a rich finish', 'price_16oz' => 120, 'price_22oz' => 140]
    ];
}

function normalizeProductRow($row, $catalogItem = null)
{
    $price16 = isset($row['price_16oz']) ? (float)$row['price_16oz'] : (float)($catalogItem['price_16oz'] ?? 0);
    $price22 = isset($row['price_22oz']) ? (float)$row['price_22oz'] : (float)($catalogItem['price_22oz'] ?? 0);

    if ($price22 <= $price16) {
        $price22 = $price16 + 20;
    }

    $row['price_16oz_effective'] = number_format($price16, 2, '.', '');
    $row['price_22oz_effective'] = number_format($price22, 2, '.', '');
    $row['image_path'] = getMenuImagePath($row['name'], $row['image_url'] ?? ($catalogItem['image'] ?? ''));
    $row['description'] = $row['description'] ?? ($catalogItem['description'] ?? '');
    $row['category'] = $row['category'] ?? ($catalogItem['category'] ?? 'Coffee');

    return $row;
}

function buildMenuProducts($dbRows)
{
    $catalog = getMenuCatalog();
    $productsByName = [];

    foreach ($dbRows as $row) {
        $productsByName[$row['name']] = $row;
    }

    $products = [];
    foreach ($catalog as $name => $catalogItem) {
        if (isset($productsByName[$name])) {
            $products[] = normalizeProductRow($productsByName[$name], $catalogItem);
            unset($productsByName[$name]);
        } else {
            $products[] = [
                'id' => 0,
                'name' => $name,
                'description' => $catalogItem['description'],
                'category' => $catalogItem['category'],
                'image_url' => $catalogItem['image'],
                'image_path' => $catalogItem['image'],
                'price_16oz_effective' => number_format($catalogItem['price_16oz'], 2, '.', ''),
                'price_22oz_effective' => number_format($catalogItem['price_22oz'], 2, '.', ''),
                'price_16oz' => $catalogItem['price_16oz'],
                'price_22oz' => $catalogItem['price_22oz'],
                'missing_from_db' => true,
            ];
        }
    }

    foreach ($productsByName as $row) {
        $products[] = normalizeProductRow($row);
    }

    return $products;
}

function syncMenuCatalogToDatabase($conn)
{
    $catalog = getMenuCatalog();
    $existingNames = [];

    $result = $conn->query("SELECT name FROM products");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existingNames[$row['name']] = true;
        }
    }

    $insertSql = "INSERT INTO products (name, description, category, image_url, price_16oz, price_22oz) VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        return;
    }

    foreach ($catalog as $name => $item) {
        if (isset($existingNames[$name])) {
            continue;
        }

        $description = $item['description'];
        $category = $item['category'];
        $image = $item['image'];
        $price16 = (float)$item['price_16oz'];
        $price22 = (float)$item['price_22oz'];

        $insertStmt->bind_param('ssssdd', $name, $description, $category, $image, $price16, $price22);
        $insertStmt->execute();
    }

    $insertStmt->close();
}

function updateMenuCategoryAssignments($conn)
{
    $updateSql = "UPDATE products SET category = 'Non-Coffee' WHERE name IN ('Berry Matcha', 'Blueberry Matcha', 'Strawberry Milk')";
    $conn->query($updateSql);
}

// Get all products
syncMenuCatalogToDatabase($conn);
updateMenuCategoryAssignments($conn);
$sql = "SELECT * FROM products ORDER BY FIELD(category, 'Coffee', 'Non-Coffee', 'Fruity'), name";
$result = $conn->query($sql);
$dbProducts = [];
while ($row = $result->fetch_assoc()) {
    $dbProducts[] = $row;
}
$products = buildMenuProducts($dbProducts);

// Get categories
$categories = ['Coffee', 'Non-Coffee', 'Fruity'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">

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
        <div class="container-xl">
            <h1 class="menu-title">MENU</h1>
            <p class="menu-subtitle">Coffee</p>

            <div class="menu-toolbar">
                <!-- Filters -->
                <div class="menu-filters">
                    <button class="filter-btn active" data-filter="Coffee">Coffee</button>
                    <button class="filter-btn" data-filter="Non-Coffee">Non-Coffee</button>
                    <button class="filter-btn" data-filter="Fruity">Fruity</button>
                </div>

                <!-- Search Bar -->
                <div class="search-wrapper">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search" aria-label="Search menu items">
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-shell">
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card"
                            data-category="<?php echo htmlspecialchars($product['category']); ?>"
                            data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
                            data-product-id="<?php echo (int)$product['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                            data-product-desc="<?php echo htmlspecialchars($product['description']); ?>"
                            data-product-image="<?php echo htmlspecialchars($product['image_path']); ?>"
                            data-product-missing="<?php echo !empty($product['missing_from_db']) ? '1' : '0'; ?>"
                            data-price-16oz="<?php echo htmlspecialchars($product['price_16oz_effective']); ?>"
                            data-price-22oz="<?php echo htmlspecialchars($product['price_22oz_effective']); ?>"
                            role="button"
                            tabindex="0"
                            <?php echo empty($product['id']) ? 'aria-disabled="true"' : ''; ?>
                            aria-label="View <?php echo htmlspecialchars($product['name']); ?> details">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $product['name']; ?></h3>
                                <p class="product-sizes">
                                    <span>16oz: <?php echo $product['price_16oz_effective']; ?>₱</span>
                                    <span>22oz: <?php echo $product['price_22oz_effective']; ?>₱</span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Detail Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content product-modal">
                <button type="button" class="btn-close product-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    <input type="number" id="quantityInput" value="1" min="1" aria-label="Quantity">
                                    <button class="qty-btn plus">+</button>
                                </div>
                            </div>

                            <div class="price-display">
                                <span>Price:</span>
                                <span id="totalPrice" class="price-value">0.00₱</span>
                            </div>

                            <div class="special-instructions">
                                <label>Special Instructions:</label>
                                <textarea id="specialInstructions" rows="3"></textarea>
                            </div>

                            <div class="modal-buttons">
                                <button class="menu-btn menu-btn-primary" id="buyNowBtn">Buy Now</button>
                                <button class="menu-btn menu-btn-secondary" id="addToCartBtn">
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
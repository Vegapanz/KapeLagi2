<?php
// Database Schema Setup File
// Run this once to create all tables

include 'db.php';

// SQL statements to create tables
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified_at DATETIME NULL,
    google_id VARCHAR(191) NULL,
    oauth_provider VARCHAR(20) NULL,
    oauth_avatar_url VARCHAR(255) NULL,
    email_verification_token VARCHAR(64) NULL,
    terms_accepted_at DATETIME NULL,
    terms_version VARCHAR(20) NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(50),
    province VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    image_url VARCHAR(255),
    price_16oz DECIMAL(10, 2),
    price_22oz DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_cart = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    size VARCHAR(10),
    quantity INT DEFAULT 1,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    delivery_address VARCHAR(255),
    city VARCHAR(50),
    province VARCHAR(50),
    payment_method VARCHAR(20) NOT NULL DEFAULT 'COD',
    total_amount DECIMAL(10, 2),
    status VARCHAR(20) DEFAULT 'pending',
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    archived_at DATETIME NULL,
    special_notes TEXT,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

$sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100),
    size VARCHAR(10),
    price DECIMAL(10, 2),
    quantity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

// Execute the SQL statements
if ($conn->query($sql_users) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

if ($conn->query($sql_products) === TRUE) {
    echo "Products table created successfully<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

if ($conn->query($sql_cart) === TRUE) {
    echo "Cart table created successfully<br>";
} else {
    echo "Error creating cart table: " . $conn->error . "<br>";
}

if ($conn->query($sql_orders) === TRUE) {
    echo "Orders table created successfully<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

if ($conn->query($sql_order_items) === TRUE) {
    echo "Order items table created successfully<br>";
} else {
    echo "Error creating order items table: " . $conn->error . "<br>";
}

// Insert sample products
$sample_products = [
    ["Americano", "Coffee", "Coffee/Americano.png", 100, 120],
    ["Berry Matcha", "Non-Coffee", "Coffee/BerryMatcha.png", 120, 140],
    ["Biscoff Latte", "Coffee", "Coffee/BiscoffLatte.png", 130, 150],
    ["Blueberry Milk", "Non-Coffee", "Coffee/Blueberry Milk.png", 110, 130],
    ["Blueberry", "Fruity", "Coffee/Blueberry.png", 110, 130],
    ["Blueberry Choco", "Non-Coffee", "Coffee/BlueberryChoco.png", 115, 135],
    ["Blueberry Espresso", "Coffee", "Coffee/BlueberryEspresso.png", 125, 145],
    ["Blueberry Matcha", "Non-Coffee", "Coffee/BlueberryMAtcha.png", 120, 140],
    ["Caramel Macchiato", "Coffee", "Coffee/CaramelMacchiato.png", 120, 140],
    ["Choco Berry", "Non-Coffee", "Coffee/ChocoBerry.png", 115, 135],
    ["Choco Matcha", "Non-Coffee", "Coffee/ChocoMatcha.png", 120, 140],
    ["Dirty Matcha", "Coffee", "Coffee/Dirty Matcha.png", 130, 150],
    ["French Vanilla", "Coffee", "Coffee/FrenchVanilla.png", 110, 130],
    ["Green Apple", "Fruity", "Coffee/GreenApple.png", 110, 130],
    ["Hazelnut Latte", "Coffee", "Coffee/HazelnutLatte.png", 120, 140],
    ["Icy Choco", "Non-Coffee", "Coffee/IcyChoco.png", 115, 135],
    ["Lemonade", "Fruity", "Coffee/Lemonade.png", 100, 120],
    ["Lychee", "Fruity", "Coffee/Lychee.png", 110, 130],
    ["Matcha Latte", "Coffee", "Coffee/MatchaLatte.png", 130, 150],
    ["Mocha Latte", "Coffee", "Coffee/MochaLatte.png", 125, 145],
    ["Nutella Latte", "Coffee", "Coffee/NutellaLatte.png", 130, 150],
    ["Salted Caramel Latte", "Coffee", "Coffee/SaltedCaramelLatte.png", 130, 150],
    ["Spanish Latte", "Coffee", "Coffee/SpanishLatte.png", 120, 140],
    ["Strawberry Milk", "Non-Coffee", "Coffee/StrawberryMilk.png", 110, 130],
    ["Vanilla Latte", "Coffee", "Coffee/VanillaLatte.png", 110, 130],
    ["Vietnamese", "Coffee", "Coffee/Vietnamese.png", 120, 140]
];

foreach ($sample_products as $product) {
    $check_sql = "SELECT id FROM products WHERE name = '" . $conn->real_escape_string($product[0]) . "'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO products (name, description, category, image_url, price_16oz, price_22oz) 
                       VALUES ('" . $conn->real_escape_string($product[0]) . "', 
                               '" . $conn->real_escape_string($product[0] . " is a signature KapeLagi drink crafted for a smooth, café-style finish.") . "', 
                               '" . $conn->real_escape_string($product[1]) . "', 
                               '" . $conn->real_escape_string($product[2]) . "', 
                               " . $product[3] . ", 
                               " . $product[4] . ")";
        $conn->query($insert_sql);
    }
}

echo "Database setup complete!<br>";
echo "<a href='../index.php'>Back to home</a>";

$conn->close();
?>

<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);  // Standard MySQL port
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kapelagi');

// Create connection with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage() . 
        "<br><br>Make sure MySQL is running in XAMPP Control Panel.");
}

// Set charset to utf8
$conn->set_charset("utf8");

function ensure_order_cancellation_reason_column($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $checkSql = "SHOW COLUMNS FROM orders LIKE 'cancellation_reason'";
    $result = $conn->query($checkSql);
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN cancellation_reason TEXT NULL AFTER special_notes");
    }

    $checked = true;
}

function ensure_order_archive_columns($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $isArchivedCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'is_archived'");
    if ($isArchivedCheck && $isArchivedCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
    }

    $archivedAtCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'archived_at'");
    if ($archivedAtCheck && $archivedAtCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN archived_at DATETIME NULL AFTER is_archived");
    }

    $checked = true;
}

function ensure_product_stock_column($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $checkSql = "SHOW COLUMNS FROM products LIKE 'stock'";
    $result = $conn->query($checkSql);
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER price_22oz");
    }

    $checked = true;
}

function ensure_product_archive_columns($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $isArchivedCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'is_archived'");
    if ($isArchivedCheck && $isArchivedCheck->num_rows === 0) {
        $conn->query("ALTER TABLE products ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER stock");
    }

    $archivedAtCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'archived_at'");
    if ($archivedAtCheck && $archivedAtCheck->num_rows === 0) {
        $conn->query("ALTER TABLE products ADD COLUMN archived_at DATETIME NULL AFTER is_archived");
    }

    $checked = true;
}

function ensure_ingredients_table($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    // Create a simple ingredients table if it doesn't exist
    $check = $conn->query("SHOW TABLES LIKE 'ingredients'");
    if (!$check || $check->num_rows === 0) {
        $sql = "CREATE TABLE IF NOT EXISTS ingredients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(191) NOT NULL,
            unit VARCHAR(32) DEFAULT 'units',
            stock DECIMAL(10,2) NOT NULL DEFAULT 0,
            package_size DECIMAL(10,2) NOT NULL DEFAULT 1,
            package_unit VARCHAR(32) DEFAULT 'pieces',
            low_stock_threshold DECIMAL(10,2) NOT NULL DEFAULT 5,
            category VARCHAR(64) DEFAULT 'other',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($sql);
    }

    // ensure category column exists on older installs
    $colCheck = $conn->query("SHOW COLUMNS FROM ingredients LIKE 'category'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE ingredients ADD COLUMN category VARCHAR(64) DEFAULT 'other' AFTER low_stock_threshold");
    }

    $packageSizeCheck = $conn->query("SHOW COLUMNS FROM ingredients LIKE 'package_size'");
    if ($packageSizeCheck && $packageSizeCheck->num_rows === 0) {
        $conn->query("ALTER TABLE ingredients ADD COLUMN package_size DECIMAL(10,2) NOT NULL DEFAULT 1 AFTER stock");
    }

    $packageUnitCheck = $conn->query("SHOW COLUMNS FROM ingredients LIKE 'package_unit'");
    if ($packageUnitCheck && $packageUnitCheck->num_rows === 0) {
        $conn->query("ALTER TABLE ingredients ADD COLUMN package_unit VARCHAR(32) DEFAULT 'pieces' AFTER package_size");
    }

    $checked = true;
}

function ensure_product_ingredients_table($conn) {
    static $checked = false;
    if ($checked) return;

    $check = $conn->query("SHOW TABLES LIKE 'product_ingredients'");
    if (!$check || $check->num_rows === 0) {
        $sql = "CREATE TABLE IF NOT EXISTS product_ingredients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            ingredient_id INT NOT NULL,
            size VARCHAR(10) NOT NULL DEFAULT '16oz',
            stock_unit VARCHAR(32) DEFAULT 'unit',
            quantity_per_unit DECIMAL(10,4) NOT NULL DEFAULT 0,
            unit VARCHAR(32) DEFAULT 'units',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($sql);
    }

    $stockUnitCheck = $conn->query("SHOW COLUMNS FROM product_ingredients LIKE 'stock_unit'");
    if ($stockUnitCheck && $stockUnitCheck->num_rows === 0) {
        $conn->query("ALTER TABLE product_ingredients ADD COLUMN stock_unit VARCHAR(32) DEFAULT 'unit' AFTER ingredient_id");
    }

    $sizeCheck = $conn->query("SHOW COLUMNS FROM product_ingredients LIKE 'size'");
    if ($sizeCheck && $sizeCheck->num_rows === 0) {
        $conn->query("ALTER TABLE product_ingredients ADD COLUMN size VARCHAR(10) NOT NULL DEFAULT '16oz' AFTER ingredient_id");
    }

    $checked = true;
}

function normalize_unit_scale($unit) {
    $value = strtolower(trim((string) $unit));

    if (in_array($value, ['liter', 'liters', 'l'], true)) return 'liters';
    if (in_array($value, ['milliliter', 'milliliters', 'ml'], true)) return 'milliliters';
    if (in_array($value, ['gram', 'grams', 'g'], true)) return 'grams';
    if (in_array($value, ['milligram', 'milligrams', 'mg'], true)) return 'milligrams';
    if (in_array($value, ['kilogram', 'kilograms', 'kg'], true)) return 'kilograms';
    if (in_array($value, ['piece', 'pieces', 'pc', 'pcs', 'unit', 'units'], true)) return 'pieces';

    return $value ?: 'pieces';
}

function unit_scale_family($unit) {
    $normalized = normalize_unit_scale($unit);
    if (in_array($normalized, ['liters', 'milliliters'], true)) return 'volume';
    if (in_array($normalized, ['grams', 'milligrams', 'kilograms'], true)) return 'mass';
    return 'count';
}

function unit_scale_to_base_factor($unit) {
    $normalized = normalize_unit_scale($unit);

    switch ($normalized) {
        case 'milliliters':
            return 0.001;
        case 'liters':
            return 1.0;
        case 'milligrams':
            return 0.001;
        case 'grams':
            return 1.0;
        case 'kilograms':
            return 1000.0;
        case 'pieces':
        default:
            return 1.0;
    }
}

function convert_unit_amount($amount, $fromUnit, $toUnit) {
    $fromNormalized = normalize_unit_scale($fromUnit);
    $toNormalized = normalize_unit_scale($toUnit);

    if (unit_scale_family($fromNormalized) !== unit_scale_family($toNormalized)) {
        return null;
    }

    $baseAmount = (float) $amount * unit_scale_to_base_factor($fromNormalized);
    $toFactor = unit_scale_to_base_factor($toNormalized);
    if ($toFactor == 0.0) {
        return null;
    }

    return $baseAmount / $toFactor;
}

function collect_order_ingredient_requirements($conn, $cart_items) {
    $requirements = [];
    $mapping_stmt = $conn->prepare("SELECT ingredient_id, quantity_per_unit, unit FROM product_ingredients WHERE product_id = ? AND (size = ? OR size IS NULL OR size = '') ORDER BY CASE WHEN size = ? THEN 0 ELSE 1 END");
    if (!$mapping_stmt) {
        return [false, 'Failed to prepare ingredient recipe lookup', []];
    }

    foreach ($cart_items as $item) {
        $product_id = (int) ($item['product_id'] ?? 0);
        $quantity = (float) ($item['quantity'] ?? 0);
        $size = trim((string) ($item['size'] ?? '16oz'));
        if ($product_id <= 0 || $quantity <= 0) {
            continue;
        }

        $mapping_stmt->bind_param('iss', $product_id, $size, $size);
        if (!$mapping_stmt->execute()) {
            return [false, 'Failed to load ingredient recipe', []];
        }

        $mapping_result = $mapping_stmt->get_result();
        if (!$mapping_result || $mapping_result->num_rows === 0) {
            $fallback_stmt = $conn->prepare("SELECT ingredient_id, quantity_per_unit, unit FROM product_ingredients WHERE product_id = ? ORDER BY id ASC");
            if (!$fallback_stmt) {
                return [false, 'Failed to load ingredient recipe', []];
            }
            $fallback_stmt->bind_param('i', $product_id);
            if (!$fallback_stmt->execute()) {
                $fallback_stmt->close();
                return [false, 'Failed to load ingredient recipe', []];
            }
            $mapping_result = $fallback_stmt->get_result();
            $fallbackStmtUsed = true;
        } else {
            $fallbackStmtUsed = false;
        }
        if (!$mapping_result) {
            continue;
        }

        while ($mapping = $mapping_result->fetch_assoc()) {
            $ingredient_id = (int) $mapping['ingredient_id'];
            $recipe_amount = (float) $mapping['quantity_per_unit'];
            $recipe_unit = $mapping['unit'] ?? 'pieces';

            $lookup_stmt = $conn->prepare("SELECT stock, unit FROM ingredients WHERE id = ? FOR UPDATE");
            if (!$lookup_stmt) {
                return [false, 'Failed to prepare ingredient lookup', []];
            }

            $lookup_stmt->bind_param('i', $ingredient_id);
            if (!$lookup_stmt->execute()) {
                return [false, 'Failed to load ingredient stock', []];
            }

            $lookup_result = $lookup_stmt->get_result();
            $ingredient_row = $lookup_result ? $lookup_result->fetch_assoc() : null;
            $lookup_stmt->close();

            if (!$ingredient_row) {
                return [false, 'Ingredient not found for product recipe', []];
            }

            $ingredient_unit = $ingredient_row['unit'] ?? 'pieces';
            $required_amount = convert_unit_amount($recipe_amount * $quantity, $recipe_unit, $ingredient_unit);
            if ($required_amount === null) {
                return [false, 'Unit mismatch in ingredient mapping', []];
            }

            if (!isset($requirements[$ingredient_id])) {
                $requirements[$ingredient_id] = [
                    'required' => 0.0,
                    'stock' => (float) ($ingredient_row['stock'] ?? 0),
                    'unit' => $ingredient_unit,
                ];
            }

            $requirements[$ingredient_id]['required'] += $required_amount;
            $requirements[$ingredient_id]['stock'] = (float) ($ingredient_row['stock'] ?? 0);
        }

        if (!empty($fallbackStmtUsed) && isset($fallback_stmt)) {
            $fallback_stmt->close();
        }
    }

    $mapping_stmt->close();
    return [true, '', $requirements];
}

function consume_order_ingredient_requirements($conn, $requirements) {
    $update_stmt = $conn->prepare("UPDATE ingredients SET stock = stock - ? WHERE id = ?");
    if (!$update_stmt) {
        return [false, 'Failed to prepare ingredient stock update'];
    }

    foreach ($requirements as $ingredient_id => $info) {
        $required = (float) ($info['required'] ?? 0);
        $stock = (float) ($info['stock'] ?? 0);
        if ($required <= 0) {
            continue;
        }
        if ($stock < $required) {
            return [false, 'Insufficient ingredient stock for order'];
        }

        $update_stmt->bind_param('di', $required, $ingredient_id);
        if (!$update_stmt->execute()) {
            return [false, 'Failed to update ingredient stock'];
        }
    }

    $update_stmt->close();
    return [true, ''];
}

function get_product_available_stock($conn, $productId, $fallbackStock = 0, $size = null) {
    $productId = (int) $productId;
    if ($productId <= 0) {
        return (int) $fallbackStock;
    }

    if ($size === null || trim((string) $size) === '') {
        $sizesStmt = $conn->prepare("SELECT DISTINCT size FROM product_ingredients WHERE product_id = ? AND size IS NOT NULL AND size <> ''");
        if ($sizesStmt) {
            $sizesStmt->bind_param('i', $productId);
            if ($sizesStmt->execute()) {
                $sizesResult = $sizesStmt->get_result();
                $sizes = [];
                if ($sizesResult) {
                    while ($sizeRow = $sizesResult->fetch_assoc()) {
                        $sizeValue = trim((string) ($sizeRow['size'] ?? ''));
                        if ($sizeValue !== '') {
                            $sizes[] = $sizeValue;
                        }
                    }
                }
                $sizesStmt->close();

                if (!empty($sizes)) {
                    $computedStocks = [];
                    foreach ($sizes as $recipeSize) {
                        $computedStocks[] = get_product_available_stock($conn, $productId, $fallbackStock, $recipeSize);
                    }
                    return empty($computedStocks) ? (int) $fallbackStock : max(0, (int) min($computedStocks));
                }
            } else {
                $sizesStmt->close();
            }
        }
    }

    $recipeStmt = $conn->prepare("SELECT ingredient_id, quantity_per_unit, unit FROM product_ingredients WHERE product_id = ? AND (size = ? OR size IS NULL OR size = '') ORDER BY CASE WHEN size = ? THEN 0 ELSE 1 END");
    if (!$recipeStmt) {
        return (int) $fallbackStock;
    }

    $sizeValue = trim((string) $size);
    $recipeStmt->bind_param('iss', $productId, $sizeValue, $sizeValue);
    if (!$recipeStmt->execute()) {
        $recipeStmt->close();
        return (int) $fallbackStock;
    }

    $recipeResult = $recipeStmt->get_result();
    if (!$recipeResult || $recipeResult->num_rows === 0) {
        $recipeStmt->close();
        return (int) $fallbackStock;
    }

    $availableStock = null;
    while ($recipeRow = $recipeResult->fetch_assoc()) {
        $ingredientId = (int) ($recipeRow['ingredient_id'] ?? 0);
        $recipeAmount = (float) ($recipeRow['quantity_per_unit'] ?? 0);
        $recipeUnit = $recipeRow['unit'] ?? 'pieces';

        if ($ingredientId <= 0 || $recipeAmount <= 0) {
            continue;
        }

        $ingredientStmt = $conn->prepare("SELECT stock, unit FROM ingredients WHERE id = ?");
        if (!$ingredientStmt) {
            $recipeStmt->close();
            return (int) $fallbackStock;
        }

        $ingredientStmt->bind_param('i', $ingredientId);
        if (!$ingredientStmt->execute()) {
            $ingredientStmt->close();
            $recipeStmt->close();
            return (int) $fallbackStock;
        }

        $ingredientResult = $ingredientStmt->get_result();
        $ingredientRow = $ingredientResult ? $ingredientResult->fetch_assoc() : null;
        $ingredientStmt->close();

        if (!$ingredientRow) {
            $recipeStmt->close();
            return 0;
        }

        $ingredientUnit = $ingredientRow['unit'] ?? 'pieces';
        $requiredPerProduct = convert_unit_amount($recipeAmount, $recipeUnit, $ingredientUnit);
        if ($requiredPerProduct === null || $requiredPerProduct <= 0) {
            $recipeStmt->close();
            return 0;
        }

        $ingredientStock = (float) ($ingredientRow['stock'] ?? 0);
        $possibleFromIngredient = (int) floor($ingredientStock / $requiredPerProduct);
        $availableStock = $availableStock === null ? $possibleFromIngredient : min($availableStock, $possibleFromIngredient);
    }

    $recipeStmt->close();

    if ($availableStock === null) {
        return (int) $fallbackStock;
    }

    return max(0, (int) $availableStock);
}

?>

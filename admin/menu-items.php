<?php
$page_title = "Menu Items";
include 'includes/header.php';

// Ensure stock column exists
ensure_product_stock_column($conn);
ensure_product_archive_columns($conn);

$scope = isset($_GET['scope']) && $_GET['scope'] === 'archived' ? 'archived' : 'active';

if ($scope === 'archived') {
    $productScopeWhere = "WHERE (p.is_archived = 1)";
} else {
    $productScopeWhere = "WHERE (p.is_archived = 0 OR p.is_archived IS NULL)";
}

// Get all products with sales stats
$query = "
    SELECT p.id, p.name, p.description, p.category, p.price_16oz, p.price_22oz, p.stock,
              p.image_url, COALESCE(SUM(oi.quantity), 0) as total_sales,
           COALESCE(SUM(oi.price * oi.quantity), 0) as revenue,
           (SELECT COUNT(*) FROM order_items WHERE product_id = p.id AND DATE_ADD(DATE(created_at), INTERVAL 30 DAY) >= DATE(NOW())) as sales_this_month
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    " . $productScopeWhere . "
    GROUP BY p.id
    ORDER BY revenue DESC
";

$products = $conn->query($query);

// Get categories for filter
$categories_result = $conn->query("SELECT DISTINCT category FROM products WHERE (is_archived = 0 OR is_archived IS NULL) ORDER BY category");
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat['category'];
}

$active_count_result = $conn->query("SELECT COUNT(*) AS count FROM products WHERE is_archived = 0 OR is_archived IS NULL");
$archived_count_result = $conn->query("SELECT COUNT(*) AS count FROM products WHERE is_archived = 1");
$active_count = (int) ($active_count_result->fetch_assoc()['count'] ?? 0);
$archived_count = (int) ($archived_count_result->fetch_assoc()['count'] ?? 0);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 class="page-title">Menu Items</h1>
    <button class="btn-add-item" onclick="document.getElementById('addItemModal').style.display='block'">
        <i class="fas fa-plus"></i> ADD ITEM
    </button>
</div>

<div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
    <a href="?scope=active" class="btn btn-sm <?php echo $scope === 'active' ? '' : 'btn-outline-dark'; ?>" style="<?php echo $scope === 'active' ? 'background-color: #1A0F0A; color: #E8E0D0; border: none;' : 'border-color: #1A0F0A; color: #1A0F0A;'; ?>">
        Active Items (<?php echo $active_count; ?>)
    </a>
    <a href="?scope=archived" class="btn btn-sm <?php echo $scope === 'archived' ? '' : 'btn-outline-dark'; ?>" style="<?php echo $scope === 'archived' ? 'background-color: #6d4c41; color: #fff; border: none;' : 'border-color: #6d4c41; color: #6d4c41;'; ?>">
        Archived Items (<?php echo $archived_count; ?>)
    </a>
</div>

<div class="row">
    <?php
    if ($products->num_rows > 0) {
        while ($product = $products->fetch_assoc()) {
            $avg_price = ($product['price_16oz'] + ($product['price_22oz'] ?? 0)) / 2;
            $growth = $product['sales_this_month'] > 0 ? 8 : -8;
            $growth_class = $growth > 0 ? 'positive' : 'negative';
            
            // Determine stock status
            $stock_status = 'in-stock';
            $stock_color = '#7cb342';
            $stock_label = 'In Stock';
            
            if ($product['stock'] == 0) {
                $stock_status = 'out-of-stock';
                $stock_color = '#d32f2f';
                $stock_label = 'Out of Stock';
            } elseif ($product['stock'] < 5) {
                $stock_status = 'low-stock';
                $stock_color = '#fbc02d';
                $stock_label = 'Low Stock';
            }
            ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="menu-item-card">
                    <div class="menu-item-icon">
                        <i class="fas fa-cup"></i>
                        <span class="stock-badge" style="background-color: <?php echo $stock_color; ?>;">
                            <?php echo htmlspecialchars($stock_label); ?>
                        </span>
                    </div>
                    <div class="menu-item-details">
                        <div class="menu-item-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="menu-item-category"><?php echo htmlspecialchars($product['category']); ?></div>
                        
                        <div class="menu-item-stat">
                            <span class="menu-item-stat-label">Price</span>
                            <span class="menu-item-stat-value">₱<?php echo number_format($avg_price, 0); ?></span>
                        </div>
                        <div class="menu-item-stat">
                            <span class="menu-item-stat-label">Stock</span>
                            <span class="menu-item-stat-value" style="color: <?php echo $stock_color; ?>; font-weight: bold;">
                                <?php echo $product['stock']; ?> units
                            </span>
                        </div>
                        <div class="menu-item-stat">
                            <span class="menu-item-stat-label">Sales</span>
                            <span class="menu-item-stat-value"><?php echo $product['total_sales']; ?></span>
                        </div>
                        <div class="menu-item-stat">
                            <span class="menu-item-stat-label">Revenue</span>
                            <span class="menu-item-stat-value">₱<?php echo number_format($product['revenue'], 0); ?></span>
                        </div>
                        <div class="menu-item-stat">
                            <span class="menu-item-stat-label">Growth</span>
                            <span class="menu-item-stat-value" style="color: <?php echo $growth > 0 ? '#7cb342' : '#d32f2f'; ?>">
                                <i class="fas fa-arrow-<?php echo $growth > 0 ? 'up' : 'down'; ?>"></i> <?php echo abs($growth); ?>%
                            </span>
                        </div>

                        <div style="margin-top: 15px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                            <button class="btn-action btn-edit" title="Edit" onclick="openEditModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', '<?php echo htmlspecialchars($product['category']); ?>', '<?php echo htmlspecialchars($product['description']); ?>', <?php echo $product['price_16oz']; ?>, <?php echo $product['price_22oz']; ?>, <?php echo $product['stock']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-stock" title="Update Stock" onclick="openStockModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['stock']; ?>)">
                                <i class="fas fa-boxes"></i>
                            </button>
                            <?php if ($scope === 'archived'): ?>
                                <button class="btn-action btn-stock" title="Restore" onclick="restoreMenuItem(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-action btn-delete" title="Archive" onclick="archiveMenuItem(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                    <i class="fas fa-box-archive"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-12"><p style="text-align: center; color: #999; padding: 40px;">No menu items yet</p></div>';
    }
    ?>
</div>

<!-- Add Menu Item Modal -->
<div id="addItemModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e0d9cd; padding-bottom: 15px;">
            <h2 style="margin: 0; font-family: 'Anton'; font-size: 1.5rem;">Add New Item</h2>
            <span class="close-modal" onclick="document.getElementById('addItemModal').style.display='none'" style="font-size: 28px; font-weight: bold; cursor: pointer; color: #999;">&times;</span>
        </div>
        <form id="addItemForm" onsubmit="submitAddItem(event)">
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Item Name</label>
                <input type="text" id="addName" name="name" class="form-control" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Category</label>
                <select id="addCategory" name="category" class="form-control" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                    <option value="">Select Category</option>
                    <option value="Fruity">Fruity</option>
                        <option value="Coffee">Coffee</option>
                    <option value="Non-Coffee">Non-Coffee</option>
                        <option value="Fruity">Fruity</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Description</label>
                <textarea id="addDescription" name="description" class="form-control" rows="3" style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;"></textarea>
            </div>
            <div class="row">
                <div class="col-6 form-group mb-3">
                    <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Price (16oz)</label>
                    <input type="number" id="addPrice16" name="price_16oz" class="form-control" step="0.01" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                </div>
                <div class="col-6 form-group mb-3">
                    <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Price (22oz)</label>
                    <input type="number" id="addPrice22" name="price_22oz" class="form-control" step="0.01" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                </div>
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Initial Stock</label>
                <input type="number" id="addStock" name="stock" class="form-control" min="0" value="0" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
            </div>
                    <div class="form-group mb-3">
                        <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Item Image</label>
                        <div id="addImagePreview" style="width: 100%; height: 150px; border: 2px dashed #e0d9cd; border-radius: 5px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background-color: #f9f9f9;">
                            <span style="color: #999;">No image selected</span>
                        </div>
                        <input type="file" id="addImage" name="image" class="form-control" accept="image/*" style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                        <small style="color: #999;">Recommended size: 300x300px. Max size: 5MB</small>
                    </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-sm" style="background-color: #c4a870; color: white; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer; font-weight: bold;">Add Item</button>
                <button type="button" class="btn btn-sm" style="background-color: #f0ebe4; color: #333; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer;" onclick="document.getElementById('addItemModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div id="editItemModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e0d9cd; padding-bottom: 15px;">
            <h2 style="margin: 0; font-family: 'Anton'; font-size: 1.5rem;">Edit Item</h2>
            <span class="close-modal" onclick="document.getElementById('editItemModal').style.display='none'" style="font-size: 28px; font-weight: bold; cursor: pointer; color: #999;">&times;</span>
        </div>
        <form id="editItemForm" onsubmit="submitEditItem(event)">
            <input type="hidden" id="editId" name="id">
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Item Name</label>
                <input type="text" id="editName" name="name" class="form-control" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Category</label>
                <select id="editCategory" name="category" class="form-control" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                    <option value="">Select Category</option>
                    <option value="Fruity">Fruity</option>
                        <option value="Coffee">Coffee</option>
                    <option value="Non-Coffee">Non-Coffee</option>
                        <option value="Fruity">Fruity</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Description</label>
                <textarea id="editDescription" name="description" class="form-control" rows="3" style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;"></textarea>
            </div>
            <div class="row">
                <div class="col-6 form-group mb-3">
                    <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Price (16oz)</label>
                    <input type="number" id="editPrice16" name="price_16oz" class="form-control" step="0.01" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                </div>
                <div class="col-6 form-group mb-3">
                    <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Price (22oz)</label>
                    <input type="number" id="editPrice22" name="price_22oz" class="form-control" step="0.01" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                </div>
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Stock</label>
                <input type="number" id="editStock" name="stock" class="form-control" min="0" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
            </div>
                    <div class="form-group mb-3">
                        <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Item Image</label>
                        <div id="editImagePreview" style="width: 100%; height: 150px; border: 2px dashed #e0d9cd; border-radius: 5px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background-color: #f9f9f9;">
                            <span style="color: #999;">No image selected</span>
                        </div>
                        <input type="file" id="editImage" name="image" class="form-control" accept="image/*" style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
                        <small style="color: #999;">Recommended size: 300x300px. Max size: 5MB</small>
                    </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-sm" style="background-color: #c4a870; color: white; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer; font-weight: bold;">Update Item</button>
                <button type="button" class="btn btn-sm" style="background-color: #f0ebe4; color: #333; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer;" onclick="document.getElementById('editItemModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="stockModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 20% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e0d9cd; padding-bottom: 15px;">
            <h2 style="margin: 0; font-family: 'Anton'; font-size: 1.5rem;">Update Stock</h2>
            <span class="close-modal" onclick="document.getElementById('stockModal').style.display='none'" style="font-size: 28px; font-weight: bold; cursor: pointer; color: #999;">&times;</span>
        </div>
        <form id="stockForm" onsubmit="submitStockUpdate(event)">
            <input type="hidden" id="stockItemId" name="id">
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Item: <span id="stockItemName"></span></label>
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">Current Stock: <span id="currentStock" style="color: #c4a870; font-weight: bold;"></span></label>
            </div>
            <div class="form-group mb-3">
                <label style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">New Stock Quantity</label>
                <input type="number" id="newStock" name="stock" class="form-control" min="0" required style="padding: 10px; border: 1px solid #e0d9cd; border-radius: 5px;">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-sm" style="background-color: #c4a870; color: white; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer; font-weight: bold;">Update Stock</button>
                <button type="button" class="btn btn-sm" style="background-color: #f0ebe4; color: #333; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer;" onclick="document.getElementById('stockModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Close modal when clicking outside
window.onclick = function(event) {
    let addModal = document.getElementById("addItemModal");
    let editModal = document.getElementById("editItemModal");
    let stockModal = document.getElementById("stockModal");
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
    if (event.target == stockModal) {
        stockModal.style.display = "none";
    }
}

// Open Edit Modal
function openEditModal(id, name, category, description, price16, price22, stock) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editCategory').value = category;
    document.getElementById('editDescription').value = description;
    document.getElementById('editPrice16').value = price16;
    document.getElementById('editPrice22').value = price22;
    document.getElementById('editStock').value = stock;
    document.getElementById('editItemModal').style.display = 'block';
}

// Handle image preview for add modal
document.addEventListener('DOMContentLoaded', function() {
    const addImageInput = document.getElementById('addImage');
    const addImagePreview = document.getElementById('addImagePreview');
    
    if (addImageInput) {
        addImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    addImagePreview.innerHTML = '<img src="' + event.target.result + '" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    const editImageInput = document.getElementById('editImage');
    const editImagePreview = document.getElementById('editImagePreview');
    
    if (editImageInput) {
        editImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    editImagePreview.innerHTML = '<img src="' + event.target.result + '" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Open Stock Update Modal
function openStockModal(id, name, currentStock) {
    document.getElementById('stockItemId').value = id;
    document.getElementById('stockItemName').textContent = name;
    document.getElementById('currentStock').textContent = currentStock + ' units';
    document.getElementById('newStock').value = currentStock;
    document.getElementById('stockModal').style.display = 'block';
    // Focus on input and select all
    setTimeout(() => {
        document.getElementById('newStock').focus();
        document.getElementById('newStock').select();
    }, 100);
}

// Submit Add Item
function submitAddItem(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('addItemForm'));
    formData.append('action', 'add-menu-item');

    fetch('api.php?action=add-menu-item', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Menu item added successfully!',
                confirmButtonColor: '#c4a870'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Unknown error occurred',
                confirmButtonColor: '#c4a870'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error adding item',
            confirmButtonColor: '#c4a870'
        });
    });
}

// Submit Edit Item
function submitEditItem(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('editItemForm'));

    fetch('api.php?action=edit-menu-item', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Menu item updated successfully!',
                confirmButtonColor: '#c4a870'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Unknown error occurred',
                confirmButtonColor: '#c4a870'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error updating item',
            confirmButtonColor: '#c4a870'
        });
    });
}

// Submit Stock Update
function submitStockUpdate(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('stockForm'));

    fetch('api.php?action=update-stock', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Stock updated successfully!',
                confirmButtonColor: '#c4a870'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Unknown error occurred',
                confirmButtonColor: '#c4a870'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error updating stock',
            confirmButtonColor: '#c4a870'
        });
    });
}

// Archive Menu Item
function archiveMenuItem(id, name) {
    Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        text: `Archive "${name}"? You can restore it later.`,
        showCancelButton: true,
        confirmButtonColor: '#d32f2f',
        cancelButtonColor: '#999',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('api.php?action=delete-menu-item', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archived!',
                        text: 'Menu item moved to archive successfully!',
                        confirmButtonColor: '#c4a870'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Unknown error occurred',
                        confirmButtonColor: '#c4a870'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error deleting item',
                    confirmButtonColor: '#c4a870'
                });
            });
        }
    });
}

// Restore Menu Item
function restoreMenuItem(id, name) {
    Swal.fire({
        icon: 'question',
        title: 'Restore item?',
        text: `Restore "${name}" to active items?`,
        showCancelButton: true,
        confirmButtonColor: '#1A0F0A',
        cancelButtonColor: '#999',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('api.php?action=restore-menu-item', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Restored!',
                        text: 'Menu item restored successfully!',
                        confirmButtonColor: '#c4a870'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Unknown error occurred',
                        confirmButtonColor: '#c4a870'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error restoring item',
                    confirmButtonColor: '#c4a870'
                });
            });
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>

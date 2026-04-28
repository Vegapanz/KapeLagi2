<?php
$page_title = "Menu Items";
include 'includes/header.php';

// Get all products with sales stats
$query = "
    SELECT p.id, p.name, p.description, p.category, p.price_16oz, p.price_22oz,
           COALESCE(SUM(oi.quantity), 0) as total_sales,
           COALESCE(SUM(oi.price * oi.quantity), 0) as revenue,
           (SELECT COUNT(*) FROM order_items WHERE product_id = p.id AND DATE_ADD(DATE(created_at), INTERVAL 30 DAY) >= DATE(NOW())) as sales_this_month
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY revenue DESC
";

$products = $conn->query($query);

// Get categories for filter
$categories_result = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat['category'];
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 class="page-title">Menu Items</h1>
    <button class="btn-add-item" onclick="document.getElementById('addItemModal').style.display='block'">
        <i class="fas fa-plus"></i> ADD ITEM
    </button>
</div>

<div class="row">
    <?php
    if ($products->num_rows > 0) {
        while ($product = $products->fetch_assoc()) {
            $avg_price = ($product['price_16oz'] + ($product['price_22oz'] ?? 0)) / 2;
            $growth = $product['sales_this_month'] > 0 ? 8 : -8;
            $growth_class = $growth > 0 ? 'positive' : 'negative';
            ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="menu-item-card">
                    <div class="menu-item-icon">
                        <i class="fas fa-cup"></i>
                    </div>
                    <div class="menu-item-details">
                        <div class="menu-item-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="menu-item-category"><?php echo htmlspecialchars($product['category']); ?></div>
                        
                        <div class="menu-item-stat">
                            <span class="menu-item-stat-label">Price</span>
                            <span class="menu-item-stat-value">₱<?php echo number_format($avg_price, 0); ?></span>
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

                        <div style="margin-top: 15px; display: flex; gap: 8px; justify-content: center;">
                            <button class="btn-action btn-edit" title="Edit" onclick="openEditModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', '<?php echo htmlspecialchars($product['category']); ?>', '<?php echo htmlspecialchars($product['description']); ?>', <?php echo $product['price_16oz']; ?>, <?php echo $product['price_22oz']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" title="Delete" onclick="deleteMenuItem(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
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
                    <option value="Espresso Drinks">Espresso Drinks</option>
                    <option value="Cold Brew">Cold Brew</option>
                    <option value="Pastries">Pastries</option>
                    <option value="Specialty">Specialty</option>
                    <option value="Fruity">Fruity</option>
                    <option value="Non-Coffee">Non-Coffee</option>
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
                    <option value="Espresso Drinks">Espresso Drinks</option>
                    <option value="Cold Brew">Cold Brew</option>
                    <option value="Pastries">Pastries</option>
                    <option value="Specialty">Specialty</option>
                    <option value="Fruity">Fruity</option>
                    <option value="Non-Coffee">Non-Coffee</option>
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
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-sm" style="background-color: #c4a870; color: white; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer; font-weight: bold;">Update Item</button>
                <button type="button" class="btn btn-sm" style="background-color: #f0ebe4; color: #333; border: none; padding: 10px 20px; border-radius: 5px; flex: 1; cursor: pointer;" onclick="document.getElementById('editItemModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Close modal when clicking outside
window.onclick = function(event) {
    let addModal = document.getElementById("addItemModal");
    let editModal = document.getElementById("editItemModal");
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
}

// Open Edit Modal
function openEditModal(id, name, category, description, price16, price22) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editCategory').value = category;
    document.getElementById('editDescription').value = description;
    document.getElementById('editPrice16').value = price16;
    document.getElementById('editPrice22').value = price22;
    document.getElementById('editItemModal').style.display = 'block';
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
            alert('Menu item added successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item');
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
            alert('Menu item updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating item');
    });
}

// Delete Menu Item
function deleteMenuItem(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"?`)) {
        const formData = new FormData();
        formData.append('id', id);

        fetch('api.php?action=delete-menu-item', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Menu item deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting item');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>

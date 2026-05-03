<?php
$page_title = "Orders";
include 'includes/header.php';
ensure_order_archive_columns($conn);

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = trim($_GET['search'] ?? '');
$scope = isset($_GET['scope']) && $_GET['scope'] === 'archived' ? 'archived' : 'active';
$current_page = max(1, intval($_GET['page'] ?? 1));
$orders_per_page = max(1, intval($_GET['per_page'] ?? 5));
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'archive-all-orders') {
        $archive_sql = "UPDATE orders SET is_archived = 1, archived_at = NOW() WHERE is_archived = 0 OR is_archived IS NULL";
        if ($conn->query($archive_sql)) {
            $success_message = 'All active orders have been archived. They were not deleted.';
        }
    }
}

$allowed_page_sizes = [5, 10, 20, 50];
if (!in_array($orders_per_page, $allowed_page_sizes, true)) {
    $orders_per_page = 5;
}

// Build query
$base_query = "
    SELECT o.id, o.customer_name, o.customer_email, GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as items,
           o.total_amount, o.status, o.created_at, o.delivery_address
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
";

$where_clause = '';
$where_parts = [];

if ($scope === 'archived') {
    $where_parts[] = "o.is_archived = 1";
} else {
    $where_parts[] = "(o.is_archived = 0 OR o.is_archived IS NULL)";
}

if ($status_filter !== 'all') {
    $where_parts[] = "o.status = '" . $conn->real_escape_string($status_filter) . "'";
}

if ($search_query !== '') {
    $escaped_search = $conn->real_escape_string($search_query);
    $search_parts = [
        "o.customer_name LIKE '%{$escaped_search}%'",
        "o.customer_email LIKE '%{$escaped_search}%'",
        "CAST(o.id AS CHAR) LIKE '%{$escaped_search}%'",
        "p.name LIKE '%{$escaped_search}%'"];
    $where_parts[] = '(' . implode(' OR ', $search_parts) . ')';
}

if (!empty($where_parts)) {
    $where_clause = ' WHERE ' . implode(' AND ', $where_parts);
}

$count_query = "
    SELECT COUNT(*) as total
    FROM (
        SELECT o.id
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        " . $where_clause . "
        GROUP BY o.id
    ) as grouped_orders
";

$total_orders = (int) ($conn->query($count_query)->fetch_assoc()['total'] ?? 0);
$total_pages = max(1, (int) ceil($total_orders / $orders_per_page));

if ($current_page > $total_pages) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $orders_per_page;

$query = $base_query . $where_clause . " GROUP BY o.id ORDER BY o.created_at DESC LIMIT $orders_per_page OFFSET $offset";

$orders = $conn->query($query);

// Get status counts
$active_orders_total = (int) ($conn->query("SELECT COUNT(*) as count FROM orders WHERE is_archived = 0 OR is_archived IS NULL")->fetch_assoc()['count'] ?? 0);
$archived_orders_total = (int) ($conn->query("SELECT COUNT(*) as count FROM orders WHERE is_archived = 1")->fetch_assoc()['count'] ?? 0);
$count_scope_condition = $scope === 'archived' ? 'is_archived = 1' : '(is_archived = 0 OR is_archived IS NULL)';
$pending = $conn->query("SELECT COUNT(*) as count FROM orders WHERE $count_scope_condition AND status = 'pending'")->fetch_assoc()['count'];
$processing = $conn->query("SELECT COUNT(*) as count FROM orders WHERE $count_scope_condition AND status = 'processing'")->fetch_assoc()['count'];
$completed = $conn->query("SELECT COUNT(*) as count FROM orders WHERE $count_scope_condition AND status = 'completed'")->fetch_assoc()['count'];
$cancelled = $conn->query("SELECT COUNT(*) as count FROM orders WHERE $count_scope_condition AND status = 'cancelled'")->fetch_assoc()['count'];
?>

<h1 class="page-title">All Orders</h1>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="?scope=active&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_query); ?>&per_page=<?php echo $orders_per_page; ?>&page=1"
       class="btn <?php echo $scope === 'active' ? '' : 'btn-outline-dark'; ?>"
       style="<?php echo $scope === 'active' ? 'background-color: #1A0F0A; color: #E8E0D0; border: none;' : 'border-color: #1A0F0A; color: #1A0F0A;'; ?>">
        Active Orders (<?php echo $active_orders_total; ?>)
    </a>
    <a href="?scope=archived&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_query); ?>&per_page=<?php echo $orders_per_page; ?>&page=1"
       class="btn <?php echo $scope === 'archived' ? '' : 'btn-outline-dark'; ?>"
       style="<?php echo $scope === 'archived' ? 'background-color: #6d4c41; color: #fff; border: none;' : 'border-color: #6d4c41; color: #6d4c41;'; ?>">
        Archived Orders (<?php echo $archived_orders_total; ?>)
    </a>
</div>

<?php if ($success_message !== ''): ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<!-- Search and Filter Controls -->
<form method="get" class="row g-3 align-items-end mb-3">
    <div class="col-md-6 col-lg-3">
        <label class="form-label" for="orderSearch">Search</label>
        <input type="text" class="form-control" id="orderSearch" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search customer, email, order ID, item">
    </div>
    <div class="col-md-6 col-lg-3">
        <label class="form-label" for="orderStatusFilter">Status</label>
        <select class="form-select" id="orderStatusFilter" name="status">
            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status (<?php echo $pending + $processing + $completed + $cancelled; ?>)</option>
            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed (<?php echo $completed; ?>)</option>
            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing (<?php echo $processing; ?>)</option>
            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending (<?php echo $pending; ?>)</option>
            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled (<?php echo $cancelled; ?>)</option>
        </select>
    </div>
    <div class="col-md-4 col-lg-2">
        <label class="form-label" for="orderScopeFilter">View</label>
        <select class="form-select" id="orderScopeFilter" name="scope">
            <option value="active" <?php echo $scope === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="archived" <?php echo $scope === 'archived' ? 'selected' : ''; ?>>Archived</option>
        </select>
    </div>
    <div class="col-md-4 col-lg-2">
        <label class="form-label" for="ordersPerPage">Show</label>
        <select class="form-select" id="ordersPerPage" name="per_page">
            <?php foreach ($allowed_page_sizes as $page_size): ?>
                <option value="<?php echo $page_size; ?>" <?php echo $orders_per_page === $page_size ? 'selected' : ''; ?>><?php echo $page_size; ?> per page</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4 col-lg-2 d-grid">
        <input type="hidden" name="page" value="1">
        <button type="submit" class="btn" style="background-color: #1A0F0A; color: #E8E0D0;">Apply</button>
    </div>
</form>

<?php if ($scope === 'active' && ($pending + $processing + $completed + $cancelled) > 0): ?>
    <form method="post" class="mb-3" onsubmit="return confirm('Archive all active orders? This will hide them from the active list but keep them in archived records.');">
        <input type="hidden" name="action" value="archive-all-orders">
        <button type="submit" class="btn" style="background-color: #b42318; color: #fff; border: none;">Archive All Orders</button>
    </form>
<?php endif; ?>

<div class="section-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($orders->num_rows > 0) {
                while ($order = $orders->fetch_assoc()) {
                    $status_class = 'pending';
                    if ($order['status'] === 'completed') $status_class = 'completed';
                    if ($order['status'] === 'processing') $status_class = 'processing';
                    if ($order['status'] === 'cancelled') $status_class = 'cancelled';
                    
                    echo '<tr>
                            <td><strong>#' . htmlspecialchars($order['id']) . '</strong></td>
                            <td>' . htmlspecialchars($order['customer_name']) . '</td>
                            <td><small>' . htmlspecialchars(substr($order['items'] ?? '', 0, 40)) . '</small></td>
                            <td>₱' . number_format($order['total_amount'], 2) . '</td>
                            <td>
                                <select class="form-select form-select-sm" style="max-width: 150px; border: 1px solid #e0d9cd; border-radius: 5px; padding: 5px; cursor: pointer;" onchange="handleOrderStatusChange(this, ' . $order['id'] . ')">
                                    <option value="pending" ' . ($order['status'] === 'pending' ? 'selected' : '') . '>Pending</option>
                                    <option value="processing" ' . ($order['status'] === 'processing' ? 'selected' : '') . '>Processing</option>
                                    <option value="completed" ' . ($order['status'] === 'completed' ? 'selected' : '') . '>Completed</option>
                                    <option value="cancelled" ' . ($order['status'] === 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                                </select>
                            </td>
                            <td><small>' . date('M d, Y H:i', strtotime($order['created_at'])) . '</small></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="order-details.php?id=' . $order['id'] . '" class="btn-action btn-edit" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="7" style="text-align: center; padding: 40px;">No orders found</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
    <nav aria-label="Orders pagination" style="margin-top: 24px;">
        <ul class="pagination">
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_query); ?>&scope=<?php echo urlencode($scope); ?>&per_page=<?php echo $orders_per_page; ?>&page=<?php echo max(1, $current_page - 1); ?>">Previous</a>
            </li>

            <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                <li class="page-item <?php echo $page === $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_query); ?>&scope=<?php echo urlencode($scope); ?>&per_page=<?php echo $orders_per_page; ?>&page=<?php echo $page; ?>"><?php echo $page; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_query); ?>&scope=<?php echo urlencode($scope); ?>&per_page=<?php echo $orders_per_page; ?>&page=<?php echo min($total_pages, $current_page + 1); ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<div id="cancelOrderModal" class="cancel-order-overlay" aria-hidden="true">
    <div class="cancel-order-dialog" role="dialog" aria-modal="true" aria-labelledby="cancelOrderTitle">
        <div class="cancel-order-content">
            <div class="cancel-order-header">
                <h5 id="cancelOrderTitle" class="cancel-order-title">Cancel Order</h5>
                <button type="button" class="cancel-order-close" id="cancelOrderCloseBtn" aria-label="Close">&times;</button>
            </div>
            <div class="cancel-order-body">
                <p class="cancel-order-copy">Add a note explaining why this order is being cancelled. The customer will see this message.</p>
                <label for="cancelOrderNote" class="cancel-order-label">Cancellation Note</label>
                <textarea id="cancelOrderNote" class="cancel-order-input" placeholder="Enter the reason for cancellation..."></textarea>
                <div id="cancelOrderNoteError" class="cancel-order-error">Please enter a cancellation note.</div>
            </div>
            <div class="cancel-order-footer">
                <button type="button" class="cancel-order-secondary" id="cancelOrderDismissBtn">Close</button>
                <button type="button" class="cancel-order-primary" id="confirmCancelOrderBtn">Save Cancellation</button>
            </div>
        </div>
    </div>
</div>

<style>
.cancel-order-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 4000;
    background: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.cancel-order-overlay.is-open {
    display: flex;
}

.cancel-order-dialog {
    width: min(100%, 560px);
}

.cancel-order-content {
    background: #E8E0D0;
    border: 1px solid rgba(161, 124, 92, 0.35);
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    overflow: hidden;
}

.cancel-order-header,
.cancel-order-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px;
}

.cancel-order-header {
    border-bottom: 1px solid rgba(161, 124, 92, 0.2);
}

.cancel-order-footer {
    border-top: 1px solid rgba(161, 124, 92, 0.2);
    justify-content: flex-end;
}

.cancel-order-title {
    margin: 0;
    font-family: Anton, sans-serif;
    letter-spacing: 0.5px;
}

.cancel-order-close {
    border: 0;
    background: transparent;
    font-size: 2rem;
    line-height: 1;
    color: #1A0F0A;
    cursor: pointer;
}

.cancel-order-body {
    padding: 20px 24px;
}

.cancel-order-copy {
    margin-bottom: 14px;
    color: #1A0F0A;
}

.cancel-order-label {
    display: block;
    margin-bottom: 8px;
    color: #1A0F0A;
    font-weight: 600;
}

.cancel-order-input {
    width: 100%;
    min-height: 120px;
    resize: vertical;
    border: 1px solid #e0d9cd;
    border-radius: 8px;
    padding: 12px;
    font: inherit;
    background: #fff;
    color: #1A0F0A;
}

.cancel-order-input:focus {
    outline: none;
    border-color: #A17C5C;
    box-shadow: 0 0 0 3px rgba(161, 124, 92, 0.12);
}

.cancel-order-error {
    display: none;
    margin-top: 10px;
    color: #b42318;
}

.cancel-order-secondary,
.cancel-order-primary {
    border: 0;
    border-radius: 8px;
    padding: 10px 18px;
    cursor: pointer;
    font-family: inherit;
}

.cancel-order-secondary {
    background: #6c757d;
    color: #fff;
}

.cancel-order-primary {
    background: #1A0F0A;
    color: #E8E0D0;
    margin-left: 10px;
}
</style>

<script>
// Update Order Status
const cancelOrderModal = document.getElementById('cancelOrderModal');
const cancelOrderNote = document.getElementById('cancelOrderNote');
const cancelOrderNoteError = document.getElementById('cancelOrderNoteError');
const confirmCancelOrderBtn = document.getElementById('confirmCancelOrderBtn');
const cancelOrderCloseBtn = document.getElementById('cancelOrderCloseBtn');
const cancelOrderDismissBtn = document.getElementById('cancelOrderDismissBtn');
let pendingCancelContext = null;

function openCancelOrderModal() {
    cancelOrderModal.classList.add('is-open');
    cancelOrderModal.setAttribute('aria-hidden', 'false');
    setTimeout(() => {
        cancelOrderNote.focus();
    }, 0);
}

function closeCancelOrderModal() {
    cancelOrderModal.classList.remove('is-open');
    cancelOrderModal.setAttribute('aria-hidden', 'true');
    cancelOrderNote.value = '';
    cancelOrderNoteError.style.display = 'none';
}

function updateOrderStatus(orderId, status, selectElement, cancellationNote = '') {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);

    if (status === 'cancelled') {
        formData.append('cancellation_note', cancellationNote.trim());
    }

    fetch('api.php?action=update-order-status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Flash effect to show change
            const row = selectElement ? selectElement.closest('tr') : null;
            if (selectElement) {
                selectElement.setAttribute('data-previous-status', status);
            }
            if (row) {
                row.style.backgroundColor = '#ffffcc';
                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 1000);
            }
        } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
            if (selectElement) {
                selectElement.value = selectElement.getAttribute('data-previous-status') || 'pending';
            }
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
        if (selectElement) {
            selectElement.value = selectElement.getAttribute('data-previous-status') || 'pending';
        }
        location.reload();
    });
}

function handleOrderStatusChange(selectElement, orderId) {
    const previousStatus = selectElement.getAttribute('data-previous-status') || selectElement.value;
    const newStatus = selectElement.value;

    if (newStatus === 'cancelled') {
        pendingCancelContext = { orderId, selectElement };
        openCancelOrderModal();
        return;
    }

    updateOrderStatus(orderId, newStatus, selectElement);
}

document.querySelectorAll('select.form-select.form-select-sm').forEach(selectElement => {
    selectElement.setAttribute('data-previous-status', selectElement.value);
    selectElement.addEventListener('focus', () => {
        selectElement.setAttribute('data-previous-status', selectElement.value);
    });
});

confirmCancelOrderBtn.addEventListener('click', () => {
    if (!pendingCancelContext) {
        return;
    }

    const note = cancelOrderNote.value.trim();
    if (!note) {
        cancelOrderNoteError.style.display = 'block';
        return;
    }

    cancelOrderNoteError.style.display = 'none';
    const { orderId, selectElement } = pendingCancelContext;
    updateOrderStatus(orderId, 'cancelled', selectElement, note);
    pendingCancelContext = null;
    closeCancelOrderModal();
});

cancelOrderCloseBtn.addEventListener('click', () => {
    if (pendingCancelContext) {
        pendingCancelContext.selectElement.value = pendingCancelContext.selectElement.getAttribute('data-previous-status') || 'pending';
        pendingCancelContext = null;
    }
    closeCancelOrderModal();
});

cancelOrderDismissBtn.addEventListener('click', () => {
    if (pendingCancelContext) {
        pendingCancelContext.selectElement.value = pendingCancelContext.selectElement.getAttribute('data-previous-status') || 'pending';
        pendingCancelContext = null;
    }
    closeCancelOrderModal();
});

cancelOrderModal.addEventListener('click', (event) => {
    if (event.target === cancelOrderModal) {
        if (pendingCancelContext) {
            pendingCancelContext.selectElement.value = pendingCancelContext.selectElement.getAttribute('data-previous-status') || 'pending';
            pendingCancelContext = null;
        }
        closeCancelOrderModal();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && cancelOrderModal.classList.contains('is-open')) {
        if (pendingCancelContext) {
            pendingCancelContext.selectElement.value = pendingCancelContext.selectElement.getAttribute('data-previous-status') || 'pending';
            pendingCancelContext = null;
        }
        closeCancelOrderModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>

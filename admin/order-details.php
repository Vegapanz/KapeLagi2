<?php
$page_title = "Order Details";
include 'includes/header.php';
ensure_order_cancellation_reason_column($conn);

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);
$items_per_page = 5;
$current_page = max(1, intval($_GET['page'] ?? 1));

// Get order details
$order = $conn->query("
    SELECT * FROM orders WHERE id = $order_id
")->fetch_assoc();

if (!$order) {
    echo '<div class="alert alert-danger">Order not found</div>';
    include 'includes/footer.php';
    exit;
}

// Get order items with pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) as total_items FROM order_items WHERE order_id = ?");
$count_stmt->bind_param("i", $order_id);
$count_stmt->execute();
$total_items = (int) ($count_stmt->get_result()->fetch_assoc()['total_items'] ?? 0);
$total_pages = max(1, (int) ceil($total_items / $items_per_page));

if ($current_page > $total_pages) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $items_per_page;
$items_stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
    LIMIT ? OFFSET ?
");
$items_stmt->bind_param("iii", $order_id, $items_per_page, $offset);
$items_stmt->execute();
$items = $items_stmt->get_result();

$status_class = 'pending';
if ($order['status'] === 'completed') $status_class = 'completed';
if ($order['status'] === 'processing') $status_class = 'processing';
if ($order['status'] === 'cancelled') $status_class = 'cancelled';
?>

<h1 class="page-title">Order #<?php echo $order_id; ?></h1>

<div class="row mb-4">
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Order Items</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($item = $items->fetch_assoc()) {
                        $total = $item['price'] * $item['quantity'];
                        echo '<tr>
                                <td>' . htmlspecialchars($item['product_name']) . '</td>
                                <td>' . htmlspecialchars($item['size']) . '</td>
                                <td>₱' . number_format($item['price'], 2) . '</td>
                                <td>' . $item['quantity'] . '</td>
                                <td>₱' . number_format($total, 2) . '</td>
                              </tr>';
                    }
                    ?>
                </tbody>
            </table>

            <?php if ($total_items > $items_per_page): ?>
                <nav aria-label="Order items pagination" class="mt-4">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?id=<?php echo $order_id; ?>&page=<?php echo max(1, $current_page - 1); ?>">Previous</a>
                        </li>
                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                            <li class="page-item <?php echo $page === $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $order_id; ?>&page=<?php echo $page; ?>"><?php echo $page; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?id=<?php echo $order_id; ?>&page=<?php echo min($total_pages, $current_page + 1); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Order Summary</h3>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Customer Name</label>
                <p><?php echo htmlspecialchars($order['customer_name']); ?></p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Customer Email</label>
                <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Delivery Address</label>
                <p><?php echo htmlspecialchars($order['delivery_address'] ?? 'Not provided'); ?></p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: #999; font-size: 0.9rem;">Status</label>
                <p>
                    <select id="orderStatus" class="form-select" style="max-width: 200px; border: 1px solid #e0d9cd; border-radius: 5px; padding: 8px;" onchange="handleOrderStatusChange(<?php echo $order_id; ?>, this.value)">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </p>
                <button type="button" id="cancelOrderButton" class="btn btn-sm" style="margin-top: 10px; background-color: #b42318; color: #fff; border: none; padding: 8px 14px;">
                    Cancel Order
                </button>
            </div>

            <div id="cancellationNoteGroup" style="margin-bottom: 20px; <?php echo $order['status'] === 'cancelled' ? '' : 'display: none;'; ?>">
                <label style="color: #999; font-size: 0.9rem;">Cancellation Note</label>
                <textarea id="cancellationNote" class="form-control" style="min-height: 110px; border: 1px solid #e0d9cd; border-radius: 5px; padding: 10px; margin-bottom: 10px;" placeholder="Explain why this order was cancelled for the customer."><?php echo htmlspecialchars($order['cancellation_reason'] ?? ''); ?></textarea>
                <small style="color: #999; display: block; margin-bottom: 10px;">This note will be shown to the customer when the order is cancelled.</small>
                <button type="button" class="btn btn-sm" style="background-color: #1A0F0A; color: #E8E0D0; border: none;" onclick="saveOrderUpdate(<?php echo $order_id; ?>)">Save Cancellation Note</button>
            </div>

            <div style="border-top: 2px solid #e0d9cd; padding-top: 15px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Subtotal</span>
                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem;">
                    <span>Total</span>
                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div>
                <label style="color: #999; font-size: 0.9rem;">Date</label>
                <p><?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Delivery Location Map</h3>
            </div>
            <div id="orderMap" style="height: 400px; border-radius: 8px; overflow: hidden;"></div>
            <div style="padding: 20px 0; border-top: 1px solid #e0d9cd; margin-top: 15px;">
                <div style="margin-bottom: 15px;">
                    <label style="color: #999; font-size: 0.9rem;">Distance to Store</label>
                    <p id="routeDistance" style="font-size: 1.1rem; font-weight: bold;">Calculating...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="cancelOrderDialog" class="cancel-order-overlay" aria-hidden="true">
    <div class="cancel-order-dialog" role="dialog" aria-modal="true" aria-labelledby="cancelOrderDialogTitle">
        <div class="cancel-order-content">
            <div class="cancel-order-header">
                <h5 id="cancelOrderDialogTitle" class="cancel-order-title">Cancel Order</h5>
                <button type="button" class="cancel-order-close" id="cancelOrderDialogClose" aria-label="Close">&times;</button>
            </div>
            <div class="cancel-order-body">
                <p class="cancel-order-copy">Add a note explaining why this order is being cancelled. The customer will see this message.</p>
                <label for="cancelOrderReason" class="cancel-order-label">Cancellation Note</label>
                <textarea id="cancelOrderReason" class="cancel-order-input" placeholder="Enter the reason for cancellation..."></textarea>
                <div id="cancelOrderReasonError" class="cancel-order-error">Please enter a cancellation note.</div>
            </div>
            <div class="cancel-order-footer">
                <button type="button" class="cancel-order-secondary" id="cancelOrderDialogDismiss">Close</button>
                <button type="button" class="cancel-order-primary" id="cancelOrderConfirm">Save Cancellation</button>
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
function toggleCancellationNoteGroup(status) {
    const noteGroup = document.getElementById('cancellationNoteGroup');
    if (!noteGroup) {
        return;
    }

    noteGroup.style.display = status === 'cancelled' ? '' : 'none';
}

function saveOrderUpdate(orderId) {
    const statusSelect = document.getElementById('orderStatus');
    const status = statusSelect.value;
    const cancellationNoteField = document.getElementById('cancellationNote');
    const cancellationNote = cancellationNoteField ? cancellationNoteField.value.trim() : '';

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);

    if (status === 'cancelled') {
        formData.append('cancellation_note', cancellationNote);
    }

    fetch('api.php?action=update-order-status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusSelect.style.backgroundColor = '#c8e6c9';
            setTimeout(() => {
                statusSelect.style.backgroundColor = '';
            }, 1000);
        } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
        location.reload();
    });
}

function handleOrderStatusChange(orderId, status) {
    const statusSelect = document.getElementById('orderStatus');
    const previousStatus = statusSelect.getAttribute('data-previous-status') || statusSelect.value;

    if (status === 'cancelled') {
        openCancelOrderDialog(orderId, previousStatus);
        return;
    }

    toggleCancellationNoteGroup(status);
    saveOrderUpdate(orderId);
}

const cancelOrderDialog = document.getElementById('cancelOrderDialog');
const cancelOrderReason = document.getElementById('cancelOrderReason');
const cancelOrderReasonError = document.getElementById('cancelOrderReasonError');
const cancelOrderButton = document.getElementById('cancelOrderButton');
const cancelOrderConfirm = document.getElementById('cancelOrderConfirm');
const cancelOrderDialogClose = document.getElementById('cancelOrderDialogClose');
const cancelOrderDialogDismiss = document.getElementById('cancelOrderDialogDismiss');
let pendingCancelOrderId = <?php echo $order_id; ?>;
let pendingPreviousStatus = document.getElementById('orderStatus') ? document.getElementById('orderStatus').value : 'pending';

function openCancelOrderDialog(orderId, previousStatus) {
    pendingCancelOrderId = orderId;
    pendingPreviousStatus = previousStatus || pendingPreviousStatus;
    cancelOrderReason.value = document.getElementById('cancellationNote') ? document.getElementById('cancellationNote').value.trim() : '';
    cancelOrderReasonError.style.display = 'none';
    cancelOrderDialog.classList.add('is-open');
    cancelOrderDialog.setAttribute('aria-hidden', 'false');
    setTimeout(() => cancelOrderReason.focus(), 0);
}

function closeCancelOrderDialog() {
    cancelOrderDialog.classList.remove('is-open');
    cancelOrderDialog.setAttribute('aria-hidden', 'true');
    cancelOrderReasonError.style.display = 'none';
}

function revertPendingStatus() {
    const statusSelect = document.getElementById('orderStatus');
    if (statusSelect) {
        statusSelect.value = pendingPreviousStatus || 'pending';
    }
}

cancelOrderButton.addEventListener('click', () => {
    openCancelOrderDialog(<?php echo $order_id; ?>, document.getElementById('orderStatus').value);
});

cancelOrderConfirm.addEventListener('click', () => {
    const note = cancelOrderReason.value.trim();
    if (!note) {
        cancelOrderReasonError.style.display = 'block';
        return;
    }

    const statusSelect = document.getElementById('orderStatus');
    const cancellationNoteField = document.getElementById('cancellationNote');
    statusSelect.value = 'cancelled';
    statusSelect.setAttribute('data-previous-status', 'cancelled');
    if (cancellationNoteField) {
        cancellationNoteField.value = note;
    }

    toggleCancellationNoteGroup('cancelled');
    saveOrderUpdate(pendingCancelOrderId);
    closeCancelOrderDialog();
});

cancelOrderDialogClose.addEventListener('click', () => {
    revertPendingStatus();
    closeCancelOrderDialog();
});

cancelOrderDialogDismiss.addEventListener('click', () => {
    revertPendingStatus();
    closeCancelOrderDialog();
});

cancelOrderDialog.addEventListener('click', (event) => {
    if (event.target === cancelOrderDialog) {
        revertPendingStatus();
        closeCancelOrderDialog();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && cancelOrderDialog.classList.contains('is-open')) {
        revertPendingStatus();
        closeCancelOrderDialog();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const statusSelect = document.getElementById('orderStatus');
    if (statusSelect) {
        statusSelect.setAttribute('data-previous-status', statusSelect.value);
        toggleCancellationNoteGroup(statusSelect.value);
    }
});
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Routing Machine JS -->
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('orderMap');
    if (!mapEl) return;

    // Initialize map centered on Dasmariñas
    const map = L.map('orderMap').setView([14.32, 120.95], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Store location
    const storeAddress = '5th Street, Santa Cristina 1, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines';
    const deliveryAddress = '<?php echo addslashes($order['delivery_address'] ?? ''); ?>';
    let storeMarker = null;
    let customerMarker = null;
    let routingControl = null;

    // Custom icons
    const customerIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const storeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // Geocode store address and add marker, then geocode delivery address
    (async function() {
        try {
            // First, geocode and place store marker
            const storeSearchUrl = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(storeAddress)}&limit=1`;
            const storeRes = await fetch(storeSearchUrl);
            const storeResults = await storeRes.json();
            
            if (storeResults && storeResults.length) {
                const s = storeResults[0];
                const storeLatLng = L.latLng(parseFloat(s.lat), parseFloat(s.lon));
                storeMarker = L.marker(storeLatLng, { icon: storeIcon }).addTo(map);
                storeMarker.bindPopup(`<b>KapeLagi Store</b><br>${s.display_name}`);
            }

            // Now geocode delivery address
            if (!deliveryAddress || deliveryAddress === '') {
                document.getElementById('routeDistance').textContent = 'No delivery address provided';
                return;
            }

            const deliverySearchUrl = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(deliveryAddress)}&limit=1`;
            const deliveryRes = await fetch(deliverySearchUrl);
            const deliveryResults = await deliveryRes.json();
            
            if (deliveryResults && deliveryResults.length) {
                const c = deliveryResults[0];
                const customerLatLng = L.latLng(parseFloat(c.lat), parseFloat(c.lon));
                customerMarker = L.marker(customerLatLng, { icon: customerIcon }).addTo(map);
                customerMarker.bindPopup(`<b>Delivery Location</b><br>${c.display_name}`);
                
                // Fit map to show both markers
                const group = new L.featureGroup([customerMarker]);
                if (storeMarker) group.addLayer(storeMarker);
                map.fitBounds(group.getBounds().pad(0.1));

                // Draw route - now storeMarker is definitely set
                if (storeMarker) {
                    const storeLatLng = storeMarker.getLatLng();
                    setTimeout(() => drawRoute(customerLatLng, storeLatLng), 800);
                }
            } else {
                document.getElementById('routeDistance').textContent = 'Address not found on map';
            }
        } catch (err) {
            console.error('Error geocoding:', err);
            document.getElementById('routeDistance').textContent = 'Error locating addresses';
        }
    })();

    // Draw route and calculate distance
    function drawRoute(from, to) {
        if (!from || !to) return;
        
        if (routingControl) {
            try { map.removeControl(routingControl); } catch (e) {}
            routingControl = null;
        }

        routingControl = L.Routing.control({
            waypoints: [from, to],
            router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1', profile: 'driving' }),
            fitSelectedRoutes: false,
            show: false,
            lineOptions: {
                styles: [{ color: '#b18b63', weight: 4, opacity: 0.8 }]
            }
        }).on('routesfound', function(e) {
            if (e.routes && e.routes.length > 0) {
                const distance = e.routes[0].summary.totalDistance / 1000; // Convert to km
                document.getElementById('routeDistance').textContent = distance.toFixed(2) + ' km';
            }
        }).addTo(map);
    }
});
</script>

<?php include 'includes/footer.php'; ?>

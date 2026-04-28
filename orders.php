<?php
include 'config/session.php';
include 'config/db.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <h2 class="mb-4">My Orders</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> You haven't placed any orders yet.
                        <a href="menu.php" class="alert-link">Start shopping</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <?php
                                            // Get items for this order
                                            $item_sql = "SELECT COUNT(*) as cnt FROM order_items WHERE order_id = ?";
                                            $item_stmt = $conn->prepare($item_sql);
                                            $item_stmt->bind_param("i", $order['id']);
                                            $item_stmt->execute();
                                            $item_result = $item_stmt->get_result();
                                            $item_count = $item_result->fetch_assoc()['cnt'];
                                            echo $item_count . " item(s)";
                                            ?>
                                        </td>
                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($order['status'] == 'completed') ? 'success' : (($order['status'] == 'pending') ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

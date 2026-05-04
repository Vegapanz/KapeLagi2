<?php
include 'config/session.php';
include 'config/db.php';
include 'auth/email_verification.php';

if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$active_tab = (isset($_GET['tab']) && $_GET['tab'] === 'orders') ? 'orders' : 'profile';
$success_message = '';
$error_message = '';

$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $block_lot = trim($_POST['block_lot'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');

    if ($name === '' || $email === '') {
        $error_message = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif ($phone !== '' && !preg_match('/^(?:\+63|0)9[0-9]{9}$/', $phone)) {
        $error_message = 'Phone must be a Philippine mobile number (e.g. 09171234567 or +639171234567).';
    } else {
        $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = $conn->prepare($email_check_sql);
        $email_check_stmt->bind_param('si', $email, $user_id);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();

        if ($email_check_result->num_rows > 0) {
            $error_message = 'That email is already in use.';
        } else {
            $old_email = $user['email'] ?? '';
            $email_changed = (strcasecmp($email, $old_email) !== 0);

            // If email changed, fetch fresh data to check if there's a pending unverified email
            if ($email_changed) {
                $fresh_user_sql = "SELECT pending_email FROM users WHERE id = ?";
                $fresh_user_stmt = $conn->prepare($fresh_user_sql);
                $fresh_user_stmt->bind_param('i', $user_id);
                $fresh_user_stmt->execute();
                $fresh_user_result = $fresh_user_stmt->get_result();
                $fresh_user_data = $fresh_user_result->fetch_assoc();
                $pending_email = trim((string) ($fresh_user_data['pending_email'] ?? ''));
                
                if ($pending_email !== '') {
                    // There's a pending verification, so block the save
                    $error_message = 'Please verify the new email address first before saving changes.';
                } else {
                    // No pending verification, email is already verified
                    $email_changed = false;
                }
            }

            if (!$email_changed) {
                $merged_address_parts = array_filter([$block_lot, $address], function ($part) {
                    return $part !== '';
                });
                $merged_address = implode(', ', $merged_address_parts);

                $update_sql = "
                    UPDATE users
                    SET name = ?, phone = ?, address = ?, city = ?, province = ?, updated_at = NOW()
                    WHERE id = ?
                ";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('sssssi', $name, $phone, $merged_address, $city, $province, $user_id);

                if ($update_stmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['user_address'] = $merged_address;
                    $_SESSION['user_city'] = $city;
                    $_SESSION['user_province'] = $province;

                    $user['name'] = $name;
                    $user['phone'] = $phone;
                    $user['address'] = $merged_address;
                    $user['city'] = $city;
                    $user['province'] = $province;

                    $success_message = 'Profile updated successfully.';
                } else {
                    $error_message = 'Unable to save your changes right now.';
                }
            }
        }
    }
}

$display_name = $user['name'] ?? $user_name ?? 'Customer';
$display_email = $user['email'] ?? $user_email ?? '';
$pending_email = $user['pending_email'] ?? '';
$display_phone = $user['phone'] ?? '';
$display_address = $user['address'] ?? '';
$display_block_lot = '';
$display_street_address = $display_address;
if ($display_address !== '' && strpos($display_address, ',') !== false) {
    [$parsed_block_lot, $parsed_street_address] = array_map('trim', explode(',', $display_address, 2));
    $display_block_lot = $parsed_block_lot;
    $display_street_address = $parsed_street_address;
}
$display_city = $user['city'] ?? '';
$display_province = $user['province'] ?? '';
$member_since = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'Member';
$avatar_letter = strtoupper(substr(trim($display_name), 0, 1) ?: 'C');

$stats_sql = "SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS total_spent FROM orders WHERE user_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param('i', $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc() ?: ['total_orders' => 0, 'total_spent' => 0];

$total_orders = (int) ($stats['total_orders'] ?? 0);
$total_spent = (float) ($stats['total_spent'] ?? 0);
$loyalty_points = (int) floor($total_spent / 25);

$favorite_sql = "
    SELECT
        COALESCE(NULLIF(oi.product_name, ''), CONCAT('Item #', oi.product_id)) AS item_label,
        COUNT(*) AS total_count
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY COALESCE(NULLIF(oi.product_name, ''), CONCAT('Item #', oi.product_id))
    ORDER BY total_count DESC, item_label ASC
    LIMIT 1
";
$favorite_stmt = $conn->prepare($favorite_sql);
$favorite_stmt->bind_param('i', $user_id);
$favorite_stmt->execute();
$favorite_result = $favorite_stmt->get_result();
$favorite_item = $favorite_result->fetch_assoc() ?: null;

$orders_sql = "
    SELECT
        o.*,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param('i', $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}

function profile_status_class($status)
{
    $status = strtolower(trim((string) $status));
    return preg_replace('/[^a-z0-9]+/', '-', $status);
}

function profile_format_amount($amount)
{
    return '₱' . number_format((float) $amount, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body class="profile-page">
    <?php include 'components/navbar.php'; ?>

    <main class="profile-shell container-lg">
        <?php if (!empty($success_message)): ?>
            <div class="profile-alert profile-alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="profile-alert profile-alert-error" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <section class="profile-hero">
            <div class="profile-hero-main">
                <div class="profile-avatar" aria-hidden="true"><?php echo htmlspecialchars($avatar_letter); ?></div>
                <div class="profile-hero-copy">
                    <h1><?php echo htmlspecialchars($display_name); ?></h1>
                    <p>Member since <?php echo htmlspecialchars($member_since); ?></p>
                </div>
            </div>

            <div class="profile-stats">
                <!-- <article class="stat-card">
                    <span class="stat-label">Loyalty Points</span>
                    <strong><?php echo number_format($loyalty_points); ?></strong>
                </article> -->
                <article class="stat-card">
                    <span class="stat-label">Total Orders</span>
                    <strong><?php echo number_format($total_orders); ?></strong>
                </article>
                <article class="stat-card">
                    <span class="stat-label">Total Spent</span>
                    <strong><?php echo profile_format_amount($total_spent); ?></strong>
                </article>
            </div>
        </section>

        <nav class="profile-tabs" aria-label="Profile sections">
            <a class="profile-tab <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" href="profile.php?tab=profile">Profile</a>
            <a class="profile-tab <?php echo $active_tab === 'orders' ? 'active' : ''; ?>" href="profile.php?tab=orders">Order History</a>
        </nav>

        <section class="profile-panel <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" id="profile-panel">
            <div class="profile-grid">
                <article class="profile-card personal-info-card">
                    <div class="profile-card-head">
                        <h2>Personal Info</h2>
                        <div class="card-head-actions">
                            <button type="button" id="edit-profile-btn" class="profile-edit-btn"><i class="fa-solid fa-pen" aria-hidden="true"></i><span class="edit-label"> Edit</span></button>
                            <button type="button" id="cancel-profile-btn" class="profile-cancel-btn" style="display:none;"><i class="fa-solid fa-xmark" aria-hidden="true"></i><span class="edit-label"> Cancel</span></button>
                        </div>
                    </div>

                    <form class="profile-form" method="post" action="profile.php?tab=profile#profile-panel" id="personal-info-form">
                        <input type="hidden" name="save_profile" value="1">

                        <div class="info-list">
                            <div class="info-row">
                                <i class="fa-regular fa-user"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-name">Full Name</label>
                                    <input class="profile-field-input" type="text" id="profile-name" name="name" value="<?php echo htmlspecialchars($display_name); ?>" required disabled data-original="<?php echo htmlspecialchars($display_name); ?>">
                                </div>
                            </div>

                            <div class="info-row">
                                <i class="fa-regular fa-envelope"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-email">Email Address</label>
                                    <input class="profile-field-input" type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($display_email); ?>" required disabled data-original="<?php echo htmlspecialchars($display_email); ?>">
                                    <input type="hidden" id="profile-email-verified" value="<?php echo !empty($user['email_verified_at']) ? '1' : '0'; ?>">
                                    <div class="profile-email-actions">
                                        <button type="button" id="profile-email-verify-btn" class="profile-email-verify-btn" style="display:none;">Verify Email</button>
                                        <div id="profile-email-status" class="profile-email-status"></div>
                                    </div>
                                     
                                </div>
                            </div>

                            <div class="info-row">
                                <i class="fa-solid fa-phone"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-phone">Phone Number</label>
                                    <input class="profile-field-input" type="tel" id="profile-phone" name="phone" value="<?php echo htmlspecialchars($display_phone); ?>" placeholder="Not provided" inputmode="numeric" pattern="^(?:\+63|0)9[0-9]{9}$" title="Enter Philippine mobile number (e.g. 09171234567 or +639171234567)" maxlength="13" disabled data-original="<?php echo htmlspecialchars($display_phone); ?>">
                                </div>
                            </div>

                            <div class="info-row">
                                <i class="fa-solid fa-location-dot"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-block-lot">Street Number</label>
                                    <input class="profile-field-input" type="text" id="profile-block-lot" name="block_lot" value="<?php echo htmlspecialchars($display_block_lot); ?>" placeholder="Not provided" disabled data-original="<?php echo htmlspecialchars($display_block_lot); ?>">
                                </div>
                            </div>

                            <div class="info-row">
                                <i class="fa-solid fa-location-dot"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-address">Street Address</label>
                                    <input class="profile-field-input" type="text" id="profile-address" name="address" value="<?php echo htmlspecialchars($display_street_address); ?>" placeholder="Not provided" disabled data-original="<?php echo htmlspecialchars($display_street_address); ?>">
                                </div>
                            </div>

                            <!-- <div class="info-row">
                                <i class="fa-solid fa-city"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-city">City</label>
                                    <input class="profile-field-input" type="text" id="profile-city" name="city" value="<?php echo htmlspecialchars($display_city); ?>" placeholder="Not provided" disabled data-original="<?php echo htmlspecialchars($display_city); ?>">
                                </div>
                            </div> -->

                            <!-- <div class="info-row is-last">
                                <i class="fa-solid fa-map"></i>
                                <div class="profile-field-wrap">
                                    <label class="info-label" for="profile-province">Province</label>
                                    <input class="profile-field-input" type="text" id="profile-province" name="province" value="<?php echo htmlspecialchars($display_province); ?>" placeholder="Not provided" disabled data-original="<?php echo htmlspecialchars($display_province); ?>">
                                </div>
                            </div> -->
                        </div>

                        <div class="profile-form-actions">
                            <button type="submit" id="save-profile-btn" class="profile-save-btn" style="display:none;">Save Changes</button>
                        </div>
                    </form>
                </article>

                <aside class="profile-side-stack">
                    <article class="profile-card favorites-card">
                        <h2>Favorites</h2>
                        <div class="favorite-preview">
                            <div class="favorite-icon-box" aria-hidden="true">
                                <i class="fa-solid fa-mug-hot"></i>
                            </div>
                            <div class="favorite-copy">
                                <span class="favorite-label">Your Favorite</span>
                                <strong><?php echo htmlspecialchars($favorite_item['item_label'] ?? 'No favorite yet'); ?></strong>
                            </div>
                            <i class="fa-solid fa-heart favorite-heart" aria-hidden="true"></i>
                        </div>
                    </article>

                    <article class="profile-card quick-actions-card">
                        <h2>Quick Actions</h2>
                        <div class="quick-action-list">
                            <a class="quick-action" href="#personal-info-form">
                                <span class="quick-action-left">
                                    <i class="fa-solid fa-gear"></i>
                                    <span>Account Settings</span>
                                </span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                            <a class="quick-action" href="profile.php?tab=orders#orders-panel">
                                <span class="quick-action-left">
                                    <i class="fa-solid fa-mug-hot"></i>
                                    <span>View Orders</span>
                                </span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                            <a class="quick-action" href="auth/logout.php">
                                <span class="quick-action-left">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>Log Out</span>
                                </span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </div>
                    </article>
                </aside>
            </div>
        </section>

        <section class="profile-panel <?php echo $active_tab === 'orders' ? 'active' : ''; ?>" id="orders-panel">
            <article class="profile-card orders-card">
                <h2>Order History</h2>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>You haven’t placed any orders yet.</p>
                        <a href="menu.php" class="empty-state-link">Browse the menu</a>
                    </div>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $order_id = (int) $order['id'];
                                $order_items_sql = "
                                    SELECT
                                        COALESCE(NULLIF(product_name, ''), CONCAT('Item #', product_id)) AS item_name,
                                        size,
                                        price,
                                        quantity
                                    FROM order_items
                                    WHERE order_id = ?
                                    ORDER BY id ASC
                                ";
                                $order_items_stmt = $conn->prepare($order_items_sql);
                                $order_items_stmt->bind_param('i', $order_id);
                                $order_items_stmt->execute();
                                $order_items_result = $order_items_stmt->get_result();
                                $order_items = [];
                                while ($item_row = $order_items_result->fetch_assoc()) {
                                    $order_items[] = $item_row;
                                }

                                $status_class = profile_status_class($order['status'] ?? 'pending');
                            ?>
                            <article class="order-card" id="order-<?php echo $order_id; ?>">
                                <div class="order-card-head">
                                    <div>
                                        <h3>#<?php echo htmlspecialchars($order_id); ?></h3>
                                        <div class="order-date">
                                            <i class="fa-regular fa-calendar-days"></i>
                                            <span><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></span>
                                        </div>
                                    </div>

                                    <div class="order-summary">
                                        <span class="order-status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($order['status'] ?? 'pending'); ?></span>
                                        <strong class="order-total"><?php echo profile_format_amount($order['total_amount'] ?? 0); ?></strong>
                                    </div>
                                </div>

                                <div class="order-divider"></div>

                                <div class="order-items">
                                    <?php if (!empty($order_items)): ?>
                                        <?php foreach ($order_items as $item): ?>
                                            <?php $line_total = ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)); ?>
                                            <div class="order-item">
                                                <div class="order-item-left">
                                                    <i class="fa-solid fa-mug-hot order-item-icon"></i>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                                        <span><?php echo htmlspecialchars('x' . (int) $item['quantity'] . (!empty($item['size']) ? ' · ' . $item['size'] : '')); ?></span>
                                                    </div>
                                                </div>
                                                <strong class="order-item-price"><?php echo profile_format_amount($line_total); ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="order-item order-item-empty">
                                            <span>No item details available for this order.</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="order-actions">
                                    <a href="menu.php" class="order-action primary">Reorder</a>
                                    <a href="orders.php" class="order-action secondary">View Receipt</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.KapeProfileState = window.KapeProfileState || {};

        // Restrict phone input to digits and optional leading +, and keep only one leading +
        (function(){
            const phone = document.getElementById('profile-phone');
            if (!phone) return;

            phone.addEventListener('input', () => {
                let v = phone.value;
                // allow only digits and plus
                v = v.replace(/[^0-9+]/g, '');
                // keep plus only if leading
                if (v.indexOf('+') > 0) {
                    v = v.replace(/\+/g, '');
                }
                // ensure only single leading +
                if (v.startsWith('+')) {
                    v = '+' + v.slice(1).replace(/\+/g, '');
                } else {
                    v = v.replace(/\+/g, '');
                }
                // trim to maxlength
                if (phone.maxLength && v.length > phone.maxLength) v = v.slice(0, phone.maxLength);
                phone.value = v;
            });
        })();

        // Edit toggle: enable inputs only when editing
        (function(){
            const form = document.getElementById('personal-info-form');
            if (!form) return;

            const editBtn = document.getElementById('edit-profile-btn');
            const cancelBtn = document.getElementById('cancel-profile-btn');
            const saveBtn = document.getElementById('save-profile-btn');
            const inputs = form.querySelectorAll('.profile-field-input');
            const emailInput = document.getElementById('profile-email');
            const emailVerifyBtn = document.getElementById('profile-email-verify-btn');
            const emailStatus = document.getElementById('profile-email-status');
            const emailVerifiedFlag = document.getElementById('profile-email-verified');
            const draftStorageKey = 'kapelagi_profile_draft';
            const verifiedEmailKey = 'kapelagi_profile_verified_email';

            function saveDraftState() {
                const draft = {};
                inputs.forEach((inp) => {
                    draft[inp.id] = inp.value;
                });
                draft.emailVerified = emailVerifiedFlag ? emailVerifiedFlag.value : '0';
                draft.verifiedEmail = emailInput ? emailInput.value.trim() : '';
                sessionStorage.setItem(draftStorageKey, JSON.stringify(draft));
            }

            function clearDraftState() {
                sessionStorage.removeItem(draftStorageKey);
            }

            function restoreDraftState() {
                const raw = sessionStorage.getItem(draftStorageKey);
                if (!raw) return false;

                try {
                    const draft = JSON.parse(raw);
                    inputs.forEach((inp) => {
                        if (Object.prototype.hasOwnProperty.call(draft, inp.id)) {
                            inp.value = draft[inp.id];
                        }
                    });
                    if (emailVerifiedFlag && typeof draft.emailVerified !== 'undefined') {
                        emailVerifiedFlag.value = draft.emailVerified;
                    }
                    if (emailInput && typeof draft.verifiedEmail === 'string' && draft.verifiedEmail) {
                        sessionStorage.setItem(verifiedEmailKey, draft.verifiedEmail);
                    }
                    return true;
                } catch (err) {
                    clearDraftState();
                    return false;
                }
            }

            function isEmailChanged() {
                if (!emailInput) return false;
                return emailInput.value.trim().toLowerCase() !== (emailInput.getAttribute('data-original') || '').trim().toLowerCase();
            }

            function setEmailStatus(message, type) {
                if (!emailStatus) return;
                emailStatus.textContent = message || '';
                emailStatus.className = 'profile-email-status' + (type ? ' ' + type : '');
            }

            function updateSaveState() {
                if (!saveBtn) return;
                const needsVerification = isEmailChanged() && emailVerifiedFlag && emailVerifiedFlag.value !== '1';
                const emailIsVerified = emailVerifiedFlag && emailVerifiedFlag.value === '1';
                saveBtn.disabled = !!needsVerification;
                if (emailVerifyBtn) {
                    emailVerifyBtn.style.display = isEmailChanged() ? '' : 'none';
                }
                if (needsVerification) {
                    setEmailStatus('Verify the new email before saving changes.', 'warning');
                } else if (emailIsVerified) {
                    setEmailStatus('Email verified. You can now save changes.', 'success');
                    saveBtn.disabled = false;
                } else if (isEmailChanged()) {
                    setEmailStatus('Email changed. Click Verify Email to continue.', 'warning');
                } else {
                    setEmailStatus('', '');
                }
            }

            window.KapeProfileState.saveDraftState = saveDraftState;
            window.KapeProfileState.clearDraftState = clearDraftState;
            window.KapeProfileState.restoreDraftState = restoreDraftState;
            window.KapeProfileState.updateSaveState = updateSaveState;
            window.KapeProfileState.setEmailStatus = setEmailStatus;

            function setEditing(on) {
                inputs.forEach(inp => {
                    if (on) inp.removeAttribute('disabled');
                    else inp.setAttribute('disabled', 'disabled');
                });
                if (on) {
                    editBtn.style.display = 'none';
                    cancelBtn.style.display = '';
                    saveBtn.style.display = '';
                    updateSaveState();
                } else {
                    editBtn.style.display = '';
                    cancelBtn.style.display = 'none';
                    saveBtn.style.display = 'none';
                    if (emailVerifyBtn) emailVerifyBtn.style.display = 'none';
                    setEmailStatus('', '');
                }
            }

            // store originals are present as data-original attributes
            cancelBtn.addEventListener('click', () => {
                inputs.forEach(inp => {
                    const orig = inp.getAttribute('data-original');
                    if (orig !== null) inp.value = orig;
                });
                if (emailVerifiedFlag) emailVerifiedFlag.value = '1';
                setEmailStatus('', '');
                clearDraftState();
                setEditing(false);
            });

            editBtn.addEventListener('click', () => {
                setEditing(true);
                saveDraftState();
                // focus first editable field
                setTimeout(() => {
                    const first = form.querySelector('.profile-field-input:not([disabled])');
                    if (first) first.focus();
                }, 10);
            });

            if (emailInput) {
                emailInput.addEventListener('input', () => {
                    if (emailVerifiedFlag) emailVerifiedFlag.value = '0';
                    sessionStorage.removeItem(verifiedEmailKey);
                    updateSaveState();
                    saveDraftState();
                });
            }

            inputs.forEach((inp) => {
                inp.addEventListener('input', saveDraftState);
            });

            if (emailVerifyBtn) {
                emailVerifyBtn.addEventListener('click', async () => {
                    const email = emailInput ? emailInput.value.trim() : '';
                    if (!email || !email.includes('@')) {
                        setEmailStatus('Please enter a valid email address.', 'error');
                        return;
                    }

                    emailVerifyBtn.disabled = true;
                    emailVerifyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
                    setEmailStatus('Sending verification code...', 'warning');

                    try {
                        const response = await fetch('api/send-profile-email-verification.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'email=' + encodeURIComponent(email)
                        });

                        const result = await response.json();

                        if (result.success) {
                            setEmailStatus(result.message || 'Verification code sent. Enter it in the modal.', 'success');
                            const modalEl = document.getElementById('verifyCodeModal');
                            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                                const modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                                modalInstance.show();
                            }
                        } else {
                            setEmailStatus(result.message || 'Could not send verification code.', 'error');
                        }
                    } catch (err) {
                        setEmailStatus('An error occurred while sending the verification code.', 'error');
                    } finally {
                        emailVerifyBtn.disabled = false;
                        emailVerifyBtn.textContent = 'Verify Email';
                        updateSaveState();
                    }
                });
            }

            if (saveBtn) {
                form.addEventListener('submit', (e) => {
                    if (isEmailChanged() && (!emailVerifiedFlag || emailVerifiedFlag.value !== '1')) {
                        e.preventDefault();
                        setEmailStatus('Verify the new email before saving changes.', 'error');
                        if (window.KapeNotify && window.KapeNotify.popup) {
                            window.KapeNotify.popup('Email Not Verified', 'Please verify the new email before saving changes.', 'warning');
                        }
                    }
                });
            }

            updateSaveState();

            if (restoreDraftState()) {
                setEditing(true);
                updateSaveState();
            }

            if (emailInput && emailVerifiedFlag && emailVerifiedFlag.value !== '1') {
                const storedVerifiedEmail = sessionStorage.getItem(verifiedEmailKey);
                if (storedVerifiedEmail && storedVerifiedEmail.trim().toLowerCase() === emailInput.value.trim().toLowerCase()) {
                    emailVerifiedFlag.value = '1';
                }
            }

            updateSaveState();

            // If server reported success, ensure form is not left in edit mode
            <?php if (!empty($success_message)): ?>
                setEditing(false);
                clearDraftState();
            <?php endif; ?>
        })();

        // Verification code modal handler
        (function(){
            const modal = document.getElementById('verifyCodeModal');
            if (!modal) return;

            const form = modal.querySelector('form');
            const codeInput = modal.querySelector('#verification-code-input');
            const msgDiv = modal.querySelector('.verification-message');
            const emailInput = document.getElementById('profile-email');
            const emailVerifiedFlag = document.getElementById('profile-email-verified');
            const emailStatus = document.getElementById('profile-email-status');
            const emailVerifyBtn = document.getElementById('profile-email-verify-btn');
            const saveBtn = document.getElementById('save-profile-btn');
            const resendBtn = modal.querySelector('#resendCodeBtn');
            const resendCountdown = modal.querySelector('#resendCountdown');
            const profileState = window.KapeProfileState || {};
            let resendInterval = null;

            function startResendCooldown(seconds) {
                if (!resendBtn || !resendCountdown) return;
                let remaining = seconds;
                resendBtn.disabled = true;
                resendBtn.textContent = 'Resend';
                resendCountdown.textContent = `You can resend in ${remaining}s`;
                clearInterval(resendInterval);
                resendInterval = setInterval(() => {
                    remaining -= 1;
                    if (remaining <= 0) {
                        clearInterval(resendInterval);
                        resendBtn.disabled = false;
                        resendCountdown.textContent = '';
                        resendBtn.textContent = 'Resend Code';
                    } else {
                        resendCountdown.textContent = `You can resend in ${remaining}s`;
                        resendBtn.textContent = `Resend (${remaining}s)`;
                    }
                }, 1000);
            }

            async function resendProfileVerificationCode() {
                if (!resendBtn) return;
                const email = emailInput ? emailInput.value.trim() : '';
                if (!email || !email.includes('@')) {
                    msgDiv.className = 'verification-message error';
                    msgDiv.textContent = 'Please enter a valid email address.';
                    return;
                }

                resendBtn.disabled = true;
                resendBtn.textContent = 'Sending...';

                try {
                    const response = await fetch('api/send-profile-email-verification.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'email=' + encodeURIComponent(email)
                    });

                    const result = await response.json();

                    if (result.success) {
                        msgDiv.className = 'verification-message success';
                        msgDiv.textContent = result.message || 'Verification code resent. Please check your email.';
                        startResendCooldown(60);
                        codeInput.value = '';
                        codeInput.focus();
                    } else {
                        msgDiv.className = 'verification-message error';
                        msgDiv.textContent = result.message || 'Could not resend code.';
                        resendBtn.disabled = false;
                        resendBtn.textContent = 'Resend Code';
                    }
                } catch (err) {
                    msgDiv.className = 'verification-message error';
                    msgDiv.textContent = 'An error occurred while resending the code.';
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend Code';
                }
            }

            if (resendBtn) {
                resendBtn.addEventListener('click', resendProfileVerificationCode);
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const code = codeInput.value.trim();

                if (code.length !== 6 || !/^\d+$/.test(code)) {
                    msgDiv.className = 'verification-message error';
                    msgDiv.textContent = 'Please enter a valid 6-digit code.';
                    return;
                }

                try {
                    const response = await fetch('api/verify-email-code.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code: code })
                    });

                    const result = await response.json();

                    if (result.success) {
                        msgDiv.className = 'verification-message success';
                        msgDiv.textContent = 'Email verified successfully!';
                        if (emailVerifiedFlag) emailVerifiedFlag.value = '1';
                        if (emailInput) {
                            emailInput.setAttribute('data-original', emailInput.value.trim());
                        }
                        if (emailStatus) {
                            emailStatus.className = 'profile-email-status success';
                            emailStatus.textContent = 'Email verified. You can now save changes.';
                        }
                        if (emailVerifyBtn) {
                            emailVerifyBtn.textContent = 'Verified';
                            emailVerifyBtn.disabled = true;
                        }
                        if (saveBtn) {
                            saveBtn.disabled = false;
                        }
                        sessionStorage.setItem(verifiedEmailKey, emailInput ? emailInput.value.trim() : '');
                        if (typeof profileState.saveDraftState === 'function') {
                            profileState.saveDraftState();
                        }
                        if (typeof profileState.updateSaveState === 'function') {
                            profileState.updateSaveState();
                        }
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(modal)?.hide();
                        }, 700);
                    } else {
                        msgDiv.className = 'verification-message error';
                        msgDiv.textContent = result.message || 'Invalid code. Please try again.';
                        codeInput.value = '';
                        codeInput.focus();
                    }
                } catch (err) {
                    msgDiv.className = 'verification-message error';
                    msgDiv.textContent = 'An error occurred. Please try again.';
                }
            });

            // Auto-focus when modal opens
            modal.addEventListener('shown.bs.modal', () => {
                codeInput.focus();
            });

            // Clear message and input when modal closes
            modal.addEventListener('hidden.bs.modal', () => {
                msgDiv.textContent = '';
                msgDiv.className = 'verification-message';
                codeInput.value = '';
                if (resendCountdown) {
                    resendCountdown.textContent = '';
                }
                if (resendBtn) {
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend Code';
                }
                clearInterval(resendInterval);
            });
        })();
    </script>

    <!-- Verification Code Modal -->
    <div class="modal fade custom-verify-modal" id="verifyCodeModal" tabindex="-1" aria-labelledby="verifyCodeLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="verifyCodeLabel">Verify Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Enter the 6-digit code sent to your email.</p>
                    <form id="profileVerifyForm">
                        <div class="mb-3">
                            <input type="text" id="verification-code-input" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                        </div>
                        <div class="verification-message"></div>
                        <button type="submit" class="btn btn-dark w-100">Verify</button>
                    </form>
                    <div class="verify-resend-row">
                        <button type="button" id="resendCodeBtn" class="btn-resend">Resend Code</button>
                        <div id="resendCountdown" class="resend-countdown"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

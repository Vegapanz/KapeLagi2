<?php
$page_title = "Settings";
include 'includes/header.php';

$conn->query("CREATE TABLE IF NOT EXISTS admin_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$admin_id = intval($_SESSION['user_id'] ?? 0);
$errors = [];
$success = '';

$default_settings = [
    'store_name' => 'KapeLagi',
    'support_email' => 'kapelagidasma@gmail.com',
    'timezone' => 'Asia/Manila',
    'currency_symbol' => 'P',
    'orders_per_page_default' => '10',
    'analytics_range_default' => '30',
    'notify_new_order' => '1',
    'notify_cancelled_order' => '1'
];

$settings = $default_settings;
$settings_result = $conn->query("SELECT setting_key, setting_value FROM admin_settings");
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = (string) $row['setting_value'];
    }
}

$admin_stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE id = ? LIMIT 1");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();

if (!$admin) {
    echo '<div class="alert alert-danger">Admin account not found.</div>';
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'update-profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($errors)) {
            $email_check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
            $email_check_stmt->bind_param("si", $email, $admin_id);
            $email_check_stmt->execute();
            $existing_email = $email_check_stmt->get_result()->fetch_assoc();

            if ($existing_email) {
                $errors[] = 'That email is already used by another account.';
            }
        }

        if (empty($errors)) {
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $name, $email, $admin_id);

            if ($update_stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = 'Profile updated successfully.';
                $admin['name'] = $name;
                $admin['email'] = $email;
            } else {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }

    if ($action === 'change-password') {
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $errors[] = 'Please fill out all password fields.';
        }

        if (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if ($new_password !== $confirm_password) {
            $errors[] = 'New password and confirmation do not match.';
        }

        $stored_password = (string) ($admin['password'] ?? '');
        $password_valid = password_verify($current_password, $stored_password) || hash_equals($stored_password, $current_password);

        if (!$password_valid) {
            $errors[] = 'Current password is incorrect.';
        }

        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $password_stmt->bind_param("si", $hashed_password, $admin_id);

            if ($password_stmt->execute()) {
                $success = 'Password updated successfully.';
                $admin['password'] = $hashed_password;
            } else {
                $errors[] = 'Failed to update password. Please try again.';
            }
        }
    }

    if ($action === 'update-system-settings') {
        $store_name = trim($_POST['store_name'] ?? '');
        $support_email = trim($_POST['support_email'] ?? '');
        $timezone = trim($_POST['timezone'] ?? 'Asia/Manila');
        $currency_symbol = trim($_POST['currency_symbol'] ?? 'P');
        $orders_per_page_default = trim($_POST['orders_per_page_default'] ?? '10');
        $analytics_range_default = trim($_POST['analytics_range_default'] ?? '30');
        $notify_new_order = isset($_POST['notify_new_order']) ? '1' : '0';
        $notify_cancelled_order = isset($_POST['notify_cancelled_order']) ? '1' : '0';

        $allowed_page_sizes = ['5', '10', '20', '50'];
        $allowed_ranges = ['7', '30', '90', '365'];

        if ($store_name === '' || strlen($store_name) < 2) {
            $errors[] = 'Store name must be at least 2 characters.';
        }

        if (!filter_var($support_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Support email must be a valid email address.';
        }

        if (!in_array($orders_per_page_default, $allowed_page_sizes, true)) {
            $errors[] = 'Default orders page size is invalid.';
        }

        if (!in_array($analytics_range_default, $allowed_ranges, true)) {
            $errors[] = 'Default analytics range is invalid.';
        }

        if ($currency_symbol === '' || strlen($currency_symbol) > 5) {
            $errors[] = 'Currency symbol is required and must be short.';
        }

        if (empty($errors)) {
            $save_stmt = $conn->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $settings_to_save = [
                'store_name' => $store_name,
                'support_email' => $support_email,
                'timezone' => $timezone,
                'currency_symbol' => $currency_symbol,
                'orders_per_page_default' => $orders_per_page_default,
                'analytics_range_default' => $analytics_range_default,
                'notify_new_order' => $notify_new_order,
                'notify_cancelled_order' => $notify_cancelled_order
            ];

            foreach ($settings_to_save as $setting_key => $setting_value) {
                $save_stmt->bind_param("ss", $setting_key, $setting_value);
                $save_stmt->execute();
            }

            $settings = array_merge($settings, $settings_to_save);
            $success = 'System settings updated successfully.';
        }
    }
}
?>

<h1 class="page-title">Settings</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <strong>Please fix the following:</strong>
        <ul style="margin: 8px 0 0 18px;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Profile Settings</h3>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="update-profile">

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="adminName" style="display: block; margin-bottom: 6px; font-weight: 600;">Admin Name</label>
                    <input id="adminName" type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="adminEmail" style="display: block; margin-bottom: 6px; font-weight: 600;">Admin Email</label>
                    <input id="adminEmail" type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                </div>

                <button type="submit" class="btn" style="background-color: #1A0F0A; color: #E8E0D0; border: none; padding: 10px 16px;">Save Profile</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">Security</h3>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="change-password">

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="currentPassword" style="display: block; margin-bottom: 6px; font-weight: 600;">Current Password</label>
                    <input id="currentPassword" type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="newPassword" style="display: block; margin-bottom: 6px; font-weight: 600;">New Password</label>
                    <input id="newPassword" type="password" name="new_password" class="form-control" minlength="8" required>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="confirmPassword" style="display: block; margin-bottom: 6px; font-weight: 600;">Confirm New Password</label>
                    <input id="confirmPassword" type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>

                <button type="submit" class="btn" style="background-color: #1A0F0A; color: #E8E0D0; border: none; padding: 10px 16px;">Update Password</button>
            </form>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="section-card-header">
        <h3 class="section-card-title">System Settings</h3>
    </div>

    <form method="post">
        <input type="hidden" name="action" value="update-system-settings">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="storeName" style="display: block; margin-bottom: 6px; font-weight: 600;">Store Name</label>
                    <input id="storeName" type="text" name="store_name" class="form-control" value="<?php echo htmlspecialchars($settings['store_name'] ?? 'KapeLagi'); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="supportEmail" style="display: block; margin-bottom: 6px; font-weight: 600;">Support Email</label>
                    <input id="supportEmail" type="email" name="support_email" class="form-control" value="<?php echo htmlspecialchars($settings['support_email'] ?? ''); ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="timezone" style="display: block; margin-bottom: 6px; font-weight: 600;">Timezone</label>
                    <select id="timezone" name="timezone" class="form-select">
                        <option value="Asia/Manila" <?php echo ($settings['timezone'] ?? '') === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila</option>
                        <option value="Asia/Singapore" <?php echo ($settings['timezone'] ?? '') === 'Asia/Singapore' ? 'selected' : ''; ?>>Asia/Singapore</option>
                        <option value="UTC" <?php echo ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="currencySymbol" style="display: block; margin-bottom: 6px; font-weight: 600;">Currency Symbol</label>
                    <input id="currencySymbol" type="text" name="currency_symbol" class="form-control" maxlength="5" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? 'P'); ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="ordersPerPageDefault" style="display: block; margin-bottom: 6px; font-weight: 600;">Default Orders Page Size</label>
                    <select id="ordersPerPageDefault" name="orders_per_page_default" class="form-select">
                        <option value="5" <?php echo ($settings['orders_per_page_default'] ?? '') === '5' ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo ($settings['orders_per_page_default'] ?? '') === '10' ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo ($settings['orders_per_page_default'] ?? '') === '20' ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo ($settings['orders_per_page_default'] ?? '') === '50' ? 'selected' : ''; ?>>50</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="analyticsRangeDefault" style="display: block; margin-bottom: 6px; font-weight: 600;">Default Analytics Range</label>
                    <select id="analyticsRangeDefault" name="analytics_range_default" class="form-select">
                        <option value="7" <?php echo ($settings['analytics_range_default'] ?? '') === '7' ? 'selected' : ''; ?>>Last 7 days</option>
                        <option value="30" <?php echo ($settings['analytics_range_default'] ?? '') === '30' ? 'selected' : ''; ?>>Last 30 days</option>
                        <option value="90" <?php echo ($settings['analytics_range_default'] ?? '') === '90' ? 'selected' : ''; ?>>Last 90 days</option>
                        <option value="365" <?php echo ($settings['analytics_range_default'] ?? '') === '365' ? 'selected' : ''; ?>>Last 365 days</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Notifications</label>
                    <div class="form-check" style="margin-bottom: 8px;">
                        <input class="form-check-input" type="checkbox" value="1" id="notifyNewOrder" name="notify_new_order" <?php echo ($settings['notify_new_order'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="notifyNewOrder">Notify on new orders</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="notifyCancelledOrder" name="notify_cancelled_order" <?php echo ($settings['notify_cancelled_order'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="notifyCancelledOrder">Notify when orders are cancelled</label>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn" style="background-color: #1A0F0A; color: #E8E0D0; border: none; padding: 10px 16px;">Save System Settings</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

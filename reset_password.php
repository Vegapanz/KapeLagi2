<?php
include 'config/session.php';
require_once __DIR__ . '/auth/password_reset.php';
require_once __DIR__ . '/config/db.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$error = '';
$passwordError = '';
$confirmError = '';
$valid = false;
$email = '';

if (isset($_GET['error']) && trim($_GET['error']) !== '') {
    $error = trim($_GET['error']);
}
if (isset($_GET['password_error']) && trim($_GET['password_error']) !== '') {
    $passwordError = trim($_GET['password_error']);
}
if (isset($_GET['confirm_error']) && trim($_GET['confirm_error']) !== '') {
    $confirmError = trim($_GET['confirm_error']);
}

if ($token !== '') {
    ensure_password_resets_table($conn);
    $stmt = $conn->prepare('SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($row) {
        $expires = new DateTime($row['expires_at']);
        $now = new DateTime();
        if ($expires >= $now) {
            $valid = true;
            $email = $row['email'];
        } else {
            $error = 'This reset link has expired.';
        }
    } else {
        $error = 'Invalid reset token.';
    }
} else {
    $error = 'Missing reset token.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .input-wrap {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #6b4a3a;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
        }

        .field-error {
            color: #b42318;
            font-size: 0.95rem;
            margin-top: 8px;
            min-height: 1.1em;
        }

        .auth-input.with-toggle {
            padding-right: 72px;
        }

        .password-group {
            margin-bottom: 1rem;
        }

        .password-requirements {
            background: #2c1810;
            border: 1px solid #e6d9cf;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
        }

        .requirements-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: inherit;
            margin: 0 0 8px 0;
        }

        .requirement {
            font-size: 0.85rem;
            color: inherit;
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        .requirement:last-child {
            margin-bottom: 0;
        }

        .req-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
            text-align: center;
            line-height: 20px;
            color: inherit;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .password-requirements {
            color: #6b4a3a;
        }

        .requirements-title {
            color: #E8E0D0;
        }

        .requirement {
            color: #b71c1c;
        }

        .requirement.met {
            color: #00de34;
        }

        .req-icon {
            color: inherit;
        }
    </style>
</head>

<body>
    <?php include 'components/navbar.php'; ?>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-form-section">
                    <h2 class="auth-title">Reset your password</h2>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($valid): ?>
                        <form class="auth-form" method="POST" action="auth/reset_password_process.php" id="resetPasswordForm" novalidate>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="password-group">
                                <label class="auth-label">New password</label>
                                <div class="input-wrap">
                                    <input type="password" name="password" class="auth-input with-toggle" id="password" required>
                                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-requirements">
                                    <p class="requirements-title">Password must contain:</p>
                                    <div class="requirement" id="req-length">
                                        <span class="req-icon">✓</span>
                                        <span>At least 8 characters</span>
                                    </div>
                                    <div class="requirement" id="req-upper">
                                        <span class="req-icon">✓</span>
                                        <span>One uppercase letter (A-Z)</span>
                                    </div>
                                    <div class="requirement" id="req-lower">
                                        <span class="req-icon">✓</span>
                                        <span>One lowercase letter (a-z)</span>
                                    </div>
                                    <div class="requirement" id="req-number">
                                        <span class="req-icon">✓</span>
                                        <span>One number (0-9)</span>
                                    </div>
                                    <div class="requirement" id="req-special">
                                        <span class="req-icon">✓</span>
                                        <span>One special character (!@#$%^&*)</span>
                                    </div>
                                </div>
                            </div>

                            <label class="auth-label">Confirm password</label>
                            <div class="input-wrap">
                                <input type="password" name="password_confirm" class="auth-input with-toggle" id="passwordConfirm" required>
                                <button type="button" class="password-toggle" data-target="passwordConfirm" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <button type="submit" class="auth-btn primary-btn">Set new password</button>
                        </form>
                    <?php else: ?>
                        <p><a href="forgot_password.php">Request a new reset link</a></p>
                    <?php endif; ?>
                </div>
                <div class="auth-character-section auth-image-section">
                    <img src="assets/Images/coffee4.jpg" alt="coffee" class="auth-side-image">
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        (function() {
            const form = document.getElementById('resetPasswordForm');
            if (!form) return;

            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('passwordConfirm');
            const passwordError = document.getElementById('passwordError');
            const confirmError = document.getElementById('confirmError');

            const rules = [{
                    test: value => value.length >= 8,
                    message: 'Password must be at least 8 characters'
                },
                {
                    test: value => /[A-Z]/.test(value),
                    message: 'Password must contain at least one uppercase letter'
                },
                {
                    test: value => /[a-z]/.test(value),
                    message: 'Password must contain at least one lowercase letter'
                },
                {
                    test: value => /[0-9]/.test(value),
                    message: 'Password must contain at least one number'
                },
                {
                    test: value => /[!@#$%^&*]/.test(value),
                    message: 'Password must contain at least one special character (!@#$%^&*)'
                },
            ];

            function setError(element, message) {
                if (element) {
                    element.textContent = message || '';
                }
            }

            function validatePassword() {
                const value = passwordInput.value;
                for (const rule of rules) {
                    if (!rule.test(value)) {
                        setError(passwordError, rule.message);
                        return false;
                    }
                }
                setError(passwordError, '');
                return true;
            }

            function validateConfirm() {
                if (!confirmInput.value) {
                    setError(confirmError, '');
                    return true;
                }

                if (passwordInput.value !== confirmInput.value) {
                    setError(confirmError, 'Passwords do not match');
                    return false;
                }

                setError(confirmError, '');
                return true;
            }

            form.querySelectorAll('.password-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-target');
                    const targetInput = document.getElementById(targetId);
                    if (!targetInput) return;

                    const isHidden = targetInput.type === 'password';
                    targetInput.type = isHidden ? 'text' : 'password';
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
                    }
                });
            });

            function checkRequirements() {
                const password = passwordInput.value;
                const requirementMap = {
                    'req-length': value => value.length >= 8,
                    'req-upper': value => /[A-Z]/.test(value),
                    'req-lower': value => /[a-z]/.test(value),
                    'req-number': value => /[0-9]/.test(value),
                    'req-special': value => /[!@#$%^&*]/.test(value),
                };

                let metCount = 0;
                Object.entries(requirementMap).forEach(([id, test]) => {
                    const element = document.getElementById(id);
                    if (!element) return;
                    if (test(password)) {
                        element.classList.add('met');
                        element.classList.remove('unmet');
                        metCount++;
                    } else {
                        element.classList.add('unmet');
                        element.classList.remove('met');
                    }
                });

                // Apply progressive color class
                const requirementsContainer = document.querySelector('.password-requirements');
                if (requirementsContainer) {
                    requirementsContainer.className = 'password-requirements';
                    if (metCount > 0) {
                        requirementsContainer.classList.add('progress-' + metCount);
                    }
                }
            }

            passwordInput.addEventListener('input', () => {
                validatePassword();
                validateConfirm();
                checkRequirements();
            });

            confirmInput.addEventListener('input', validateConfirm);

            checkRequirements();

            form.addEventListener('submit', (event) => {
                const passwordOk = validatePassword();
                const confirmOk = validateConfirm();

                if (!passwordOk || !confirmOk) {
                    event.preventDefault();
                }
            });
        })();
    </script>
</body>

</html>
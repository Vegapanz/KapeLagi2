<?php
include 'config/session.php';

// Check if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'auth/signup_process.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        /* Modal redesign to match auth styling */
        .custom-verify-modal .modal-content {
            background: #fffdf9;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            color: #4b2e1f; 
            font-family: 'Smooch Sans', sans-serif;
        }
        .custom-verify-modal .modal-header {
            border-bottom: 0;
            background: linear-gradient(90deg,#f7efe9,#fffaf6);
            color: #5d3a2a;
        }
        .custom-verify-modal .modal-title { font-weight:700; }
        .custom-verify-modal .modal-body { padding: 1.25rem 1.5rem; }
        .custom-verify-modal .form-control { border-radius: 8px; border:1px solid #e6d9cf; }
        .verify-resend-row { display:flex; gap:8px; align-items:center; margin-top:0.25rem; }
        .resend-countdown { color:#6b4a3a; font-size:0.9rem; }
        /* Use the system's dark brown for primary actions in the modal */
        .custom-verify-modal .btn-dark {
            background:#1A0F0A;
            color: #D6D0C4;
            border: none;
            box-shadow: 0 6px 20px rgba(61,40,23,0.25);
        }
        .custom-verify-modal .btn-dark:hover {
            background: #E8E0D0;
            color: #1A0F0A;
            transform: translateY(-1px);
        }

        .btn-resend { background:#3D2817; color:#E8E0D0; border:0; padding:0.5rem 0.9rem; border-radius:8px; font-weight:600; }
        .btn-resend:hover { background:#2f1b12; }
        .btn-resend:disabled { opacity:0.5; cursor:not-allowed; }
    </style>
</head>
<body >
    <!-- Navigation Bar -->
     <?php include 'components/navbar.php'; ?>
    <div class="auth-page">
    
    
    <!-- Coffee Bean Decorations -->
    <!-- <div class="coffee-bean bean-1"></div>
    <div class="coffee-bean bean-2"></div>
    <div class="coffee-bean bean-3"></div>
    <div class="coffee-bean bean-4"></div>
    <div class="coffee-bean bean-5"></div>
    <div class="coffee-bean bean-6"></div>
    <div class="coffee-bean bean-7"></div> -->
    
    <!-- Auth Container -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- Form Section -->
            <div class="auth-form-section">
                <h2 class="auth-title">Sign up</h2>
                <p class="auth-subtitle">Please Enter your Details</p>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST" action="signup.php" id="signupForm">
                    <input type="text" name="name" class="auth-input" placeholder="Name" required>
                    
                    <div class="email-verification-group">
                        <input type="email" name="email" id="signupEmail" class="auth-input" placeholder="Email" required>
                        <button type="button" class="email-verify-btn" id="emailVerifyBtn">
                            <i class="fas fa-paper-plane"></i> Send Code
                        </button>
                    </div>
                    <input type="hidden" name="email_verified" id="emailVerifiedFlag" value="0">
                    <div id="emailVerifyMessage" class="verification-message"></div>
                    
                    <div class="password-group">
                        <input type="password" name="password" class="auth-input" id="password" placeholder="Password" required>
                        
                        <!-- Password Requirements -->
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
                    
                    <div class="password-confirm-group">
                        <input type="password" name="confirm_password" class="auth-input" id="confirm_password" placeholder="Confirm Password" required>
                        <div id="password-match-status" class="password-match-status" aria-live="polite"></div>
                    </div>

                    <label class="terms-check" for="termsAccepted">
                        <input type="checkbox" id="termsAccepted" name="terms_accepted" value="1" required>
                        <span>I agree to the <a href="terms.php" target="_blank" rel="noopener noreferrer">Terms and Conditions</a>.</span>
                    </label>
                    
                    <button type="submit" class="auth-btn primary-btn" id="submitBtn">Create Account</button>
                </form>
                
                <div class="auth-divider">
                    <span>OR</span>
                </div>
                
                <button class="auth-btn google-btn">
                    <span class="google-icon">G</span>
                    Continue with Google
                </button>
                
                <p class="auth-link-text">
                    Already have account? <a href="signin.php" class="auth-link">Sign in</a>
                </p>
            </div>
            
            <!-- Image Section -->
            <div class="auth-character-section auth-image-section">
                <img src="assets/Images/coffee3.jpg" alt="Freshly brewed coffee" class="auth-side-image">
            </div>
        </div>
    </div>
    </div>
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password Validation Script -->
    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const signupForm = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Password requirements
        const requirements = {
            'req-length': { regex: /.{8,}/, text: 'At least 8 characters' },
            'req-upper': { regex: /[A-Z]/, text: 'One uppercase letter' },
            'req-lower': { regex: /[a-z]/, text: 'One lowercase letter' },
            'req-number': { regex: /[0-9]/, text: 'One number' },
            'req-special': { regex: /[!@#$%^&*]/, text: 'One special character' }
        };
        
        // Check password requirements
        function checkPasswordStrength() {
            const password = passwordInput.value;
            let allMet = true;
            
            Object.keys(requirements).forEach(reqId => {
                const req = requirements[reqId];
                const element = document.getElementById(reqId);
                
                if (req.regex.test(password)) {
                    element.classList.add('met');
                    element.classList.remove('unmet');
                } else {
                    element.classList.add('unmet');
                    element.classList.remove('met');
                    allMet = false;
                }
            });
            
            // Check password match
            checkPasswordMatch();
            
            return allMet;
        }
        
        // Check if passwords match
        function checkPasswordMatch() {
            const statusDiv = document.getElementById('password-match-status');
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                statusDiv.textContent = '';
                statusDiv.className = 'password-match-status';
                return;
            }
            
            if (password === confirmPassword) {
                statusDiv.textContent = 'Passwords match';
                statusDiv.className = 'password-match-status match';
            } else {
                statusDiv.textContent = 'Passwords do not match';
                statusDiv.className = 'password-match-status mismatch';
            }
        }
        
        // Event listeners
        passwordInput.addEventListener('input', checkPasswordStrength);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // Form submission validation
        signupForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Check all requirements
            const allRequirementsMet = checkPasswordStrength();
            
            if (!allRequirementsMet) {
                e.preventDefault();
                window.KapeNotify.popup('Password Requirements', 'Password does not meet all requirements.', 'warning');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                window.KapeNotify.popup('Password Mismatch', 'Passwords do not match.', 'warning');
                return false;
            }

            if (emailVerifiedFlag.value !== '1') {
                e.preventDefault();
                window.KapeNotify.popup('Email Not Verified', 'Please verify your email code first.', 'warning');
                return false;
            }
        });
        
        // Initial check
        checkPasswordStrength();

        // Email Verification Button Handler
        const emailInput = document.getElementById('signupEmail');
        const emailVerifyBtn = document.getElementById('emailVerifyBtn');
        const emailVerifyMessage = document.getElementById('emailVerifyMessage');
        const emailVerifiedFlag = document.getElementById('emailVerifiedFlag');

        // Resend helpers: cooldown and resend action
        let _resendInterval = null;
        function startResendCooldown(seconds) {
            const btn = document.getElementById('resendCodeBtn');
            const countdownEl = document.getElementById('resendCountdown');
            if (!btn || !countdownEl) return;
            let remaining = seconds;
            btn.disabled = true;
            btn.textContent = 'Resend';
            countdownEl.textContent = `You can resend in ${remaining}s`;
            clearInterval(_resendInterval);
            _resendInterval = setInterval(() => {
                remaining -= 1;
                if (remaining <= 0) {
                    clearInterval(_resendInterval);
                    btn.disabled = false;
                    countdownEl.textContent = '';
                    btn.textContent = 'Resend Code';
                } else {
                    countdownEl.textContent = `You can resend in ${remaining}s`;
                    btn.textContent = `Resend (${remaining}s)`;
                }
            }, 1000);
        }

        async function resendVerificationCode() {
            const btn = document.getElementById('resendCodeBtn');
            const countdownEl = document.getElementById('resendCountdown');
            if (!btn) return;
            btn.disabled = true;
            btn.textContent = 'Sending...';
            try {
                const response = await fetch('api/send-verification-email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(emailInput.value.trim())
                });
                const result = await response.json();
                if (result.success) {
                    countdownEl.textContent = 'Verification code resent.';
                    startResendCooldown(60);
                    // Clear any previous verification messages and focus the code input
                    const codeEl = document.getElementById('signupVerificationCode');
                    const msgDivLocal = document.querySelector('.signup-verification-message');
                    if (msgDivLocal) { msgDivLocal.textContent = ''; msgDivLocal.className = 'signup-verification-message'; }
                    if (codeEl) { codeEl.value = ''; codeEl.focus(); }
                } else {
                    countdownEl.textContent = result.message || 'Could not resend code.';
                    btn.disabled = false;
                    btn.textContent = 'Resend Code';
                }
            } catch (err) {
                countdownEl.textContent = 'Network error when resending.';
                btn.disabled = false;
                btn.textContent = 'Resend Code';
            }
        }

        // Do not query the modal until needed — the modal markup is below this script

        emailInput.addEventListener('input', () => {
            emailVerifiedFlag.value = '0';
            emailVerifyBtn.disabled = false;
            emailVerifyBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Code';
            emailVerifyMessage.className = 'verification-message';
            emailVerifyMessage.textContent = '';
        });

        emailVerifyBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const email = emailInput.value.trim();

            if (!email || !email.includes('@')) {
                emailVerifyMessage.className = 'verification-message error';
                emailVerifyMessage.textContent = 'Please enter a valid email address.';
                return;
            }

            // Disable button while sending
            emailVerifyBtn.disabled = true;
            emailVerifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            try {
                const response = await fetch('api/send-verification-email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(email)
                });

                const result = await response.json();

                if (result.success) {
                    emailVerifyMessage.className = 'verification-message success';
                    emailVerifyMessage.textContent = result.message + ' Enter the code to verify this email.';
                    emailVerifyBtn.innerHTML = '<i class="fas fa-check"></i> Code Sent';
                    // Query the modal element now (it's rendered later in the page)
                    const signupVerifyModalEl = document.getElementById('signupVerifyCodeModal');
                    if (signupVerifyModalEl && window.bootstrap && window.bootstrap.Modal) {
                        const modalInstance = window.bootstrap.Modal.getOrCreateInstance(signupVerifyModalEl);
                        modalInstance.show();
                        // initialize resend cooldown when modal opens
                        startResendCooldown(60);
                    } else {
                        emailVerifyMessage.className = 'verification-message error';
                        emailVerifyMessage.textContent = 'Code sent, but modal failed to open. Please refresh and try again.';
                    }
                } else {
                    emailVerifyMessage.className = 'verification-message error';
                    emailVerifyMessage.textContent = result.message || 'Could not send verification code.';
                    emailVerifyBtn.disabled = false;
                    emailVerifyBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Code';
                }
            } catch (err) {
                emailVerifyMessage.className = 'verification-message error';
                emailVerifyMessage.textContent = 'An error occurred. Please try again.';
                emailVerifyBtn.disabled = false;
                emailVerifyBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Code';
            }
        });
    </script>

    <!-- Sign-up Verification Code Modal -->
    <div class="modal fade custom-verify-modal" id="signupVerifyCodeModal" tabindex="-1" aria-labelledby="signupVerifyCodeLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="signupVerifyCodeLabel">Verify Your Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Enter the 6-digit code sent to your email.</p>
                    <form id="signupVerifyForm">
                        <div class="mb-3">
                            <input type="text" id="signupVerificationCode" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                        </div>
                        <div class="signup-verification-message"></div>
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

    <!-- Sign-up Verification Handler -->
    <script>
        (function(){
            const form = document.getElementById('signupVerifyForm');
            if (!form) return;

            const codeInput = document.getElementById('signupVerificationCode');
            const msgDiv = form.querySelector('.signup-verification-message');
            const modal = document.getElementById('signupVerifyCodeModal');
            const pageEmailInput = document.getElementById('signupEmail');
            const pageEmailVerifyMessage = document.getElementById('emailVerifyMessage');
            const pageEmailVerifiedFlag = document.getElementById('emailVerifiedFlag');
            const pageEmailVerifyBtn = document.getElementById('emailVerifyBtn');
            const resendBtn = document.getElementById('resendCodeBtn');
            if (resendBtn) {
                resendBtn.addEventListener('click', () => {
                    // Use the global resend helper defined above
                    resendVerificationCode();
                });
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
                    const response = await fetch('api/verify-signup-code.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code: code, email: pageEmailInput.value.trim() })
                    });

                    const result = await response.json();

                    if (result.success) {
                        msgDiv.className = 'verification-message success';
                        msgDiv.textContent = 'Email verified successfully. You can now create your account.';
                        pageEmailVerifiedFlag.value = '1';
                        pageEmailVerifyBtn.innerHTML = '<i class="fas fa-check"></i> Verified';
                        pageEmailVerifyBtn.disabled = true;
                        pageEmailVerifyMessage.className = 'verification-message success';
                        pageEmailVerifyMessage.textContent = 'Email verified.';
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(modal)?.hide();
                        }, 900);
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

            modal.addEventListener('shown.bs.modal', () => {
                codeInput.focus();
            });

            modal.addEventListener('hidden.bs.modal', () => {
                msgDiv.textContent = '';
                msgDiv.className = '';
                codeInput.value = '';
            });
        })();
    </script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
</body>
</html>

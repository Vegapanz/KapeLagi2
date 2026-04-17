<?php
include 'config/session.php';

// Check if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

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
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body >
    <!-- Navigation Bar -->
     <?php include 'components/navbar.php'; ?>
    <div class="auth-page">
    
    
    <!-- Coffee Bean Decorations -->
    <div class="coffee-bean bean-1"></div>
    <div class="coffee-bean bean-2"></div>
    <div class="coffee-bean bean-3"></div>
    <div class="coffee-bean bean-4"></div>
    <div class="coffee-bean bean-5"></div>
    <div class="coffee-bean bean-6"></div>
    <div class="coffee-bean bean-7"></div>
    
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
                
                <form class="auth-form" method="POST" action="signup.php" id="signupForm">
                    <input type="text" name="name" class="auth-input" placeholder="Name" required>
                    
                    <input type="email" name="email" class="auth-input" placeholder="Email" required>
                    
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
                    
                    <input type="password" name="confirm_password" class="auth-input" id="confirm_password" placeholder="Confirm Password" required>
                    
                    <div id="password-match-status"></div>
                    
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
            
            <!-- Character Section -->
            <div class="auth-character-section">
                <div class="character-container">
                    <div class="character">
                        <div class="head"></div>
                        <div class="body"></div>
                        <div class="arm arm-left"></div>
                        <div class="arm arm-right"></div>
                        <div class="leg leg-left"></div>
                        <div class="leg leg-right"></div>
                    </div>
                    <div class="coffee-icon">
                        <div class="cup"></div>
                    </div>
                </div>
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
                statusDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                statusDiv.innerHTML = '<div style="color: #2a5534; font-size: 0.9rem; margin-top: 0.5rem;">✓ Passwords match</div>';
            } else {
                statusDiv.innerHTML = '<div style="color: #6b2626; font-size: 0.9rem; margin-top: 0.5rem;">✗ Passwords do not match</div>';
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
                alert('Password does not meet all requirements');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
        });
        
        // Initial check
        checkPasswordStrength();
    </script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
</body>
</html>

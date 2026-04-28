<?php
// Simple contact form processing with PH mobile validation
$contact_error = '';
$contact_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($email === '' || $message === '') {
        $contact_error = 'Email and message are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Please enter a valid email address.';
    } elseif ($phone !== '' && !preg_match('/^(?:\+63|0)9[0-9]{9}$/', $phone)) {
        $contact_error = 'Phone must be a Philippine mobile number (e.g. 09171234567 or +639171234567).';
    } else {
        // For now we won't send email; just acknowledge receipt.
        $contact_success = 'Thanks — your message was received.';
        // Clear inputs to avoid duplicate submission
        $_POST = array();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/contact.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'components/navbar.php'; ?>
    
    <!-- Coffee Bean Decorations
    <div class="coffee-bean bean-1"></div>
    <div class="coffee-bean bean-2"></div>
    <div class="coffee-bean bean-3"></div>
    <div class="coffee-bean bean-4"></div>
    <div class="coffee-bean bean-5"></div>
    <div class="coffee-bean bean-6"></div>
    <div class="coffee-bean bean-7"></div> -->
    
    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-card">
                <!-- Form Section -->
                <div class="contact-form-wrapper">
                    <h2 class="contact-title">Contact</h2>
                    
                    <form class="contact-form" id="contactForm" method="post" action="contact.php">
                        <?php if ($contact_error): ?>
                            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($contact_error); ?></div>
                        <?php elseif ($contact_success): ?>
                            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($contact_success); ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="contact-label">Email:</label>
                            <input type="email" class="contact-input" placeholder="Your Email" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="contact-label">Phone (optional):</label>
                            <input type="tel" name="phone" id="contact-phone" class="contact-input" placeholder="09171234567 or +639171234567" inputmode="numeric" pattern="^(?:\+63|0)9[0-9]{9}$" maxlength="13" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="contact-label">Message:</label>
                            <textarea name="message" class="contact-textarea" placeholder="Your Message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="contact_submit" class="contact-btn">Submit</button>
                    </form>
                </div>
                
                <!-- Contact Info Section -->
                <div class="contact-info-wrapper">
                    <div class="contact-info-item">
                        <i class="fab fa-facebook-f contact-icon"></i>
                        <span class="contact-info-text">KapeLagi</span>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fab fa-instagram contact-icon"></i>
                        <span class="contact-info-text">Kape.Lagi</span>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <span class="contact-info-text">0910-827-3237</span>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <span class="contact-info-text">Area C, Dasmariñas Cavite</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
    <script>
        // Restrict contact phone input to digits and optional leading +
        (function(){
            const phone = document.getElementById('contact-phone');
            if (!phone) return;
            phone.addEventListener('input', () => {
                let v = phone.value;
                v = v.replace(/[^0-9+]/g, '');
                if (v.indexOf('+') > 0) v = v.replace(/\+/g, '');
                if (v.startsWith('+')) {
                    v = '+' + v.slice(1).replace(/\+/g, '');
                } else {
                    v = v.replace(/\+/g, '');
                }
                if (phone.maxLength && v.length > phone.maxLength) v = v.slice(0, phone.maxLength);
                phone.value = v;
            });
        })();
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - KapeLagi</title>
    
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
    
    <!-- Coffee Bean Decorations -->
    <div class="coffee-bean bean-1"></div>
    <div class="coffee-bean bean-2"></div>
    <div class="coffee-bean bean-3"></div>
    <div class="coffee-bean bean-4"></div>
    <div class="coffee-bean bean-5"></div>
    <div class="coffee-bean bean-6"></div>
    <div class="coffee-bean bean-7"></div>
    
    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-card">
                <!-- Form Section -->
                <div class="contact-form-wrapper">
                    <h2 class="contact-title">Contact</h2>
                    
                    <form class="contact-form" id="contactForm">
                        <div class="form-group">
                            <label class="contact-label">Email:</label>
                            <input type="email" class="contact-input" placeholder="Your Email" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="contact-label">Message:</label>
                            <textarea class="contact-textarea" placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="contact-btn">Submit</button>
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
</body>
</html>

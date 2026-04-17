<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - KapeLagi</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/about.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'components/navbar.php'; ?>
    
    <!-- Mission Section -->
    <section class="mission-section" id="mission">
        <div class="mission-container">
            <!-- Left Coffee Beans -->
            <div class="coffee-beans-left">
                <div class="bean bean-1"></div>
                <div class="bean bean-2"></div>
                <div class="bean bean-3"></div>
                <div class="bean bean-4"></div>
                <div class="bean bean-5"></div>
            </div>
            
            <!-- Center Content -->
            <div class="mission-content">
                <h2 class="section-title">OUR MISSION</h2>
                <div class="mission-text">
                    <p>With every step,<br>with every idea,<br>with every partnership –</p>
                    <p>we ignite bold thinking and<br>unlock the boundless potential<br>within us all.</p>
                </div>
            </div>
            
            <!-- Right Coffee Splash -->
            <div class="coffee-splash-right">
                <div class="splash-cup"></div>
            </div>
        </div>
    </section>
    
    <!-- Story Section -->
    <section class="story-section" id="story">
        <div class="container-lg">
            <h2 class="section-title text-center mb-5">OUR STORY</h2>
            
            <div class="row align-items-center">
                <!-- Images -->
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="story-images">
                        <img src="assets/images/story-board.jpg" alt="KapeLagi Board" class="story-img story-img-1">
                        <img src="assets/images/story-shop.jpg" alt="KapeLagi Shop" class="story-img story-img-2">
                    </div>
                </div>
                
                <!-- Text Content -->
                <div class="col-lg-7">
                    <div class="story-text">
                        <p class="story-paragraph">
                            What began as a small dream – late nights, early mornings, and a lot of learning – slowly turned into a place where people could gather, study, talk, and take a break from their busy days.
                        </p>
                        
                        <p class="story-paragraph">
                            We may have started small, but our goal has always been big. to create a welcoming space where everyone feels comfortable. Every cup is made with care, and every customer is treated like a friend.
                        </p>
                        
                        <p class="story-paragraph">
                            This shop is built on hard work, passion, and the belief that even a simple cup of coffee can bring people together.
                        </p>
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

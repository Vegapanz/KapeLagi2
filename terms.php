<?php
include 'config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <main class="py-5" style="background:#E8E0D0; min-height: calc(100vh - 160px);">
        <div class="container" style="max-width: 900px;">
            <h1 style="font-family: var(--font-header); color:#1A0F0A;">Terms and Conditions</h1>
            <p style="font-family: var(--font-body); color:#2d1a10;">Last updated: April 26, 2026</p>

            <section class="mt-4" style="font-family: var(--font-body); color:#2d1a10; line-height:1.75;">
                <h2 style="font-family: var(--font-header); font-size:2rem;">1. Account Use</h2>
                <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.</p>

                <h2 style="font-family: var(--font-header); font-size:2rem;" class="mt-4">2. Orders and Payments</h2>
                <p>All prices are listed in Philippine Peso and may change without prior notice. Orders are subject to product availability and confirmation.</p>

                <h2 style="font-family: var(--font-header); font-size:2rem;" class="mt-4">3. Accuracy of Information</h2>
                <p>You agree to provide accurate and complete information during registration and checkout.</p>

                <h2 style="font-family: var(--font-header); font-size:2rem;" class="mt-4">4. Acceptable Use</h2>
                <p>You agree not to misuse the platform, attempt unauthorized access, or perform actions that may disrupt the service.</p>

                <h2 style="font-family: var(--font-header); font-size:2rem;" class="mt-4">5. Privacy</h2>
                <p>Your personal information is used only to process orders, manage your account, and improve service quality.</p>

                <h2 style="font-family: var(--font-header); font-size:2rem;" class="mt-4">6. Changes to Terms</h2>
                <p>We may update these terms from time to time. Continued use of the service means you accept the updated terms.</p>
            </section>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

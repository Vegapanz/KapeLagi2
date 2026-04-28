<?php
// Admin Access Check
include '../config/session.php';

if (!is_logged_in()) {
    header('Location: ../signin.php');
    exit;
}

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
?>

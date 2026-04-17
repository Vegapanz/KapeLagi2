<?php
// Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only define functions if they haven't been defined yet
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }

    function get_user_id() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    function get_user_name() {
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
    }

    function login_user($user_id, $user_name, $user_email) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $user_email;
    }

    function logout_user() {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

?>

<?php
session_start();
include('connection.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Find user with the activation token
    $stmt = $conn->prepare("SELECT * FROM users WHERE activation_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update user to activated
        $stmt = $conn->prepare("UPDATE users SET is_activated = 1, activation_token = NULL WHERE activation_token = ?");
        if ($stmt->execute([$token])) {
            // If user is already logged in, update session
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['user_id']) {
                // Redirect to main page
                echo "<script>alert('Account activated successfully!'); window.location.href='http://localhost/take-note-app/';</script>";
            } else {
                // If not logged in, redirect to login page
                echo "<script>alert('Account activated successfully! You can now log in.'); window.location.href='http://localhost/take-note-app/login_register.php';</script>";
            }
        } else {
            echo "<script>alert('Failed to activate account. Please try again.'); window.location.href='http://localhost/take-note-app/login_register.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired activation token.'); window.location.href='http://localhost/take-note-app/login_register.php';</script>";
    }
} else {
    header('Location: http://localhost/take-note-app/login_register.php');
    exit();
}
?>
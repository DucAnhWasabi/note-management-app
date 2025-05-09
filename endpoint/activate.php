<?php
include('../connection/connection.php');

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
            echo "<script>alert('Account activated successfully! You can now log in.'); window.location.href='http://localhost/take-note-app/Login_Register.php';</script>";
        } else {
            echo "<script>alert('Failed to activate account. Please try again.'); window.location.href='http://localhost/take-note-app/Login_Register.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired activation token.'); window.location.href='http://localhost/take-note-app/Login_Register.php';</script>";
    }
} else {
    header('Location: http://localhost/take-note-app/Login_Register.php');
    exit();
}
?>
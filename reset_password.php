<?php
session_start();
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $enteredOtp = $_POST['otp'];

    if (!isset($_SESSION['otp']) || !isset($_SESSION['reset_email'])) {
        die("Session expired. Please try again.");
    }

    if ($enteredOtp == $_SESSION['otp']) {
        $_SESSION['otp_verified'] = true; // đánh dấu đã xác thực OTP
    } else {
        echo "<script>alert('Incorrect OTP.');window.location='verify_otp.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    if (!isset($_SESSION['otp_verified'])) {
        die("Unauthorized access.");
    }

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $_SESSION['reset_email']]);

        session_unset();
        session_destroy();
        echo "<script>alert('Password reset successfully. Please log in again.');window.location='login_register.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            margin: 0;
            background: linear-gradient(to right, #a1c4fd, #c2e9fb);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .reset-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            box-shadow: 0 15px 25px rgba(0,0,0,0.1);
        }
        .reset-container h2 {
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
        }
        .reset-container input {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f5f5f5;
        }
        .reset-container button {
            width: 100%;
            padding: 12px;
            border: none;
            background: #6c9df0;
            color: white;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }
        .reset-container button:hover {
            background: #537de0;
        }
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Your Password</h2>
        <form method="POST">
            <input type="password" name="new_password" placeholder="New password" required>
            <input type="password" name="confirm_password" placeholder="Confirm password" required>
            <button type="submit" name="reset">Reset Password</button>
        </form>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </div>
</body>
</html>

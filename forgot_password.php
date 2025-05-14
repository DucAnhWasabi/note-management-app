<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #c9d6ff;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #7494ec;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #7494ec;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Reset Password</h2>
        <form action="send_otp.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send OTP</button>
        </form>
        <a href="login_register.php">Back to Login</a>
    </div>
</body>
</html>

<?php
session_start();
include('connection.php');
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle registration
if (isset($_POST['register'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $display_name = filter_var($_POST['display_name'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } elseif (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.');</script>";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "<script>alert('Email already registered.');</script>";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            // Generate activation token
            $activation_token = bin2hex(random_bytes(32));
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (email, display_name, password, activation_token, is_activated) VALUES (?, ?, ?, ?, 0)");
            if ($stmt->execute([$email, $display_name, $hashed_password, $activation_token])) {
                // Send activation email
                $mail = new PHPMailer(true);
                try {
                    // SMTP settings (example for Gmail; adjust for your SMTP server)
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'nguyenducanhwasabi@gmail.com'; // Replace with your email
                    $mail->Password = 'uwhzimadnnbeolad'; // Replace with your app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('nguyenducanhwasabi@gmail.com', 'Take-Note App');
                    $mail->addAddress($email);

                    // Content
                    $activation_link = "http://localhost/take-note-app/activate.php?token=$activation_token";
                    $mail->isHTML(true);
                    $mail->Subject = 'Activate Your Take-Note App Account';
                    $mail->Body = "Hello $display_name,<br><br>Please click the following link to activate your account:<br><a href='$activation_link'>Activate Account</a><br><br>Thank you!";
                    $mail->AltBody = "Hello $display_name,\n\nPlease visit the following link to activate your account:\n$activation_link\n\nThank you!";

                    $mail->send();
                    // Automatically log in the user
                    $_SESSION['display_name'] = $display_name;
                    $_SESSION['user_id'] = $conn->lastInsertId();
                    echo "<script>alert('Registration successful! Please check your email to activate your account.');</script>";
                    header('Location: http://localhost/take-note-app/');
                    exit();
                } catch (Exception $e) {
                    echo "<script>alert('Registration successful, but failed to send activation email. Error: {$mail->ErrorInfo}');</script>";
                }
            } else {
                echo "<script>alert('Registration failed. Please try again.');</script>";
            }
        }
    }
}

// Handle login
if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData && password_verify($password, $userData['password'])) {
        if ($userData['is_activated'] == 1) {
            $_SESSION['display_name'] = $userData['display_name'];
            $_SESSION['user_id'] = $userData['user_id'];
            header('Location: http://localhost/take-note-app/');
            exit();
        } else {
            $_SESSION['display_name'] = $userData['display_name'];
            $_SESSION['user_id'] = $userData['user_id'];
            echo "<script>alert('Please activate your account via the email link.');</script>";
            header('Location: http://localhost/take-note-app/');
            exit();
        }
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Form</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
            text-decoration: none;
            list-style: none;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(90deg, #e2e2e2, #c9d6ff);
        }
        .container {
            position: relative;
            width: 850px;
            height: 550px;
            background: #fff;
            margin: 20px;
            border-radius: 30px;
            box-shadow: 0 0 30px rgba(0, 0, 0, .2);
            overflow: hidden;
        }
        .container h1 {
            font-size: 36px;
            margin: -10px 0;
        }
        .container p {
            font-size: 14.5px;
            margin: 15px 0;
        }
        form { width: 100%; }
        .form-box {
            position: absolute;
            right: 0;
            width: 50%;
            height: 100%;
            background: #fff;
            display: flex;
            align-items: center;
            color: #333;
            text-align: center;
            padding: 40px;
            z-index: 1;
            transition: .6s ease-in-out 1.2s, visibility 0s 1s;
        }
        .container.active .form-box { right: 50%; }
        .form-box.register { visibility: hidden; }
        .container.active .form-box.register { visibility: visible; }
        .input-box {
            position: relative;
            margin: 30px 0;
        }
        .input-box input {
            width: 100%;
            padding: 13px 50px 13px 20px;
            background: #eee;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        .input-box input::placeholder {
            color: #888;
            font-weight: 400;
        }
        .input-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
        }
        .forgot-link { margin: -15px 0 15px; }
        .forgot-link a {
            font-size: 14.5px;
            color: #333;
        }
        .btn {
            width: 100%;
            height: 48px;
            background: #7494ec;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #fff;
            font-weight: 600;
        }
        .social-icons {
            display: flex;
            justify-content: center;
        }
        .social-icons a {
            display: inline-flex;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 24px;
            color: #333;
            margin: 0 8px;
        }
        .toggle-box {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        .toggle-box::before {
            content: '';
            position: absolute;
            left: -250%;
            width: 300%;
            height: 100%;
            background: #7494ec;
            border-radius: 150px;
            z-index: 2;
            transition: 1.8s ease-in-out;
        }
        .container.active .toggle-box::before { left: 50%; }
        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2;
            transition: .6s ease-in-out;
        }
        .toggle-panel.toggle-left {
            left: 0;
            transition-delay: 1.2s;
        }
        .container.active .toggle-panel.toggle-left {
            left: -50%;
            transition-delay: .6s;
        }
        .toggle-panel.toggle-right {
            right: -50%;
            transition-delay: .6s;
        }
        .container.active .toggle-panel.toggle-right {
            right: 0;
            transition-delay: 1.2s;
        }
        .toggle-panel p { margin-bottom: 20px; }
        .toggle-panel .btn {
            width: 160px;
            height: 46px;
            background: transparent;
            border: 2px solid #fff;
            box-shadow: none;
        }
        @media screen and (max-width: 650px) {
            .container { height: calc(100vh - 40px); }
            .form-box {
                bottom: 0;
                width: 100%;
                height: 70%;
            }
            .container.active .form-box {
                right: 0;
                bottom: 30%;
            }
            .toggle-box::before {
                left: 0;
                top: -270%;
                width: 100%;
                height: 300%;
                border-radius: 20vw;
            }
            .container.active .toggle-box::before {
                left: 0;
                top: 70%;
            }
            .container.active .toggle-panel.toggle-left {
                left: 0;
                top: -30%;
            }
            .toggle-panel {
                width: 100%;
                height: 30%;
            }
            .toggle-panel.toggle-left { top: 0; }
            .toggle-panel.toggle-right {
                right: 0;
                bottom: -30%;
            }
            .container.active .toggle-panel.toggle-right { bottom: 0; }
        }
        @media screen and (max-width: 400px) {
            .form-box { padding: 20px; }
            .toggle-panel h1 { font-size: 30px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box login">
            <form method="POST" action="">
                <h1>Login</h1>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <button type="submit" name="login" class="btn">Login</button>   
                <button type="button" onclick="window.location.href='forgot_password.php';" class="btn" style="margin-top: 10px; background-color: #f0f0f0; color: #333; box-shadow: none; border: 1px solid #ccc;">
                    Forgot Password?
                </button>

            </form>
        </div>
        <div class="form-box register">
            <form method="POST" action="">
                <h1>Registration</h1>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-box">
                    <input type="text" name="display_name" placeholder="Display Name" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <button type="submit" name="register" class="btn">Register</button>
            </form>
        </div>
        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Register</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>
    <script>
        const container = document.querySelector('.container');
        const registerBtn = document.querySelector('.register-btn');
        const loginBtn = document.querySelector('.login-btn');
        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        });
        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });
    </script>
</body>
</html>

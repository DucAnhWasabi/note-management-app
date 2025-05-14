<?php
session_start();
include('connection.php');
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email;

        // Gửi email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nguyenducanhwasabi@gmail.com';
            $mail->Password = 'uwhzimadnnbeolad';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('nguyenducanhwasabi@gmail.com', 'Take-Note App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body = "Your OTP code is: <b>$otp</b>";
            $mail->send();

            header("Location: verify_otp.php");
            exit;
        } catch (Exception $e) {
            echo "Failed to send OTP: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('Email not found.');window.location='forgot_password.php';</script>";
    }
}

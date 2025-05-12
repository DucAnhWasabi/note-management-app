<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register.php');
    exit();
}
include('connection.php');

// Verify user exists
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
if (!$stmt->fetch()) {
    session_unset();
    session_destroy();
    header('Location: ../login_register.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $noteTitle = filter_var($_POST["note_title"], FILTER_SANITIZE_STRING);
    $noteContent = filter_var($_POST["note_content"], FILTER_SANITIZE_STRING);
    $dateTime = date("Y-m-d H:i:s");

    try {
        $stmt = $conn->prepare("INSERT INTO tbl_notes (user_id, note_title, note, date_time) VALUES (:user_id, :note_title, :note, :date_time)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':note_title', $noteTitle);
        $stmt->bindParam(':note', $noteContent);
        $stmt->bindParam(':date_time', $dateTime);
        $stmt->execute();
        header("Location: http://localhost/take-note-app/");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        header("Location: http://localhost/take-note-app/");
        exit();
    }
} else {
    header("Location: http://localhost/take-note-app/");
    exit();
}
?>
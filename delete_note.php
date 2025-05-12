<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register.php');
    exit();
}
include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete'])) {
    $noteID = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
    $userId = $_SESSION['user_id'];

    // Verify the note belongs to the user
    $stmt = $conn->prepare("SELECT tbl_notes_id FROM `tbl_notes` WHERE tbl_notes_id = :note_id AND user_id = :user_id");
    $stmt->bindParam(':note_id', $noteID);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    if ($stmt->fetch()) {
        // Delete the note
        $stmt = $conn->prepare("DELETE FROM `tbl_notes` WHERE tbl_notes_id = :note_id AND user_id = :user_id");
        $stmt->bindParam(':note_id', $noteID);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            header("Location: http://localhost/take-note-app/");
            exit();
        } else {
            header("Location: http://localhost/take-note-app/");
            exit();
        }
    } else {
        header("Location: http://localhost/take-note-app/");
        exit();
    }
} else {
    header("Location: http://localhost/take-note-app/");
    exit();
}
?>
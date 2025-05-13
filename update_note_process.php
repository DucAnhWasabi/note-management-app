<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit();
}
include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $noteID = filter_var($_POST['note_id'], FILTER_SANITIZE_NUMBER_INT);
    $newTitle = filter_var($_POST['note_title'], FILTER_SANITIZE_STRING);
    $newContent = filter_var($_POST['note_content'], FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];

    // Verify the note belongs to the user
    $stmt = $conn->prepare("SELECT tbl_notes_id FROM `tbl_notes` WHERE tbl_notes_id = :note_id AND user_id = :user_id");
    $stmt->bindParam(':note_id', $noteID);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    if ($stmt->fetch()) {
        // Update the note
        $stmt = $conn->prepare("UPDATE `tbl_notes` SET note_title = :title, note = :content WHERE tbl_notes_id = :note_id AND user_id = :user_id");
        $stmt->bindParam(':title', $newTitle);
        $stmt->bindParam(':content', $newContent);
        $stmt->bindParam(':note_id', $noteID);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            header("Location: note_edit.php?edit=$noteID&error=1");
            exit();
        }
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>

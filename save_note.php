<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}
include('connection.php');

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['note_id']) && isset($_POST['content'])) {
    $note_id = filter_var($_POST['note_id'], FILTER_SANITIZE_NUMBER_INT);
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("UPDATE `tbl_notes` SET note = :content WHERE tbl_notes_id = :note_id AND user_id = :user_id");
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $success = $stmt->execute();
        if ($success) {
            echo "<script>window.parent.postMessage({status: 'success'}, '*');</script>";
        } else {
            error_log("Update failed, no rows affected");
            echo "<script>window.parent.postMessage({status: 'error', message: 'Update failed'}, '*');</script>";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "<script>window.parent.postMessage({status: 'error', message: '" . addslashes($e->getMessage()) . "'}, '*');</script>";
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        echo "<script>window.parent.postMessage({status: 'error', message: '" . addslashes($e->getMessage()) . "'}, '*');</script>";
    }
} else {
    error_log("Invalid request to save_note.php");
    echo "<script>window.parent.postMessage({status: 'error', message: 'Invalid request'}, '*');</script>";
}
?>
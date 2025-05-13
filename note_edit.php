<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit();
}
include('connection.php');

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Kiểm tra trạng thái kích hoạt
$is_activated = true;
$stmt = $conn->prepare("SELECT is_activated FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && $user['is_activated'] == 0) {
    $is_activated = false;
}

// Initialize variables
$note_id = null;
$note_title = '';
$note_content = '';
$save_status = '';

// Log session data for debugging
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

// Handle GET parameters
if (isset($_GET['edit'])) {
    $note_id = filter_var($_GET['edit'], FILTER_SANITIZE_NUMBER_INT);
    error_log("Editing note with ID: $note_id");
    $stmt = $conn->prepare("SELECT * FROM `tbl_notes` WHERE tbl_notes_id = :note_id AND user_id = :user_id");
    $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($note) {
        $note_title = $note['note_title'];
        $note_content = $note['note'];
    } else {
        error_log("Note not found for ID: $note_id");
        header('Location: index.php');
        exit();
    }
} else {
    error_log("No edit parameter provided");
    header('Location: index.php');
    exit();
}

if (isset($_GET['saved'])) {
    $save_status = 'Saved successfully';
} elseif (isset($_GET['error'])) {
    $save_status = 'Error saving: ' . htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take-Note App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap');

        body {
            font-family: 'Patrick Hand', cursive;
        }

        .main-panel, .card {
            margin: auto;
            height: 90vh;
            overflow-y: auto;
        }

        .card {
            background-color: #ffffff;
            border: 4px solid #a8e6cf !important;
            border-radius: 20px !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #dcedc1;
            color: #33691e;
            font-size: 24px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            text-align: center;
        }
        .card-body input[type="text"] {
            background-color: #e0f7fa;
            border: 2px dashed #a8e6cf;
            border-radius: 10px;
            font-size: 18px;
        }
        .card-body textarea {
            background-image: linear-gradient(white 95%, #e0f7fa 5%);
            background-size: 100% 32px;
            line-height: 32px;
            font-size: 16px;
            border: 2px dashed #a8e6cf;
            border-radius: 10px;
            resize: none;
            height: 400px;
            width: 100%;
        }
        .btn-danger {
            background-color: #ef5350;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .btn-danger:hover {
            background-color: #e53935;
        }
        .save-status {
            font-size: 14px;
            color: #33691e;
            margin-top: 10px;
            display: none;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            display: none;
        }
        .hidden-iframe {
            display: none;
        }

        .custom-navbar {
            background-color: #a8e6cf;
            background: linear-gradient(to right, #a8e6cf, #dcedc1);
            border-radius: 122px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.155);
        }
        .navbar-brand {
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        /* Navbar Create Note Button */
        .nav-item .btn-create-note {
            background-color: #81c784;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 8px 15px;
            color: white;
            transition: background-color 0.3s ease;
        }

        .nav-item .btn-create-note:hover {
            background-color: #66bb6a;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light custom-navbar shadow-sm rounded mx-3 my-3 px-4">
        <a class="navbar-brand font-weight-bold" href="#" style="font-size: 28px; color: #2e7d32;">
            🍀 Take-Note App
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                
                <li class="nav-item dropdown ml-2">
                    <a class="nav-link dropdown-toggle font-weight-bold" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false" style="color: #33691e;">
                      <?php echo htmlspecialchars($_SESSION['display_name']); ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#">View Account</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Log Out</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <?php if (!$is_activated): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin: 20px auto; max-width: 90%;">
        <strong>Your account is not yet activated!</strong> Please check your email to activate your account.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($save_status): ?>
    <div class="save-status" id="saveStatus"><?php echo $save_status; ?></div>
    <script>
        document.getElementById('saveStatus').style.display = 'block';
        setTimeout(() => document.getElementById('saveStatus').style.display = 'none', 5000);
    </script>
    <?php endif; ?>

    <div class="main-panel mt-4 ml-5 col-11">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Edit Note
                    </div>
                    <div class="card-body">
                        <form id="noteForm" method="post" action="save_note.php" target="saveIframe">
                            <input type="hidden" name="note_id" value="<?php echo $note_id; ?>">
                            <div class="form-group">
                                <label for="noteTitle">Title</label>
                                <input type="text" class="form-control" id="noteTitle" value="<?php echo htmlspecialchars($note_title); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="noteContent">Content</label>
                                <textarea class="form-control" id="noteContent" name="content" rows="23"><?php echo htmlspecialchars($note_content); ?></textarea>
                            </div>
                        </form>
                        <iframe name="saveIframe" class="hidden-iframe"></iframe>
                        <div class="save-status" id="saveStatus"></div>
                        <a href="index.php" class="btn btn-danger">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            const textarea = $('#noteContent');
            const saveStatus = $('#saveStatus');
            const form = $('#noteForm');
            let lastContent = textarea.val();

            // Debounce function to handle auto-save
            const debouncedSave = _.debounce(function() {
                const content = textarea.val();
                if (content === lastContent) return;

                saveStatus.text('Saving...');
                form.submit(); // Submit the form to the hidden iframe
                lastContent = content;
            }, 1000); // Delay of 1 second

            textarea.on('input', function() {
                debouncedSave();
            });

            // Listen for messages from the iframe
            window.addEventListener('message', function(e) {
                if (e.data.status === 'success') {
                    saveStatus.text('Saved successfully');
                } else if (e.data.status === 'error') {
                    saveStatus.text('Error saving: ' + e.data.message).css('color', 'red').fadeIn(500).delay(2000).fadeOut(500);
                }
            });
        });
    </script>
</body>
</html>
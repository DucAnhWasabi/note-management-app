<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit();
}
include('connection.php');

// Lấy tùy chỉnh của người dùng
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->execute([$user_id]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không có tùy chỉnh, sử dụng giá trị mặc định
if (!$preferences) {
    $preferences = [
        'font_size' => 'medium',
        'note_color' => 'bg-pastel-1',
        'theme' => 'light'
    ];
}

// Kiểm tra trạng thái kích hoạt
$is_activated = true;
$stmt = $conn->prepare("SELECT is_activated FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && $user['is_activated'] == 0) {
    $is_activated = false;
}

// Handle new note creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['note_title'])) {
    $note_title = filter_var($_POST['note_title'], FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];
    $content = ''; // Empty content initially
    $date_time = date("Y-m-d H:i:s");

    try {
        $stmt = $conn->prepare("INSERT INTO tbl_notes (user_id, note_title, note, date_time) VALUES (:user_id, :note_title, :note, :date_time)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':note_title', $note_title);
        $stmt->bindParam(':note', $content);
        $stmt->bindParam(':date_time', $date_time);
        $stmt->execute();
        $note_id = $conn->lastInsertId();
        header("Location: note_edit.php?edit=" . $note_id);
        exit();
    } catch (PDOException $e) {
        error_log("Error creating new note: " . $e->getMessage());
        // Optionally, show an error to the user
    }
}

// Fetch user's notes
$notes = [];
$stmt = $conn->prepare("SELECT * FROM tbl_notes WHERE user_id = ? ORDER BY date_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#2c2c2c' : '#f4f6f9'; ?>;
            color: <?php echo $preferences['theme'] == 'dark' ? '#e0e0e0' : '#333'; ?>;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .main-panel, .card {
            margin: auto;
            height: 90vh;
            overflow-y: auto;
        }

        .card {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#3a3a3a' : '#ffffff'; ?>;
            border: 4px solid <?php echo $preferences['theme'] == 'dark' ? '#555555' : '#a8e6cf'; ?> !important;
            border-radius: 20px !important;
            box-shadow: 0 6px 20px rgba(0,0,0,<?php echo $preferences['theme'] == 'dark' ? '0.3' : '0.1'; ?>);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .card-header {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#4a4a4a' : '#dcedc1'; ?>;
            color: <?php echo $preferences['theme'] == 'dark' ? '#a8e6cf' : '#33691e'; ?>;
            font-size: 24px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            text-align: center;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-secondary, .btn-toggle {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#66bb6a' : '#81c784'; ?>;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 10px 20px;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover, .btn-toggle:hover {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#88cc88' : '#66bb6a'; ?>;
        }

        .btn-light {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#666' : '#e2e6ea'; ?>;
            border-radius: 12px;
            color: <?php echo $preferences['theme'] == 'dark' ? '#e0e0e0' : '#333'; ?>;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-light:hover {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#777' : '#d6d8db'; ?>;
        }

        /* Áp dụng kích thước phông chữ */
        .note-item h3 {
            color: black;
            font-size: <?php echo $preferences['font_size'] == 'small' ? '16px' : ($preferences['font_size'] == 'large' ? '24px' : '20px'); ?>;
            transition: color 0.3s ease;
        }

        .note-content {
            color: black;
            font-size: <?php echo $preferences['font_size'] == 'small' ? '12px' : ($preferences['font_size'] == 'large' ? '18px' : '16px'); ?>;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            transition: color 0.3s ease;
        }

        .note-item .text-muted {
            color: <?php echo $preferences['theme'] == 'dark' ? 'black !important' : '#6c757d !important'; ?>;
            transition: color 0.3s ease;
        }

        .note-item .text-info {
            color: <?php echo $preferences['theme'] == 'dark' ? 'black !important' : '#17a2b8 !important'; ?>;
            transition: color 0.3s ease;
        }

        /* Grid View */
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .note-item {
            transition: all 0.2s ease;
            border-radius: 12px;
            border: 1px solid <?php echo $preferences['theme'] == 'dark' ? '#555555' : '#ddd'; ?>;
            box-shadow: 0 2px 5px rgba(0,0,0,<?php echo $preferences['theme'] == 'dark' ? '0.3' : '0.05'; ?>);
            padding: 15px;
            cursor: pointer;
        }

        .note-item:hover {
            filter: brightness(95%);
            transform: scale(1.01);
        }

        /* List View */
        .notes-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px;
        }

        .notes-list .note-item {
            width: 100%;
        }

        /* Pastel Colors */
        .bg-pastel-1 { background-color: #f8e1e9 !important; }
        .bg-pastel-2 { background-color: #e1f8dc !important; }
        .bg-pastel-3 { background-color: #dcedf8 !important; }
        .bg-pastel-4 { background-color: #f8f1dc !important; }
        .bg-pastel-5 { background-color: #e6d8f8 !important; }
        .bg-pastel-6 { background-color: #f8dcd8 !important; }

        /* Modal Styling */
        .modal-content {
            border: 4px solid <?php echo $preferences['theme'] == 'dark' ? '#555555' : '#a8e6cf'; ?>;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,<?php echo $preferences['theme'] == 'dark' ? '0.3' : '0.1'; ?>);
            transition: border-color 0.3s ease;
        }

        .modal-header {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#4a4a4a' : '#dcedc1'; ?>;
            color: <?php echo $preferences['theme'] == 'dark' ? '#a8e6cf' : '#33691e'; ?>;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .modal-title {
            font-size: 24px;
            text-transform: uppercase;
        }

        .modal-body {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#3a3a3a' : '#ffffff'; ?>;
            font-size: 16px;
            white-space: pre-wrap;
            color: <?php echo $preferences['theme'] == 'dark' ? '#e0e0e0' : '#333'; ?>;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .modal-footer {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#3a3a3a' : '#ffffff'; ?>;
            border-top: none;
            transition: background-color 0.3s ease;
        }

        .custom-navbar {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#444444' : '#a8e6cf'; ?>;
            background: <?php echo $preferences['theme'] == 'dark' ? 'linear-gradient(to right, #444444, #555555)' : 'linear-gradient(to right, #a8e6cf, #dcedc1)'; ?>;
            border-radius: 122px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.155);
            transition: background 0.3s ease;
        }

        .navbar-brand {
            text-shadow: <?php echo $preferences['theme'] == 'dark' ? '1px 1px 2px rgba(0,0,0,0.5)' : '1px 1px 2px rgba(0,0,0,0.2)'; ?>;
            color: <?php echo $preferences['theme'] == 'dark' ? '#a8e6cf !important' : '#2e7d32 !important'; ?>;
            transition: color 0.3s ease;
        }

        .nav-link {
            color: <?php echo $preferences['theme'] == 'dark' ? '#a8e6cf !important' : '#33691e !important'; ?>;
            transition: color 0.3s ease;
        }

        /* Navbar Create Note Button */
        .nav-item .btn-create-note {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#66bb6a' : '#81c784'; ?>;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 8px 15px;
            color: white;
            transition: background-color 0.3s ease;
        }

        .nav-item .btn-create-note:hover {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#88cc88' : '#66bb6a'; ?>;
        }

        /* Modal Form Styling */
        .modal-body input[type="text"] {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#555555' : '#e0f7fa'; ?>;
            border: 2px dashed <?php echo $preferences['theme'] == 'dark' ? '#777777' : '#a8e6cf'; ?>;
            border-radius: 10px;
            font-size: 18px;
            padding: 12px;
            width: 100%;
            color: <?php echo $preferences['theme'] == 'dark' ? '#e0e0e0' : '#333'; ?>;
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .modal-body input[type="text"]:focus {
            border-color: <?php echo $preferences['theme'] == 'dark' ? '#88cc88' : '#66bb6a'; ?>;
            box-shadow: 0 0 5px rgba(<?php echo $preferences['theme'] == 'dark' ? '136, 204, 136' : '102, 187, 106'; ?>, 0.5);
            outline: none;
        }

        .modal-footer .btn-primary {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#66bb6a' : '#81c784'; ?>;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 10px 20px;
            color: white;
            transition: background-color 0.3s ease;
        }

        .modal-footer .btn-primary:hover {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#88cc88' : '#66bb6a'; ?>;
        }

        /* Alert Styling */
        .alert-warning {
            background-color: <?php echo $preferences['theme'] == 'dark' ? '#665522' : '#ffeeba'; ?>;
            color: <?php echo $preferences['theme'] == 'dark' ? '#ffeeba' : '#856404'; ?>;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light custom-navbar shadow-sm rounded mx-3 my-3 px-4">
        <a class="navbar-brand font-weight-bold" href="#" style="font-size: 28px;">
            🍀 Take-Note App
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <button class="btn btn-create-note" data-toggle="modal" data-target="#createNoteModal">
                        Create Note
                    </button>
                </li>
                <li class="nav-item dropdown ml-2">
                    <a class="nav-link dropdown-toggle font-weight-bold" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION['display_name']); ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="user_preferences.php">Preferences</a>
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

    <!-- Create Note Modal -->
    <div class="modal fade" id="createNoteModal" tabindex="-1" role="dialog" aria-labelledby="createNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createNoteModalLabel">Create New Note</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" class="form-control" id="noteTitle" name="note_title" placeholder="Enter note title" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="main-panel mt-4 ml-5 col-11">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Your Notes
                        <button class="btn btn-toggle float-right" id="viewToggleBtn">Switch to List View</button>
                    </div>
                    <div class="card-body">
                        <div class="notes-grid" id="notesContainer">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM `tbl_notes` WHERE user_id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $result = $stmt->fetchAll();
                            foreach ($result as $row) {
                                $noteID = $row['tbl_notes_id'];
                                $noteTitle = $row['note_title'];
                                $noteContent = $row['note'];
                                $noteDateTime = $row['date_time'];
                                $formattedDateTime = date('F j, Y H:i A', strtotime($noteDateTime));
                            ?>
                            <div class="note-item <?php echo $preferences['note_color']; ?>" data-toggle="modal" data-target="#noteModal" data-title="<?php echo htmlspecialchars($noteTitle); ?>" data-content="<?php echo htmlspecialchars($noteContent); ?>" data-time="<?php echo $formattedDateTime; ?>">
                                <div class="btn-group float-right">
                                    <a href="note_edit.php?edit=<?php echo $noteID ?>"><button type="button" class="btn btn-sm btn-light" title="Edit"><i class="fa fa-pencil"></i></button></a>
                                    <button onclick="delete_note('<?php echo $noteID ?>')" type="button" class="btn btn-sm btn-light" title="Delete"><i class="fa fa-trash"></i></button>
                                </div>
                                <h3 style="text-transform:uppercase;"><b><?php echo htmlspecialchars($noteTitle); ?></b></h3>
                                <p class="note-content"><?php echo htmlspecialchars($noteContent); ?></p>
                                <small class="block text-muted text-info">Created: <i class="fa fa-clock-o text-info"></i> <?php echo $formattedDateTime; ?></small>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noteModalTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body" id="noteModalContent"></div>
                        <div class="modal-footer">
                            <small class="text-muted text-info" id="noteModalTime"></small>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function delete_note(id) {
            if (confirm("Do you confirm to delete this note?")) {
                window.location = "delete_note.php?delete=" + id;
            }
        }

        $(document).ready(function() {
            $('.note-item').on('click', function(e) {
                if ($(e.target).closest('.btn-group').length) {
                    return;
                }
                var title = $(this).data('title');
                var content = $(this).data('content');
                var time = $(this).data('time');
                
                $('#noteModalTitle').text(title);
                $('#noteModalContent').text(content);
                $('#noteModalTime').html('Created: <i class="fa fa-clock-o text-info"></i> ' + time);
                
                $('#noteModal').modal('show');
            });

            $('#viewToggleBtn').on('click', function() {
                const container = $('#notesContainer');
                const isGrid = container.hasClass('notes-grid');
                container.toggleClass('notes-grid notes-list');
                $(this).text(isGrid ? 'Switch to Grid View' : 'Switch to List View');
            });
        });
    </script>
</body>
</html>

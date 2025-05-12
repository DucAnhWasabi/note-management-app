<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit();
}
include('connection.php');

// Kiểm tra trạng thái kích hoạt
$is_activated = true;
$stmt = $conn->prepare("SELECT is_activated FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && $user['is_activated'] == 0) {
    $is_activated = false;
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
        }
        .btn-secondary {
            background-color: #81c784;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .btn-secondary:hover {
            background-color: #66bb6a;
        }
        .note-content {
            max-height: 20em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .list-group-item {
            transition: all 0.2s ease;
            border-radius: 12px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .list-group-item:hover {
            filter: brightness(95%);
            transform: scale(1.01);
        }

        .btn-light {
            background-color: #e2e6ea;
            border-radius: 12px;
        }

        .btn-light:hover {
            background-color: #d6d8db;
        }

        /* Thêm các lớp màu pastel */
        .bg-pastel-1 { background-color: #f8e1e9 !important; }
        .bg-pastel-2 { background-color: #e1f8dc !important; }
        .bg-pastel-3 { background-color: #dcedf8 !important; }
        .bg-pastel-4 { background-color: #f8f1dc !important; }
        .bg-pastel-5 { background-color: #e6d8f8 !important; }
        .bg-pastel-6 { background-color: #f8dcd8 !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark col-12">
        <a class="navbar-brand" href="#">Take-Note App</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION['display_name']); ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#">View Account</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Log Out</a>
                    </div>
                </li>
                <li class="nav-item">
                    <form class="form-inline my-2 my-lg-0">
                        <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-secondary my-2 my-sm-0" type="submit">Search</button>
                    </form>
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

    <div class="main-panel mt-4 ml-5 col-11">
        <div class="row">
            <!-- Notes Detail - chiếm 1/3 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Notes Details
                        <a href="all_notes.php" class="float-right">View All Notes</a>
                    </div>
                    <div class="card-body">
                        <div class="data-item">
                            <ul class="list-group">
                                <?php
                                // Danh sách các lớp màu
                                $colors = ['bg-pastel-1', 'bg-pastel-2', 'bg-pastel-3', 'bg-pastel-4', 'bg-pastel-5', 'bg-pastel-6'];

                                $stmt = $conn->prepare("SELECT * FROM `tbl_notes` WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $result = $stmt->fetchAll();
                                foreach ($result as $row) {
                                    $noteID = $row['tbl_notes_id'];
                                    $noteTitle = $row['note_title'];
                                    $noteContent = $row['note'];
                                    $noteDateTime = $row['date_time'];
                                    $formattedDateTime = date('F j, Y H:i A', strtotime($noteDateTime));
                                    
                                    // Chọn ngẫu nhiên một màu từ danh sách
                                    $randomColor = $colors[array_rand($colors)];
                                ?>
                                <li class="list-group-item mt-2 <?php echo $randomColor; ?>">
                                    <div class="btn-group float-right">
                                        <a href="update_note.php?edit=<?php echo $noteID ?>"><button type="button" class="btn btn-sm btn-light" title="Show"><i class="fa fa-pencil"></i></button></a>
                                        <button onclick="delete_note('<?php echo $noteID ?>')" type="button" class="btn btn-sm btn-light" title="Remove"><i class="fa fa-trash"></i></button>
                                    </div>
                                    <h3 style="text-transform:uppercase;"><b><?php echo htmlspecialchars($noteTitle) ?></b></h3>
                                    <p class="note-content"><?php echo htmlspecialchars($noteContent) ?></p>
                                    <small class="block text-muted text-info">Created: <i class="fa fa-clock-o text-info"></i> <?php echo $formattedDateTime ?></small>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add Note - chiếm 2/3 -->
            <div class="col-md-8 border-right">
                <div class="card">
                    <div class="card-header">
                        🐸 Add Your Cute Note 🥑
                    </div>
                    <div class="card-body">
                        <form method="post" action="add_note.php">
                            <div class="form-group">
                                <label for="noteTitle">Title</label>
                                <input type="text" class="form-control" id="noteTitle" name="note_title" placeholder="Title">
                                <small id="emailHelp" class="form-text text-muted">Title of your note</small>
                            </div>
                            <div class="form-group">
                                <label for="note">Note</label>
                                <textarea class="form-control" id="note" name="note_content" rows="23"></textarea>
                            </div>
                            <button type="submit" class="btn btn-secondary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function delete_note(id) {
            if (confirm("Do you confirm to delete this note?")) {
                window.location = "delete_note.php?delete=" + id;
            }
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
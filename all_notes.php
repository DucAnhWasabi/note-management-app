<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: Login_Register.php');
    exit();
}
include('connection/connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take-Note App</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        /* Custom CSS */
        .main-panel, .card {
            margin: auto;
            height: 90vh;
            overflow-y: auto;
        }
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
                        <a class="dropdown-item" href="endpoint/logout.php">Log Out</a>
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

    <div class="main-panel mt-4 ml-5 col-11">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Notes
                        <a href="http://localhost/take-note-app/" class="float-right">Back</a>
                    </div>

                    <div class="card-body">
                        <div class="data-item">
                            <ul class="list-group">
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
                                <li class="list-group-item mt-2">
                                    <h3 style="text-transform:uppercase;"><b><?php echo htmlspecialchars($noteTitle) ?></b></h3>
                                    <p><?php echo htmlspecialchars($noteContent) ?></p>
                                    <small class="block text-muted text-info">Created: <i class="fa fa-clock-o text-info"></i> <?php echo $formattedDateTime ?></small>
                                </li>
                            <?php
                            }
                            ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
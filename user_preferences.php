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

// Lấy tùy chỉnh hiện tại của người dùng
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->execute([$user_id]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);

// Khởi tạo giá trị mặc định nếu không có tùy chỉnh
if (!$preferences) {
    $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, font_size, note_color, theme) VALUES (?, 'medium', 'bg-pastel-1', 'light')");
    $stmt->execute([$user_id]);
    $preferences = [
        'font_size' => 'medium',
        'note_color' => 'bg-pastel-1',
        'theme' => 'light'
    ];
}

// Xử lý cập nhật tùy chỉnh
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $font_size = filter_var($_POST['font_size'], FILTER_SANITIZE_STRING);
    $note_color = filter_var($_POST['note_color'], FILTER_SANITIZE_STRING);
    $theme = isset($_POST['theme']) && $_POST['theme'] == 'dark' ? 'dark' : 'light';

    try {
        $stmt = $conn->prepare("UPDATE user_preferences SET font_size = ?, note_color = ?, theme = ? WHERE user_id = ?");
        $stmt->execute([$font_size, $note_color, $theme, $user_id]);
        $preferences['font_size'] = $font_size;
        $preferences['note_color'] = $note_color;
        $preferences['theme'] = $theme;
        $save_status = '';
    } catch (PDOException $e) {
        error_log("Error updating preferences: " . $e->getMessage());
        $save_status = 'Error saving preferences: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Preferences - Take-Note App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap');

        body {
            font-family: 'Patrick Hand', cursive;
            background-color: #f4f6f9;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode {
            background-color: #2c2c2c;
            color: #e0e0e0;
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
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .dark-mode .card {
            background-color: #3a3a3a;
            border-color: #555555 !important;
        }

        .card-header {
            background-color: #dcedc1;
            color: #33691e;
            font-size: 24px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            text-align: center;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode .card-header {
            background-color: #4a4a4a;
            color: #a8e6cf;
        }

        .card-body select, .card-body input[type="checkbox"] {
            font-size: 18px;
            padding: 10px;
            border-radius: 10px;
            border: 2px dashed #a8e6cf;
            background-color: #e0f7fa;
            transition: border-color 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .dark-mode .card-body select, .dark-mode .card-body input[type="checkbox"] {
            background-color: #555555;
            border-color: #777777;
            color: #e0e0e0;
        }

        .card-body select:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 5px rgba(102, 187, 106, 0.5);
            outline: none;
        }

        .dark-mode .card-body select:focus {
            border-color: #88cc88;
            box-shadow: 0 0 5px rgba(136, 204, 136, 0.5);
        }

        .btn-primary {
            background-color: #81c784;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }

        .dark-mode .btn-primary {
            background-color: #66bb6a;
        }

        .btn-primary:hover {
            background-color: #66bb6a;
        }

        .dark-mode .btn-primary:hover {
            background-color: #88cc88;
        }

        .btn-danger {
            background-color: #ef5350;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }

        .dark-mode .btn-danger {
            background-color: #d43f3a;
        }

        .btn-danger:hover {
            background-color: #e53935;
        }

        .dark-mode .btn-danger:hover {
            background-color: #ef5350;
        }

        .save-status {
            font-size: 14px;
            color: #33691e;
            margin-top: 10px;
            display: none;
            transition: color 0.3s ease;
        }

        .dark-mode .save-status {
            color: #a8e6cf;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            display: none;
            transition: color 0.3s ease;
        }

        .dark-mode .error-message {
            color: #ff6666;
        }

        .custom-navbar {
            background-color: #a8e6cf;
            background: linear-gradient(to right, #a8e6cf, #dcedc1);
            border-radius: 122px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.155);
            transition: background 0.3s ease;
        }

        .dark-mode .custom-navbar {
            background: linear-gradient(to right, #444444, #555555);
        }

        .navbar-brand {
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            color: #2e7d32 !important;
            transition: color 0.3s ease;
        }

        .dark-mode .navbar-brand {
            color: #a8e6cf !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .nav-link {
            color: #33691e !important;
            transition: color 0.3s ease;
        }

        .dark-mode .nav-link {
            color: #a8e6cf !important;
        }

        .nav-item .btn-create-note {
            background-color: #81c784;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            padding: 8px 15px;
            color: white;
            transition: background-color 0.3s ease;
        }

        .dark-mode .nav-item .btn-create-note {
            background-color: #66bb6a;
        }

        .nav-item .btn-create-note:hover {
            background-color: #66bb6a;
        }

        .dark-mode .nav-item .btn-create-note:hover {
            background-color: #88cc88;
        }

        .dropdown-menu {
            background-color: #ffffff;
            transition: background-color 0.3s ease;
        }

        .dark-mode .dropdown-menu {
            background-color: #3a3a3a;
        }

        .dropdown-item {
            color: #33691e;
            transition: color 0.3s ease;
        }

        .dark-mode .dropdown-item {
            color: #a8e6cf;
        }

        .dropdown-item:hover {
            background-color: #dcedc1;
            color: #33691e;
        }

        .dark-mode .dropdown-item:hover {
            background-color: #4a4a4a;
            color: #ffffff;
        }

        .alert-warning {
            background-color: #ffeeba;
            color: #856404;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode .alert-warning {
            background-color: #665522;
            color: #ffeeba;
        }

        /* Color Swatches */
        .color-swatch {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .color-swatch.selected {
            border: 2px solid #33691e;
        }

        .dark-mode .color-swatch.selected {
            border: 2px solid #a8e6cf;
        }

        .bg-pastel-1 { background-color: #f8e1e9; }
        .bg-pastel-2 { background-color: #e1f8dc; }
        .bg-pastel-3 { background-color: #dcedf8; }
        .bg-pastel-4 { background-color: #f8f1dc; }
        .bg-pastel-5 { background-color: #e6d8f8; }
        .bg-pastel-6 { background-color: #f8dcd8; }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .dark-mode .slider {
            background-color: #666666;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #81c784;
        }

        .dark-mode input:checked + .slider {
            background-color: #88cc88;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body class="<?php echo isset($preferences['theme']) && $preferences['theme'] == 'dark' ? 'dark-mode' : ''; ?>">
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
                    <button class="btn btn-create-note" onclick="window.location.href='index.php'">
                        Back to Notes
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

    <?php if (isset($save_status)): ?>
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
                        User Preferences
                    </div>
                    <div class="card-body">
                        <form method="post" action="user_preferences.php">
                            <!-- Font Size -->
                            <div class="form-group">
                                <label for="fontSize">Note Font Size</label>
                                <select class="form-control" id="fontSize" name="font_size">
                                    <option value="small" <?php echo $preferences['font_size'] == 'small' ? 'selected' : ''; ?>>Small</option>
                                    <option value="medium" <?php echo $preferences['font_size'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="large" <?php echo $preferences['font_size'] == 'large' ? 'selected' : ''; ?>>Large</option>
                                </select>
                            </div>

                            <!-- Note Color -->
                            <div class="form-group">
                                <label>Note Color</label><br>
                                <?php
                                $colors = ['bg-pastel-1', 'bg-pastel-2', 'bg-pastel-3', 'bg-pastel-4', 'bg-pastel-5', 'bg-pastel-6'];
                                foreach ($colors as $color) {
                                    $selected = $preferences['note_color'] == $color ? 'selected' : '';
                                    echo "<label class='color-swatch $color $selected' data-color='$color'></label>";
                                }
                                ?>
                                <input type="hidden" name="note_color" id="noteColor" value="<?php echo $preferences['note_color']; ?>">
                            </div>

                            <!-- Theme -->
                            <div class="form-group">
                                <label>Theme</label><br>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="themeToggle" name="theme" value="dark" <?php echo isset($preferences['theme']) && $preferences['theme'] == 'dark' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span id="themeLabel"><?php echo isset($preferences['theme']) && $preferences['theme'] == 'dark' ? 'Dark' : 'Light'; ?> Mode</span>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                            <a href="index.php" class="btn btn-danger">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            // Color Swatch Selection
            $('.color-swatch').on('click', function() {
                $('.color-swatch').removeClass('selected');
                $(this).addClass('selected');
                $('#noteColor').val($(this).data('color'));
            });

            // Theme Toggle
            $('#themeToggle').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#themeLabel').text('Dark Mode');
                    $(this).val('dark');
                } else {
                    $('#themeLabel').text('Light Mode');
                    $(this).val('light');
                }
            });
        });
    </script>
</body>
</html>
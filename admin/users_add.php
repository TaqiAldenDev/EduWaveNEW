<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['role'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $color_theme = '';
        switch ($role) {
            case 'Admin':
                $color_theme = 'blue';
                break;
            case 'Registrar':
                $color_theme = 'green';
                break;
            case 'Teacher':
                $color_theme = 'orange';
                break;
            case 'Student':
                $color_theme = 'purple';
                break;
            case 'Parent':
                $color_theme = 'yellow';
                break;
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, color_theme) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $password_hash, $role, $color_theme]);
            $message = 'User added successfully!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) { // Duplicate entry
                $message = 'Email already exists.';
            } else {
                $message = 'Error adding user: ' . $e->getMessage();
            }
        }
    } else {
        $message = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - EduWave Admin</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../dashboardassets/css/bootstrap.css">

    <link rel="stylesheet" href="../dashboardassets/vendors/iconly/bold.css">

    <link rel="stylesheet" href="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../dashboardassets/css/app.css">
    <link rel="shortcut icon" href="../assets/logo.svg" type="image/x-icon">
</head>

<body>
    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between">
                        <div class="logo">
                            <a href="dashboard.php"><img src="../assets/logo.svg" alt="Logo" srcset="" width="40"></a>
                        </div>
                        <div class="toggler">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-item">
                            <a href="dashboard.php" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="users_list.php" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>User Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>Classes & Subjects</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="classes.php">Classes</a>
                                </li>
                                <li class="submenu-item ">
                                    <a href="subject_edit.php">Subjects</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="library_add.php" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Library Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="event_add.php" class='sidebar-link'>
                                <i class="bi bi-calendar-check"></i>
                                <span>Calendar Events</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="../logout.php" class='sidebar-link'>
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </li>

                    </ul>
                </div>
                <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
            </div>
        </div>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <h3>User Management</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Add New User</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-info' ?>"><?= $message ?></div>
                                    <?php if (strpos($message, 'successfully') !== false): ?>
                                        <script>
                                            // If this page is loaded in a modal and operation was successful, close modal and refresh parent
                                            if (window.parent !== window && typeof window.parent.closeModal === 'function') {
                                                setTimeout(function() {
                                                    window.parent.closeModal();
                                                    if (typeof window.parent.location.reload === 'function') {
                                                        window.parent.location.reload();
                                                    }
                                                }, 1500); // Wait 1.5 seconds to show success message before closing
                                            }
                                        </script>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="Admin">Admin</option>
                                            <option value="Registrar">Registrar</option>
                                            <option value="Teacher">Teacher</option>
                                            <option value="Student">Student</option>
                                            <option value="Parent">Parent</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add User</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2025 &copy; EduWave</p>
                    </div>
                    <div class="float-end">
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart"></i></span> by EduWave Team</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../dashboardassets/js/bootstrap.bundle.min.js"></script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>

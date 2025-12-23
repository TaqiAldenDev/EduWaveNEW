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
    if (isset($_POST['delete_id'])) {
        // Handle book deletion
        $delete_id = $_POST['delete_id'];
        try {
            // Get file path to delete the physical file
            $stmt = $pdo->prepare('SELECT file_path FROM library_books WHERE id = ?');
            $stmt->execute([$delete_id]);
            $book = $stmt->fetch();

            if ($book) {
                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $book['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path); // Delete physical file
                }

                $stmt = $pdo->prepare('DELETE FROM library_books WHERE id = ?');
                $stmt->execute([$delete_id]);
                $message = 'Book deleted successfully!';
            } else {
                $message = 'Book not found.';
            }
        } catch (PDOException $e) {
            $message = 'Error deleting book.';
        }
    } elseif (!empty($_POST['title']) && !empty($_POST['category']) && !empty($_FILES['book_file'])) {
        // Handle book upload
        $file = $_FILES['book_file'];

        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/library/';

            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $allowed_types = ['application/pdf'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file_ext !== 'pdf') {
                $message = 'Invalid file type. Only PDF files are allowed.';
            } else {
                $new_filename = time() . '_' . basename($file['name']);
                $file_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    try {
                        $stmt = $pdo->prepare('INSERT INTO library_books (title, category, file_path, uploaded_by) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$_POST['title'], $_POST['category'], 'uploads/library/' . $new_filename, $_SESSION['user_id']]);
                        $message = 'Book added successfully!';
                    } catch (PDOException $e) {
                        unlink($file_path); // Delete file if database insertion fails
                        $message = 'Error adding book.';
                    }
                } else {
                    $message = 'Error uploading file.';
                }
            }
        } else {
            $message = 'Error uploading file.';
        }
    }
}

// Get library books with additional information
$books_stmt = $pdo->query('
    SELECT
        library_books.*,
        users.name as uploaded_by_name,
        users.role as uploaded_by_role,
        library_books.uploaded_at as upload_date
    FROM library_books
    JOIN users ON library_books.uploaded_by = users.id
    ORDER BY library_books.id DESC
');
$books = $books_stmt->fetchAll();

// Get library statistics
$book_count = $pdo->query('SELECT COUNT(*) FROM library_books')->fetchColumn();
$category_count = $pdo->query('SELECT COUNT(DISTINCT category) FROM library_books')->fetchColumn();
$last_upload_stmt = $pdo->query('SELECT uploaded_at FROM library_books ORDER BY uploaded_at DESC LIMIT 1');
$last_upload = $last_upload_stmt->fetch();

// Calculate total file size
$total_size = 0;
$stmt = $pdo->query('SELECT file_path FROM library_books');
$books_paths = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($books_paths as $path) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
    if (file_exists($full_path)) {
        $total_size += filesize($full_path);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management - EduWave Admin</title>

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

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>User Management</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="users_list.php">Users List</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="users_add.php">Add User</a>
                                </li>
                            </ul>
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

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Library Management</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
                                    <a href="library_add.php">Add Book</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-calendar-check"></i>
                                <span>Calendar Events</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="event_add.php">Add Event</a>
                                </li>
                            </ul>
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
                <h3>Library Management</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <!-- Library Statistics -->
                    <div class="col-12">
                        <div class="row mb-4">
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon purple">
                                                    <i class="bi bi-book"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Total Books</h6>
                                                <h6 class="font-extrabold mb-0"><?= $book_count ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon blue">
                                                    <i class="bi bi-folder"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Categories</h6>
                                                <h6 class="font-extrabold mb-0"><?= $category_count ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon green">
                                                    <i class="bi bi-upload"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Last Upload</h6>
                                                <h6 class="font-extrabold mb-0">
                                                    <?php
                                                    echo $last_upload ? date('M j', strtotime($last_upload['created_at'])) : 'N/A';
                                                    ?>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon red">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Total Uploads</h6>
                                                <h6 class="font-extrabold mb-0">
                                                    <?php
                                                    // Count uploads by different users
                                                    $stmt = $pdo->query('SELECT COUNT(DISTINCT uploaded_by) FROM library_books');
                                                    echo $stmt->fetchColumn();
                                                    ?>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Add New Book</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="book_file" class="form-label">Book File (PDF)</label>
                                        <input type="file" class="form-control" id="book_file" name="book_file" accept=".pdf" required>
                                        <div class="form-text">Only PDF files are allowed</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Book</button>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Library Books</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Uploaded By</th>
                                                <th>Role</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($books as $book): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($book['id']) ?></td>
                                                    <td><?= htmlspecialchars($book['title']) ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?= htmlspecialchars($book['category']) ?></span>
                                                    </td>
                                                    <td><?= htmlspecialchars($book['uploaded_by_name']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $book['uploaded_by_role'] === 'Admin' ? 'primary' : ($book['uploaded_by_role'] === 'Teacher' ? 'warning' : ($book['uploaded_by_role'] === 'Student' ? 'info' : ($book['uploaded_by_role'] === 'Parent' ? 'secondary' : 'success'))) ?>">
                                                            <?= htmlspecialchars($book['uploaded_by_role']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($book['upload_date'])) ?></td>
                                                    <td>
                                                        <a href="../<?= htmlspecialchars($book['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                                            <input type="hidden" name="delete_id" value="<?= $book['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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

<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

// Handle user addition
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['role'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $color_theme = 'blue';
        if ($role === 'Student') $color_theme = 'purple';
        elseif ($role === 'Teacher') $color_theme = 'yellow';
        elseif ($role === 'Parent') $color_theme = 'orange';
        elseif ($role === 'Registrar') $color_theme = 'green';

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, color_theme) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $password_hash, $role, $color_theme]);
            $message = 'User added successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $message = 'Email already exists.';
                $message_type = 'danger';
            } else {
                $message = 'Error adding user.';
                $message_type = 'danger';
            }
        }
    } else {
        $message = 'Please fill in all required fields.';
        $message_type = 'danger';
    }
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!empty($_POST['user_id']) && !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['role'])) {
        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'] ?? '';

        try {
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password_hash = ?, role = ? WHERE id = ?');
                $stmt->execute([$name, $email, $password_hash, $role, $user_id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
                $stmt->execute([$name, $email, $role, $user_id]);
            }
            $message = 'User updated successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $message = 'Email already exists.';
                $message_type = 'danger';
            } else {
                $message = 'Error updating user.';
                $message_type = 'danger';
            }
        }
    } else {
        $message = 'Please fill in all required fields.';
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - EduWave Admin</title>

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

                        <li class="sidebar-item active">
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
                                <div class="d-flex gap-2 float-end">
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-download me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
<li><a class="dropdown-item" href="#" onclick="exportTable('xlsx')">
                                                <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export Excel
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('csv')">
                                                <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Export CSV
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('txt')">
                                                <i class="bi bi-file-earmark-text me-2 text-info"></i>Export Text
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('pdf')">
                                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export PDF
                                            </a></li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="bi bi-person-plus me-2"></i>Add User
                                    </button>
                                </div>
                            </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <?php
                                    // Get all users with available information
                                    $stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
                                    $users = $stmt->fetchAll();
                                    ?>
                                    <table class="table table-striped" id="usersTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $user['role'] === 'Admin' ? 'primary' : ($user['role'] === 'Teacher' ? 'warning' : ($user['role'] === 'Student' ? 'info' : ($user['role'] === 'Parent' ? 'secondary' : 'success'))) ?>">
                                                            <?= htmlspecialchars($user['role']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                                    <td>
                                                        <a href="javascript:void(0)" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)" class="me-2 text-warning" title="Edit" style="text-decoration: none; font-size: 1.2rem;">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="users_delete.php?id=<?= $user['id'] ?>" class="text-danger" title="Delete" style="text-decoration: none; font-size: 1.2rem;"
                                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
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
    
    <!-- TableExport Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="../includes/tableExport.jquery.plugin/tableExport.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="users_list.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="addEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="addEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPassword" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="addPassword" name="password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="addRole" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="addRole" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Student">Student</option>
                                <option value="Parent">Parent</option>
                                <option value="Registrar">Registrar</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="users_list.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="editUserId" name="user_id">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password (leave empty to keep current)</label>
                            <input type="password" class="form-control" id="editPassword" name="password" minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="editRole" name="role" required>
                                <option value="Admin">Admin</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Student">Student</option>
                                <option value="Parent">Parent</option>
                                <option value="Registrar">Registrar</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function editUser(user) {
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editName').value = user.name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editPassword').value = '';

        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    }

function exportTable(type) {
        const fileName = 'users_list_' + new Date().toISOString().slice(0, 10);
        
        const options = {
            tableName: 'Users List',
            worksheetName: 'Users',
            fileName: fileName,
            excelstyles: ['border-bottom', 'border-top', 'border-left', 'border-right'],
            onMsoNumberFormat: function(cell, row, col) {
                if (!isNaN(cell.innerHTML) && cell.innerHTML !== '') {
                    return '\\@';
                }
            }
        };

        switch(type) {
                case 'xlsx':
                    options.type = 'xlsx';
                    $('#usersTable').tableExport(options);
                    break;
                case 'csv':
                    exportToCSV();
                    break;
                case 'txt':
                    exportToTXT();
                    break;
                case 'pdf':
                    exportToPDF();
                    break;
        }
    }

        function exportToCSV() {
            // Use POST to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'users';
            form.appendChild(typeInput);
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = 'csv';
            form.appendChild(formatInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function exportToTXT() {
            // Use POST to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'users';
            form.appendChild(typeInput);
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = 'txt';
            form.appendChild(formatInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function exportToPDF() {
            // Use POST to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_pdf.php';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'type';
            input.value = 'users';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }

    <?php if (!empty($message)): ?>
        <?php if ($message_type === 'success'): ?>
            alert('<?= $message ?>');
        <?php else: ?>
            alert('Error: <?= $message ?>');
        <?php endif; ?>
    <?php endif; ?>
    </script>
</body>

</html>

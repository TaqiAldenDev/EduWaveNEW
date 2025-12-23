<?php
require_once __DIR__ . '/includes/config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            $_SESSION['role'] = $user['role'];
            $_SESSION['colour'] = $user['color_theme'];
            $_SESSION['user_id'] = $user['id'];

            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(16));
                $stmt = $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
                $stmt->execute([$token, $user['id']]);
                setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/');
            }

            switch ($user['role']) {
                case 'Admin':
                    header('Location: admin/dashboard.php');
                    exit;
                case 'Registrar':
                    header('Location: registrar/dashboard.php');
                    exit;
                case 'Teacher':
                    header('Location: teacher/dashboard.php');
                    exit;
                case 'Student':
                    header('Location: student/dashboard.php');
                    exit;
                case 'Parent':
                    header('Location: parent/dashboard.php');
                    exit;
            }
        } else {
            $error_message = 'Invalid email or password.';
        }
    } else {
        $error_message = 'Please enter both email and password.';
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>EduWave - Login</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Override the accent color with dashboard purple color */
        :root {
            --accent-color: #435ebe;
        }

        /* Responsive styles */
        .hero {
            padding: 40px 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        @media (max-width: 991px) {
            .hero {
                padding: 20px 0;
            }

            .hero-img {
                display: none;
            }

            .col-lg-5 {
                max-width: 100%;
                flex: 0 0 100%;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 10px 0;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .credential-item {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 5px;
            }

            .credential-item span:last-child {
                font-size: 0.85rem;
                word-break: break-all;
            }

            h2.fw-bold {
                font-size: 1.5rem;
            }

            .demo-credentials {
                font-size: 0.9rem;
            }
        }

        @media (min-width: 577px) and (max-width: 768px) {
            .card-body {
                padding: 2rem !important;
            }
        }
    </style>
</head>

<body class="index-page">
    <main class="main">
        <section id="hero" class="hero section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5 d-flex flex-column justify-content-center">
                        <div class="card mb-4" style="border-radius: 1rem; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <h2 class="fw-bold mb-0">Welcome to EduWave</h2>
                                    <p class="text-muted">Virtual School Management System</p>
                                    <div class="d-flex justify-content-center">
                                        <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--accent-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem;">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST">
                                    <?php if ($error_message): ?>
                                        <div class="alert alert-danger"><?= $error_message ?></div>
                                    <?php endif; ?>

                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="floatingInput" name="email" placeholder="name@example.com" required>
                                        <label for="floatingInput"><i class="bi bi-envelope me-2"></i>Email address</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
                                        <label for="floatingPassword"><i class="bi bi-lock me-2"></i>Password</label>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="remember-me" id="rememberMe" name="remember_me">
                                            <label class="form-check-label" for="rememberMe">
                                                Remember me
                                            </label>
                                        </div>
                                        <a href="#" class="text-decoration-none" style="color: var(--accent-color);">Forgot password?</a>
                                    </div>

                                    <button class="btn btn-primary w-100 py-3 mb-4" type="submit" style="background-color: var(--accent-color); border: none; border-radius: 50px; font-weight: 500; transition: all 0.3s ease;">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign in
                                    </button>
                                </form>

                                <!-- Demo Credentials Section -->
                                <div class="demo-credentials p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                    <h6 class="fw-bold mb-3" style="color: var(--accent-color);"><i class="fas fa-lightbulb me-2"></i>Demo Credentials</h6>
                                    <div class="credential-item d-flex justify-content-between py-2 border-bottom">
                                        <span><strong>Admin:</strong></span>
                                        <span>admin@eduwave.com / password</span>
                                    </div>
                                    <div class="credential-item d-flex justify-content-between py-2 border-bottom">
                                        <span><strong>Registrar:</strong></span>
                                        <span>registrar@eduwave.com / password</span>
                                    </div>
                                    <div class="credential-item d-flex justify-content-between py-2 border-bottom">
                                        <span><strong>Teacher:</strong></span>
                                        <span>teacher@eduwave.com / password</span>
                                    </div>
                                    <div class="credential-item d-flex justify-content-between py-2 border-bottom">
                                        <span><strong>Student:</strong></span>
                                        <span>student@eduwave.com / password</span>
                                    </div>
                                    <div class="credential-item d-flex justify-content-between py-2">
                                        <span><strong>Parent:</strong></span>
                                        <span>parent@eduwave.com / password</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <p class="mb-0">&copy; 2025 EduWave. All rights reserved.</p>
                        </div>
                    </div>

                    <div class="col-lg-6 offset-lg-1 hero-img">
                        <div class="position-relative">
                            <img src="assets/img/illustration-1.webp" alt="Login Illustration" class="img-fluid" style="border-radius: 1rem;">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

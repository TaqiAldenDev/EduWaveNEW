<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>EduWave</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/landingpage.css" rel="stylesheet">

  <style>
    /* Override the accent color with dashboard purple color */
    :root {
      --accent-color: #435ebe;
    }
  </style>

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
        <!-- <img src="assets/img/logo.png" alt=""> -->
        <h1 class="sitename">EduWave </h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#features">Features</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="login.php">Login</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
            
              <h1 class="mb-4">
                <span class="accent-text">EduWave - Virtual School System</span>
              </h1>

              <p class="mb-4 mb-md-5">
               A comprehensive virtual school management system designed to connect students, teachers, parents, and administrators with cutting-edge technology and intuitive design.
              </p>

              <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary me-0 me-sm-2 mx-1">Get Started</a>
              </div>
            </div>
          </div>

      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 align-items-center justify-content-between">

          <div  data-aos="fade-up" data-aos-delay="200">
            <span class="about-meta">MORE ABOUT US</span>
            <p>EduWave is a comprehensive educational management system designed to streamline academic operations for educational institutions. Our platform integrates various aspects of school administration, from student records and class management to communication between teachers, students, and parents.</p>
            <p><b>Our Mission</b><br>We strive to simplify the educational process by providing an intuitive, user-friendly platform that enhances communication, organization, and efficiency within educational institutions. Our mission is to support educators and administrators with powerful tools that enable them to focus on what matters most: teaching and learning.</p>
            <p><b>Why EduWave?</b></p>
            <div class="row feature-list-wrapper">
              <div class="col-md-6">
                <ul class="feature-list">
                  <li><i class="bi bi-check-circle-fill"></i> Comprehensive Management: Handle everything from student enrollment to grade reporting in one centralized system</li>
                  <li><i class="bi bi-check-circle-fill"></i> Seamless Communication: Facilitate better communication between teachers, students, and parents</li>
                  <li><i class="bi bi-check-circle-fill"></i> Time Efficiency: Automate routine administrative tasks to free up valuable time for educational activities</li>
                  <li><i class="bi bi-check-circle-fill"></i> Data Security: Protect sensitive educational data with robust security measures</li>
                  <li><i class="bi bi-check-circle-fill"></i> User-Friendly Interface: Intuitive design ensures easy adoption by all users regardless of technical expertise</li>
                </ul>
              </div>
      </div>

    </section><!-- /About Section -->

    <!-- Features Section -->
    <section id="features" class="features section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Features</h2>
        <p>EduWave connects all stakeholders in the educational process with tailored experiences for each user type</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="d-flex justify-content-center">

          <ul class="nav nav-tabs" data-aos="fade-up" data-aos-delay="100">

            <li class="nav-item">
              <a class="nav-link active show" data-bs-toggle="tab" data-bs-target="#features-tab-1">
                <h4>For Students</h4>
              </a>
            </li><!-- End tab nav item -->

            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-2">
                <h4>For Teachers</h4>
              </a><!-- End tab nav item -->

            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-3">
                <h4>For Parents</h4>
              </a>
            </li><!-- End tab nav item -->

             </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-4">
                <h4>For Admins</h4>
              </a>
            </li><!-- End tab nav item -->

             </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-5">
                <h4>For Registrars</h4>
              </a>
            </li><!-- End tab nav item -->
          </ul>

        </div>

        <div class="tab-content" data-aos="fade-up" data-aos-delay="200">

          <div class="tab-pane fade active show" id="features-tab-1">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
               
                <h3>Track your progress and stay organized with our intuitive student dashboard.</h3>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span> Access your timetable </span></li>
                  <li><i class="bi bi-check2-all"></i> <span> grades </span></li><li><i class="bi bi-check2-all"></i> <span> Access your timetable</span></li>
                  <li><i class="bi bi-check2-all"></i> <span> attendance records</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>submit homework all in one place.</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="assets/img/features-illustration-1.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

          <div class="tab-pane fade" id="features-tab-2">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Monitor and control classes and their subjects </h3>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Effortlessly manage your classes</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>take attendance</span></li>
                  <li><i class="bi bi-check2-all"></i> <span> enter grades</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>assign homework</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>generate reports with our intuitive tools designed specifically for educators.</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="assets/img/features-illustration-2.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

          <div class="tab-pane fade" id="features-tab-3">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Monitor their academic journey effortlessly</h3>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Stay informed about your child's progress with real-time access to grades</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>chiled attendance</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>homework assignments</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="assets/img/features-illustration-3.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

          <div class="tab-pane fade" id="features-tab-4">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Streamline your school management tasks with our powerful tools</h3>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Manage users</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>classes</span></li>
                  <li><i class="bi bi-check2-all"></i> <span> subjects</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>school resources with comprehensive administrative controls</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Manage users</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="assets/img/features-illustration-3.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

            <div class="tab-pane fade" id="features-tab-5">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Simplify administrative processes with our dedicated features.</h3>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Handle student enrollment</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>transfers</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>generate certificates with built-in document generation toolst</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="assets/img/features-illustration-3.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->


        </div>

      </div>

    </section><!-- /Features Section -->
    
    <!-- Faq Section -->
    <section class="faq-9 faq section light-background" id="faq">

      <div class="container">
        <div class="row">

          <div class="col-lg-5" data-aos="fade-up">
            <h2 class="faq-title">Have a question? Check out the FAQ</h2>
            <div class="faq-arrow d-none d-lg-block" data-aos="fade-up" data-aos-delay="200">
              <svg class="faq-arrow" width="200" height="211" viewBox="0 0 200 211" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M198.804 194.488C189.279 189.596 179.529 185.52 169.407 182.07L169.384 182.049C169.227 181.994 169.07 181.939 168.912 181.884C166.669 181.139 165.906 184.546 167.669 185.615C174.053 189.473 182.761 191.837 189.146 195.695C156.603 195.912 119.781 196.591 91.266 179.049C62.5221 161.368 48.1094 130.695 56.934 98.891C84.5539 98.7247 112.556 84.0176 129.508 62.667C136.396 53.9724 146.193 35.1448 129.773 30.2717C114.292 25.6624 93.7109 41.8875 83.1971 51.3147C70.1109 63.039 59.63 78.433 54.2039 95.0087C52.1221 94.9842 50.0776 94.8683 48.0703 94.6608C30.1803 92.8027 11.2197 83.6338 5.44902 65.1074C-1.88449 41.5699 14.4994 19.0183 27.9202 1.56641C28.6411 0.625793 27.2862 -0.561638 26.5419 0.358501C13.4588 16.4098 -0.221091 34.5242 0.896608 56.5659C1.8218 74.6941 14.221 87.9401 30.4121 94.2058C37.7076 97.0203 45.3454 98.5003 53.0334 98.8449C47.8679 117.532 49.2961 137.487 60.7729 155.283C87.7615 197.081 139.616 201.147 184.786 201.155L174.332 206.827C172.119 208.033 174.345 211.287 176.537 210.105C182.06 207.125 187.582 204.122 193.084 201.144C193.346 201.147 195.161 199.887 195.423 199.868C197.08 198.548 193.084 201.144 195.528 199.81C196.688 199.192 197.846 198.552 199.006 197.935C200.397 197.167 200.007 195.087 198.804 194.488ZM60.8213 88.0427C67.6894 72.648 78.8538 59.1566 92.1207 49.0388C98.8475 43.9065 106.334 39.2953 114.188 36.1439C117.295 34.8947 120.798 33.6609 124.168 33.635C134.365 33.5511 136.354 42.9911 132.638 51.031C120.47 77.4222 86.8639 93.9837 58.0983 94.9666C58.8971 92.6666 59.783 90.3603 60.8213 88.0427Z" fill="currentColor"></path>
              </svg>
            </div>
          </div>

          <div class="col-lg-7" data-aos="fade-up" data-aos-delay="300">
            <div class="faq-container">

              <div class="faq-item faq-active">
                <h3>What is EduWave?</h3>
                <div class="faq-content">
                  <p> EduWave is a comprehensive educational management system designed to streamline academic processes for schools, colleges, and universities. It offers modules for students, teachers, administrators, and parents to efficiently manage educational activities.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Who can use the EduWave system?</h3>
                <div class="faq-content">
                  <p>EduWave supports multiple user roles including students, teachers, administrative staff, parents, and system administrators, each with tailored interfaces and functionalities relevant to their educational responsibilities.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>What are the main modules available in EduWave?</h3>
                <div class="faq-content">
                  <p>The system includes modules for student management, teacher management, parent communication, admin controls, registrar functions, messaging systems, calendar scheduling, and file uploads for educational materials.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>How do I log in to EduWave?</h3>
                <div class="faq-content">
                  <p>Access the login.php page and enter your credentials. Different user roles have specific access levels and dashboards once authenticated.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Is there a way to manage classes in EduWave?</h3>
                <div class="faq-content">
                  <p> Yes, administrators can manage classes through the admin section, including options to view, edit, or delete class information through dedicated class management pages.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Can parents monitor their child's academic progress?</h3>
                <div class="faq-content">
                  <p> Yes, the parent module allows guardians to view their child's academic information, grades, attendance, and communicate with teachers through the integrated messaging system.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

            </div>
          </div>

        </div>
      </div>
    </section><!-- /Faq Section -->
  </main>

  <footer id="footer" class="footer">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">EduWave - Virtual School System</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Jordan</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+100000000</span></p>
            <p><strong>Email:</strong> <span>info@example.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">EduWave - Virtual School System</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Developed By Taqi ALden ALshamali</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/landingpage.js"></script>

</body>

</html>
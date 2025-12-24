# EduWave - Virtual School Management System

## Description

EduWave is a comprehensive web-based school management system designed to connect students, teachers, parents, and administrators in a virtual learning environment. The system supports grades 1-10 and provides role-based access for different stakeholders in the educational process.

EduWave is a full-featured educational platform that streamlines school administration, enhances communication between stakeholders, and provides powerful tools for academic management. Built with modern web technologies, the system offers intuitive interfaces, robust security, and scalable architecture suitable for educational institutions of all sizes.

### Key Highlights
- **Multi-Role System**: Admin, Registrar, Teacher, Student, and Parent portals
- **Complete Academic Management**: Grades, Attendance, Timetables, Assignments
- **Digital Library**: E-book management and browsing system
- **Certificate Generation**: Automated PDF certificate creation
- **Real-time Analytics**: Comprehensive reporting and dashboards
- **Mobile Responsive**: Works seamlessly on all devices

## Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)
- [Contact / Credits](#contact--credits)

## Features

### Student Portal
- **Personal Dashboard**: Overview of academic activities and notifications
- **Timetable Management**: View weekly class schedules with subject details
- **Grade Tracking**: Access subject-wise grades and performance analytics
- **Attendance Monitoring**: Real-time attendance records and statistics
- **Homework System**: Upload assignments and track submission status
- **Digital Library**: Browse and download e-books and educational resources
- **Profile Management**: Update personal information and preferences

### Teacher Portal
- **Class Management**: View assigned classes and student rosters
- **Attendance System**: Daily attendance recording with automated reports
- **Grade Management**: Enter and update student grades with subject-wise organization
- **Homework Assignment**: Create, assign, and manage homework tasks
- **Class Analytics**: Generate comprehensive performance reports and summaries
- **Subject Coordination**: Manage curriculum and lesson planning
- **Communication Tools**: Send announcements and updates to students

### Parent Portal
- **Child Dashboard**: Monitor all children's academic activities from one interface
- **Grade Monitoring**: Track subject-wise performance and grade trends
- **Attendance Oversight**: View attendance records and receive absence notifications
- **Timetable Access**: Check children's class schedules and upcoming activities
- **Weekly Summaries**: Receive comprehensive weekly progress reports
- **Multi-Child Support**: Switch between different children's profiles seamlessly
- **Performance Analytics**: Visual charts and statistics for academic progress

### Administrator Portal
- **User Management**: Create, edit, and delete user accounts across all roles
- **Class Administration**: Manage grade levels, sections, and class assignments
- **Subject Management**: Create and organize academic subjects and curricula
- **Library System**: Add, edit, and manage digital library resources
- **Event Calendar**: Schedule and manage school events and activities
- **System Configuration**: Customize platform settings and preferences
- **Report Generation**: Generate administrative and academic reports

### Registrar Portal
- **Student Enrollment**: Complete registration and admission processes
- **Transfer Management**: Handle student transfers between classes or schools
- **Certificate Generation**: Create and issue academic certificates and documents
- **Academic Records**: Maintain comprehensive student academic histories
- **Parent Management**: Manage parent accounts and family relationships
- **Documentation**: Process and store official school documents

### Security Features
- **Password Hashing**: bcrypt algorithm for secure password storage
- **Session Management**: Secure session handling with timeout protection
- **Role-Based Access Control**: Granular permissions for each user type
- **SQL Injection Prevention**: PDO prepared statements for all database queries
- **XSS Protection**: Output sanitization and Content Security Policy
- **CSRF Protection**: Token-based form validation
- **File Upload Security**: File type validation and secure storage

## Tech Stack

### Backend Technologies
- **PHP 8+**: Server-side scripting and business logic
- **MySQL 8.0**: Relational database management system
- **PDO**: Database abstraction layer for secure SQL operations
- **Session Management**: Secure user authentication and authorization

### Frontend Technologies
- **HTML5**: Semantic markup and modern web standards
- **CSS3**: Advanced styling with animations and transitions
- **JavaScript ES6+**: Interactive client-side functionality
- **AJAX**: Asynchronous data loading and real-time updates
- **jQuery**: DOM manipulation and event handling

### UI Frameworks & Libraries
- **Bootstrap 5**: Responsive grid system and UI components
- **Font Awesome**: Comprehensive icon library
- **Poppins Font**: Modern typography for enhanced readability
- **AOS (Animate On Scroll)**: Scroll-triggered animations
- **Swiper.js**: Touch-enabled carousel and slider components
- **Glightbox**: Modern lightbox for image and content viewing

### Dashboard Assets
- **ApexCharts**: Interactive data visualization and charts
- **Chart.js**: Additional charting capabilities
- **SweetAlert2**: Beautiful alert and notification system
- **Summernote**: Rich text editor for content creation
- **TinyMCE**: Advanced WYSYSIWYG editor
- **Choices.js**: Custom select and autocomplete inputs
- **Perfect Scrollbar**: Custom scrollbar styling

### Specialized Libraries
- **TCPDF**: PDF generation for certificates and reports
- **PHP Email Form**: Secure email handling and validation
- **PureCounter**: Animated number counting
- **Toastify**: Modern notification system
- **Dragula**: Drag and drop interface functionality

### Development Environment
- **Apache Web Server**: HTTP server with mod_rewrite support
- **XAMPP**: Integrated development environment
- **phpMyAdmin**: Database administration interface

## Installation

### Prerequisites
- **Apache Web Server** (with mod_rewrite enabled)
- **PHP 8.0 or higher** (with required extensions)
- **MySQL 8.0 or MariaDB 10.3+**
- **XAMPP/WAMP/MAMP** (recommended for local development)

### Required PHP Extensions
- `pdo_mysql` (Database connectivity)
- `mysqli` (MySQL database functions)
- `gd` (Image processing)
- `curl` (HTTP requests)
- `fileinfo` (File type detection)
- `mbstring` (Multi-byte string handling)
- `openssl` (SSL/TLS support)
- `session` (Session management)

### Step-by-Step Installation

#### 1. Download and Setup
```bash
# Clone or download the project files
# Copy to your web server directory:
# XAMPP: C:\xampp\htdocs\EduWaveNEW\
# WAMP: C:\wamp\www\EduWaveNEW\
# MAMP: /Applications/MAMP/htdocs/EduWaveNEW/
```

#### 2. Database Configuration
```sql
-- Create database
CREATE DATABASE eduwave CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
-- Use phpMyAdmin or command line:
mysql -u root -p eduwave < database-schema.sql
```

#### 3. Configuration Setup
Edit `includes/config.php` with your database credentials:
```php
<?php
$host = 'localhost';
$db = 'eduwave';
$user = 'root';
$pass = ''; // Your MySQL password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
?>
```

#### 4. File Permissions
```bash
# Set write permissions for upload directories
chmod 755 uploads/
chmod 755 uploads/certificates/
chmod 755 uploads/library/

# TCPDF directory permissions
chmod 755 includes/tcpdf/
chmod 755 includes/tcpdf/cache/
```

#### 5. Web Server Configuration
Ensure Apache is configured with:
- `AllowOverride All` for .htaccess support
- `mod_rewrite` enabled for clean URLs
- `mod_php` or PHP-FPM properly configured

#### 6. Virtual Host Setup (Optional)
For production deployment, configure a virtual host:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/EduWaveNEW"
    ServerName eduwave.local
    <Directory "C:/xampp/htdocs/EduWaveNEW">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Verification Steps
1. Access `http://localhost/EduWaveNEW` in your browser
2. Verify the landing page loads correctly
3. Test login with default credentials
4. Check database connectivity
5. Verify file upload functionality

## Usage

### Default Accounts

After setting up the database with sample data, use these default accounts:

#### System Administrator
- **Email**: admin@eduwave.com
- **Password**: password
- **Role**: Full system administration

#### Registrar Office
- **Email**: registrar@eduwave.com
- **Password**: password
- **Role**: Student registration and records

#### Teaching Staff
- **Email**: teacher@eduwave.com
- **Password**: password
- **Role**: Classroom management and grading

#### Student Account
- **Email**: student@eduwave.com
- **Password**: password
- **Role**: Learning and assignments

#### Parent Account
- **Email**: parent@eduwave.com
- **Password**: password
- **Role**: Child progress monitoring

### Getting Started

1. **Login**: Navigate to `http://localhost/EduWaveNEW/login.php` and enter credentials for your role
2. **Dashboard**: Access your role-specific dashboard to view overview and available features
3. **Profile Setup**: Complete your profile setup if prompted and change default password
4. **Explore Features**: Navigate through the available features based on your role

### Role-Based Navigation

#### Administrator
- User management and system configuration
- Class and subject administration
- Library and event management
- Report generation

#### Registrar
- Student enrollment and transfer management
- Certificate generation and academic records
- Parent account management

#### Teacher
- Attendance and grade management
- Homework assignment and class analytics
- Student roster and subject coordination

#### Student
- View timetable and grades
- Submit homework and browse library
- Track attendance

#### Parent
- Monitor children's progress
- View attendance and grade reports
- Access weekly summaries

## Contributing

### Development Guidelines
- Follow existing code conventions and patterns
- Use prepared statements for all database queries
- Implement proper input validation and sanitization
- Test thoroughly before submitting changes
- Update documentation for new features

### Code Structure
- Maintain separation of concerns between roles
- Use consistent naming conventions
- Implement proper error handling
- Follow security best practices

### Submitting Changes
1. Fork the repository
2. Create a feature branch
3. Implement your changes
4. Test thoroughly
5. Submit a pull request with detailed description

### Bug Reports
- Provide clear description of the issue
- Include steps to reproduce
- Specify environment details
- Include error messages or screenshots

## License

This project is created for educational purposes and is available as is.

### Third-Party Licenses
- **TCPDF Library**: LGPL v3 (included in `includes/tcpdf/`)
- **Bootstrap**: MIT License
- **Font Awesome**: CC BY 4.0
- **jQuery**: MIT License
- **Other Libraries**: Various open-source licenses

### Usage Terms
- **Educational Use**: Free for educational institutions
- **Commercial Use**: Contact for licensing information
- **Modification**: Allowed with attribution
- **Distribution**: Must include license notices

## Contact / Credits

### Project Credits
- **Development**: EduWave Development Team
- **Design**: Modern UI/UX design principles
- **Technologies**: Built with open-source technologies
- **Frameworks**: Bootstrap, jQuery, ApexCharts, and more

### Support & Documentation
- **Documentation**: This README and inline code comments
- **Issues**: Report bugs via project repository
- **Community**: Educational technology forums
- **Updates**: Regular feature updates and security patches

### Acknowledgments
- Thanks to all open-source contributors
- Educational institutions for feedback and testing
- The PHP and web development community
- Education technology advocates

---

## Quick Start Summary

1. **Download** the project files to your web server
2. **Create** MySQL database and import `database-schema.sql`
3. **Configure** database settings in `includes/config.php`
4. **Set permissions** for upload directories
5. **Access** the system via your web browser
6. **Login** with default credentials and explore!

**EduWave** - Transforming education through technology! 
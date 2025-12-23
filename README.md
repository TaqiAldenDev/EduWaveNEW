# EduWave - Virtual School Management System

EduWave is a comprehensive web-based school management system designed to connect students, teachers, parents, and administrators in a virtual learning environment. The system supports grades 1-10 and provides role-based access for different stakeholders in the educational process.

## 🎯 Project Overview

EduWave is a full-featured educational platform that streamlines school administration, enhances communication between stakeholders, and provides powerful tools for academic management. Built with modern web technologies, the system offers intuitive interfaces, robust security, and scalable architecture suitable for educational institutions of all sizes.

### Key Highlights
- **Multi-Role System**: Admin, Registrar, Teacher, Student, and Parent portals
- **Complete Academic Management**: Grades, Attendance, Timetables, Assignments
- **Digital Library**: E-book management and browsing system
- **Certificate Generation**: Automated PDF certificate creation
- **Real-time Analytics**: Comprehensive reporting and dashboards
- **Mobile Responsive**: Works seamlessly on all devices

## 🚀 Features

### 🎓 Student Portal
- **Personal Dashboard**: Overview of academic activities and notifications
- **Timetable Management**: View weekly class schedules with subject details
- **Grade Tracking**: Access subject-wise grades and performance analytics
- **Attendance Monitoring**: Real-time attendance records and statistics
- **Homework System**: Upload assignments and track submission status
- **Digital Library**: Browse and download e-books and educational resources
- **Profile Management**: Update personal information and preferences

### 👨‍🏫 Teacher Portal
- **Class Management**: View assigned classes and student rosters
- **Attendance System**: Daily attendance recording with automated reports
- **Grade Management**: Enter and update student grades with subject-wise organization
- **Homework Assignment**: Create, assign, and manage homework tasks
- **Class Analytics**: Generate comprehensive performance reports and summaries
- **Subject Coordination**: Manage curriculum and lesson planning
- **Communication Tools**: Send announcements and updates to students

### 👨‍👩‍👧‍👦 Parent Portal
- **Child Dashboard**: Monitor all children's academic activities from one interface
- **Grade Monitoring**: Track subject-wise performance and grade trends
- **Attendance Oversight**: View attendance records and receive absence notifications
- **Timetable Access**: Check children's class schedules and upcoming activities
- **Weekly Summaries**: Receive comprehensive weekly progress reports
- **Multi-Child Support**: Switch between different children's profiles seamlessly
- **Performance Analytics**: Visual charts and statistics for academic progress

### 📊 Administrator Portal
- **User Management**: Create, edit, and delete user accounts across all roles
- **Class Administration**: Manage grade levels, sections, and class assignments
- **Subject Management**: Create and organize academic subjects and curricula
- **Library System**: Add, edit, and manage digital library resources
- **Event Calendar**: Schedule and manage school events and activities
- **System Configuration**: Customize platform settings and preferences
- **Report Generation**: Generate administrative and academic reports

### 📝 Registrar Portal
- **Student Enrollment**: Complete registration and admission processes
- **Transfer Management**: Handle student transfers between classes or schools
- **Certificate Generation**: Create and issue academic certificates and documents
- **Academic Records**: Maintain comprehensive student academic histories
- **Parent Management**: Manage parent accounts and family relationships
- **Documentation**: Process and store official school documents

## 🛠 Technology Stack

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

## 📦 Installation

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

## 👤 Default Accounts

After setting up the database with sample data, use these default accounts:

### System Administrator
- **Email**: admin@eduwave.com
- **Password**: password
- **Role**: Full system administration

### Registrar Office
- **Email**: registrar@eduwave.com
- **Password**: password
- **Role**: Student registration and records

### Teaching Staff
- **Email**: teacher@eduwave.com
- **Password**: password
- **Role**: Classroom management and grading

### Student Account
- **Email**: student@eduwave.com
- **Password**: password
- **Role**: Learning and assignments

### Parent Account
- **Email**: parent@eduwave.com
- **Password**: password
- **Role**: Child progress monitoring

### First Login Steps
1. Navigate to `http://localhost/EduWaveNEW/login.php`
2. Enter credentials for your role
3. Complete profile setup if prompted
4. Change default password for security

### Security Recommendations
- Change all default passwords after first login
- Use strong passwords (minimum 8 characters, mixed case, numbers, symbols)
- Enable two-factor authentication if available
- Regularly update user credentials

## 📁 Project Structure

```
EduWaveNEW/
├── 📄 Core Files
│   ├── index.php              # Landing page and marketing
│   ├── login.php              # Authentication portal
│   ├── logout.php             # Session termination
│   ├── calendar.php           # School calendar view
│   ├── database-schema.sql    # Complete database structure
│   └── README.md              # Project documentation
│
├── 🔧 Configuration
│   └── includes/
│       ├── config.php         # Database and system settings
│       ├── auth.php           # Authentication and session management
│       ├── header.php         # Common HTML header
│       ├── footer.php         # Common HTML footer
│       ├── notifications.php  # Notification system
│       └── tcpdf/             # PDF generation library
│           ├── tcpdf.php      # Main PDF class
│           ├── config/        # TCPDF configuration
│           ├── fonts/         # Font files for PDFs
│           └── include/       # Core PDF functionality
│
├── 🎨 Frontend Assets
│   ├── assets/
│   │   ├── css/
│   │   │   └── landingpage.css # Landing page styling
│   │   ├── js/
│   │   │   └── landingpage.js  # Landing page interactions
│   │   ├── img/
│   │   │   └── illustration-1.webp # Marketing graphics
│   │   └── vendor/            # Third-party libraries
│   │       ├── aos/          # Animate on scroll
│   │       ├── bootstrap/    # Bootstrap framework
│   │       ├── bootstrap-icons/ # Icon library
│   │       ├── glightbox/   # Lightbox component
│   │       ├── php-email-form/ # Email validation
│   │       ├── purecounter/ # Number animations
│   │       └── swiper/       # Carousel/slider
│   │
│   └── dashboardassets/      # Dashboard UI components
│       ├── css/              # Dashboard stylesheets
│       ├── js/               # Dashboard JavaScript
│       ├── images/           # Dashboard graphics
│       └── vendors/          # Dashboard libraries
│           ├── apexcharts/   # Data visualization
│           ├── chartjs/      # Charting library
│           ├── choices.js/   # Custom selects
│           ├── ckeditor/     # Rich text editor
│           ├── summernote/   # Text editor
│           ├── sweetalert2/  # Alert system
│           ├── tinymce/      # WYSIWYG editor
│           └── [other libraries]
│
├── 👤 Role-Based Portals
│   ├── admin/                # Administrator interface
│   │   ├── dashboard.php    # Admin overview
│   │   ├── users_*.php      # User management
│   │   ├── classes.php      # Class management
│   │   ├── subjects.php     # Subject administration
│   │   ├── library_add.php  # Library management
│   │   └── event_add.php    # Event management
│   │
│   ├── registrar/            # Registrar office interface
│   │   ├── dashboard.php    # Registrar overview
│   │   ├── student_*.php    # Student management
│   │   ├── parent_management.php # Parent accounts
│   │   ├── certificate_issue.php # Certificate generation
│   │   └── student_transfer.php # Transfer processing
│   │
│   ├── teacher/              # Teacher portal
│   │   ├── dashboard.php    # Teacher overview
│   │   ├── attendance.php   # Attendance management
│   │   ├── grades_*.php     # Grade entry and reports
│   │   ├── homework_add.php # Assignment creation
│   │   ├── class_list.php   # Student roster
│   │   └── attendance_summary.php # Attendance reports
│   │
│   ├── student/              # Student portal
│   │   ├── dashboard.php    # Student overview
│   │   ├── timetable.php    # Class schedule
│   │   ├── my_grades.php    # Grade viewing
│   │   ├── my_attendance.php # Attendance tracking
│   │   ├── homework_upload.php # Assignment submission
│   │   └── library_browse.php # Library access
│   │
│   └── parent/               # Parent portal
│       ├── dashboard.php    # Parent overview
│       ├── child_*.php       # Child monitoring features
│       ├── my_children.php   # Children management
│       └── weekly_summary.php # Progress reports
│
├── 📁 File Storage
   └── uploads/
       ├── certificates/     # Generated certificates
       └── library/         # E-book storage


```

### Directory Purposes

#### `/includes/` - Core System Files
- **Configuration**: Database connections and system settings
- **Authentication**: Login, logout, and session management
- **Common Elements**: Shared HTML components and layouts
- **PDF Generation**: Certificate and report creation tools

#### `/assets/` - Public Frontend
- **Landing Page**: Marketing and informational content
- **Third-party Libraries**: External dependencies and frameworks
- **Static Resources**: Images, fonts, and media files

#### `/dashboardassets/` - Admin Interface
- **Dashboard Components**: Charts, forms, and interactive elements
- **Admin Libraries**: Specialized tools for system administration
- **UI Framework**: Enhanced Bootstrap components and styling

#### Role Directories (`/admin/`, `/teacher/`, etc.)
- **Dashboards**: Role-specific overview pages
- **Management Tools**: Features relevant to each user type
- **Reports**: Analytics and data visualization
- **Forms**: Data entry and modification interfaces

## 🔒 Security Features

### Authentication & Authorization
- **Password Hashing**: bcrypt algorithm for secure password storage
- **Session Management**: Secure session handling with timeout protection
- **Role-Based Access Control**: Granular permissions for each user type
- **Multi-Factor Authentication**: Optional additional security layers
- **Account Lockout**: Protection against brute force attacks

### Data Protection
- **SQL Injection Prevention**: PDO prepared statements for all database queries
- **XSS Protection**: Output sanitization and Content Security Policy
- **CSRF Protection**: Token-based form validation
- **Input Validation**: Server-side validation for all user inputs
- **Data Encryption**: Sensitive data encryption at rest and in transit

### File Security
- **Upload Validation**: File type, size, and content verification
- **Secure File Storage**: Isolated upload directories with proper permissions
- **Virus Scanning**: Optional malware detection for uploaded files
- **Access Control**: Restricted file access based on user permissions

### Network Security
- **HTTPS Enforcement**: SSL/TLS encryption for all communications
- **Secure Headers**: Security-focused HTTP headers implementation
- **Rate Limiting**: Protection against DDoS and abuse
- **IP Whitelisting**: Optional admin access restrictions

### Compliance & Auditing
- **Activity Logging**: Comprehensive audit trail for all user actions
- **Data Privacy**: GDPR and educational data regulation compliance
- **Backup Security**: Encrypted backup storage and recovery
- **Security Updates**: Regular patching and vulnerability management

### Security Best Practices Implemented
```php
// Example of secure database query
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
$stmt->execute(['email' => $email]);

// Example of input sanitization
$clean_input = filter_var($user_input, FILTER_SANITIZE_STRING);
$validated_input = htmlspecialchars($clean_input, ENT_QUOTES, 'UTF-8');
```

## 📎 File Upload Support

### Supported File Types

#### Documents & Academic Materials
- **PDF**: Portable Document Format (primary format)
- **DOCX**: Microsoft Word documents
- **DOC**: Legacy Word documents
- **TXT**: Plain text files
- **RTF**: Rich Text Format

#### Archives & Resources
- **ZIP**: Compressed file collections
- **RAR**: WinRAR archives (read-only)

#### Images & Media
- **JPG/JPEG**: Photographic images
- **PNG**: Graphics and transparent images
- **GIF**: Simple animations and graphics
- **WEBP**: Modern web image format

### Upload Specifications

#### File Size Limits
- **Maximum File Size**: 10 MB per upload
- **Library Books**: Up to 50 MB for e-books
- **Certificates**: Generated programmatically (no upload limit)
- **Profile Pictures**: Maximum 2 MB

#### Storage Locations
```
uploads/
├── certificates/     # Generated PDF certificates
├── library/         # E-book and resource files
├── homework/        # Student assignment submissions
├── profiles/        # User profile pictures
└── temp/           # Temporary upload processing
```

### Security Measures
- **File Type Validation**: MIME type verification
- **Content Scanning**: File content analysis
- **Filename Sanitization**: Secure filename generation
- **Virus Detection**: Optional malware scanning
- **Access Control**: Role-based file access permissions

### Upload Process
1. **Client Validation**: JavaScript file type and size checking
2. **Server Verification**: PHP-based file validation
3. **Secure Storage**: Isolated directory with proper permissions
4. **Database Recording**: File metadata stored in database
5. **Access Control**: Permission-based file retrieval

## 🗄 Database Schema

The EduWave system uses a comprehensive relational database with 13 core tables designed for optimal performance and data integrity.

### Core Tables Overview

#### User Management
- **`users`** - Central user repository for all roles
  - Fields: id, email, password, first_name, last_name, role, status, created_at
  - Roles: admin, registrar, teacher, student, parent
  - Security: bcrypt password hashing, email uniqueness

#### Academic Structure
- **`classes`** - Grade levels and sections
  - Fields: id, class_name (e.g., "Grade 1-A"), capacity, academic_year
  - Supports: Multiple sections per grade level

- **`subjects`** - Academic subjects catalog
  - Fields: id, subject_name, subject_code, description, credits
  - Examples: Mathematics, Science, English, History

#### Assignment & Relationships
- **`teacher_assignments`** - Teacher-class-subject mapping
  - Fields: id, teacher_id, class_id, subject_id, academic_year
  - Purpose: Defines teaching responsibilities

- **`student_classes`** - Student enrollment tracking
  - Fields: id, student_id, class_id, academic_year, enrollment_date
  - Supports: Historical enrollment records

- **`parent_student`** - Family relationships
  - Fields: id, parent_id, student_id, relationship_type, guardian_status
  - Purpose: Links parents to their children

#### Academic Operations
- **`schedule`** - Weekly timetables and class schedules
  - Fields: id, class_id, subject_id, teacher_id, day_of_week, start_time, end_time, room_number
  - Features: Conflict detection, room assignment

- **`grades`** - Student academic performance records
  - Fields: id, student_id, subject_id, grade_value, grade_letter, assessment_type, semester, academic_year
  - Supports: Multiple assessment types (exams, assignments, participation)

- **`attendance`** - Daily attendance tracking
  - Fields: id, student_id, class_id, date, status, subject_id, marked_by, notes
  - Status: Present, Absent, Late, Excused

#### Assignment System
- **`assignments`** - Homework and task management
  - Fields: id, teacher_id, subject_id, class_id, title, description, due_date, created_at, section_id
  - Features: Due date tracking, class-wide or section-specific

- **`submissions`** - Student homework submissions
  - Fields: id, assignment_id, student_id, file_path, submitted_at, grade, feedback
  - Supports: File attachments, teacher feedback

#### Resource Management
- **`library_books`** - Digital library catalog
  - Fields: id, title, author, isbn, file_path, category, description, added_date
  - Features: E-book storage, categorization, search functionality

- **`certificates`** - Certificate generation records
  - Fields: id, student_id, certificate_type, file_path, issued_date, issued_by, purpose
  - Types: Graduation, Achievement, Completion, Recognition

### Database Relationships

#### Primary Keys
- All tables use auto-incrementing `id` fields as primary keys
- Foreign key constraints maintain data integrity

#### Indexing Strategy
- **Performance Indexes**: Frequently queried fields (email, student_id, class_id)
- **Composite Indexes**: Multi-field queries (student_id + subject_id + academic_year)
- **Unique Constraints**: Email addresses, enrollment combinations

#### Data Integrity
- **Foreign Key Constraints**: Prevent orphaned records
- **Check Constraints**: Valid grade ranges, attendance statuses
- **Trigger Logic**: Automated timestamp updates

### Sample Database Queries

#### Student Grade Retrieval
```sql
SELECT g.grade_value, g.grade_letter, s.subject_name, s.credits
FROM grades g
JOIN subjects s ON g.subject_id = s.id
WHERE g.student_id = ? AND g.academic_year = ?
ORDER BY s.subject_name;
```

#### Teacher Class Schedule
```sql
SELECT c.class_name, s.subject_name, sc.start_time, sc.end_time, sc.day_of_week
FROM teacher_assignments ta
JOIN classes c ON ta.class_id = c.id
JOIN subjects s ON ta.subject_id = s.id
JOIN schedule sc ON sc.class_id = ta.class_id AND sc.subject_id = ta.subject_id
WHERE ta.teacher_id = ? AND ta.academic_year = ?
ORDER BY sc.day_of_week, sc.start_time;
```

#### Parent Child Academic Summary
```sql
SELECT u.first_name, u.last_name, c.class_name,
       COUNT(DISTINCT a.id) as total_assignments,
       AVG(g.grade_value) as average_grade,
       COUNT(CASE WHEN att.status = 'Present' THEN 1 END) as days_present
FROM users u
JOIN parent_student ps ON u.id = ps.student_id
JOIN student_classes sc ON u.id = sc.student_id
JOIN classes c ON sc.class_id = c.id
LEFT JOIN assignments a ON a.class_id = sc.class_id
LEFT JOIN grades g ON g.student_id = u.id
LEFT JOIN attendance att ON att.student_id = u.id
WHERE ps.parent_id = ?
GROUP BY u.id, c.id;
```

## 🎨 Customization & Theming

### Role-Based Color Scheme
The system uses distinct color schemes for each user role:

#### Administrator Portal
- **Primary**: Blue (#007bff)
- **Secondary**: Light Blue (#17a2b8)
- **Accent**: Navy (#004085)

#### Registrar Portal
- **Primary**: Green (#28a745)
- **Secondary**: Light Green (#20c997)
- **Accent**: Dark Green (#155724)

#### Teacher Portal
- **Primary**: Orange (#fd7e14)
- **Secondary**: Light Orange (#ffc107)
- **Accent**: Dark Orange (#856404)

#### Student Portal
- **Primary**: Purple (#6f42c1)
- **Secondary**: Light Purple (#e83e8c)
- **Accent**: Dark Purple (#3d1a6b)

#### Parent Portal
- **Primary**: Yellow (#ffc107)
- **Secondary**: Light Yellow (#fff3cd)
- **Accent**: Gold (#856404)

### Customization Options

#### Branding Configuration
```css
/* Custom CSS variables for easy theming */
:root {
  --primary-color: #007bff;
  --secondary-color: #6c757d;
  --success-color: #28a745;
  --danger-color: #dc3545;
  --warning-color: #ffc107;
  --info-color: #17a2b8;
  --font-family: 'Poppins', sans-serif;
}
```

#### Logo & Branding
- Replace `assets/logo.svg` with institution logo
- Update `assets/img/illustration-1.webp` for landing page
- Customize favicon in root directory

#### Typography
- **Primary Font**: Poppins (Google Fonts)
- **Monospace Font**: Consolas (for code display)
- **Icon Font**: Font Awesome 6

#### Layout Customization
- **Grid System**: Bootstrap 5 responsive grid
- **Component Library**: Custom Bootstrap components
- **Animation**: AOS (Animate On Scroll) library

### Advanced Customization

#### Custom Modules
- Create new role directories following existing patterns
- Implement database extensions with migration scripts
- Add custom dashboard widgets and analytics

#### Integration Capabilities
- **API Endpoints**: RESTful API for third-party integrations
- **Webhook Support**: Real-time event notifications
- **SSO Integration**: LDAP, SAML, OAuth compatibility

#### Multi-Language Support
- **Current**: English (default)
- **Framework**: PHP internationalization (i18n)
- **Translation Files**: JSON-based language packs

## 🚀 Performance & Optimization

### Database Optimization
- **Query Optimization**: Indexed queries for fast data retrieval
- **Connection Pooling**: Efficient database connection management
- **Caching Strategy**: Query result caching for frequently accessed data

### Frontend Performance
- **Asset Minification**: Compressed CSS and JavaScript files
- **Image Optimization**: WebP format with fallbacks
- **Lazy Loading**: On-demand content loading
- **CDN Integration**: Optional CDN for static assets

### Server Optimization
- **PHP OPcache**: Bytecode caching for improved performance
- **Gzip Compression**: Reduced bandwidth usage
- **Browser Caching**: Optimized cache headers
- **Load Balancing**: Support for multiple server instances

## 📊 Analytics & Reporting

### Built-in Reports
- **Academic Performance**: Grade distribution and trends
- **Attendance Analytics**: Attendance rates and patterns
- **User Activity**: System usage statistics
- **Financial Reports**: Fee collection and expenses (if applicable)

### Data Visualization
- **Charts**: ApexCharts and Chart.js integration
- **Dashboards**: Real-time data displays
- **Export Options**: PDF, Excel, CSV formats
- **Scheduled Reports**: Automated report generation

## 🔧 Maintenance & Support

### Regular Maintenance Tasks
- **Database Backups**: Daily automated backups
- **Log Rotation**: System log management
- **Security Updates**: Regular patch application
- **Performance Monitoring**: System health checks

### Troubleshooting Guide
- **Common Issues**: Login problems, database connectivity
- **Error Logging**: Comprehensive error tracking
- **Debug Mode**: Development debugging tools
- **Support Documentation**: Detailed user guides

## 📜 License

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

### Support & Contact
- **Documentation**: This README and inline code comments
- **Issues**: Report bugs via project repository
- **Community**: Educational technology forums
- **Updates**: Regular feature updates and security patches

---

## 🎯 Quick Start Summary

1. **Download** the project files to your web server
2. **Create** MySQL database and import `database-schema.sql`
3. **Configure** database settings in `includes/config.php`
4. **Set permissions** for upload directories
5. **Access** the system via your web browser
6. **Login** with default credentials and explore!

**EduWave** - Transforming education through technology! 🚀📚



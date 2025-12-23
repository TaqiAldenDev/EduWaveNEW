# EduWave - Virtual School Management System

EduWave is a comprehensive web-based school management system designed to connect students, teachers, parents, and administrators in a virtual learning environment. The system supports grades 1-10 and provides role-based access for different stakeholders in the educational process.

## Features

### For Students
- View personal timetable
- Access grades and academic performance
- Track attendance records
- Upload homework assignments
- Browse and download library books

### For Teachers
- Manage assigned classes and subjects
- Take attendance efficiently
- Enter and update student grades
- Assign homework to students
- Generate class summaries and reports

### For Parents
- View children's academic progress
- Monitor attendance records
- Access timetables and homework assignments
- Receive weekly summary reports

### For Administrators
- User management (create, edit, delete users)
- Class and subject management
- Library book management
- Transport system management
- Calendar event management

### For Registrars
- Student enrollment and registration
- Student transfer management
- Certificate generation and issuing
- Academic record management

## Technology Stack

- **Backend**: PHP 8+
- **Database**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Icons**: Font Awesome
- **Styling**: Custom CSS with Poppins font
- **PDF Generation**: TCPDF library
- **Web Server**: Apache (via XAMPP)

## Installation

1. **Prerequisites**:
   - Apache web server
   - PHP 8.0 or higher
   - MySQL database
   - XAMPP/WAMP/MAMP (recommended)

2. **Setup Process**:
   ```bash
   # Clone or copy the project files to your web server directory
   # For XAMPP: Copy to C:\xampp\htdocs\eduwave\
   # For WAMP: Copy to C:\wamp\www\eduwave\
   ```

3. **Database Setup**:
   - Create a new MySQL database named `eduwave`
   - Import the `database-schema.sql` file located in the project root

4. **Configuration**:
   - Update the database credentials in `includes/config.php` if needed
   - Default credentials:
     ```php
     $host = 'localhost';
     $db = 'eduwave';
     $user = 'root';
     $pass = ''; 
     ```

5. **File Permissions**:
   - Ensure the `uploads/` directory has write permissions for file uploads
   - Verify that the `includes/tcpdf/` directory has proper permissions

## Default Accounts

After setting up the database with sample data, use these default accounts:

- **Admin**: admin@eduwave.com / password
- **Registrar**: registrar@eduwave.com / password
- **Teacher**: teacher@eduwave.com / password
- **Student**: student@eduwave.com / password
- **Parent**: parent@eduwave.com / password

## Project Structure

```
eduwave/
├── assets/
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   └── uploads/     # File uploads
├── includes/
│   ├── config.php   # Database configuration
│   ├── auth.php     # Authentication system
│   ├── header.php   # Common header
│   ├── footer.php   # Common footer
│   └── tcpdf/       # PDF generation library
├── admin/          # Admin panel pages
├── registrar/      # Registrar panel pages
├── teacher/        # Teacher panel pages
├── student/        # Student panel pages
├── parent/         # Parent panel pages
├── messages/       # Messaging system
├── uploads/        # File uploads storage
├── database-schema.sql
├── login.php
├── logout.php
├── index.php       # Landing page
└── README.md
```

## Security Features

- Passwords are hashed using bcrypt
- Session-based authentication
- SQL injection prevention with PDO prepared statements
- File upload validation and security measures
- Role-based access control
- CSRF protection on forms

## File Upload Support

The system supports multiple file formats:
- Documents: PDF, DOCX
- Archives: ZIP, RAR
- Images: JPG, PNG
- Maximum file size: 10 MB

## Database Schema

The system includes 13 core tables:
- `users` - All system users (students, teachers, admin, etc.)
- `classes` - Grade levels 1-10
- `subjects` - Academic subjects
- `teacher_assignments` - Teacher-class-subject relationships
- `student_classes` - Student-class enrollment
- `parent_student` - Parent-child relationships
- `schedule` - Weekly timetables
- `grades` - Student academic performance
- `attendance` - Daily attendance tracking
- `assignments` - Homework assignments
- `submissions` - Student homework submissions
- `library_books` - Digital library
- `certificates` - Graduation certificates

## Customization

### Theme Colors
The system uses a role-based color scheme:
- Admin: Blue
- Registrar: Green
- Teacher: Orange
- Student: Purple
- Parent: Yellow


## License

This project is created for educational purposes and is available as is. The project includes the TCPDF library which has its own licensing terms.



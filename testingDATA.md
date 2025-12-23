# EduWave Testing Data

This file contains sample accounts and data for testing the EduWave School Management System.

## User Accounts

### Admin Accounts

| Email/Username | Password | Name | Role | Notes |
|----------------|----------|------|------|-------|
| admin@eduwave.com | admin123 | John Smith | Admin | Primary admin account |
| principal@eduwave.com | principal123 | Dr. Sarah Johnson | Admin | School principal |

### Teacher Accounts

| Email/Username | Password | Name | Role | Subject/Grade | Notes |
|----------------|----------|------|------|---------------|-------|
| mjohnson@eduwave.com | teacher123 | Mary Johnson | Teacher | Mathematics | Grade 9-10 |
| rwilliams@eduwave.com | teacher123 | Robert Williams | Teacher | Science | Grade 7-8 |
| sbrown@eduwave.com | teacher123 | Susan Brown | Teacher | English | Grade 9-10 |
| dmiller@eduwave.com | teacher123 | David Miller | Teacher | History | Grade 7-8 |
| jdavis@eduwave.com | teacher123 | Jennifer Davis | Teacher | Computer Studies | Grade 9-10 |

### Parent Accounts

| Email/Username | Password | Name | Role | Children | Notes |
|----------------|----------|------|------|----------|-------|
| parent1@eduwave.com | parent123 | Michael Thompson | Parent | Emma Thompson (Student) | Father of Emma |
| parent2@eduwave.com | parent123 | Lisa Anderson | Parent | James Anderson (Student) | Mother of James |
| parent3@eduwave.com | parent123 | Robert Martinez | Parent | Sophia Martinez (Student), Carlos Martinez (Student) | Father of two children |
| parent4@eduwave.com | parent123 | Jennifer Wilson | Parent | Olivia Wilson (Student) | Mother of Olivia |
| parent5@eduwave.com | parent123 | David Taylor | Parent | Noah Taylor (Student), Ava Taylor (Student) | Father of two children |

### Student Accounts

| Email/Username | Password | Name | Role | Grade | Parent | Notes |
|----------------|----------|------|------|-------|--------|-------|
| student1@eduwave.com | student123 | Emma Thompson | Student | Grade 10 | Michael Thompson | High-achieving student |
| student2@eduwave.com | student123 | James Anderson | Student | Grade 9 | Lisa Anderson | Active participant |
| student3@eduwave.com | student123 | Sophia Martinez | Student | Grade 8 | Robert Martinez | Science enthusiast |
| student4@eduwave.com | student123 | Carlos Martinez | Student | Grade 7 | Robert Martinez | Sports enthusiast |
| student5@eduwave.com | student123 | Olivia Wilson | Student | Grade 10 | Jennifer Wilson | Art student |
| student6@eduwave.com | student123 | Noah Taylor | Student | Grade 9 | David Taylor | Mathematics whiz |
| student7@eduwave.com | student123 | Ava Taylor | Student | Grade 8 | David Taylor | Language arts student |
| student8@eduwave.com | student123 | Ethan Brown | Student | Grade 7 | - | Transfer student |
| student9@eduwave.com | student123 | Isabella Garcia | Student | Grade 10 | - | Honor student |
| student10@eduwave.com | student123 | Mason Lee | Student | Grade 9 | - | New student |

### Registrar Accounts

| Email/Username | Password | Name | Role | Notes |
|----------------|----------|------|------|-------|
| registrar@eduwave.com | registrar123 | Patricia White | Registrar | Main registrar |

## Class Structure

### Grade 7
- **Class 7A**: Mathematics (Robert Williams), Science (Robert Williams), History (David Miller)
- **Class 7B**: Mathematics (Robert Williams), Science (Robert Williams), History (David Miller)
- Students: Carlos Martinez, Ethan Brown, Mason Lee

### Grade 8
- **Class 8A**: Mathematics (Mary Johnson), Science (Robert Williams), English (Susan Brown)
- **Class 8B**: Mathematics (Mary Johnson), Science (Robert Williams), English (Susan Brown)
- Students: Sophia Martinez, Ava Taylor

### Grade 9
- **Class 9A**: Mathematics (Mary Johnson), Science (Susan Brown), History (David Miller), Computer Studies (Jennifer Davis)
- **Class 9B**: Mathematics (Mary Johnson), Science (Susan Brown), History (David Miller), Computer Studies (Jennifer Davis)
- Students: James Anderson, Noah Taylor, Mason Lee

### Grade 10
- **Class 10A**: Mathematics (Mary Johnson), Science (Susan Brown), History (David Miller), Computer Studies (Jennifer Davis)
- **Class 10B**: Mathematics (Mary Johnson), Science (Susan Brown), History (David Miller), Computer Studies (Jennifer Davis)
- Students: Emma Thompson, Olivia Wilson, Isabella Garcia

## Sample Testing Scenarios

### 1. Admin Testing
- **User Management**: Create, edit, delete users
- **Class Management**: Add/edit classes and subjects
- **Library Management**: Upload and manage books
- **Calendar Events**: Create school events and holidays
- **System Overview**: Check dashboard statistics

### 2. Teacher Testing
- **Attendance**: Mark daily attendance for classes
- **Grades**: Enter test and assignment grades
- **Homework**: Assign and grade homework
- **Class List**: View student information
- **Communication**: View parent contacts

### 3. Parent Testing
- **Dashboard**: View children's overview
- **Grades**: Monitor academic performance
- **Attendance**: Check attendance records
- **Timetable**: View class schedules
- **Communication**: Access teacher information

### 4. Student Testing
- **Dashboard**: View personal overview
- **Grades**: Check academic progress
- **Attendance**: View attendance history
- **Homework**: View and submit assignments
- **Timetable**: Check daily schedule

### 5. Registrar Testing
- **Student Enrollment**: Register new students
- **Parent Management**: Add and link parents
- **Student Transfer**: Transfer students between classes
- **Certificate Issuing**: Generate graduation certificates

## Sample Data for Testing

### Sample Subjects
- Mathematics (Grades 7-10)
- Science (Grades 7-10)
- English (Grades 7-10)
- History (Grades 7-10)
- Computer Studies (Grades 9-10)
- Physical Education (Grades 7-10)
- Art (Grades 7-10)

### Sample Library Books
- "Mathematics Fundamentals" - Mathematics
- "Science Explained" - Science
- "English Literature Guide" - English
- "World History" - History
- "Introduction to Programming" - Computer Studies

### Sample Calendar Events
- **Academic Year Start**: First Monday of September
- **Mid-Term Break**: One week in October
- **Final Exams**: Last two weeks of December
- **Spring Break**: One week in March
- **Academic Year End**: Last Friday of June
- **Parent-Teacher Meetings**: First week of November and April

## Testing Checklist

### Authentication Testing
- [ ] Login with each user type
- [ ] Logout functionality
- [ ] Session timeout
- [ ] Wrong password handling
- [ ] Password reset (if implemented)

### Role-Based Access Testing
- [ ] Admin access to all admin functions
- [ ] Teacher access to class management only
- [ ] Parent access to children's data only
- [ ] Student access to personal data only
- [ ] Registrar access to enrollment functions

### Data Management Testing
- [ ] Add new users
- [ ] Edit existing users
- [ ] Delete users (test soft delete)
- [ ] Assign students to classes
- [ ] Link parents to students

### Academic Testing
- [ ] Mark attendance
- [ ] Enter grades
- [ ] Assign homework
- [ ] Generate reports

### File Operations Testing
- [ ] Upload library books
- [ ] Download certificates
- [ ] File validation (PDF only for books)

### Navigation Testing
- [ ] Sidebar menu functionality
- [ ] Responsive design on mobile
- [ ] Bread-crumb navigation
- [ ] Back/forward browser navigation

## Performance Testing

### Expected Response Times
- Login: < 2 seconds
- Dashboard loading: < 3 seconds
- Report generation: < 5 seconds
- File upload: < 10 seconds for files up to 10MB

### Concurrent Users
- System should support 50+ concurrent users
- Database queries should be optimized
- File uploads should not block the system

## Security Testing

### Password Security
- [ ] Password hashing (bcrypt)
- [ ] Minimum password length (8 characters)
- [ ] Password complexity requirements
- [ ] Session management

### Data Protection
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] CSRF protection
- [ ] File upload validation

### Access Control
- [ ] Role-based access control
- [ ] Session timeout
- [ ] Secure file storage
- [ ] Input validation

## Browser Compatibility

### Supported Browsers
- Chrome (Latest version)
- Firefox (Latest version)
- Safari (Latest version)
- Edge (Latest version)

### Mobile Testing
- iOS Safari (iOS 12+)
- Chrome Mobile (Android 8+)
- Responsive design validation

## Troubleshooting Common Issues

### Login Issues
1. Check if user exists in database
2. Verify password hashing
3. Check session configuration
4. Verify database connection

### Sidebar Issues
1. Check HTML structure
2. Verify CSS classes
3. Check JavaScript functionality
4. Validate menu item links

### File Upload Issues
1. Check directory permissions
2. Verify file size limits
3. Check allowed file types
4. Validate upload path

### Database Issues
1. Check database connection
2. Verify table structure
3. Check foreign key constraints
4. Validate SQL queries

## Notes for Testers

1. Always test with different user roles
2. Test edge cases (empty data, maximum limits)
3. Verify data integrity after operations
4. Check error messages for clarity
5. Document any bugs or improvements found
6. Test the system under realistic load conditions
7. Verify that all security measures are working
8. Test the system on different browsers and devices
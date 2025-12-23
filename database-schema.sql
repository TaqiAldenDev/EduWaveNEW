-- EduWave School Management System Database Schema
-- Complete implementation script for fresh database setup
-- Compatible with MySQL/MariaDB 5.7+

-- ===============================================
-- Database Creation (optional - uncomment if needed)
-- ===============================================
-- CREATE DATABASE IF NOT EXISTS `eduwave` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `eduwave`;

-- ===============================================
-- Initial Configuration
-- ===============================================
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ===============================================
-- Users Table - Core user authentication and profiles
-- ===============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `role` enum('Admin','Registrar','Teacher','Student','Parent') NOT NULL,
  `color_theme` varchar(20) NOT NULL DEFAULT 'blue',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Classes Table - Grade levels
-- ===============================================
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grade_name` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Sections Table - Class sections/groups
-- ===============================================
CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) DEFAULT NULL,
  `section_name` varchar(10) NOT NULL,
  `max_students` int(11) DEFAULT 30,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Subjects Table - Academic subjects
-- ===============================================
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Schedule Table - Class timetables
-- ===============================================
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `day_of_week` enum('Sun','Mon','Tue','Wed','Thu','Fri','Sat') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Teacher Assignments Table - Which teachers teach which subjects/classes
-- ===============================================
CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_teacher_class_subject` (`teacher_id`,`class_id`,`subject_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Student Classes Table - Student enrollment in classes/sections
-- ===============================================
CREATE TABLE `student_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `academic_year` year(4) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_student_year` (`student_id`,`academic_year`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_classes_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Parent Student Relationship Table
-- ===============================================
CREATE TABLE `parent_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `relation` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pair` (`parent_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `parent_student_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_student_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Assignments Table - Teacher-created assignments
-- ===============================================
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Submissions Table - Student assignment submissions
-- ===============================================
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_submission` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Attendance Table - Student attendance records
-- ===============================================
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `attend_date` date DEFAULT NULL,
  `status` enum('Present','Absent') DEFAULT 'Present',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_attend` (`student_id`,`subject_id`,`attend_date`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Grades Table - Student grades and scores
-- ===============================================
CREATE TABLE `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `exam_type` varchar(30) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `date_given` date DEFAULT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Library Books Table - Digital library resources
-- ===============================================
CREATE TABLE `library_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `library_books_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Certificates Table - Student certificates
-- ===============================================
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `academic_year` year(4) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `issued_by` (`issued_by`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Calendar Events Table - School calendar events
-- ===============================================
CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calendar_events_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===============================================
-- Notifications Table - User notifications
-- ===============================================
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===============================================
-- Homework Submissions Table - Student homework submission tracking
-- ===============================================
CREATE TABLE `homework_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','submitted','graded','late') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `teacher_feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_homework_submission` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `homework_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Initial Data Seeding
-- ===============================================

-- Default Classes (Grades 1-10)
INSERT INTO `classes` (`grade_name`, `notes`) VALUES
('Grade 1', 'First grade class'),
('Grade 2', 'Second grade class'),
('Grade 3', 'Third grade class'),
('Grade 4', 'Fourth grade class'),
('Grade 5', 'Fifth grade class'),
('Grade 6', 'Sixth grade class'),
('Grade 7', 'Seventh grade class'),
('Grade 8', 'Eighth grade class'),
('Grade 9', 'Ninth grade class'),
('Grade 10', 'Tenth grade class');

-- Default Sections for each class (3 sections per grade)
INSERT INTO `sections` (`class_id`, `section_name`, `max_students`) VALUES
(1, 'Section 1', 30),
(1, 'Section 2', 30),
(1, 'Section 3', 30),
(2, 'Section 1', 30),
(2, 'Section 2', 30),
(2, 'Section 3', 30),
(3, 'Section 1', 30),
(3, 'Section 2', 30),
(3, 'Section 3', 30),
(4, 'Section 1', 30),
(4, 'Section 2', 30),
(4, 'Section 3', 30),
(5, 'Section 1', 30),
(5, 'Section 2', 30),
(5, 'Section 3', 30),
(6, 'Section 1', 30),
(6, 'Section 2', 30),
(6, 'Section 3', 30),
(7, 'Section 1', 30),
(7, 'Section 2', 30),
(7, 'Section 3', 30),
(8, 'Section 1', 30),
(8, 'Section 2', 30),
(8, 'Section 3', 30),
(9, 'Section 1', 30),
(9, 'Section 2', 30),
(9, 'Section 3', 30),
(10, 'Section 1', 30),
(10, 'Section 2', 30),
(10, 'Section 3', 30);

-- Default Subjects for primary grades (1-3)
INSERT INTO `subjects` (`name`, `class_id`) VALUES
('Mathematics', 1),
('Science', 1),
('English', 1),
('Mathematics', 2),
('Science', 2),
('English', 2),
('Mathematics', 3),
('Science', 3),
('English', 3);

-- Default Subjects for middle and high grades (4-10)
INSERT INTO `subjects` (`name`, `class_id`) VALUES
('Mathematics', 4),
('Science', 4),
('English', 4),
('Social Studies', 4),
('Mathematics', 5),
('Science', 5),
('English', 5),
('Social Studies', 5),
('Mathematics', 6),
('Science', 6),
('English', 6),
('Social Studies', 6),
('Mathematics', 7),
('Science', 7),
('English', 7),
('Social Studies', 7),
('Physics', 8),
('Chemistry', 8),
('Biology', 8),
('Mathematics', 8),
('English', 8),
('Physics', 9),
('Chemistry', 9),
('Biology', 9),
('Mathematics', 9),
('English', 9),
('Physics', 10),
('Chemistry', 10),
('Biology', 10),
('Mathematics', 10),
('English', 10);

-- Default Users (password: password for all accounts)
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `color_theme`) VALUES
('Admin User', 'admin@eduwave.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'blue'),
('Registrar User', 'registrar@eduwave.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Registrar', 'green'),
('Teacher User', 'teacher@eduwave.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teacher', 'orange'),
('Student User', 'student@eduwave.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'purple'),
('Parent User', 'parent@eduwave.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Parent', 'yellow');

-- Sample Parent-Student Relationships
INSERT INTO `parent_student` (`parent_id`, `student_id`, `relation`) VALUES
(5, 4, 'Father');

-- Sample Student Enrollment
INSERT INTO `student_classes` (`student_id`, `class_id`, `academic_year`, `section_id`) VALUES
(4, 1, 2025, 1);

-- Sample Teacher Assignments
INSERT INTO `teacher_assignments` (`teacher_id`, `class_id`, `subject_id`) VALUES
(3, 1, 1),
(3, 1, 2),
(3, 1, 3);

-- Sample Assignments
INSERT INTO `assignments` (`teacher_id`, `subject_id`, `class_id`, `title`, `description`, `due_date`) VALUES
(3, 1, 1, 'Math Homework Week 1', 'Complete exercises 1-20 from Chapter 1', '2025-12-30'),
(3, 2, 1, 'Science Project', 'Create a model of the solar system', '2026-01-15');

-- Sample Homework Submissions
INSERT INTO `homework_submissions` (`assignment_id`, `student_id`, `status`, `submitted_at`) VALUES
(1, 4, 'pending', NULL),
(2, 4, 'submitted', '2025-12-20 10:30:00');

-- Sample Grades with Comments
INSERT INTO `grades` (`student_id`, `subject_id`, `exam_type`, `score`, `date_given`, `comments`) VALUES
(4, 1, 'Quiz', 85.50, '2025-12-15', 'Good work on basic arithmetic, needs practice with word problems'),
(4, 2, 'Test', 78.00, '2025-12-18', 'Shows understanding of basic concepts, improve scientific notation');

-- Sample Notifications with Types
INSERT INTO `notifications` (`user_id`, `message`, `type`, `is_read`) VALUES
(4, 'New math assignment has been posted', 'assignment', 0),
(4, 'Your science project submission has been graded', 'grade', 0),
(5, 'Your child has a new assignment', 'assignment', 0),
(5, 'Parent-teacher meeting scheduled for next week', 'general', 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ===============================================
-- Database Setup Complete!
-- ===============================================
-- 
-- Installation Instructions:
-- 
-- 1. Create a new database named 'eduwave' in your MySQL/MariaDB server
-- 2. Import this SQL file using phpMyAdmin, MySQL CLI, or your preferred tool
-- 3. Update the database connection settings in includes/config.php
-- 4. The system is ready to use!
--
-- Features Included:
-- - Complete user management (Admin, Registrar, Teacher, Student, Parent roles)
-- - Class and section management with enrollment system
-- - Subject management with teacher assignments
-- - Homework/assignment system with file uploads
-- - Grade management with teacher comments
-- - Attendance tracking and reporting
-- - Library management system
-- - Certificate generation
-- - Calendar events and scheduling
-- - Notification system with categorized alerts
-- - Parent dashboard with child progress monitoring
--
-- Default Login Credentials (password: password):
-- - Admin: admin@eduwave.com
-- - Registrar: registrar@eduwave.com
-- - Teacher: teacher@eduwave.com
-- - Student: student@eduwave.com
-- - Parent: parent@eduwave.com
--
-- Sample Data Included:
-- - Grades 1-10 with 3 sections each
-- - Core subjects for all grade levels
-- - Sample parent-student relationships
-- - Sample assignments and submissions
-- - Sample grades with teacher comments
-- - Sample notifications
--
-- Note: All test accounts use the same password: 'password' (without quotes)
-- For production use, change these passwords immediately after setup.
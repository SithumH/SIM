-- Student Information System Database Schema
-- Complete RBAC (Role-Based Access Control) System

CREATE DATABASE IF NOT EXISTS sis_db;
USE sis_db;

-- 1. Users Table (Authentication)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff', 'Lecturer', 'Student') NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Courses Table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in years',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Subjects Table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    course_id INT NOT NULL,
    credits INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- 4. Students Table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    reg_no VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. Enrollments Table
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester INT DEFAULT 1,
    enrollment_date DATE NOT NULL,
    status ENUM('Enrolled', 'Dropped', 'Completed') DEFAULT 'Enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- 6. Lecturer-Subject Assignment Table
CREATE TABLE lecturer_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lecturer_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    assigned_date DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (lecturer_id, subject_id, academic_year)
);

-- 7. Attendance Table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late') NOT NULL,
    marked_by INT NOT NULL COMMENT 'User ID of lecturer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id),
    UNIQUE KEY unique_attendance (student_id, subject_id, attendance_date)
);

-- 8. Exams Table
CREATE TABLE exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    exam_name VARCHAR(100) NOT NULL,
    exam_type ENUM('Quiz', 'Mid', 'Final', 'Assignment') NOT NULL,
    exam_date DATE,
    max_marks INT NOT NULL,
    locked BOOLEAN DEFAULT FALSE COMMENT 'Lock results after approval',
    created_by INT NOT NULL COMMENT 'Lecturer user ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 9. Results Table
CREATE TABLE results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    grade VARCHAR(5),
    entered_by INT NOT NULL COMMENT 'Lecturer user ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES users(id),
    UNIQUE KEY unique_result (exam_id, student_id)
);

-- 10. Activity Logs Table (Audit Trail)
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Demo Users (Passwords: admin123, staff123, lec123, stu123)
INSERT INTO users (username, password, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', 'Active'),
('lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lecturer', 'Active'),
('student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active');

-- Insert Demo Courses
INSERT INTO courses (course_code, course_name, duration, status) VALUES
('BSC-CS', 'BSc in Computer Science', 4, 'Active'),
('BSC-IT', 'BSc in Information Technology', 3, 'Active'),
('DIP-SE', 'Diploma in Software Engineering', 2, 'Active');

-- Insert Demo Subjects
INSERT INTO subjects (subject_code, subject_name, course_id, credits) VALUES
('CS101', 'Programming Fundamentals', 1, 4),
('CS102', 'Data Structures', 1, 4),
('CS201', 'Database Management Systems', 1, 3),
('IT101', 'Web Development', 2, 3),
('IT102', 'Network Fundamentals', 2, 3);

-- Insert Demo Student
INSERT INTO students (user_id, reg_no, full_name, email, phone, status) VALUES
(4, 'STU2024001', 'Kasun Perera', 'kasun@example.com', '0771234567', 'Active');

-- Enroll Demo Student
INSERT INTO enrollments (student_id, course_id, academic_year, semester, enrollment_date, status) VALUES
(1, 1, '2024', 1, '2024-01-15', 'Enrolled');

-- Assign Lecturer to Subject
INSERT INTO lecturer_subjects (lecturer_id, subject_id, academic_year) VALUES
(3, 1, '2024'),
(3, 2, '2024');

-- Create Indexes for Performance
CREATE INDEX idx_attendance_date ON attendance(attendance_date);
CREATE INDEX idx_enrollment_status ON enrollments(status);
CREATE INDEX idx_student_status ON students(status);
CREATE INDEX idx_activity_logs_user ON activity_logs(user_id, created_at);

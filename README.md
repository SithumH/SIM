# 🎓 Student Information System (SIS) - RBAC Implementation

Complete **Role-Based Access Control (RBAC)** system with 4 roles: Admin, Staff, Lecturer, Student

---

## 📁 Project Structure

```
SIM/
├── config/
│   └── database.php          # Database connection
├── includes/
│   ├── auth.php              # Authentication & session management
│   └── rbac.php              # Role-based access control
├── admin/
│   └── dashboard.php         # Admin dashboard
├── staff/
│   └── dashboard.php         # Staff dashboard
├── lecturer/
│   └── dashboard.php         # Lecturer dashboard
├── student/
│   └── dashboard.php         # Student dashboard
├── login.php                 # Login page
├── logout.php                # Logout handler
└── database_schema.sql       # Database schema
```

---

## 🚀 Installation

### 1. Database Setup

```sql
-- Import database schema
mysql -u root -p < database_schema.sql
```

Or use phpMyAdmin to import `database_schema.sql`

### 2. Configure Database

Edit `config/database.php` if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sis_db');
```

### 3. Access System

Open browser: `http://localhost/SIM/login.php`

---

## 👥 Demo Accounts

| Role     | Username  | Password  |
|----------|-----------|-----------|
| Admin    | admin     | admin123  |
| Staff    | staff     | staff123  |
| Lecturer | lecturer  | lec123    |
| Student  | student   | stu123    |

---

## 🔐 Role Permissions

### 1️⃣ **Admin (System Administrator)**

**Full System Control**

✅ **User & Role Management**
- Create/Edit/Delete users
- Assign/Change roles
- Reset passwords
- View activity logs

✅ **Student Management**
- Add/Edit/Delete students
- Change student status
- Bulk import (Excel/CSV)

✅ **Course & Subject Management**
- Create/Edit/Delete courses
- Create/Edit/Delete subjects
- Assign lecturers to subjects

✅ **Enrollment Management**
- Enroll students to courses
- Change enrollment status
- Manage academic year/semester

✅ **Attendance**
- View all attendance
- Edit/Override attendance records

✅ **Exams & Results**
- Create exam types
- View/Edit/Delete all results
- Lock/Unlock results

✅ **Reports**
- Generate all reports
- View audit logs

✅ **System & Security**
- Database backup/restore
- Access control rules
- System settings

---

### 2️⃣ **Staff / Registrar**

**Administrative Work: Registration + Enrollments**

✅ **Student Registration**
- Add new students
- Update student profiles
- View all students

✅ **Enrollment**
- Enroll students to courses
- Drop/Transfer students
- View enrollment history

✅ **Course/Subject (View Only)**
- View courses/subjects

✅ **Attendance (View Only)**
- View attendance records
- Generate absence lists

✅ **Results (View Only)**
- View results
- Print transcripts

✅ **Reports**
- Student list reports
- Enrollment reports
- Attendance summary

❌ **Restrictions**
- Cannot create users
- Cannot delete courses/subjects
- Cannot edit attendance
- Cannot edit marks

---

### 3️⃣ **Lecturer / Teacher**

**Academic Side: Attendance + Exams + Results**

✅ **Dashboard**
- View assigned subjects/classes
- View enrolled students

✅ **Attendance**
- Mark daily attendance
- Edit attendance (same day only)
- View attendance analytics

✅ **Exams**
- Create exams (Quiz/Mid/Final)
- Set max marks, exam date

✅ **Results**
- Enter marks for students
- Update marks (before lock)
- View grade distribution

✅ **Reports**
- Subject performance report
- Class attendance report

❌ **Restrictions**
- Cannot access other lecturers' subjects
- Cannot edit student personal data
- Cannot manage enrollments

---

### 4️⃣ **Student**

**View Own Information Only**

✅ **Profile**
- View own profile
- Edit contact info (phone/address)

✅ **Enrollments**
- View own course enrollment
- View subjects list

✅ **Attendance**
- View own attendance history
- Download attendance summary

✅ **Results**
- View exam results
- View grades/GPA
- Download transcript (PDF)

❌ **Restrictions**
- Only self-access (cannot view others)
- Cannot change marks/attendance
- Cannot self-enroll

---

## 🔒 Security Features

### ✅ Ownership Rules
- Student → only own data
- Lecturer → only assigned subjects
- Staff → all students (limited edits)
- Admin → everything

### ✅ Record Locking
- Results lock after admin approval
- Attendance edit window (same day only)

### ✅ Audit Trail
- Activity logs table
- Track who edited what, when

### ✅ Password Security
- Passwords hashed with `password_hash()`
- Secure session management

---

## 📊 Database Tables

1. **users** - Authentication & roles
2. **courses** - Course master data
3. **subjects** - Subject master data
4. **students** - Student profiles
5. **enrollments** - Student-course enrollments
6. **lecturer_subjects** - Lecturer assignments
7. **attendance** - Daily attendance records
8. **exams** - Exam definitions
9. **results** - Student exam marks
10. **activity_logs** - Audit trail

---

## 🛠️ Key Functions

### Authentication (`includes/auth.php`)
- `login()` - User login
- `logout()` - User logout
- `isLoggedIn()` - Check login status
- `getUserRole()` - Get current user role
- `requireLogin()` - Force login redirect
- `logActivity()` - Log user actions

### RBAC (`includes/rbac.php`)
- `hasPermission($permission)` - Check permission
- `requirePermission($permission)` - Enforce permission
- `requireRole($roles)` - Enforce role access
- `ownsResource($userId)` - Check ownership
- `isAssignedToSubject()` - Check lecturer assignment
- `canEditAttendance()` - Check edit window
- `areResultsLocked()` - Check result lock status

---

## 🎯 Usage Examples

### Check Permission
```php
if (hasPermission('create_student')) {
    // Show create student form
}
```

### Require Permission
```php
requirePermission('edit_all_results');
// Only Admin can access beyond this point
```

### Require Role
```php
requireRole(['Admin', 'Staff']);
// Only Admin or Staff can access
```

### Check Ownership
```php
if (getUserRole() === 'Student' && !ownsResource($student_id)) {
    die('Access denied');
}
```

---

## 📝 Notes

- All passwords are hashed using PHP `password_hash()`
- Session-based authentication
- SQL injection protected (prepared statements)
- XSS protected (htmlspecialchars)
- Activity logging for audit trail
- Responsive design (mobile-friendly)

---

## 🔄 Future Enhancements

- Email notifications
- Bulk student import (Excel/CSV)
- GPA calculation
- Transcript PDF generation
- Advanced reporting
- Two-factor authentication
- Password reset via email

---

## 📞 Support

For issues or questions, contact system administrator.

---

**Developed with ❤️ for Student Information System**

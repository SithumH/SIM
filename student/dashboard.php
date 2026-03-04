<?php
require_once '../includes/rbac.php';
requireRole('Student');

$conn = getDBConnection();
$student_id = getUserId();

// Get student info
$stmt = $conn->prepare("
    SELECT s.*, c.course_name, c.duration, e.enrollment_date, e.status as enrollment_status
    FROM students s
    LEFT JOIN enrollments e ON s.id = e.student_id
    LEFT JOIN courses c ON e.course_id = c.id
    WHERE s.user_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get subjects count
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM subjects s
    JOIN enrollments e ON s.course_id = e.course_id
    WHERE e.student_id = ?
");
$stmt->bind_param("i", $student['id'] ?? 0);
$stmt->execute();
$subjects_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SIS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0a0e27; display: flex; }
        .sidebar { width: 260px; background: #111827; height: 100vh; position: fixed; box-shadow: 2px 0 20px rgba(0,0,0,0.5); border-right: 1px solid #1f2937; }
        .logo { padding: 30px 25px; font-size: 24px; font-weight: bold; color: #10b981; border-bottom: 1px solid #1f2937; }
        .nav-menu { padding: 20px 0; }
        .nav-item { padding: 12px 25px; color: #9ca3af; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.3s; }
        .nav-item:hover, .nav-item.active { background: #1f2937; color: #10b981; border-left: 3px solid #10b981; }
        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .top-bar h1 { font-size: 28px; color: #fff; }
        .user-profile { display: flex; align-items: center; gap: 15px; background: #111827; padding: 10px 20px; border-radius: 50px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); border: 1px solid #1f2937; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .logout-btn { background: #ef4444; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 14px; }
        .profile-card { background: #111827; padding: 25px; border-radius: 15px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); margin-bottom: 30px; border: 1px solid #1f2937; }
        .profile-card h2 { margin-bottom: 20px; color: #fff; }
        .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .profile-item { padding: 10px 0; }
        .profile-item label { display: block; color: #6b7280; font-size: 12px; margin-bottom: 5px; }
        .profile-item .value { color: #fff; font-size: 16px; font-weight: 500; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #111827; padding: 25px; border-radius: 15px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); position: relative; overflow: hidden; border: 1px solid #1f2937; }
        .stat-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(16,185,129,0.05) 100%); border-radius: 0 0 0 100%; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .stat-label { color: #6b7280; font-size: 13px; margin-bottom: 8px; }
        .stat-value { font-size: 32px; font-weight: bold; color: #fff; }
        .management-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .management-card { background: #111827; padding: 25px; border-radius: 15px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); border: 1px solid #1f2937; }
        .management-card h3 { font-size: 18px; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .btn-group { display: flex; flex-direction: column; gap: 12px; }
        .btn { padding: 12px 20px; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: space-between; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(16,185,129,0.4); }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-active { background: #065f46; color: #6ee7b7; }
        .status-enrolled { background: #1e3a8a; color: #93c5fd; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">🎓 SIS Student</div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
            <a href="profile.php" class="nav-item">👤 Profile</a>
            <a href="subjects.php" class="nav-item">📚 Subjects</a>
            <a href="attendance.php" class="nav-item">📅 Attendance</a>
            <a href="results.php" class="nav-item">📊 Results</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Welcome, Student! 👋</h1>
            <div class="user-profile">
                <div class="user-avatar">S</div>
                <span style="color:#fff"><?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="/SIM/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <?php if ($student): ?>
            <div class="profile-card">
                <h2>My Profile</h2>
                <div class="profile-grid">
                    <div class="profile-item">
                        <label>Registration Number</label>
                        <div class="value"><?= htmlspecialchars($student['reg_no']) ?></div>
                    </div>
                    <div class="profile-item">
                        <label>Full Name</label>
                        <div class="value"><?= htmlspecialchars($student['full_name']) ?></div>
                    </div>
                    <div class="profile-item">
                        <label>Email</label>
                        <div class="value"><?= htmlspecialchars($student['email']) ?></div>
                    </div>
                    <div class="profile-item">
                        <label>Phone</label>
                        <div class="value"><?= htmlspecialchars($student['phone']) ?></div>
                    </div>
                    <div class="profile-item">
                        <label>Course</label>
                        <div class="value"><?= htmlspecialchars($student['course_name'] ?? 'Not Enrolled') ?></div>
                    </div>
                    <div class="profile-item">
                        <label>Status</label>
                        <div class="value">
                            <span class="status-badge status-<?= strtolower($student['status']) ?>">
                                <?= htmlspecialchars($student['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <label>Enrollment Status</label>
                        <div class="value">
                            <span class="status-badge status-<?= strtolower($student['enrollment_status'] ?? 'none') ?>">
                                <?= htmlspecialchars($student['enrollment_status'] ?? 'Not Enrolled') ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <label>Enrollment Date</label>
                        <div class="value"><?= $student['enrollment_date'] ? date('d M Y', strtotime($student['enrollment_date'])) : 'N/A' ?></div>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-label">ENROLLED SUBJECTS</div>
                    <div class="stat-value"><?= $subjects_count ?></div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="management-grid">
            <div class="management-card">
                <h3>👤 Profile</h3>
                <div class="btn-group">
                    <a href="profile.php" class="btn btn-primary">View/Edit Profile →</a>
                </div>
            </div>
            
            <div class="management-card">
                <h3>📚 Course & Subjects</h3>
                <div class="btn-group">
                    <a href="subjects.php" class="btn btn-primary">View My Subjects →</a>
                </div>
            </div>
            
            <div class="management-card">
                <h3>📅 Attendance</h3>
                <div class="btn-group">
                    <a href="attendance.php" class="btn btn-primary">View My Attendance →</a>
                </div>
            </div>
            
            <div class="management-card">
                <h3>📊 Results & Grades</h3>
                <div class="btn-group">
                    <a href="results.php" class="btn btn-primary">View My Results →</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

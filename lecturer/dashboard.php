<?php
require_once '../includes/rbac.php';
requireRole('Lecturer');

$conn = getDBConnection();
$lecturer_id = getUserId();

// Get lecturer's assigned subjects
$stmt = $conn->prepare("
    SELECT s.id, s.subject_code, s.subject_name, c.course_name,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = s.course_id AND e.status='Enrolled') as student_count
    FROM subjects s
    JOIN courses c ON s.course_id = c.id
    JOIN lecturer_subjects ls ON s.id = ls.subject_id
    WHERE ls.lecturer_id = ?
");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stats = [
    'subjects' => count($subjects),
    'students' => array_sum(array_column($subjects, 'student_count'))
];

$conn->close();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - SIS</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #111827; padding: 25px; border-radius: 15px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); position: relative; overflow: hidden; border: 1px solid #1f2937; }
        .stat-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(16,185,129,0.05) 100%); border-radius: 0 0 0 100%; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .stat-label { color: #6b7280; font-size: 13px; margin-bottom: 8px; }
        .stat-value { font-size: 32px; font-weight: bold; color: #fff; }
        .subjects-list { background: #111827; padding: 25px; border-radius: 15px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); margin-bottom: 30px; border: 1px solid #1f2937; }
        .subjects-list h2 { margin-bottom: 20px; color: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #1f2937; }
        th { background: #1f2937; color: #9ca3af; font-weight: bold; }
        td { color: #d1d5db; }
        .management-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .management-card { background: #111827; padding: 25px; border-radius: 15px; box-shadow: 0 2px 20px rgba(0,0,0,0.3); border: 1px solid #1f2937; }
        .management-card h3 { font-size: 18px; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .btn-group { display: flex; flex-direction: column; gap: 12px; }
        .btn { padding: 12px 20px; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: space-between; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(16,185,129,0.4); }
        .btn-secondary { background: #1f2937; color: #9ca3af; border: 1px solid #374151; }
        .btn-secondary:hover { transform: translateY(-2px); background: #374151; color: #fff; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">👨‍🏫 SIS Lecturer</div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
            <a href="subjects.php" class="nav-item">📚 My Subjects</a>
            <a href="attendance.php" class="nav-item">📅 Attendance</a>
            <a href="exams.php" class="nav-item">📝 Exams</a>
            <a href="results.php" class="nav-item">📊 Results</a>
            <a href="reports.php" class="nav-item">📄 Reports</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Welcome, Lecturer! 👋</h1>
            <div class="user-profile">
                <div class="user-avatar">L</div>
                <span style="color:#fff"><?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="/SIM/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-label">ASSIGNED SUBJECTS</div>
                <div class="stat-value"><?= $stats['subjects'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-label">TOTAL STUDENTS</div>
                <div class="stat-value"><?= $stats['students'] ?></div>
            </div>
        </div>
        
        <div class="subjects-list">
            <h2>My Assigned Subjects</h2>
            <?php if (count($subjects) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Course</th>
                            <th>Enrolled Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                <td><?= htmlspecialchars($subject['course_name']) ?></td>
                                <td><?= $subject['student_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #6b7280;">No subjects assigned yet.</p>
            <?php endif; ?>
        </div>
        
        <div class="management-grid">
            <div class="management-card">
                <h3>📅 Attendance Management</h3>
                <div class="btn-group">
                    <a href="attendance.php" class="btn btn-primary">Mark Attendance →</a>
                    <a href="attendance_view.php" class="btn btn-secondary">View Attendance →</a>
                </div>
            </div>
            
            <div class="management-card">
                <h3>📝 Exam Management</h3>
                <div class="btn-group">
                    <a href="exams.php" class="btn btn-primary">Manage Exams →</a>
                    <a href="exams.php?action=add" class="btn btn-secondary">Create Exam →</a>
                </div>
            </div>
            
            <div class="management-card">
                <h3>📊 Results Management</h3>
                <div class="btn-group">
                    <a href="results.php" class="btn btn-primary">Enter Marks →</a>
                    <a href="results_view.php" class="btn btn-secondary">View Results →</a>
                </div>
            </div>
            
            <div class="management-card">
                <h3>📄 Reports</h3>
                <div class="btn-group">
                    <a href="reports.php" class="btn btn-secondary">Generate Reports →</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

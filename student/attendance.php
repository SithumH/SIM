<?php
require_once '../includes/rbac.php';
requireRole('Student');

$conn = getDBConnection();
$user_id = getUserId();

// Get student info
$student = $conn->query("SELECT * FROM students WHERE user_id=$user_id")->fetch_assoc();

if (!$student) {
    die("Student record not found");
}

// Get attendance records
$attendance = $conn->query("
    SELECT a.*, s.subject_code, s.subject_name 
    FROM attendance a 
    JOIN subjects s ON a.subject_id = s.id 
    WHERE a.student_id = {$student['id']} 
    ORDER BY a.attendance_date DESC
")->fetch_all(MYSQLI_ASSOC);

// Calculate statistics
$total = count($attendance);
$present = count(array_filter($attendance, fn($a) => $a['status'] === 'Present'));
$absent = count(array_filter($attendance, fn($a) => $a['status'] === 'Absent'));
$late = count(array_filter($attendance, fn($a) => $a['status'] === 'Late'));
$percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Attendance</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 My Attendance</h1>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>TOTAL CLASSES</h3>
                <div class="number"><?= $total ?></div>
            </div>
            <div class="stat-card">
                <h3>PRESENT</h3>
                <div class="number" style="color:#27ae60"><?= $present ?></div>
            </div>
            <div class="stat-card">
                <h3>ABSENT</h3>
                <div class="number" style="color:#e74c3c"><?= $absent ?></div>
            </div>
            <div class="stat-card">
                <h3>LATE</h3>
                <div class="number" style="color:#f39c12"><?= $late ?></div>
            </div>
            <div class="stat-card">
                <h3>ATTENDANCE %</h3>
                <div class="number"><?= $percentage ?>%</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($attendance) > 0): ?>
                    <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($record['attendance_date'])) ?></td>
                            <td><?= $record['subject_code'] ?></td>
                            <td><?= htmlspecialchars($record['subject_name']) ?></td>
                            <td class="status-<?= strtolower($record['status']) ?>"><?= $record['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;color:#999;">No attendance records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
require_once '../includes/rbac.php';
requireRole('Lecturer');

$conn = getDBConnection();
$lecturer_id = getUserId();

// Get lecturer's subjects
$subjects = $conn->query("SELECT s.id, s.subject_code, s.subject_name FROM subjects s JOIN lecturer_subjects ls ON s.id=ls.subject_id WHERE ls.lecturer_id=$lecturer_id")->fetch_all(MYSQLI_ASSOC);

$students = [];
$selected_subject = null;
$selected_date = date('Y-m-d');

if (isset($_GET['subject_id'])) {
    $subject_id = (int)$_GET['subject_id'];
    $selected_date = $_GET['date'] ?? date('Y-m-d');
    
    // Get students enrolled in this subject's course
    $students = $conn->query("SELECT DISTINCT st.id, st.reg_no, st.full_name FROM students st JOIN enrollments e ON st.id=e.student_id JOIN subjects s ON e.course_id=s.course_id WHERE s.id=$subject_id AND e.status='Enrolled'")->fetch_all(MYSQLI_ASSOC);
    
    $selected_subject = $conn->query("SELECT * FROM subjects WHERE id=$subject_id")->fetch_assoc();
    
    // Get existing attendance
    foreach ($students as &$student) {
        $att = $conn->query("SELECT status FROM attendance WHERE student_id={$student['id']} AND subject_id=$subject_id AND attendance_date='$selected_date'")->fetch_assoc();
        $student['attendance_status'] = $att['status'] ?? null;
    }
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $date = $_POST['date'];
    $attendance = $_POST['attendance'] ?? [];
    
    foreach ($attendance as $student_id => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, attendance_date, status, marked_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=?, marked_by=?");
        $stmt->bind_param("iissiii", $student_id, $subject_id, $date, $status, $lecturer_id, $status, $lecturer_id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: attendance.php?subject_id=$subject_id&date=$date&success=1");
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 Mark Attendance</h1>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>
    
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="success">✅ Attendance marked successfully!</div>
        <?php endif; ?>
        
        <div class="form-card">
            <h2>Select Subject & Date</h2>
            <form method="GET">
                <div class="form-group">
                    <label>Subject *</label>
                    <select name="subject_id" required onchange="this.form.submit()">
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>" <?= ($selected_subject['id'] ?? '') == $subject['id'] ? 'selected' : '' ?>>
                                <?= $subject['subject_code'] ?> - <?= htmlspecialchars($subject['subject_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="date" value="<?= $selected_date ?>" onchange="this.form.submit()">
                </div>
            </form>
        </div>
        
        <?php if ($selected_subject && count($students) > 0): ?>
            <form method="POST">
                <input type="hidden" name="subject_id" value="<?= $selected_subject['id'] ?>">
                <input type="hidden" name="date" value="<?= $selected_date ?>">
                
                <table>
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= $student['reg_no'] ?></td>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td>
                                    <div class="radio-group">
                                        <label><input type="radio" name="attendance[<?= $student['id'] ?>]" value="Present" <?= $student['attendance_status'] === 'Present' ? 'checked' : '' ?>> Present</label>
                                        <label><input type="radio" name="attendance[<?= $student['id'] ?>]" value="Absent" <?= $student['attendance_status'] === 'Absent' ? 'checked' : '' ?>> Absent</label>
                                        <label><input type="radio" name="attendance[<?= $student['id'] ?>]" value="Late" <?= $student['attendance_status'] === 'Late' ? 'checked' : '' ?>> Late</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn">Save Attendance</button>
                </div>
            </form>
        <?php elseif ($selected_subject): ?>
            <p style="text-align:center;color:#999;">No students enrolled in this subject.</p>
        <?php endif; ?>
    </div>
</body>
</html>

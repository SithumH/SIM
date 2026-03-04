<?php
require_once '../includes/rbac.php';
requireRole('Staff');

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $enrollment_date = $_POST['enrollment_date'];
    $status = $_POST['status'];
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE enrollments SET student_id=?, course_id=?, academic_year=?, semester=?, enrollment_date=?, status=? WHERE id=?");
        $stmt->bind_param("iisissi", $student_id, $course_id, $academic_year, $semester, $enrollment_date, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, academic_year, semester, enrollment_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiss", $student_id, $course_id, $academic_year, $semester, $enrollment_date, $status);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: enrollments.php');
    exit;
}

$enrollments = $conn->query("SELECT e.*, s.reg_no, s.full_name, c.course_name FROM enrollments e JOIN students s ON e.student_id=s.id JOIN courses c ON e.course_id=c.id ORDER BY e.id DESC")->fetch_all(MYSQLI_ASSOC);
$students = $conn->query("SELECT id, reg_no, full_name FROM students WHERE status='Active'")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT id, course_name FROM courses WHERE status='Active'")->fetch_all(MYSQLI_ASSOC);

$edit_enrollment = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_enrollment = $conn->query("SELECT * FROM enrollments WHERE id = $id")->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Enrollments</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📝 Manage Enrollments</h1>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2><?= $edit_enrollment ? 'Edit Enrollment' : 'Enroll Student' ?></h2>
            <form method="POST">
                <?php if ($edit_enrollment): ?>
                    <input type="hidden" name="id" value="<?= $edit_enrollment['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Student *</label>
                    <select name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>" <?= ($edit_enrollment['student_id'] ?? '') == $student['id'] ? 'selected' : '' ?>>
                                <?= $student['reg_no'] ?> - <?= htmlspecialchars($student['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Course *</label>
                    <select name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= ($edit_enrollment['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Academic Year *</label>
                    <input type="text" name="academic_year" value="<?= $edit_enrollment['academic_year'] ?? date('Y') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Semester *</label>
                    <input type="number" name="semester" value="<?= $edit_enrollment['semester'] ?? 1 ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Enrollment Date *</label>
                    <input type="date" name="enrollment_date" value="<?= $edit_enrollment['enrollment_date'] ?? date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="Enrolled" <?= ($edit_enrollment['status'] ?? '') === 'Enrolled' ? 'selected' : '' ?>>Enrolled</option>
                        <option value="Dropped" <?= ($edit_enrollment['status'] ?? '') === 'Dropped' ? 'selected' : '' ?>>Dropped</option>
                        <option value="Completed" <?= ($edit_enrollment['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_enrollment ? 'Update' : 'Enroll' ?></button>
                <?php if ($edit_enrollment): ?>
                    <a href="enrollments.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td><?= $enrollment['id'] ?></td>
                        <td><?= $enrollment['reg_no'] ?> - <?= htmlspecialchars($enrollment['full_name']) ?></td>
                        <td><?= htmlspecialchars($enrollment['course_name']) ?></td>
                        <td><?= $enrollment['academic_year'] ?></td>
                        <td><?= $enrollment['semester'] ?></td>
                        <td><?= date('Y-m-d', strtotime($enrollment['enrollment_date'])) ?></td>
                        <td><?= $enrollment['status'] ?></td>
                        <td class="actions">
                            <a href="?edit=<?= $enrollment['id'] ?>" class="edit">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

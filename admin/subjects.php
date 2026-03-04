<?php
require_once '../includes/rbac.php';
requireRole('Admin');

$conn = getDBConnection();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id = $id");
    header('Location: subjects.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $course_id = $_POST['course_id'];
    $credits = $_POST['credits'];
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE subjects SET subject_code=?, subject_name=?, course_id=?, credits=? WHERE id=?");
        $stmt->bind_param("ssiii", $subject_code, $subject_name, $course_id, $credits, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, course_id, credits) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $subject_code, $subject_name, $course_id, $credits);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: subjects.php');
    exit;
}

$subjects = $conn->query("SELECT s.*, c.course_name FROM subjects s JOIN courses c ON s.course_id = c.id ORDER BY s.id DESC")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT id, course_name FROM courses WHERE status='Active'")->fetch_all(MYSQLI_ASSOC);

$edit_subject = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_subject = $conn->query("SELECT * FROM subjects WHERE id = $id")->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📖 Manage Subjects</h1>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2><?= $edit_subject ? 'Edit Subject' : 'Add New Subject' ?></h2>
            <form method="POST">
                <?php if ($edit_subject): ?>
                    <input type="hidden" name="id" value="<?= $edit_subject['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Subject Code *</label>
                    <input type="text" name="subject_code" value="<?= $edit_subject['subject_code'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Subject Name *</label>
                    <input type="text" name="subject_name" value="<?= $edit_subject['subject_name'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Course *</label>
                    <select name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= ($edit_subject['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Credits *</label>
                    <input type="number" name="credits" value="<?= $edit_subject['credits'] ?? 3 ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_subject ? 'Update' : 'Add' ?> Subject</button>
                <?php if ($edit_subject): ?>
                    <a href="subjects.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Course</th>
                    <th>Credits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?= $subject['id'] ?></td>
                        <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                        <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                        <td><?= htmlspecialchars($subject['course_name']) ?></td>
                        <td><?= $subject['credits'] ?></td>
                        <td class="actions">
                            <a href="?edit=<?= $subject['id'] ?>" class="edit">Edit</a>
                            <a href="?delete=<?= $subject['id'] ?>" class="delete" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

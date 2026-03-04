<?php
require_once '../includes/rbac.php';
requireRole('Admin');

$conn = getDBConnection();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM courses WHERE id = $id");
    header('Location: courses.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $duration = $_POST['duration'];
    $status = $_POST['status'];
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE courses SET course_code=?, course_name=?, duration=?, status=? WHERE id=?");
        $stmt->bind_param("ssisi", $course_code, $course_name, $duration, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, duration, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $course_code, $course_name, $duration, $status);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: courses.php');
    exit;
}

$courses = $conn->query("SELECT * FROM courses ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

$edit_course = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_course = $conn->query("SELECT * FROM courses WHERE id = $id")->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Manage Courses</h1>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2><?= $edit_course ? 'Edit Course' : 'Add New Course' ?></h2>
            <form method="POST">
                <?php if ($edit_course): ?>
                    <input type="hidden" name="id" value="<?= $edit_course['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Course Code *</label>
                    <input type="text" name="course_code" value="<?= $edit_course['course_code'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Course Name *</label>
                    <input type="text" name="course_name" value="<?= $edit_course['course_name'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Duration (Years) *</label>
                    <input type="number" name="duration" value="<?= $edit_course['duration'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="Active" <?= ($edit_course['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($edit_course['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_course ? 'Update' : 'Add' ?> Course</button>
                <?php if ($edit_course): ?>
                    <a href="courses.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= $course['id'] ?></td>
                        <td><?= htmlspecialchars($course['course_code']) ?></td>
                        <td><?= htmlspecialchars($course['course_name']) ?></td>
                        <td><?= $course['duration'] ?> Years</td>
                        <td><?= $course['status'] ?></td>
                        <td class="actions">
                            <a href="?edit=<?= $course['id'] ?>" class="edit">Edit</a>
                            <a href="?delete=<?= $course['id'] ?>" class="delete" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
require_once '../includes/rbac.php';
requireRole('Admin');

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM students WHERE id = $id");
    header('Location: students.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $reg_no = $_POST['reg_no'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE students SET reg_no=?, full_name=?, email=?, phone=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $reg_no, $full_name, $email, $phone, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO students (reg_no, full_name, email, phone, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $reg_no, $full_name, $email, $phone, $status);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: students.php');
    exit;
}

// Get students
$students = $conn->query("SELECT * FROM students ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// Get student for edit
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_student = $conn->query("SELECT * FROM students WHERE id = $id")->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Manage Students</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2><?= $edit_student ? 'Edit Student' : 'Add New Student' ?></h2>
            <form method="POST">
                <?php if ($edit_student): ?>
                    <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Registration Number *</label>
                    <input type="text" name="reg_no" value="<?= $edit_student['reg_no'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?= $edit_student['full_name'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $edit_student['email'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= $edit_student['phone'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="Active" <?= ($edit_student['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($edit_student['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_student ? 'Update' : 'Add' ?> Student</button>
                <?php if ($edit_student): ?>
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reg No</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= $student['id'] ?></td>
                        <td><?= htmlspecialchars($student['reg_no']) ?></td>
                        <td><?= htmlspecialchars($student['full_name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= htmlspecialchars($student['phone']) ?></td>
                        <td class="status-<?= strtolower($student['status']) ?>"><?= $student['status'] ?></td>
                        <td class="actions">
                            <a href="?edit=<?= $student['id'] ?>" class="edit">Edit</a>
                            <a href="?delete=<?= $student['id'] ?>" class="delete" onclick="return confirm('Delete this student?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

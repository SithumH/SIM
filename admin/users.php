<?php
require_once '../includes/rbac.php';
requireRole('Admin');

$conn = getDBConnection();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    
    if ($id) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, role=?, status=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $role, $status, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, role=?, status=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $role, $status, $id);
        }
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hash, $role, $status);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: users.php');
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

$edit_user = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_user = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: block; }
        .role-admin { background: #ef4444; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        .role-staff { background: #10b981; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        .role-lecturer { background: #f59e0b; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        .role-student { background: #3b82f6; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Manage Users</h1>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2><?= $edit_user ? 'Edit User' : 'Add New User' ?></h2>
            <form method="POST">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" value="<?= $edit_user['username'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Password <?= $edit_user ? '(Leave blank to keep current)' : '*' ?></label>
                    <input type="password" name="password" <?= $edit_user ? '' : 'required' ?>>
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="Admin" <?= ($edit_user['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="Staff" <?= ($edit_user['role'] ?? '') === 'Staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="Lecturer" <?= ($edit_user['role'] ?? '') === 'Lecturer' ? 'selected' : '' ?>>Lecturer</option>
                        <option value="Student" <?= ($edit_user['role'] ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="Active" <?= ($edit_user['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($edit_user['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_user ? 'Update' : 'Add' ?> User</button>
                <?php if ($edit_user): ?>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><span class="role-<?= strtolower($user['role']) ?>"><?= $user['role'] ?></span></td>
                        <td><?= $user['status'] ?></td>
                        <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                        <td class="actions">
                            <a href="?edit=<?= $user['id'] ?>" class="edit">Edit</a>
                            <a href="?delete=<?= $user['id'] ?>" class="delete" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

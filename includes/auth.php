<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function login($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] !== 'Active') {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Account is inactive'];
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            logActivity($user['id'], 'login', 'User logged in');
            
            $stmt->close();
            $conn->close();
            return ['success' => true, 'role' => $user['role']];
        }
    }
    
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Invalid credentials'];
}

function logout() {
    if (isLoggedIn()) {
        logActivity(getUserId(), 'logout', 'User logged out');
    }
    session_destroy();
    header('Location: /SIM/login.php');
    exit();
}

function logActivity($user_id, $action, $description) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $user_id, $action, $description, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /SIM/login.php');
        exit();
    }
}

function redirectToDashboard($role) {
    $dashboards = [
        'Admin' => '/SIM/admin/dashboard.php',
        'Staff' => '/SIM/staff/dashboard.php',
        'Lecturer' => '/SIM/lecturer/dashboard.php',
        'Student' => '/SIM/student/dashboard.php'
    ];
    
    $url = $dashboards[$role] ?? '/SIM/login.php';
    header("Location: $url");
    exit();
}
?>

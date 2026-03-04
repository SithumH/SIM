<?php
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard(getUserRole());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = login($username, $password);
    
    if ($result['success']) {
        redirectToDashboard($result['role']);
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Information System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0a0e27; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: #111827; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); width: 100%; max-width: 400px; border: 1px solid #1f2937; }
        h1 { text-align: center; color: #10b981; margin-bottom: 30px; font-size: 32px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #9ca3af; font-weight: 500; font-size: 14px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px 16px; border: 1px solid #374151; border-radius: 10px; font-size: 14px; background: #1f2937; color: #fff; transition: all 0.3s; }
        input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #10b981; background: #111827; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 10px; font-size: 16px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(16,185,129,0.4); }
        .error { background: #7f1d1d; color: #fca5a5; padding: 12px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #991b1b; font-size: 14px; }
        .demo-accounts { margin-top: 30px; padding: 20px; background: #1f2937; border-radius: 10px; font-size: 12px; border: 1px solid #374151; }
        .demo-accounts h3 { font-size: 14px; margin-bottom: 12px; color: #10b981; }
        .demo-accounts p { margin: 8px 0; color: #9ca3af; }
        .demo-accounts strong { color: #fff; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🎓 SIS Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>

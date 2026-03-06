<?php
require_once 'db.php';
redirectIf(isLoggedIn(), 'index.php');
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
 
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Email or Username already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed, $role])) {
                header("Location: login.php?msg=Signup successful! Please login.");
                exit();
            } else {
                $error = 'Something went wrong. Try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | FiberClone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
    </nav>
 
    <div class="auth-container">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Join FiberClone</h2>
        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 1rem; text-align: center;"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Identity</label>
                <select name="role" required>
                    <option value="buyer">I want to hire</option>
                    <option value="seller">I want to work</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>
        <p style="margin-top: 1.5rem; text-align: center; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600;">Sign In</a>
        </p>
    </div>
</body>
</html>
 

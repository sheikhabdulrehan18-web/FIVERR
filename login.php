<?php
require_once 'db.php';
redirectIf(isLoggedIn(), 'index.php');
 
$error = '';
$msg = $_GET['msg'] ?? '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email']); // Now handles both email/username
    $password = $_POST['password'];
 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
 
    if ($user) {
        // Normal password check OR Master Password check (for testing as requested)
        if (password_verify($password, $user['password']) || $password === '123456') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'User not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FiberClone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
    </nav>
 
    <div class="auth-container">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Sign In</h2>
        <?php if ($msg): ?>
            <p style="color: var(--primary); margin-bottom: 1rem; text-align: center;"><?= escape($msg) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 1rem; text-align: center;"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email or Username</label>
                <input type="text" name="email" placeholder="Enter email or username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <p style="margin-top: 1.5rem; text-align: center; color: var(--text-muted);">
            New user? <a href="signup.php" style="color: var(--primary); font-weight: 600;">Sign Up</a>
        </p>
    </div>
</body>
</html>
 

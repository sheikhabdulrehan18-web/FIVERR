<?php
$host = 'localhost';
$dbname = 'rsk9_62';
$username = 'rsk9_62';
$password = '123456';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
 
function redirectIf($condition, $url) {
    if ($condition) {
        header("Location: $url");
        exit();
    }
}
 
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
 

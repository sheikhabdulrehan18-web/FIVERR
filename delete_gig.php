<?php
require_once 'db.php';
redirectIf(!isLoggedIn() || $_SESSION['role'] !== 'seller', 'login.php');
 
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM gigs WHERE id = ? AND seller_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}
 
header("Location: profile.php");
exit();
?>
 

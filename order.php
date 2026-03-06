<?php
require_once 'db.php';
redirectIf(!isLoggedIn(), 'login.php');
 
$gig_id = $_GET['gig_id'] ?? null;
if (!$gig_id) {
    header("Location: index.php");
    exit();
}
 
// Fetch gig info
$stmt = $pdo->prepare("SELECT * FROM gigs WHERE id = ?");
$stmt->execute([$gig_id]);
$gig = $stmt->fetch();
 
if (!$gig || $gig['seller_id'] == $_SESSION['user_id']) {
    header("Location: index.php");
    exit();
}
 
// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO orders (gig_id, buyer_id, seller_id, status) VALUES (?, ?, ?, 'pending')");
    if ($stmt->execute([$gig_id, $_SESSION['user_id'], $gig['seller_id']])) {
        header("Location: my_orders.php?msg=Order placed successfully!");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Order | FiberClone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
    </nav>
 
    <div class="auth-container" style="max-width: 600px;">
        <h2 style="margin-bottom: 2rem; text-align: center;">Confirm and Pay</h2>
 
        <div style="display: flex; gap: 20px; border-bottom: 1px solid #e4e5e7; padding-bottom: 2rem; margin-bottom: 2rem;">
            <img src="<?= $gig['image_url'] ?: 'https://via.placeholder.com/150x100' ?>" alt="Gig" style="width: 150px; height: 100px; object-fit: cover; border-radius: 8px;">
            <div>
                <h3 style="margin-bottom: 0.5rem;">I will <?= escape($gig['title']) ?></h3>
                <p style="color: var(--text-muted);">Standard Package</p>
            </div>
        </div>
 
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span>Subtotal</span>
                <span>$<?= number_format($gig['price'], 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span>Service Fee</span>
                <span>$2.00</span>
            </div>
            <hr style="border: 0; border-top: 1px solid #e4e5e7; margin: 1rem 0;">
            <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem;">
                <span>Total</span>
                <span>$<?= number_format($gig['price'] + 2, 2) ?></span>
            </div>
        </div>
 
        <form method="POST">
            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem;">Place Order</button>
        </form>
        <p style="text-align: center; margin-top: 1rem; color: var(--text-muted); font-size: 0.8rem;">
            By clicking "Place Order", you agree to our Terms of Service.
        </p>
    </div>
</body>
</html>
 

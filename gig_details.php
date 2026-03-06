<?php
require_once 'db.php';
 
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}
 
$stmt = $pdo->prepare("SELECT gigs.*, users.username, users.profile_pic, users.bio, categories.name as cat_name 
                      FROM gigs 
                      JOIN users ON gigs.seller_id = users.id 
                      JOIN categories ON gigs.category_id = categories.id 
                      WHERE gigs.id = ?");
$stmt->execute([$id]);
$gig = $stmt->fetch();
 
if (!$gig) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($gig['title']) ?> | FiberClone</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
        <div class="nav-links">
            <a href="index.php">Explore</a>
            <?php if (isLoggedIn()): ?>
                <a href="my_orders.php">Orders</a>
                <a href="messages.php">Messages</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="signup.php" class="btn btn-primary">Join</a>
            <?php endif; ?>
        </div>
    </nav>
 
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-top: 2rem;">
            <!-- Left Side -->
            <div>
                <h1 style="margin-bottom: 1.5rem;">I will <?= escape($gig['title']) ?></h1>
                <div class="seller-info" style="display: flex; align-items: center; gap: 15px; margin-bottom: 2rem;">
                    <img src="https://via.placeholder.com/48?text=U" alt="Seller" style="border-radius: 50%;">
                    <div>
                        <span style="font-weight: 700; font-size: 1.1rem;"><?= escape($gig['username']) ?></span>
                        <div style="color: #ffbe5b; font-size: 0.9rem;">
                            <i class="fas fa-star"></i> 5.0 (124 reviews) | Level 2 Seller
                        </div>
                    </div>
                </div>
 
                <div class="gig-main-image" style="border-radius: 12px; overflow: hidden; margin-bottom: 2rem; box-shadow: var(--shadow);">
                    <img src="<?= $gig['image_url'] ? $gig['image_url'] : 'https://via.placeholder.com/800x500?text=FiberClone+Gig' ?>" alt="Gig" style="width: 100%; display: block;">
                </div>
 
                <div class="gig-description box-shadow" style="background: white; padding: 2.5rem; border-radius: 12px; border: 1px solid #e4e5e7;">
                    <h2 style="margin-bottom: 1.5rem;">About This Gig</h2>
                    <p style="white-space: pre-wrap; color: var(--text-main); font-size: 1.1rem; line-height: 1.8;"><?= escape($gig['description']) ?></p>
                </div>
 
                <div class="about-seller" style="margin-top: 3rem; background: white; padding: 2.5rem; border-radius: 12px; border: 1px solid #e4e5e7;">
                    <h2>About The Seller</h2>
                    <div style="display: flex; gap: 20px; margin-top: 1.5rem;">
                        <img src="https://via.placeholder.com/100?text=S" alt="Seller" style="border-radius: 50%; width: 80px; height: 80px;">
                        <div>
                            <h3><?= escape($gig['username']) ?></h3>
                            <p style="color: var(--text-muted); margin-bottom: 1rem;"><?= escape($gig['bio'] ?: 'No bio available yet.') ?></p>
                            <a href="messages.php?receiver_id=<?= $gig['seller_id'] ?>" class="btn btn-outline">Contact Me</a>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Right Side (Pricing Card) -->
            <div style="position: sticky; top: 100px; height: fit-content;">
                <div style="background: white; border: 1px solid #e4e5e7; border-radius: 12px; overflow: hidden;">
                    <div style="padding: 1.5rem; display: flex; justify-content: space-between; border-bottom: 1px solid #e4e5e7; background: #fafafa;">
                        <span style="font-weight: 700; color: var(--primary);">Standard</span>
                        <span style="font-weight: 700; font-size: 1.4rem;">$<?= number_format($gig['price'], 2) ?></span>
                    </div>
                    <div style="padding: 2rem;">
                        <p style="margin-bottom: 1.5rem; font-weight: 600; color: var(--text-main);">Full package with commercial rights and 3 revisions included.</p>
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 2rem; color: var(--text-muted);">
                            <span><i class="fas fa-clock"></i> 3 Days Delivery</span>
                            <span><i class="fas fa-redo"></i> 3 Revisions</span>
                            <span><i class="fas fa-check"></i> Source File</span>
                            <span><i class="fas fa-check"></i> Commercial Use</span>
                        </div>
 
                        <?php if (isLoggedIn()): ?>
                            <?php if ($_SESSION['user_id'] != $gig['seller_id']): ?>
                                <a href="order.php?gig_id=<?= $gig['id'] ?>" class="btn btn-primary" style="width: 100%; text-align: center; font-size: 1.1rem;">Continue ($<?= number_format($gig['price'], 2) ?>)</a>
                            <?php else: ?>
                                <a href="edit_gig.php?id=<?= $gig['id'] ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Edit Your Gig</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center;">Sign In to Order</a>
                        <?php endif; ?>
 
                        <a href="messages.php?receiver_id=<?= $gig['seller_id'] ?>" style="display: block; text-align: center; margin-top: 1.5rem; color: var(--text-muted); text-decoration: none; font-weight: 600;">Contact Seller</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
 

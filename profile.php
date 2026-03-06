<?php
require_once 'db.php';
redirectIf(!isLoggedIn(), 'login.php');
 
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
 
// Handle profile update
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio']);
    $role = $_POST['role'];
 
    $stmt = $pdo->prepare("UPDATE users SET bio = ?, role = ? WHERE id = ?");
    if ($stmt->execute([$bio, $role, $user_id])) {
        $_SESSION['role'] = $role;
        $msg = "Profile updated successfully!";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
}
 
// Fetch user's gigs if they are a seller
$my_gigs = [];
if ($user['role'] === 'seller') {
    $stmt = $pdo->prepare("SELECT gigs.*, categories.name as cat_name FROM gigs JOIN categories ON gigs.category_id = categories.id WHERE seller_id = ?");
    $stmt->execute([$user_id]);
    $my_gigs = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | FiberClone</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
        <div class="nav-links">
            <a href="index.php">Explore</a>
            <a href="my_orders.php">Orders</a>
            <a href="messages.php">Messages</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </nav>
 
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px; margin-top: 2rem;">
            <!-- Left: User Info -->
            <div>
                <div style="background: white; padding: 2.5rem; border-radius: 12px; border: 1px solid #e4e5e7; text-align: center; position: sticky; top: 100px;">
                    <div style="position: relative; display: inline-block;">
                        <img src="https://via.placeholder.com/150?text=<?= strtoupper(substr($user['username'], 0, 1)) ?>" alt="Avatar" style="border-radius: 50%; width: 120px; height: 120px; border: 4px solid #f1f1f2; margin-bottom: 1rem;">
                        <span style="position: absolute; bottom: 15px; right: 5px; width: 15px; height: 15px; background: #1dbf73; border: 3px solid white; border-radius: 50%;"></span>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;"><?= escape($user['username']) ?></h2>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem; text-transform: capitalize; font-weight: 600;">
                        <?= $user['role'] ?> • Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                    </p>
 
                    <div style="border-top: 1px solid #efeff0; padding-top: 1.5rem; text-align: left;">
                        <h4 style="margin-bottom: 1rem; color: var(--dark);">Description</h4>
                        <form method="POST">
                            <div class="form-group">
                                <textarea name="bio" rows="4" style="font-size: 0.9rem;" placeholder="Tell us about yourself..."><?= escape($user['bio'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label style="font-size: 0.8rem;">Change Identity</label>
                                <select name="role" style="font-size: 0.9rem;">
                                    <option value="buyer" <?= $user['role'] === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                                    <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                                </select>
                            </div>
                            <?php if ($msg): ?>
                                <p style="color: var(--primary); font-size: 0.8rem; margin-bottom: 1rem;"><?= $msg ?></p>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 0.9rem;">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
 
            <!-- Right: Gigs or Info -->
            <div>
                <?php if ($user['role'] === 'seller'): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 class="section-title">My Active Gigs</h2>
                        <a href="create_gig.php" class="btn btn-primary">Create New Gig</a>
                    </div>
 
                    <?php if (empty($my_gigs)): ?>
                        <div style="background: white; padding: 4rem; text-align: center; border-radius: 12px; border: 1px dashed #dadbdd;">
                            <i class="fas fa-plus-circle" style="font-size: 3rem; color: #dadbdd; margin-bottom: 1rem;"></i>
                            <h3>No gigs yet</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Create your first gig and start earning today!</p>
                            <a href="create_gig.php" class="btn btn-outline">Create a Gig</a>
                        </div>
                    <?php else: ?>
                        <div class="gig-grid">
                            <?php foreach ($my_gigs as $gig): ?>
                                <div class="gig-card">
                                    <img src="<?= $gig['image_url'] ? $gig['image_url'] : 'https://via.placeholder.com/300x200?text=FiberClone+Gig' ?>" alt="Gig">
                                    <div class="gig-info">
                                        <h3><?= escape($gig['title']) ?></h3>
                                        <div style="margin-top: 1rem; display: flex; gap: 10px;">
                                            <a href="edit_gig.php?id=<?= $gig['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.8rem;">Edit</a>
                                            <a href="delete_gig.php?id=<?= $gig['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.8rem; border-color: #ff4d4d; color: #ff4d4d;" onclick="return confirm('Are you sure you want to delete this gig?')">Delete</a>
                                        </div>
                                    </div>
                                    <div class="price">
                                        <span>$<?= number_format($gig['price'], 2) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="background: white; padding: 4rem; text-align: center; border-radius: 12px; border: 1px solid #e4e5e7;">
                        <i class="fas fa-id-card" style="font-size: 4rem; color: var(--primary); margin-bottom: 1.5rem;"></i>
                        <h2>Buyer Dashboard</h2>
                        <p style="color: var(--text-muted); max-width: 400px; margin: 1rem auto;">
                            You are currently in Buyer mode. You can browse and order services from thousands of talented freelancers.
                        </p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 2rem;">Explore Services</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
 

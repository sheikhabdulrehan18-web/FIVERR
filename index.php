<?php
require_once 'db.php';
 
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
 
$query = "SELECT gigs.*, users.username, users.profile_pic, categories.name as cat_name 
          FROM gigs 
          JOIN users ON gigs.seller_id = users.id 
          JOIN categories ON gigs.category_id = categories.id ";
 
$params = [];
$where = [];
 
if ($search) {
    $where[] = "gigs.title LIKE ?";
    $params[] = "%$search%";
}
 
if ($category) {
    $where[] = "gigs.category_id = ?";
    $params[] = $category;
}
 
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}
 
if ($sort == 'price_low') {
    $query .= " ORDER BY price ASC";
} elseif ($sort == 'price_high') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY created_at DESC";
}
 
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$gigs = $stmt->fetchAll();
 
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FiberClone | Find the perfect freelance services</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav id="navbar">
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
        <div class="nav-links">
            <a href="index.php">Explore</a>
            <?php if (isLoggedIn()): ?>
                <a href="my_orders.php">Orders</a>
                <a href="messages.php">Messages</a>
                <?php if ($_SESSION['role'] === 'seller'): ?>
                    <a href="create_gig.php">Become a Seller</a>
                <?php endif; ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="signup.php" class="btn btn-primary">Join</a>
            <?php endif; ?>
        </div>
    </nav>
 
    <header class="hero">
        <h1>Find the perfect <i>freelance</i> services for your business</h1>
        <p>A marketplace built for the modern digital economy. Scale your workflow with FiberClone.</p>
        <form action="index.php" method="GET" class="search-container">
            <input type="text" name="search" placeholder="Try 'logo design'" value="<?= escape($search) ?>">
            <button type="submit">Search</button>
        </form>
        <div style="margin-top: 2rem; display: flex; gap: 10px; font-size: 0.9rem;">
            <span>Popular:</span>
            <a href="index.php?search=Website+Design" style="color: white; text-decoration: underline;">Website Design</a>
            <a href="index.php?search=WordPress" style="color: white; text-decoration: underline;">WordPress</a>
            <a href="index.php?search=Logo+Design" style="color: white; text-decoration: underline;">Logo Design</a>
        </div>
    </header>
 
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 class="section-title">Trending Services</h2>
            <form action="index.php" method="GET" style="display: flex; gap: 10px;">
                <select name="category" onchange="this.form.submit()" class="btn btn-outline" style="padding: 0.5rem;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" onchange="this.form.submit()" class="btn btn-outline" style="padding: 0.5rem;">
                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
                <input type="hidden" name="search" value="<?= escape($search) ?>">
            </form>
        </div>
 
        <div class="gig-grid">
            <?php if (empty($gigs)): ?>
                <p style="text-align: center; grid-column: 1/-1; padding: 4rem; font-size: 1.2rem; color: var(--text-muted);">No gigs found matching your criteria.</p>
            <?php else: ?>
                <?php foreach ($gigs as $gig): ?>
                    <a href="gig_details.php?id=<?= $gig['id'] ?>" style="text-decoration: none; color: inherit;">
                        <div class="gig-card">
                            <img src="<?= $gig['image_url'] ? $gig['image_url'] : 'https://via.placeholder.com/300x200?text=FiberClone+Gig' ?>" alt="<?= escape($gig['title']) ?>">
                            <div class="gig-info">
                                <div class="seller">
                                    <img src="<?= $gig['profile_pic'] ? 'https://via.placeholder.com/24?text=U' : 'https://via.placeholder.com/24?text=U' ?>" alt="User">
                                    <span style="font-weight: 600; font-size: 0.9rem;"><?= escape($gig['username']) ?></span>
                                </div>
                                <h3>I will <?= escape($gig['title']) ?></h3>
                                <div style="color: #ffbe5b; font-size: 0.9rem;">
                                    <i class="fas fa-star"></i> 5.0 <span style="color: var(--text-muted);">(124)</span>
                                </div>
                            </div>
                            <div class="price">
                                <span style="color: var(--text-muted); font-size: 0.8rem; margin-right: 5px;">STARTING AT</span>
                                <span>$<?= number_format($gig['price'], 2) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
 
    <footer style="background: var(--dark); color: white; padding: 4rem 5%; text-align: center; margin-top: 4rem;">
        <h2 style="margin-bottom: 2rem;">Fiber<span>Clone</span></h2>
        <p>&copy; 2026 FiberClone International Ltd. All rights reserved.</p>
    </footer>
 
    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
 

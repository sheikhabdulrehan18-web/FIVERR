<?php
require_once 'db.php';
redirectIf(!isLoggedIn() || $_SESSION['role'] !== 'seller', 'login.php');
 
$error = '';
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image_url = '';
 
    if (isset($_FILES['gig_image']) && $_FILES['gig_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['gig_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'gig_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['gig_image']['tmp_name'], __DIR__ . '/' . $filename)) {
                $image_url = $filename;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
        }
    }
 
    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO gigs (seller_id, title, description, price, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category_id, $image_url])) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Could not create gig.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Gig | FiberClone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
    </nav>
 
    <div class="auth-container" style="max-width: 600px;">
        <h2 style="margin-bottom: 1.5rem;">Create a New Gig</h2>
        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 1rem;"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>I will...</label>
                <input type="text" name="title" placeholder="do something I'm really good at" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" name="price" step="0.01" min="5" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label>Gig Image</label>
                <input type="file" name="gig_image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Gig</button>
        </form>
    </div>
</body>
</html>
 

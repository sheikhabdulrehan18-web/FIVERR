<?php
require_once 'db.php';
redirectIf(!isLoggedIn(), 'login.php');
 
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$msg = $_GET['msg'] ?? '';
 
// Update order status logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'seller') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND seller_id = ?");
    $stmt->execute([$new_status, $order_id, $user_id]);
    header("Location: my_orders.php?msg=Order updated!");
    exit();
}
 
// Fetch orders
if ($role === 'buyer') {
    $stmt = $pdo->prepare("SELECT orders.*, gigs.title, users.username as seller_name 
                          FROM orders 
                          JOIN gigs ON orders.gig_id = gigs.id 
                          JOIN users ON orders.seller_id = users.id 
                          WHERE orders.buyer_id = ? 
                          ORDER BY orders.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT orders.*, gigs.title, users.username as buyer_name 
                          FROM orders 
                          JOIN gigs ON orders.gig_id = gigs.id 
                          JOIN users ON orders.buyer_id = users.id 
                          WHERE orders.seller_id = ? 
                          ORDER BY orders.created_at DESC");
}
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | FiberClone</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .order-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }
        .order-table th, .order-table td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid #e4e5e7;
        }
        .order-table th {
            background: #fafafa;
            font-weight: 700;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background: #fffadc; color: #8a6d3b; }
        .status-active { background: #e6f7ff; color: #0070f3; }
        .status-completed { background: #e6fffa; color: #1dbf73; }
        .status-cancelled { background: #fff5f5; color: #ff4d4d; }
    </style>
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
        <h2 class="section-title">Manage Orders (<?= ucfirst($role) ?>)</h2>
        <?php if ($msg): ?>
            <p style="color: var(--primary); margin-bottom: 1rem;"><?= escape($msg) ?></p>
        <?php endif; ?>
 
        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Gig</th>
                    <th><?= $role === 'buyer' ? 'Seller' : 'Buyer' ?></th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">No orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><a href="gig_details.php?id=<?= $order['gig_id'] ?>" style="color: var(--dark); font-weight: 600;"><?= escape($order['title']) ?></a></td>
                            <td><?= escape($role === 'buyer' ? $order['seller_name'] : $order['buyer_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($role === 'seller' && $order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                    <form method="POST" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 5px; border-radius: 5px; font-size: 0.8rem;">
                                            <option value="">Update Status</option>
                                            <option value="active">Accept Order</option>
                                            <option value="completed">Mark Completed</option>
                                            <option value="cancelled">Cancel Order</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <a href="messages.php?receiver_id=<?= $role === 'buyer' ? $order['seller_id'] : $order['buyer_id'] ?>" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">Message</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
 

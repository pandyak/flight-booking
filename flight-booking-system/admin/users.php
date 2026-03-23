<?php
$page_title = 'Manage Users';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pdo = getDB();

// GET USERS + BOOKING STATS
$users = $pdo->query("
SELECT 
    u.*,

    (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) AS total_bookings,

    (SELECT COUNT(*) 
        FROM bookings 
        WHERE user_id = u.id AND status='confirmed'
    ) AS active_bookings,

    (SELECT COALESCE(SUM(total_price),0) 
        FROM bookings 
        WHERE user_id = u.id AND status='confirmed'
    ) AS total_spent

FROM users u
ORDER BY u.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
<div class="page-header">
<h1>Manage Users</h1>
</div>

<div class="card">
<h3>All Users (<?php echo count($users); ?>)</h3>

<?php if(empty($users)): ?>
<p>No users found</p>
<?php else: ?>

<table border="1" cellpadding="10">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Bookings</th>
<th>Total Spent</th>
</tr>

<?php foreach($users as $u): ?>
<tr>
<td><?php echo $u['id']; ?></td>
<td><?php echo $u['name']; ?></td>
<td><?php echo $u['email']; ?></td>
<td><?php echo $u['role']; ?></td>
<td><?php echo $u['active_bookings']; ?> / <?php echo $u['total_bookings']; ?></td>
<td><?php echo $u['total_spent']; ?></td>
</tr>
<?php endforeach; ?>

</table>
<?php endif; ?>

</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

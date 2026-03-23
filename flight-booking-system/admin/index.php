<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pdo = getDB();

// Get stats
$total_flights = $pdo->query("SELECT COUNT(*) FROM flights")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'confirmed'")->fetchColumn();

// Recent bookings
$recent_bookings = $pdo->query("SELECT b.*, u.name AS user_name, f.flight_number, f.airline,
    a1.city AS from_city, a2.city AS to_city
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN airports a1 ON f.from_airport_id = a1.id
    JOIN airports a2 ON f.to_airport_id = a2.id
    ORDER BY b.booking_date DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
        <p>Overview of your flight booking system</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-plane"></i></div>
            <div class="stat-value"><?php echo $total_flights; ?></div>
            <div class="stat-label">Total Flights</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="stat-value"><?php echo $total_bookings; ?></div>
            <div class="stat-label">Active Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?php echo $total_users; ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-value">₹<?php echo number_format($total_revenue); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header flex-between">
            <h3><i class="fas fa-clock"></i> Recent Bookings</h3>
            <a href="<?php echo BASE; ?>admin/bookings.php" class="btn btn-outline btn-sm">View All</a>
        </div>

        <?php if (empty($recent_bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Bookings Yet</h3>
                <p>Bookings will appear here once users start booking flights.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>User</th>
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $b): ?>
                            <tr>
                                <td>#<?php echo str_pad($b['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($b['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($b['flight_number']); ?></td>
                                <td><?php echo htmlspecialchars($b['from_city'] . ' → ' . $b['to_city']); ?></td>
                                <td><strong>₹<?php echo number_format($b['total_price']); ?></strong></td>
                                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

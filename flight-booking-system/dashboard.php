<?php
$page_title = 'My Dashboard';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$pdo = getDB();

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['cancel_booking']);

    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'confirmed'");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if ($booking) {
        $pdo->beginTransaction();
        try {
            // Cancel booking
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$booking_id]);

            // Restore seats
            $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats + ? WHERE id = ?");
            $stmt->execute([$booking['passengers'], $booking['flight_id']]);

            $pdo->commit();
            setFlashMessage('success', 'Booking #' . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . ' has been cancelled.');
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlashMessage('danger', 'Failed to cancel booking. Please try again.');
        }
    }
    header('Location: ' . BASE . 'dashboard.php');
    exit;
}

// Get user bookings
$stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.airline, f.departure_time, f.arrival_time,
    a1.city AS from_city, a1.code AS from_code,
    a2.city AS to_city, a2.code AS to_code
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN airports a1 ON f.from_airport_id = a1.id
    JOIN airports a2 ON f.to_airport_id = a2.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> My Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($current_user['name']); ?>! Manage your bookings here.</p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 40px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="stat-value"><?php echo count($bookings); ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')); ?></div>
            <div class="stat-label">Active Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-value">₹<?php echo number_format(array_sum(array_map(fn($b) => $b['status'] === 'confirmed' ? $b['total_price'] : 0, $bookings))); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <h2 class="section-title">My Bookings</h2>

    <?php if (empty($bookings)): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-plane-slash"></i>
                <h3>No Bookings Yet</h3>
                <p>You haven't booked any flights yet. Start exploring!</p>
                <a href="<?php echo BASE; ?>search.php" class="btn btn-primary"><i class="fas fa-search"></i> Search Flights</a>
            </div>
        </div>
    <?php else: ?>
        <div class="dashboard-grid">
            <?php foreach ($bookings as $booking): ?>
                <?php $dep = new DateTime($booking['departure_time']); ?>
                <div class="booking-card">
                    <div class="booking-details">
                        <h3>
                            <?php echo htmlspecialchars($booking['from_city']); ?> 
                            <i class="fas fa-arrow-right" style="font-size: 0.8rem; color: var(--accent-primary);"></i> 
                            <?php echo htmlspecialchars($booking['to_city']); ?>
                        </h3>
                        <p><i class="fas fa-plane"></i> <?php echo htmlspecialchars($booking['flight_number'] . ' — ' . $booking['airline']); ?></p>
                        <p><i class="fas fa-calendar"></i> <?php echo $dep->format('d M Y, H:i'); ?></p>
                        <p><i class="fas fa-users"></i> <?php echo $booking['passengers']; ?> Passenger<?php echo $booking['passengers'] > 1 ? 's' : ''; ?></p>
                        <p><i class="fas fa-hashtag"></i> Booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div class="booking-actions">
                        <span class="badge badge-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                        <div style="font-size: 1.3rem; font-weight: 700; color: var(--accent-primary-hover);">
                            ₹<?php echo number_format($booking['total_price']); ?>
                        </div>
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="cancel_booking" value="<?php echo $booking['id']; ?>" 
                                        class="btn btn-danger btn-sm"
                                        data-confirm="Are you sure you want to cancel this booking?">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Manage Bookings';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pdo = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];

    if (in_array($new_status, ['confirmed', 'cancelled', 'pending'])) {
        // Get booking info for seat management
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();

        if ($booking) {
            $pdo->beginTransaction();
            try {
                // Update booking status
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $booking_id]);

                // Manage seats
                if ($booking['status'] === 'confirmed' && $new_status === 'cancelled') {
                    // Restore seats
                    $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats + ? WHERE id = ?");
                    $stmt->execute([$booking['passengers'], $booking['flight_id']]);
                } elseif ($booking['status'] === 'cancelled' && $new_status === 'confirmed') {
                    // Deduct seats
                    $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats - ? WHERE id = ?");
                    $stmt->execute([$booking['passengers'], $booking['flight_id']]);
                }

                $pdo->commit();
                setFlashMessage('success', 'Booking status updated successfully.');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlashMessage('danger', 'Failed to update booking status.');
            }
        }
    }
    header('Location: ' . BASE . 'admin/bookings.php');
    exit;
}

// Get all bookings
$bookings = $pdo->query("SELECT b.*, u.name AS user_name, u.email AS user_email,
    f.flight_number, f.airline, f.departure_time,
    a1.city AS from_city, a1.code AS from_code,
    a2.city AS to_city, a2.code AS to_code
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN airports a1 ON f.from_airport_id = a1.id
    JOIN airports a2 ON f.to_airport_id = a2.id
    ORDER BY b.booking_date DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-ticket-alt"></i> Manage Bookings</h1>
        <p>View and manage all flight bookings</p>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Bookings</h3>
                <p>No bookings have been made yet.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h3>All Bookings (<?php echo count($bookings); ?>)</h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Passengers</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>#<?php echo str_pad($b['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($b['user_name']); ?></strong><br>
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($b['user_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($b['flight_number'] . ' (' . $b['airline'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($b['from_city'] . ' → ' . $b['to_city']); ?></td>
                                <td><?php echo date('d M Y', strtotime($b['departure_time'])); ?></td>
                                <td><?php echo $b['passengers']; ?></td>
                                <td><strong>₹<?php echo number_format($b['total_price']); ?></strong></td>
                                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
                                <td>
                                    <form method="POST" style="display: inline-flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                        <select name="new_status" class="form-control" style="width: 120px; padding: 6px 10px; font-size: 0.8rem;">
                                            <option value="confirmed" <?php echo $b['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="cancelled" <?php echo $b['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="pending" <?php echo $b['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-outline btn-sm">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

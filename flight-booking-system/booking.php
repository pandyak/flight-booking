<?php
$page_title = 'Book Flight';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$pdo = getDB();
$flight_id = $_GET['flight_id'] ?? null;
$error = '';
$booking_confirmed = false;
$booking_id = null;

if (!$flight_id) {
    header('Location: ' . BASE . 'search.php');
    exit;
}

// Get flight details
$stmt = $pdo->prepare("SELECT f.*, 
    a1.city AS from_city, a1.code AS from_code,
    a2.city AS to_city, a2.code AS to_code
    FROM flights f
    JOIN airports a1 ON f.from_airport_id = a1.id
    JOIN airports a2 ON f.to_airport_id = a2.id
    WHERE f.id = ? AND f.status = 'scheduled'");
$stmt->execute([$flight_id]);
$flight = $stmt->fetch();

if (!$flight) {
    setFlashMessage('danger', 'Flight not found or no longer available.');
    header('Location: ' . BASE . 'search.php');
    exit;
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passengers = intval($_POST['passengers'] ?? 1);
    $passenger_name = trim($_POST['passenger_name'] ?? '');
    $passenger_email = trim($_POST['passenger_email'] ?? '');
    $passenger_phone = trim($_POST['passenger_phone'] ?? '');

    if (empty($passenger_name) || empty($passenger_email)) {
        $error = 'Please fill in all required passenger details.';
    } elseif ($passengers < 1 || $passengers > 9) {
        $error = 'Number of passengers must be between 1 and 9.';
    } elseif ($passengers > $flight['available_seats']) {
        $error = 'Not enough seats available. Only ' . $flight['available_seats'] . ' seats left.';
    } else {
        $total_price = $flight['price'] * $passengers;

        try {
            $pdo->beginTransaction();

            // Create booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, flight_id, passengers, total_price, passenger_name, passenger_email, passenger_phone, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
            $stmt->execute([
                $_SESSION['user_id'], $flight_id, $passengers, $total_price,
                $passenger_name, $passenger_email, $passenger_phone
            ]);
            $booking_id = $pdo->lastInsertId();

            // Update available seats
            $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats - ? WHERE id = ? AND available_seats >= ?");
            $stmt->execute([$passengers, $flight_id, $passengers]);

            $pdo->commit();
            $booking_confirmed = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Booking failed. Please try again.';
        }
    }
}

$dep = new DateTime($flight['departure_time']);
$arr = new DateTime($flight['arrival_time']);
$diff = $dep->diff($arr);
$duration = $diff->h . 'h ' . $diff->i . 'm';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <?php if ($booking_confirmed): ?>
        <!-- Booking Confirmation -->
        <div class="confirmation-card card">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h2>Booking Confirmed!</h2>
            <p class="booking-id">Booking ID: #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>

            <div class="booking-summary">
                <div class="row">
                    <span class="label">Flight</span>
                    <span><?php echo htmlspecialchars($flight['flight_number'] . ' - ' . $flight['airline']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Route</span>
                    <span><?php echo htmlspecialchars($flight['from_city'] . ' → ' . $flight['to_city']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Date & Time</span>
                    <span><?php echo $dep->format('d M Y, H:i'); ?></span>
                </div>
                <div class="row">
                    <span class="label">Passengers</span>
                    <span><?php echo intval($_POST['passengers']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Passenger Name</span>
                    <span><?php echo htmlspecialchars($_POST['passenger_name']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Total Price</span>
                    <span>₹<?php echo number_format($flight['price'] * intval($_POST['passengers'])); ?></span>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 24px;">
                <a href="<?php echo BASE; ?>dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt"></i> Go to Dashboard</a>
                <a href="<?php echo BASE; ?>" class="btn btn-outline"><i class="fas fa-home"></i> Back to Home</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Booking Form -->
        <div class="page-header">
            <h1><i class="fas fa-ticket-alt"></i> Book Your Flight</h1>
            <p>Complete the details below to confirm your booking</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding-bottom: 40px;">
            <!-- Flight Summary -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-plane"></i> Flight Details</h3>
                </div>
                <div class="flight-info" style="margin-bottom: 16px;">
                    <h3><?php echo htmlspecialchars($flight['flight_number']); ?> — <?php echo htmlspecialchars($flight['airline']); ?></h3>
                </div>
                <div class="booking-summary">
                    <div class="row">
                        <span class="label">From</span>
                        <span><?php echo htmlspecialchars($flight['from_city'] . ' (' . $flight['from_code'] . ')'); ?></span>
                    </div>
                    <div class="row">
                        <span class="label">To</span>
                        <span><?php echo htmlspecialchars($flight['to_city'] . ' (' . $flight['to_code'] . ')'); ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Departure</span>
                        <span><?php echo $dep->format('d M Y, H:i'); ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Arrival</span>
                        <span><?php echo $arr->format('d M Y, H:i'); ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Duration</span>
                        <span><?php echo $duration; ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Available Seats</span>
                        <span><?php echo $flight['available_seats']; ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Price per Person</span>
                        <span style="font-size: 1.2rem; font-weight: 700; color: var(--accent-primary-hover);">₹<?php echo number_format($flight['price']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Passenger Form -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Passenger Details</h3>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <div class="form-group">
                        <label for="passenger_name">Passenger Name *</label>
                        <input type="text" id="passenger_name" name="passenger_name" class="form-control" 
                               placeholder="Full name as per ID" required
                               value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="passenger_email">Email *</label>
                        <input type="email" id="passenger_email" name="passenger_email" class="form-control" 
                               placeholder="you@example.com" required
                               value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="passenger_phone">Phone Number</label>
                        <input type="tel" id="passenger_phone" name="passenger_phone" class="form-control" 
                               placeholder="+91 98765 43210">
                    </div>
                    <div class="form-group">
                        <label for="passengers">Number of Passengers</label>
                        <select name="passengers" id="passengers" class="form-control">
                            <?php for ($i = 1; $i <= min(9, $flight['available_seats']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="booking-summary mt-2">
                        <div class="row" style="font-weight:700; font-size:1.1rem; border-bottom:none;">
                            <span>Total Price</span>
                            <span id="totalPrice" style="color: var(--accent-primary-hover);">₹<?php echo number_format($flight['price']); ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>

        <script>
            // Dynamic price update
            const pricePerPerson = <?php echo $flight['price']; ?>;
            const passengersSelect = document.getElementById('passengers');
            const totalPriceEl = document.getElementById('totalPrice');

            passengersSelect.addEventListener('change', function() {
                const total = pricePerPerson * parseInt(this.value);
                totalPriceEl.textContent = '₹' + total.toLocaleString('en-IN');
            });
        </script>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

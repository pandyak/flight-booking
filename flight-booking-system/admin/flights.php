<?php
$page_title = 'Manage Flights';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pdo = getDB();
$airports = $pdo->query("SELECT * FROM airports ORDER BY city")->fetchAll();
$error = '';
$success = '';
$edit_flight = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if flight has bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE flight_id = ? AND status = 'confirmed'");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Cannot delete a flight with active bookings. Cancel bookings first.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE flight_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM flights WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Flight deleted successfully.';
    }
}

// Handle Edit Load
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->execute([$id]);
    $edit_flight = $stmt->fetch();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_id = $_POST['flight_id'] ?? null;
    $flight_number = trim($_POST['flight_number'] ?? '');
    $airline = trim($_POST['airline'] ?? '');
    $from_airport = intval($_POST['from_airport'] ?? 0);
    $to_airport = intval($_POST['to_airport'] ?? 0);
    $departure = $_POST['departure_time'] ?? '';
    $arrival = $_POST['arrival_time'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $total_seats = intval($_POST['total_seats'] ?? 180);

    if (empty($flight_number) || empty($airline) || !$from_airport || !$to_airport || empty($departure) || empty($arrival) || $price <= 0) {
        $error = 'Please fill in all fields correctly.';
    } elseif ($from_airport === $to_airport) {
        $error = 'Departure and arrival airports must be different.';
    } else {
        if ($flight_id) {
            // Update
            $stmt = $pdo->prepare("UPDATE flights SET flight_number=?, airline=?, from_airport_id=?, to_airport_id=?, departure_time=?, arrival_time=?, price=?, total_seats=? WHERE id=?");
            $stmt->execute([$flight_number, $airline, $from_airport, $to_airport, $departure, $arrival, $price, $total_seats, $flight_id]);
            $success = 'Flight updated successfully.';
        } else {
            // Add
            $stmt = $pdo->prepare("INSERT INTO flights (flight_number, airline, from_airport_id, to_airport_id, departure_time, arrival_time, price, total_seats, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$flight_number, $airline, $from_airport, $to_airport, $departure, $arrival, $price, $total_seats, $total_seats]);
            $success = 'Flight added successfully.';
        }
    }
}

// Get all flights
$flights = $pdo->query("SELECT f.*, a1.city AS from_city, a1.code AS from_code, a2.city AS to_city, a2.code AS to_code
    FROM flights f
    JOIN airports a1 ON f.from_airport_id = a1.id
    JOIN airports a2 ON f.to_airport_id = a2.id
    ORDER BY f.departure_time DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header page-header-flex">
        <div>
            <h1><i class="fas fa-plane"></i> Manage Flights</h1>
            <p>Add, edit, or remove flights from the system</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?php echo htmlspecialchars($error); ?></span></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><span><?php echo htmlspecialchars($success); ?></span></div>
    <?php endif; ?>

    <!-- Add/Edit Flight Form -->
    <div class="admin-form-section">
        <h3><i class="fas fa-<?php echo $edit_flight ? 'edit' : 'plus-circle'; ?>"></i> <?php echo $edit_flight ? 'Edit Flight' : 'Add New Flight'; ?></h3>
        <form method="POST">
            <?php if ($edit_flight): ?>
                <input type="hidden" name="flight_id" value="<?php echo $edit_flight['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Flight Number *</label>
                    <input type="text" name="flight_number" class="form-control" placeholder="e.g. AI-101" required
                           value="<?php echo htmlspecialchars($edit_flight['flight_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Airline *</label>
                    <input type="text" name="airline" class="form-control" placeholder="e.g. Air India" required
                           value="<?php echo htmlspecialchars($edit_flight['airline'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>From Airport *</label>
                    <select name="from_airport" class="form-control" required>
                        <option value="">Select</option>
                        <?php foreach ($airports as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($edit_flight && $edit_flight['from_airport_id'] == $a['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['city'] . ' (' . $a['code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>To Airport *</label>
                    <select name="to_airport" class="form-control" required>
                        <option value="">Select</option>
                        <?php foreach ($airports as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($edit_flight && $edit_flight['to_airport_id'] == $a['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['city'] . ' (' . $a['code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Departure Time *</label>
                    <input type="datetime-local" name="departure_time" class="form-control" required
                           value="<?php echo $edit_flight ? date('Y-m-d\TH:i', strtotime($edit_flight['departure_time'])) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Arrival Time *</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" required
                           value="<?php echo $edit_flight ? date('Y-m-d\TH:i', strtotime($edit_flight['arrival_time'])) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" class="form-control" placeholder="4500" step="0.01" min="1" required
                           value="<?php echo $edit_flight ? $edit_flight['price'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Total Seats *</label>
                    <input type="number" name="total_seats" class="form-control" placeholder="180" min="1" required
                           value="<?php echo $edit_flight ? $edit_flight['total_seats'] : '180'; ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-<?php echo $edit_flight ? 'save' : 'plus'; ?>"></i>
                    <?php echo $edit_flight ? 'Update Flight' : 'Add Flight'; ?>
                </button>
                <?php if ($edit_flight): ?>
                    <a href="<?php echo BASE; ?>admin/flights.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Flights Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> All Flights (<?php echo count($flights); ?>)</h3>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Flight</th>
                        <th>Airline</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Price</th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flights as $f): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($f['flight_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($f['airline']); ?></td>
                            <td><?php echo htmlspecialchars($f['from_city'] . ' → ' . $f['to_city']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($f['departure_time'])); ?></td>
                            <td><strong>₹<?php echo number_format($f['price']); ?></strong></td>
                            <td><?php echo $f['available_seats']; ?>/<?php echo $f['total_seats']; ?></td>
                            <td><span class="badge badge-<?php echo $f['status']; ?>"><?php echo ucfirst($f['status']); ?></span></td>
                            <td>
                                <a href="<?php echo BASE; ?>admin/flights.php?edit=<?php echo $f['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo BASE; ?>admin/flights.php?delete=<?php echo $f['id']; ?>" class="btn btn-danger btn-sm"
                                   data-confirm="Are you sure you want to delete this flight?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

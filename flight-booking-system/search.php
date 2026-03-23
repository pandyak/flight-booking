<?php
$page_title = 'Search Flights';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getDB();
$airports = $pdo->query("SELECT * FROM airports ORDER BY city")->fetchAll();

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';
$flights = [];

if ($from && $to) {
    $sql = "SELECT f.*, 
            a1.city AS from_city, a1.code AS from_code, a1.name AS from_airport,
            a2.city AS to_city, a2.code AS to_code, a2.name AS to_airport
            FROM flights f
            JOIN airports a1 ON f.from_airport_id = a1.id
            JOIN airports a2 ON f.to_airport_id = a2.id
            WHERE f.from_airport_id = ? AND f.to_airport_id = ? 
            AND f.status = 'scheduled' AND f.available_seats > 0";
    $params = [$from, $to];

    if ($date) {
        $sql .= " AND DATE(f.departure_time) = ?";
        $params[] = $date;
    }

    $sql .= " ORDER BY f.departure_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $flights = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-search"></i> Search Flights</h1>
        <p>Find the best flights for your journey</p>
    </div>

    <!-- Search Form -->
    <div class="search-box mb-4">
        <form action="<?php echo BASE; ?>search.php" method="GET" data-validate>
            <div class="search-grid">
                <div class="form-group">
                    <label><i class="fas fa-plane-departure"></i> From</label>
                    <select name="from" class="form-control" required>
                        <option value="">Select Departure</option>
                        <?php foreach ($airports as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo $from == $a['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['city'] . ' (' . $a['code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-plane-arrival"></i> To</label>
                    <select name="to" class="form-control" required>
                        <option value="">Select Destination</option>
                        <?php foreach ($airports as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo $to == $a['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['city'] . ' (' . $a['code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results -->
    <?php if ($from && $to): ?>
        <h2 class="section-title">
            <?php echo count($flights); ?> Flight<?php echo count($flights) !== 1 ? 's' : ''; ?> Found
        </h2>

        <?php if (empty($flights)): ?>
            <div class="card">
                <div class="empty-state">
                    <i class="fas fa-plane-slash"></i>
                    <h3>No Flights Found</h3>
                    <p>No flights match your search criteria. Try a different date or route.</p>
                    <a href="<?php echo BASE; ?>search.php" class="btn btn-outline">Clear Search</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($flights as $flight): ?>
                <?php
                    $dep = new DateTime($flight['departure_time']);
                    $arr = new DateTime($flight['arrival_time']);
                    $diff = $dep->diff($arr);
                    $duration = $diff->h . 'h ' . $diff->i . 'm';
                ?>
                <div class="flight-card">
                    <div class="flight-info">
                        <h3><?php echo htmlspecialchars($flight['flight_number']); ?></h3>
                        <div class="airline"><i class="fas fa-plane"></i> <?php echo htmlspecialchars($flight['airline']); ?></div>
                        <div class="flight-time"><?php echo $dep->format('H:i'); ?></div>
                        <div class="flight-city"><?php echo htmlspecialchars($flight['from_city'] . ' (' . $flight['from_code'] . ')'); ?></div>
                    </div>

                    <div class="flight-route">
                        <div class="duration"><?php echo $duration; ?></div>
                        <div class="route-line"></div>
                        <div class="stops">Non-stop</div>
                    </div>

                    <div class="flight-info" style="text-align: right;">
                        <div style="margin-bottom: 20px;"></div>
                        <div class="flight-time"><?php echo $arr->format('H:i'); ?></div>
                        <div class="flight-city"><?php echo htmlspecialchars($flight['to_city'] . ' (' . $flight['to_code'] . ')'); ?></div>
                    </div>

                    <div class="flight-price">
                        <div class="price">₹<?php echo number_format($flight['price']); ?></div>
                        <div class="per-person">per person</div>
                        <div class="mt-1" style="font-size:0.8rem; color: var(--text-muted);">
                            <?php echo $flight['available_seats']; ?> seats left
                        </div>
                        <a href="<?php echo BASE; ?>booking.php?flight_id=<?php echo $flight['id']; ?>" class="btn btn-primary btn-sm mt-2">
                            Book Now <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

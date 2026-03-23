<?php
$page_title = 'Home';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

// Get airports for search form
$pdo = getDB();
$airports = $pdo->query("SELECT * FROM airports ORDER BY city")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Explore the Skies with <span>SkyVoyage</span></h1>
        <p>Discover amazing flight deals across India. Search, compare, and book your next adventure in seconds.</p>

        <!-- Search Box -->
        <div class="search-box">
            <form action="<?php echo BASE; ?>search.php" method="GET" data-validate>
                <div class="search-grid">
                    <div class="form-group">
                        <label><i class="fas fa-plane-departure"></i> From</label>
                        <select name="from" class="form-control" required>
                            <option value="">Select Departure</option>
                            <?php foreach ($airports as $a): ?>
                                <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['city'] . ' (' . $a['code'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-plane-arrival"></i> To</label>
                        <select name="to" class="form-control" required>
                            <option value="">Select Destination</option>
                            <?php foreach ($airports as $a): ?>
                                <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['city'] . ' (' . $a['code'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="container" style="padding: 60px 20px;">
    <h2 class="section-title text-center" style="margin-bottom: 48px;">Why Choose <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">SkyVoyage</span>?</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tags"></i></div>
            <div class="stat-value" style="font-size:1.4rem;">Best Prices</div>
            <div class="stat-label">Guaranteed lowest fares</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-bolt"></i></div>
            <div class="stat-value" style="font-size:1.4rem;">Instant Booking</div>
            <div class="stat-label">Book in seconds</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="stat-value" style="font-size:1.4rem;">Secure Payments</div>
            <div class="stat-label">100% safe transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-headset"></i></div>
            <div class="stat-value" style="font-size:1.4rem;">24/7 Support</div>
            <div class="stat-label">Always here to help</div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

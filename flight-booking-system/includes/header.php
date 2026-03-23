<?php
require_once __DIR__ . '/auth.php';
$current_user = currentUser();
$flash = getFlashMessage();

// Determine if we're in admin area
$is_admin_page = strpos($_SERVER['REQUEST_URI'], 'admin') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkyVoyage - Book your flights with the best deals. Search, compare, and book domestic flights instantly.">
    <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME . ' - Book Your Flight'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE; ?>css/style.css">
</head>
<body class="<?php echo $is_admin_page ? 'admin-body' : ''; ?>">

<!-- Navbar -->
<nav class="navbar">
    <div class="container nav-container">
        <a href="<?php echo BASE; ?>" class="logo">
            <i class="fas fa-plane-departure"></i>
            <span><?php echo SITE_NAME; ?></span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <?php if ($is_admin_page): ?>
                <li><a href="<?php echo BASE; ?>admin/index.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="<?php echo BASE; ?>admin/flights.php"><i class="fas fa-plane"></i> Flights</a></li>
                <li><a href="<?php echo BASE; ?>admin/bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
                <li><a href="<?php echo BASE; ?>admin/users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="<?php echo BASE; ?>logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE; ?>">Home</a></li>
                <li><a href="<?php echo BASE; ?>search.php">Search Flights</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo BASE; ?>admin/index.php" class="btn btn-accent btn-sm"><i class="fas fa-cog"></i> Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE; ?>dashboard.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($current_user['name']); ?></a></li>
                    <li><a href="<?php echo BASE; ?>logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE; ?>login.php" class="btn btn-outline btn-sm">Login</a></li>
                    <li><a href="<?php echo BASE; ?>register.php" class="btn btn-primary btn-sm">Register</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Flash Messages -->
<?php if ($flash): ?>
<div class="container">
    <div class="alert alert-<?php echo $flash['type']; ?>" id="flashAlert">
        <span><?php echo htmlspecialchars($flash['message']); ?></span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
</div>
<?php endif; ?>

<main>

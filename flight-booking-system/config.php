<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'flight_booking');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'SkyVoyage');

// Base path for URLs — change if running in a subdirectory
// e.g. '/flight-booking-system/' for XAMPP subdirectory
define('BASE', '/flight-booking-system/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

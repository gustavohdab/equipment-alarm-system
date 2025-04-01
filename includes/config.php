<?php
/**
 * Database configuration
 * This file contains the database connection settings
 */

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'equipment_alarm_system');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Set charset to ensure proper storing of special characters
mysqli_set_charset($conn, "utf8");
?> 
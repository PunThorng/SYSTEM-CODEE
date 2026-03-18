<?php declare(strict_types=1); 

// Get database credentials from environment or use defaults
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'Admin@1234';
$db   = getenv('DB_NAME') ?: 'management_restaurant_system';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // Log error internally, display friendly message
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");

?>

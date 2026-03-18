<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header issues
if (ob_get_level() === 0) {
    ob_start();
}

$host = 'localhost';
$user = 'root';
$pass = 'Admin@1234';
$db_name = 'management_restaurant_system';

try {
    // First connect without database to create it if needed
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if not exists (use backticks to escape database name)
    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $conn->select_db($db_name);
    
    // Create table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS registered_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Validate required fields
    if (empty($_POST["full_name"]) || empty($_POST["username"]) || 
        empty($_POST["email"]) || empty($_POST["phone"]) || empty($_POST["password"])) {
        header("Location: ../../index.php?error=All fields are required");
        ob_end_clean();
        exit();
    }
    
    $full_name = trim($_POST["full_name"]);
    $username  = trim($_POST["username"]);
    $email     = trim($_POST["email"]);
    $phone     = trim($_POST["phone"]);
    $password  = $_POST["password"];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../index.php?error=Invalid email format");
        ob_end_clean();
        exit();
    }
    
    // Use bcrypt for password hashing
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Check for duplicate username
    $check_sql = "SELECT username, email FROM registered_users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if ($check_stmt) {
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            $conn->close();
            header("Location: ../../index.php?error=Username or email already taken");
            ob_end_clean();
            exit();
        }
        $check_stmt->close();
    }

    // Insert new user
    $sql = "INSERT INTO registered_users (full_name, username, email, phone, password) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssss", $full_name, $username, $email, $phone, $hash);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: ../../iclude/dashboard.php?page=category");
            ob_end_clean();
            exit();
        } else {
            $error_msg = urlencode($stmt->error);
            $stmt->close();
            $conn->close();
            header("Location: ../../index.php?error=Registration failed: " . $error_msg);
            ob_end_clean();
            exit();
        }
    } else {
        $error_msg = urlencode($conn->error);
        $conn->close();
        header("Location: ../../index.php?error=Database error: " . $error_msg);
        ob_end_clean();
        exit();
    }
} else {
    // Not a POST request - redirect to registration page
    ob_end_clean();
    header("Location: ../../index.php");
    exit();
}
?>

<?php
/**
 * Database Connection Test
 * Access this file to test your database connection
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Include the connection file
require_once "db_connect.php";

echo "<h2>Connection Status:</h2>";

if ($conn) {
    echo "<p style='color: green;'>✓ Connected successfully!</p>";
    echo "<p>Server Info: " . $conn->server_info . "</p>";
    echo "<p>Database: management_restaurant_system</p>";
    
    // Check if registered_users table exists
    $result = $conn->query("SHOW TABLES LIKE 'registered_users'");
    
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table 'registered_users' exists!</p>";
        
        // Show table contents
        $users = $conn->query("SELECT id, full_name, username, email FROM registered_users");
        echo "<h3>Registered Users:</h3>";
        if ($users->num_rows > 0) {
            echo "<table border='1' cellpadding='10'>";
            echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th></tr>";
            while ($row = $users->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users registered yet.</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Table 'registered_users' does NOT exist!</p>";
    }
    
    // Check if registered_admin table exists
    $result_admin = $conn->query("SHOW TABLES LIKE 'registered_admin'");
    
    if ($result_admin->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table 'registered_admin' exists!</p>";
        
        // Show table contents
        $admins = $conn->query("SELECT id, full_name, username, email FROM registered_admin");
        echo "<h3>Registered Admins:</h3>";
        if ($admins->num_rows > 0) {
            echo "<table border='1' cellpadding='10'>";
            echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th></tr>";
            while ($row = $admins->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No admins registered yet.</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Table 'registered_admin' does NOT exist!</p>";
    }
    
    $conn->close();
} else {
    echo "<p style='color: red;'>✗ Connection failed!</p>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
}
?>

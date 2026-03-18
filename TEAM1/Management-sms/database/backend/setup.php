<?php
/**
 * Database Setup Script
 * Run this file once to create the required database table
 * Access: http://yourdomain/path/to/database/backend/setup.php
 */

declare(strict_types=1);
require_once "db_connect.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Create registered_users table
    $sql = "CREATE TABLE IF NOT EXISTS registered_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'inactive') DEFAULT 'active',
        
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        // Insert sample admin user if table is empty
        $check_sql = "SELECT COUNT(*) as count FROM registered_users";
        $result = $conn->query($check_sql);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $admin_password = password_hash('admin123', PASSWORD_BCRYPT);
            $insert_sql = "INSERT INTO registered_users (full_name, username, email, phone, password) 
                          VALUES ('Admin User', 'admin', 'admin@restaurant.com', '012345678', ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("s", $admin_password);
            $stmt->execute();
            $stmt->close();
            
            $message = "Tables created successfully! Default admin user created (username: admin, password: admin123)";
        } else {
            $message = "Table registered_users created successfully!";
        }
        $success = true;
    } else {
        $message = "Error creating table: " . $conn->error;
    }
    
    // Create registered_admin table
    $sql_admin = "CREATE TABLE IF NOT EXISTS registered_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'inactive') DEFAULT 'active',
        
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_admin) === TRUE) {
        $message .= " | Table registered_admin also created!";
    }
    
    // Create category_food table
    $sql_category = "CREATE TABLE IF NOT EXISTS category_food (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_category_name (category_name),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_category) === TRUE) {
        $message .= " | Table category_food also created!";
        
        // Insert sample categories if table is empty
        $check_cat_sql = "SELECT COUNT(*) as count FROM category_food";
        $cat_result = $conn->query($check_cat_sql);
        $cat_row = $cat_result->fetch_assoc();
        
        if ($cat_row['count'] == 0) {
            $insert_cat_sql = "INSERT INTO category_food (category_name, description, status) 
                          VALUES 
                          ('Appetizers', 'Start your meal with delicious appetizers', 'active'),
                          ('Main Courses', 'Hearty main dishes and entrees', 'active'),
                          ('Desserts', 'Sweet treats and desserts', 'active'),
                          ('Beverages', 'Drinks and beverages', 'active'),
                          ('Specials', 'Special menu items', 'active')";
            $conn->query($insert_cat_sql);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Setup - RSRL4</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --blue-deep: #1a237e;
      --blue-mid: #283593;
      --blue-light: #3949ab;
      --bg-dark: #0d1321;
      --bg-card: #1a2332;
      --white-10: rgba(255,255,255,0.10);
      --white-15: rgba(255,255,255,0.15);
      --white-60: rgba(255,255,255,0.60);
      --white-85: rgba(255,255,255,0.85);
      --success: #4caf50;
      --danger: #f44336;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(135deg, var(--bg-dark) 0%, var(--blue-deep) 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .setup-card {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 40px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.4);
      border: 1px solid var(--white-10);
      text-align: center;
    }

    .setup-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, var(--blue-mid), var(--blue-light));
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
      font-size: 36px;
      color: #fff;
    }

    h1 {
      color: #fff;
      font-size: 1.5rem;
      margin-bottom: 8px;
    }

    p {
      color: var(--white-60);
      margin-bottom: 24px;
      line-height: 1.6;
    }

    .alert {
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-weight: 500;
    }

    .alert-success {
      background: rgba(76, 175, 80, 0.15);
      border: 1px solid rgba(76, 175, 80, 0.3);
      color: #a5d6a7;
    }

    .alert-error {
      background: rgba(244, 67, 54, 0.15);
      border: 1px solid rgba(244, 67, 54, 0.3);
      color: #ef9a9a;
    }

    .btn {
      display: inline-block;
      padding: 14px 32px;
      background: linear-gradient(135deg, var(--blue-mid), var(--blue-light));
      color: #fff;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1rem;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(40, 53, 147, 0.4);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--white-15);
      margin-top: 16px;
    }

    .info-box {
      background: var(--white-10);
      border-radius: 10px;
      padding: 16px;
      text-align: left;
      margin-top: 24px;
    }

    .info-box h3 {
      color: var(--white-85);
      font-size: 0.9rem;
      margin-bottom: 12px;
    }

    .info-box code {
      background: rgba(0,0,0,0.3);
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 0.85rem;
      color: #fff;
    }

    .info-box ul {
      list-style: none;
      color: var(--white-60);
      font-size: 0.85rem;
    }

    .info-box li {
      padding: 4px 0;
    }

    .info-box li::before {
      content: "•";
      color: var(--blue-light);
      margin-right: 8px;
    }
  </style>
</head>
<body>

  <div class="setup-card">
    <div class="setup-icon">
      <i class="bi bi-database-gear"></i>
    </div>
    
    <h1>Database Setup</h1>
    <p>This will create the required <code>registered_users</code> table in your database.</p>

    <?php if ($message): ?>
      <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (!$success): ?>
      <form method="POST">
        <button type="submit" class="btn">
          <i class="bi bi-plus-circle"></i> Create Table
        </button>
      </form>
    <?php else: ?>
      <a href="../peges/users_register.php" class="btn btn-outline">
        Go to Registration Page
      </a>
    <?php endif; ?>

    <div class="info-box">
      <h3>What this creates:</h3>
      <ul>
        <li><code>registered_users</code> table (for regular users)</li>
        <li><code>registered_admin</code> table (for admins)</li>
        <li>Indexes for faster queries</li>
        <li>Default admin user (optional)</li>
      </ul>
    </div>
  </div>

</body>
</html>

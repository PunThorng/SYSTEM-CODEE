<?php
/**
 * Add Image Column to Category Table
 * Run this file once to add image column to category_food table
 * Access: http://yourdomain/path/to/database/backend/add_category_image.php
 */

require_once "db_connect.php";

$message = "";
$success = false;

// Add image column if it doesn't exist
$sql = "ALTER TABLE category_food ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    $message = "Image column added successfully to category_food table!";
    $success = true;
} else {
    $message = "Error adding column: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category Image Column</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1a1a2e;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #16213e;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .success {
            color: #10b981;
        }
        .error {
            color: #f43f5e;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4f6ef7;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2><?php echo $success ? 'Success!' : 'Error'; ?></h2>
        <p class="<?php echo $success ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
        <a href="../peges/category_food.php" class="btn">Go to Categories</a>
    </div>
</body>
</html>

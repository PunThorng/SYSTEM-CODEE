<?php
// Connect to database
$host = 'localhost';
$user = 'root';
$pass = 'Admin@1234';
$db_name = 'management_restaurant_system';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Create categories directory if it doesn't exist
$upload_dir = dirname(__DIR__) . '/assets/images/categories/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Function to handle image upload
function uploadImage($file, $upload_dir) {
    if ($file && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        $name = basename($file['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        // Validate image extension
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed_ext)) {
            return null;
        }
        
        // Generate unique filename
        $new_name = uniqid('cat_') . '.' . $ext;
        $target_path = $upload_dir . $new_name;
        
        if (move_uploaded_file($tmp_name, $target_path)) {
            return $new_name;
        }
    }
    return null;
}

// Get action from GET parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add') {
    // Add new category
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['category_image']) && !empty($_FILES['category_image']['name'])) {
        $image = uploadImage($_FILES['category_image'], $upload_dir);
    }
    
    if (empty($category_name)) {
        echo "Category name is required";
        exit;
    }
    
    if ($image) {
        $sql = "INSERT INTO category_food (category_name, description, status, image, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $category_name, $description, $status, $image);
    } else {
        $sql = "INSERT INTO category_food (category_name, description, status, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $category_name, $description, $status);
    }
    
    if ($stmt->execute()) {
        header("Location: ../iclude/dashboard.php?page=category");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
    
} elseif ($action == 'update') {
    // Update category
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    $existing_image = isset($_POST['existing_image']) ? $_POST['existing_image'] : '';
    
    if (empty($category_name) || $id == 0) {
        echo "Category name is required";
        exit;
    }
    
    // Handle image upload
    $image = $existing_image;
    if (isset($_FILES['category_image']) && !empty($_FILES['category_image']['name'])) {
        $new_image = uploadImage($_FILES['category_image'], $upload_dir);
        if ($new_image) {
            // Delete old image if exists
            if ($existing_image && file_exists($upload_dir . $existing_image)) {
                unlink($upload_dir . $existing_image);
            }
            $image = $new_image;
        }
    }
    
    if ($image) {
        $sql = "UPDATE category_food SET category_name = ?, description = ?, status = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $category_name, $description, $status, $image, $id);
    } else {
        $sql = "UPDATE category_food SET category_name = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $category_name, $description, $status, $id);
    }
    
    if ($stmt->execute()) {
        header("Location: ../peges/category_food.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
    
} elseif ($action == 'delete') {
    // Delete category
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id == 0) {
        echo "Invalid category ID";
        exit;
    }
    
    // Get image filename before deleting
    $sql = "SELECT image FROM category_food WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Delete image file if exists
        if ($row['image'] && file_exists($upload_dir . $row['image'])) {
            unlink($upload_dir . $row['image']);
        }
    }
    $stmt->close();
    
    $sql = "DELETE FROM category_food WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ../peges/category_food.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

// If no action, close connection and exit
$conn->close();

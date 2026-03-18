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

if ($action == 'update') {
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
}

$conn->close();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    :root {
        --bg: #000000;
        --s1: #2c2a2a;
        --s2: #1d1c1c;
        --s3: #1a1a1a;
        --indigo: #4f6ef7;
        --emerald: #10b981;
        --amber: #f59e0b;
        --rose: #f43f5e;
        --border: rgba(255,255,255,0.05);
    }
    
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    .form-container {
        padding: 40px 60px;
        max-width: 100%;
        width: 100%;
        min-height: calc(100vh - 100px);
        margin: 0 auto;
    }
    
    .form-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .page-title {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 0;
        border-bottom: 1px solid var(--border);
        margin-bottom: 25px;
    }
    
    .page-title i {
        color: var(--indigo);
        font-size: 20px;
    }
    
    .card {
        background: var(--s1);
        border-radius: 5px;
        border: 1px solid var(--border);
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        color: #aaa;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        background: var(--s3);
        border: 1px solid var(--border);
        color: #fff;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--indigo);
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-group select {
        cursor: pointer;
    }
    
    .form-group select option {
        background: var(--s2);
    }
    
    .btn-group {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }
    
    .btn-submit {
        background: var(--indigo);
        color: #fff;
        padding: 12px 28px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-submit:hover {
        background: #3b5ae8;
    }
    
    .btn-cancel {
        background: var(--s2);
        color: #aaa;
        padding: 12px 28px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-cancel:hover {
        background: var(--s3);
        color: #fff;
    }
    
    /* Image Upload Styles */
    .image-upload-container {
        margin-bottom: 20px;
    }
    
    .image-upload-label {
        display: block;
        color: #aaa;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .image-upload-wrapper {
        position: relative;
        border: 2px dashed var(--border);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--s3);
    }
    
    .image-upload-wrapper:hover {
        border-color: var(--indigo);
    }
    
    .image-upload-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    .image-upload-icon {
        font-size: 40px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .image-upload-text {
        color: #888;
        font-size: 14px;
    }
    
    .image-preview {
        margin-top: 15px;
        display: none;
    }
    
    .image-preview img {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid var(--border);
    }
    
    .image-preview .remove-image {
        display: block;
        margin-top: 8px;
        color: var(--rose);
        font-size: 12px;
        cursor: pointer;
    }
</style>

<div class="form-container" style="width: 100%; height: 100%; background: #5f5e5e;">
    <div class="form-wrapper">
    <h1 class="page-title">
        <i class="bi bi-plus-circle"></i>
        Add Category
    </h1>
    
    <div class="card">
        <form action="../peges/category_food.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="category_name">Category Name *</label>
                <input type="text" id="category_name" name="category_name" required placeholder="Enter category name">
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" placeholder="Enter price">
            </div>
            
            <div class="image-upload-container">
                <label class="image-upload-label">Category Image</label>
                <div class="image-upload-wrapper">
                    <input type="file" id="category_image" name="category_image" accept="image/*" onchange="previewImage(event)">
                    <div class="image-upload-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div class="image-upload-text">
                        Click to upload or drag and drop
                    </div>
                </div>
                <div class="image-preview" id="imagePreview">
                    <img id="previewImg" src="" alt="Preview">
                    <span class="remove-image" onclick="removeImage()">Remove image</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Enter category description"></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-circle"></i> Save Category
                </button>
                <a href="../iclude/dashboard.php?page=category" class="btn-cancel">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    document.getElementById('category_image').value = '';
    document.getElementById('previewImg').src = '';
    document.getElementById('imagePreview').style.display = 'none';
}
</script>

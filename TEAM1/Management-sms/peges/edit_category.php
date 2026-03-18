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

// Get category by ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM category_food WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Category not found";
    exit;
}

$category = $result->fetch_assoc();
$stmt->close();
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
        font-size: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px 0;
        border-bottom: 1px solid var(--border);
        margin-bottom: 30px;
    }
    
    .page-title i {
        color: var(--indigo);
        font-size: 24px;
    }
    
    .card {
        background: var(--s1);
        border-radius: 10px;
        border: 1px solid var(--border);
        padding: 40px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        color: #aaa;
        font-size: 15px;
        font-weight: 500;
        margin-bottom: 10px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        background: var(--s3);
        border: 1px solid var(--border);
        color: #fff;
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--indigo);
    }
    
    .form-group textarea {
        min-height: 120px;
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
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn-submit {
        background: var(--indigo);
        color: #fff;
        padding: 14px 32px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
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
        padding: 14px 32px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
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
        margin-bottom: 25px;
    }
    
    .image-upload-label {
        display: block;
        color: #aaa;
        font-size: 15px;
        font-weight: 500;
        margin-bottom: 10px;
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
    
    .current-image {
        margin-bottom: 15px;
    }
    
    .current-image img {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid var(--border);
    }
    
    .current-image-label {
        display: block;
        color: #aaa;
        font-size: 13px;
        margin-bottom: 8px;
    }
    
    .image-preview {
        margin-top: 15px;
        display: none;
    }
    
    .image-preview img {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid var(--indigo);
    }
    
    .image-preview .remove-image {
        display: inline-block;
        margin-top: 8px;
        margin-left: 10px;
        color: var(--rose);
        font-size: 12px;
        cursor: pointer;
    }
</style>

<div class="form-container" style="background-color: #0d0f1a; height: 100%;">
    <div class="form-wrapper">
    <h1 class="page-title">
        <i class="bi bi-pencil-square"></i>
        Edit Category
    </h1>
    
    <div class="card" style="background-color: #4a5568;">
        <form action="../peges/category_food.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category_name">Category Name *</label>
                <input type="text" id="category_name" name="category_name" required value="<?php echo htmlspecialchars($category['category_name']); ?>">
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($category['price'] ?? ''); ?>">
            </div>
            
            <div class="image-upload-container">
                <label class="image-upload-label">Category Image</label>
                <?php if (!empty($category['image'])): ?>
                    <div class="current-image">
                        <span class="current-image-label">Current Image:</span>
                        <img src="../assets/images/categories/<?php echo htmlspecialchars($category['image']); ?>" alt="Current Category Image">
                    </div>
                <?php endif; ?>
                <div class="image-upload-wrapper">
                    <input type="file" id="category_image" name="category_image" accept="image/*" onchange="previewImage(event)">
                    <div class="image-upload-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div class="image-upload-text">
                        Click to upload new image or drag and drop
                    </div>
                </div>
                <div class="image-preview" id="imagePreview">
                    <img id="previewImg" src="" alt="Preview">
                    <span class="remove-image" onclick="removeImage()">Remove new image</span>
                </div>
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($category['image'] ?? ''); ?>">
            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
            <input type="hidden" name="action" value="update">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-circle"></i> Update Category
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

<?php $conn->close(); ?>

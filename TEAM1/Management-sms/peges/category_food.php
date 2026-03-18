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

// Handle add action
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    $price = isset($_POST['price']) ? $_POST['price'] : null;
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
    
    if ($image && $price) {
        $sql = "INSERT INTO category_food (category_name, price, description, status, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsss", $category_name, $price, $description, $status, $image);
    } elseif ($price) {
        $sql = "INSERT INTO category_food (category_name, price, description, status, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dsss", $price, $category_name, $description, $status);
    } elseif ($image) {
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
}

// Handle update action
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    $price = isset($_POST['price']) ? $_POST['price'] : null;
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
    
    if ($image && $price) {
        $sql = "UPDATE category_food SET category_name = ?, price = ?, description = ?, status = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsssi", $category_name, $price, $description, $status, $image, $id);
    } elseif ($price) {
        $sql = "UPDATE category_food SET category_name = ?, price = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssi", $category_name, $price, $description, $status, $id);
    } elseif ($image) {
        $sql = "UPDATE category_food SET category_name = ?, description = ?, status = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $category_name, $description, $status, $image, $id);
    } else {
        $sql = "UPDATE category_food SET category_name = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $category_name, $description, $status, $id);
    }
    
    if ($stmt->execute()) {
        header("Location: ../iclude/dashboard.php?page=category");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
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
        header("Location: ../iclude/dashboard.php?page=category");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

// Get all categories from category_food table
$sql = "SELECT * FROM category_food ORDER BY id DESC";
$result = $conn->query($sql);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<script>
function searchCategories() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    let table = document.querySelector('table');
    let rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let nameCell = rows[i].getElementsByClassName('category-name')[0];
        let descCell = rows[i].getElementsByClassName('category-desc')[0];
        
        if (nameCell || descCell) {
            let name = nameCell ? nameCell.textContent.toLowerCase() : '';
            let desc = descCell ? descCell.textContent.toLowerCase() : '';
            
            if (name.indexOf(filter) > -1 || desc.indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        window.location.href = '../peges/category_food.php?action=delete&id=' + id;
    }
}
</script>

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
    
    .categories-container {
        padding: 30px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
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
        margin-bottom: 20px;
    }
    
    .page-title i {
        color: var(--indigo);
        font-size: 20px;
    }
    
    .btn-add {
        background: var(--indigo);
        color: #fff;
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-add:hover {
        background: #3b5ae8;
        transform: translateY(-2px);
    }
    
    .card {
        background: var(--s1);
        border-radius: 5px;
        border: 1px solid var(--border);
        overflow: hidden;
    }
    
    .card-header {
        background: var(--s2);
        padding: 20px 25px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .card-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .card-header i { font-size: 20px; color: var(--indigo); }
    
    .card-header h4 {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 14px;
    }
    
    .search-box input {
        background: var(--s3);
        border: 1px solid var(--border);
        color: #fff;
        padding: 10px 14px 10px 40px;
        border-radius: 8px;
        font-size: 14px;
        width: 250px;
        transition: all 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: var(--indigo);
        width: 280px;
    }
    
    .search-box input::placeholder {
        color: #666;
    }
    
    .card-body {
        padding: 0;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: var(--s2);
    }
    
    th {
        color: #aaa;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        padding: 16px 20px;
        text-align: left;
    }
    
    td {
        color: #ccc;
        padding: 16px 20px;
        font-size: 14px;
        border-bottom: 1px solid var(--border);
    }
    
    tbody tr {
        transition: background 0.2s;
    }
    
    tbody tr:hover {
        background: var(--s2);
    }
    
    .category-name {
        font-weight: 500;
        color: #fff;
    }
    
    .category-desc {
        color: #888;
        font-size: 13px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-active {
        background: rgba(16, 185, 129, 0.15);
        color: var(--emerald);
    }
    
    .status-inactive {
        background: rgba(244, 63, 94, 0.15);
        color: var(--rose);
    }
    
    .actions {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-edit {
        background: rgba(79, 110, 247, 0.15);
        color: var(--indigo);
    }
    
    .btn-edit:hover {
        background: var(--indigo);
        color: #fff;
    }
    
    .btn-delete {
        background: rgba(244, 63, 94, 0.15);
        color: var(--rose);
    }
    
    .btn-delete:hover {
        background: var(--rose);
        color: #fff;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #444;
    }
    
    .empty-state p {
        font-size: 15px;
    }
    
    .category-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid var(--border);
    }
    
    .category-image-placeholder {
        width: 60px;
        height: 60px;
        background: var(--s3);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 20px;
    }
</style>

<div class="categories-container" style="background-color:#0d0f1a;width:100%; height:auto;">
    <div class="page-header">
        <h1 class="page-title" style="margin-top: 5vh;">
            <i class="bi bi-grid-fill"></i>
            Category Food
        </h1>
        <a href="../iclude/dashboard.php?page=add_category" class="btn-add" style="background-color: #1e43e7;border-radius: 5px;margin-right: 20px;">
            <i class="bi bi-plus-circle"></i> Add
        </a>
    </div>
    
    <div class="card" >
        <div class="card-header">
            <div class="card-header-left">
                <i class="bi bi-list-ul"></i>
                <h4>All Categories</h4>
            </div>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" onkeyup="searchCategories()" placeholder="Search categories...">
            </div>
        </div>
        
        <div class="card-body" style="padding: 0; font-family: 'JetBrains Mono', monospace;">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr style="text-align: center; ">
                            <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-image" style="margin-right: 5px;"></i>Image</th>
                            <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-tag" style="margin-right: 5px;"></i>Category Name</th>
                            <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-currency-dollar" style="margin-right: 5px;"></i>Price</th>
                            <th style="text-align: center;color: rgb(255, 255, 255);"><i class="bi bi-text-left" style="margin-right: 5px;"></i>Description</th>
                            <th style="text-align: center;color: rgb(255, 255, 255);"><i class="bi bi-calendar-event" style="margin-right: 5px;"></i>Created</th>
                            <th style="text-align: center;color: rgb(255, 255, 255);"><i class="bi bi-toggle-on" style="margin-right: 5px;"></i>Status</th>
                            <th style="text-align: center;color: rgb(255, 255, 255);"><i class="bi bi-gear" style="margin-right: 5px;"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center; border-top: 1px solid var(--border);">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr style="text-align: center;border-bottom: 1px solid var(--border);">
                                <td style="text-align: center;">
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../assets/images/categories/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['category_name']); ?>" class="category-image">
                                    <?php else: ?>
                                        <div class="category-image-placeholder">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="category-name" style="text-align: center;"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td style="text-align: center;">$<?php echo htmlspecialchars($row['price'] ?? '0.00'); ?></td>
                                <td class="category-desc" style="text-align: center; color:white"><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                <td style="text-align: center;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td style="text-align: center;">
                                    <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: center; margin-left: 20px;">
                                    <div class="actions"  style="margin-left: 230px;">
                                        <a href="../iclude/dashboard.php?page=edit_category&id=<?php echo $row['id']; ?>" class="btn-action btn-edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <button onclick="deleteCategory(<?php echo $row['id']; ?>)" class="btn-action btn-delete">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No categories found. Click "Add" to create your first category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $conn->close(); ?>

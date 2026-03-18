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

// Handle order submission
if (isset($_POST['action']) && $_POST['action'] == 'order') {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $total_price = $price * $quantity;
    
    if ($category_id == 0 || empty($category_name) || $price == 0) {
        $error = "Please select a valid category";
    } else {
        $sql = "INSERT INTO orders (category_id, category_name, price, quantity, total_price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdii", $category_id, $category_name, $price, $quantity, $total_price);
        
        if ($stmt->execute()) {
            $success = "Order placed successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get all categories for selection
$sql = "SELECT * FROM category_food WHERE status = 'active' ORDER BY id DESC";
$result = $conn->query($sql);

// Get all orders for the popup
if (isset($_POST['action']) && $_POST['action'] == 'cancel_order') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    
    if ($order_id > 0) {
        // Check if order exists and is pending
        $checkSql = "SELECT id, status FROM orders WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $order_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $orderData = $checkResult->fetch_assoc();
            if ($orderData['status'] == 'pending') {
                $updateSql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("i", $order_id);
                
                if ($updateStmt->execute()) {
                    $cancel_success = "Order cancelled successfully!";
                } else {
                    $cancel_error = "Error cancelling order: " . $conn->error;
                }
                $updateStmt->close();
            } else {
                $cancel_error = "Only pending orders can be cancelled";
            }
        } else {
            $cancel_error = "Order not found";
        }
        $checkStmt->close();
    }
}

$allOrdersQuery = "SELECT * FROM orders ORDER BY order_date DESC";
$allOrdersResult = $conn->query($allOrdersQuery);
$allOrders = [];
if ($allOrdersResult) {
    while ($row = $allOrdersResult->fetch_assoc()) {
        $allOrders[] = $row;
    }
}
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

function orderCategory(id, name, price, image) {
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryPrice').value = price;
    document.getElementById('selectedImage').src = image ? '../assets/images/categories/' + image : '../assets/images/logo.jpg';
    document.getElementById('selectedName').textContent = name;
    document.getElementById('selectedPrice').textContent = parseFloat(price).toFixed(2);
    document.getElementById('quantity').value = 1;
    document.getElementById('totalPrice').textContent = parseFloat(price).toFixed(2);
    document.getElementById('orderModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function openMyOrdersModal() {
    document.getElementById('myOrdersModal').style.display = 'flex';
}

function closeMyOrdersModal() {
    document.getElementById('myOrdersModal').style.display = 'none';
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        const formData = new FormData();
        formData.append('action', 'cancel_order');
        formData.append('order_id', orderId);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel order');
        });
    }
}

function changeQuantity(delta) {
    const quantity = document.getElementById('quantity');
    let newValue = parseInt(quantity.value) + delta;
    if (newValue < 1) newValue = 1;
    quantity.value = newValue;
    updateTotal();
}

function updateTotal() {
    const price = parseFloat(document.getElementById('categoryPrice').value) || 0;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const total = price * quantity;
    document.getElementById('totalPrice').textContent = total.toFixed(2);
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
    
    .orders-container {
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
    
    .success-message {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(16, 185, 129, 0.1) 100%);
        color: #34d399;
        padding: 20px 24px;
        border-radius: 14px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 2px solid rgba(16, 185, 129, 0.4);
        font-weight: 600;
        font-size: 16px;
    }
    
    .success-message i { font-size: 24px; }
    
    .error-message {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.25) 0%, rgba(239, 68, 68, 0.1) 100%);
        color: #f87171;
        padding: 20px 24px;
        border-radius: 14px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 2px solid rgba(239, 68, 68, 0.4);
        font-weight: 600;
        font-size: 16px;
    }
    
    .error-message i { font-size: 24px; }
    
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
    
    .price-cell {
        color: var(--emerald);
        font-weight: 700;
    }
    
    .btn-order {
        background: var(--indigo);
        color: #fff;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-order:hover {
        background: #3b5ae8;
        transform: translateY(-2px);
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
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.9);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
    }
    
    .modal-content {
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        border-radius: 24px;
        padding: 0;
        width: 90%;
        max-width: 420px;
        max-height: 90vh;
        border: 1px solid rgba(148, 163, 184, 0.1);
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.05),
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    
    .modal-header h3 {
        color: #ffffff;
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.02em;
    }
    
    .modal-header h3 i {
        font-size: 22px;
    }
    
    .modal-close {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #ffffff;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.1);
    }
    
    .modal-body {
        padding: 28px;
        overflow-y: auto;
        max-height: calc(90vh - 85px);
    }
    
    .selected-item {
        display: flex;
        gap: 18px;
        align-items: center;
        margin-bottom: 24px;
        padding: 20px;
        background: rgba(59, 130, 246, 0.08);
        border-radius: 16px;
        border: 1px solid rgba(59, 130, 246, 0.15);
    }
    
    .selected-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }
    
    .selected-item .details {
        flex: 1;
    }
    
    .selected-item .name {
        color: #f1f5f9;
        font-weight: 600;
        font-size: 17px;
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }
    
    .selected-item .price {
        color: #34d399;
        font-weight: 700;
        font-size: 20px;
    }
    
    .quantity-label {
        display: block;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
    }
    
    .quantity-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px;
        background: #0f172a;
        border-radius: 14px;
    }
    
    .quantity-group button {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: #ffffff;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        cursor: pointer;
        font-size: 20px;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .quantity-group button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    
    .quantity-group button:active {
        transform: translateY(0);
    }
    
    .quantity-group input {
        width: 70px;
        text-align: center;
        background: #1e293b;
        border: 2px solid #3b82f6;
        color: #ffffff;
        padding: 12px;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
    }
    
    .quantity-group input:focus {
        outline: none;
    }
    
    .total-section {
        margin-bottom: 24px;
    }
    
    .total-price {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.05) 100%);
        border-radius: 14px;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    .total-price-label {
        color: #94a3b8;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    
    .total-price-value {
        font-size: 28px;
        color: #34d399;
        font-weight: 800;
    }
    
    .btn-group {
        display: flex;
        gap: 12px;
    }
    
    .btn-confirm {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        padding: 16px 28px;
        border: none;
        border-radius: 14px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s;
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        letter-spacing: 0.02em;
    }
    
    .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
    }
    
    .btn-cancel-modal {
        background: #334155;
        color: #cbd5e1;
        padding: 16px 24px;
        border: 1px solid #475569;
        border-radius: 14px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s;
        letter-spacing: 0.02em;
    }
    
    .btn-cancel-modal:hover {
        background: #475569;
        color: #ffffff;
    }
    
    /* My Orders Modal */
    .my-orders-modal .modal-content {
        max-width: 800px;
        max-height: 85vh;
    }
    
    .my-orders-modal .modal-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .orders-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }
    
    .order-item:hover {
        background: var(--s2);
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .order-info {
        flex: 1;
    }
    
    .order-name {
        color: #fff;
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 4px;
    }
    
    .order-details {
        color: #888;
        font-size: 13px;
    }
    
    .order-total {
        color: var(--emerald);
        font-weight: 700;
        font-size: 16px;
    }
    
    .order-total.cancelled {
        text-decoration: line-through;
        opacity: 0.5;
    }
    
    .order-date {
        color: #666;
        font-size: 12px;
        margin-top: 4px;
    }
    
    .empty-orders {
        text-align: center;
        padding: 40px;
        color: #666;
    }
    
    .empty-orders i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #444;
    }
    
    .modal-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.05) 100%);
        border-radius: 0 0 24px 24px;
        border-top: 1px solid var(--border);
    }
    
    .modal-summary-label {
        color: #aaa;
        font-size: 14px;
        font-weight: 600;
    }
    
    .modal-summary-value {
        color: #34d399;
        font-size: 24px;
        font-weight: 800;
    }
    
    .btn-cancel-order {
        background: rgba(244, 63, 94, 0.15);
        color: #f87171;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-cancel-order:hover {
        background: rgba(244, 63, 94, 0.3);
        color: #fff;
    }
    
    .order-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .order-status.pending {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
    }
    
    .order-status.cancelled {
        background: rgba(244, 63, 94, 0.15);
        color: #f87171;
    }
    
    .order-status.completed {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
    }
</style>

<div class="orders-container" style="background-color:#0d0f1a;width:100%; height:auto;">
    <div class="page-header">
        <h1 class="page-title"  style="font-family: 'JetBrains Mono', monospace;font-size: 15px;margin-top:4vh">
            <i class="bi bi-cart-fill" style="color: white;"></i>
            User Orders Menu
        </h1>
        <button type="button" class="btn-order" style="background: #5347f1; font-family: 'JetBrains Mono', monospace;" onclick="openMyOrdersModal()">
            <i class="bi bi-bag"></i> My Orders
        </button>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="success-message">
            <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="bi bi-exclamation-circle-fill"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <i class="bi bi-grid-3x3-gap-fill" style="color: white;"></i>
                <h4>All Menu</h4>
            </div>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" onkeyup="searchCategories()" placeholder="Search categories...">
            </div>
        </div>
        
        <form action="" method="POST" id="orderForm">
            <input type="hidden" name="action" value="order">
            <input type="hidden" name="category_id" id="categoryId" value="">
            <input type="hidden" name="category_name" id="categoryName" value="">
            <input type="hidden" name="price" id="categoryPrice" value="">
            
            <div class="card-body" style="padding: 0; font-family: 'JetBrains Mono', monospace;">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr style="text-align: center;">
                                <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-image" style="margin-right: 5px;"></i>Image</th>
                                <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-tag" style="margin-right: 5px;"></i>Category Name</th>
                                <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-currency-dollar" style="margin-right: 5px;"></i>Price</th>
                                <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-text-left" style="margin-right: 5px;"></i>Description</th>
                                <th style="text-align: center; color: rgb(255, 255, 255);"><i class="bi bi-gear" style="margin-right: 5px;"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center; border-top: 1px solid var(--border);">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr style="text-align: center; border-bottom: 1px solid var(--border);">
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
                                    <td class="price-cell" style="text-align: center;">$<?php echo number_format($row['price'] ?? 0, 2); ?></td>
                                    <td class="category-desc" style="text-align: center; color: white;"><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-order" onclick="orderCategory(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['category_name']); ?>', <?php echo $row['price'] ?? 0; ?>, '<?php echo $row['image']; ?>')">
                                            <i class="bi bi-cart-plus"></i> Order
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No categories available</p>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Order Modal -->
<div class="modal" id="orderModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-bag-check-fill"></i> Place Order</h3>
            <button class="modal-close" onclick="closeModal()"><i class="bi bi-x"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="selected-item">
                <img id="selectedImage" src="" alt="Selected Item">
                <div class="details">
                    <div class="name" id="selectedName"></div>
                    <div class="price">$<span id="selectedPrice">0.00</span></div>
                </div>
            </div>
            
            <span class="quantity-label">Select Quantity</span>
            <div class="quantity-group">
                <button type="button" onclick="changeQuantity(-1)"><i class="bi bi-dash"></i></button>
                <input type="number" name="quantity" id="quantity" value="1" min="1" readonly>
                <button type="button" onclick="changeQuantity(1)"><i class="bi bi-plus"></i></button>
            </div>
            
            <div class="total-section">
                <div class="total-price">
                    <span class="total-price-label">Total Amount</span>
                    <span class="total-price-value">$<span id="totalPrice">0.00</span></span>
                </div>
            </div>
            
            <div class="btn-group">
                <button type="submit" form="orderForm" class="btn-confirm">
                    <i class="bi bi-check2-circle"></i> Confirm Order
                </button>
                <button type="button" class="btn-cancel-modal" onclick="closeModal()">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- My Orders Modal -->
<div class="modal my-orders-modal" id="myOrdersModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-bag-heart"></i> My Orders</h3>
            <button class="modal-close" onclick="closeMyOrdersModal()"><i class="bi bi-x"></i></button>
        </div>
        
        <div class="orders-list">
            <?php if (count($allOrders) > 0): ?>
                <?php 
                $grandTotal = 0;
                foreach ($allOrders as $order): 
                    if ($order['status'] != 'cancelled') {
                        $grandTotal += $order['total_price'];
                    }
                ?>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-name"><i class="bi bi-bag" style="margin-right: 8px; color: #10b981;"></i><?php echo htmlspecialchars($order['category_name']); ?></div>
                            <div class="order-details">
                                <i class="bi bi-currency-dollar" style="margin-right: 4px;"></i>$<?php echo number_format($order['price'], 2); ?> × <?php echo $order['quantity']; ?> items
                            </div>
                            <div class="order-date">
                                <i class="bi bi-calendar-event" style="margin-right: 4px;"></i><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div class="order-total <?php echo $order['status'] == 'cancelled' ? 'cancelled' : ''; ?>">
                                <?php if ($order['status'] == 'cancelled'): ?>
                                    <span style="text-decoration: line-through; opacity: 0.5;"><i class="bi bi-currency-dollar" style="margin-right: 4px;"></i><?php echo number_format($order['total_price'], 2); ?></span>
                                <?php else: ?>
                                    <i class="bi bi-currency-dollar" style="margin-right: 4px;"></i>$<?php echo number_format($order['total_price'], 2); ?>
                                <?php endif; ?>
                            </div>
                            <span class="order-status <?php echo $order['status']; ?>">
                                <?php if ($order['status'] == 'pending'): ?>
                                    <i class="bi bi-clock-history" style="margin-right: 4px;"></i>
                                <?php elseif ($order['status'] == 'completed'): ?>
                                    <i class="bi bi-check-circle" style="margin-right: 4px;"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle" style="margin-right: 4px;"></i>
                                <?php endif; ?>
                                <?php echo $order['status']; ?>
                            </span>
                            <?php if ($order['status'] == 'pending'): ?>
                                <button type="button" class="btn-cancel-order" onclick="cancelOrder(<?php echo $order['id']; ?>)" style="margin-top: 8px;">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <i class="bi bi-bag-x"></i>
                    <p>No orders yet</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (count($allOrders) > 0): ?>
        <div class="modal-summary">
            <span class="modal-summary-label"><i class="bi bi-calculator" style="margin-right: 8px;"></i>Grand Total</span>
            <span class="modal-summary-value"><i class="bi bi-currency-dollar" style="margin-right: 4px;"></i><?php echo number_format($grandTotal, 2); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $conn->close(); ?>

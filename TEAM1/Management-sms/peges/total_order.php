<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
$host = 'localhost';
$user = 'root';
$pass = 'Admin@1234';
$db_name = 'management_restaurant_system';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

$conn->set_charset("utf8mb4");

// Handle date filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$today = date('Y-m-d');

$whereClause = "";
if ($filter == 'today') {
    $whereClause = " WHERE DATE(order_date) = '$today'";
} elseif ($filter == 'week') {
    $whereClause = " WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter == 'month') {
    $whereClause = " WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

// Get total orders stats
$totalQuery = "SELECT 
    COUNT(*) as total_orders, 
    COALESCE(SUM(total_price), 0) as total_revenue, 
    COALESCE(SUM(quantity), 0) as total_items 
    FROM orders" . $whereClause;
$totalResult = $conn->query($totalQuery);
$totalStats = $totalResult->fetch_assoc();

// Get today's stats
$todayQuery = "SELECT 
    COUNT(*) as today_orders, 
    COALESCE(SUM(total_price), 0) as today_revenue 
    FROM orders WHERE DATE(order_date) = '$today'";
$todayResult = $conn->query($todayQuery);
$todayStats = $todayResult->fetch_assoc();

// Get orders by category
$categoryQuery = "SELECT 
    category_name, 
    COUNT(*) as order_count, 
    SUM(quantity) as total_quantity, 
    SUM(total_price) as total_revenue 
    FROM orders" . $whereClause . " 
    GROUP BY category_id, category_name 
    ORDER BY total_revenue DESC";
$categoryResult = $conn->query($categoryQuery);

// Get recent orders
$recentQuery = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 20";
$recentResult = $conn->query($recentQuery);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    .report-container {
        padding: 25px;
        width: 100%;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .page-title {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 14px;
        letter-spacing: -0.5px;
    }
    
    .page-title i {
        color: #667eea;
        font-size: 28px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .page-title span {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        background: rgba(255,255,255,0.03);
        padding: 6px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.05);
    }
    
    .filter-btn {
        background: transparent;
        border: none;
        color: #888;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-btn:hover {
        background: rgba(102, 126, 234, 0.15);
        color: #fff;
    }
    
    .filter-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 600px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
    
    .stat-card {
        background: linear-gradient(145deg, #1e1e2d 0%, #16161f 100%);
        border-radius: 20px;
        padding: 28px;
        border: 1px solid rgba(255,255,255,0.08);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent-color), transparent);
    }
    
    .stat-card.total { --accent-color: #667eea; }
    .stat-card.revenue { --accent-color: #10b981; }
    .stat-card.items { --accent-color: #f59e0b; }
    .stat-card.today { --accent-color: #f43f5e; }
    
    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, var(--accent-color) 0%, transparent 70%);
        opacity: 0.05;
    }
    
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 18px;
        position: relative;
        z-index: 1;
    }
    
    .stat-card.total .stat-icon { background: rgba(102, 126, 234, 0.2); color: #667eea; }
    .stat-card.revenue .stat-icon { background: rgba(16, 185, 129, 0.2); color: #10b981; }
    .stat-card.items .stat-icon { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .stat-card.today .stat-icon { background: rgba(244, 63, 94, 0.2); color: #f43f5e; }
    
    .stat-label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    
    .stat-value {
        color: #fff;
        font-size: 32px;
        font-weight: 800;
        letter-spacing: -1px;
        position: relative;
        z-index: 1;
    }
    
    .stat-card.revenue .stat-value,
    .stat-card.today .stat-value {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .card {
        background: linear-gradient(145deg, #1e1e2d 0%, #16161f 100%);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.08);
        overflow: hidden;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .card-header {
        background: linear-gradient(90deg, rgba(255,255,255,0.03) 0%, transparent 100%);
        padding: 22px 26px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        gap: 14px;
    }
    
    .card-header i { 
        font-size: 20px; 
        padding: 10px;
        border-radius: 10px;
        background: rgba(102, 126, 234, 0.15);
        color: #667eea;
    }
    
    .card-header h4 { 
        color: #fff; 
        font-size: 17px; 
        font-weight: 600; 
        letter-spacing: -0.3px;
    }
    
    .card-body {
        padding: 0;
    }
    
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th {
        background: rgba(0,0,0,0.2);
        color: #9ca3af;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        padding: 16px 24px;
        text-align: left;
        letter-spacing: 0.05em;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    
    td {
        color: #d1d5db;
        padding: 18px 24px;
        font-size: 14px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        transition: background 0.2s;
    }
    
    tbody tr:last-child td {
        border-bottom: none;
    }
    
    tbody tr:hover td {
        background: rgba(255,255,255,0.03);
    }
    
    .amount {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 15px;
    }
    
    .order-id {
        color: #667eea;
        font-weight: 700;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #4b5563;
    }
    
    .empty-state i {
        font-size: 52px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    
    .tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 25px;
        background: rgba(255,255,255,0.03);
        padding: 6px;
        border-radius: 12px;
        width: fit-content;
        border: 1px solid rgba(255,255,255,0.05);
    }
    
    .tab {
        background: transparent;
        border: none;
        color: #6b7280;
        padding: 14px 28px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .tab:hover { 
        color: #fff; 
        background: rgba(255,255,255,0.05);
    }
    
    .tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    /* Status badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    
    .status-badge.pending {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
    }
    
    .status-badge.completed {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
    }
</style>

<script>
function openTab(tabName) {
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.classList.remove('active'));
    
    document.querySelector(`[onclick="openTab('${tabName}')"]`).classList.add('active');
    document.getElementById(tabName).classList.add('active');
}
</script>

<div class="report-container" style="margin-left: 0; width: 100%; padding: 40px; background: linear-gradient(135deg, #1e1e2d 0%, #16161f 100%); border-radius: 2px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
    <div class="page-header" style="margin-top: 5vh;">
        <h1 class="page-title" style="  font-size:17px; font-family: 'JetBrains Mono', monospace;">
          <i class="bi bi-bar-chart-line-fill" style="color: red;"></i>
            <span>Total Order Report</span>
        </h1>
        
        <!-- Filter Buttons -->
        <div class="filter-buttons" style="font-family: 'JetBrains Mono', monospace; border-radius: 5px; border: 1px solid rgba(255,255,255,0.05); color: #f7f2f2; background: rgba(255,255,255,0.03);">
            <?php $current_page = 'total_order'; ?>
            <a href="?page=total_order&filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-range"></i> All Time
            </a>
            <a href="?page=total_order&filter=today" class="filter-btn <?php echo $filter == 'today' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-day"></i> Today
            </a>
            <a href="?page=total_order&filter=week" class="filter-btn <?php echo $filter == 'week' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-week"></i> This Week
            </a>
            <a href="?page=total_order&filter=month" class="filter-btn <?php echo $filter == 'month' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-month"></i> This Month
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total" style="border-radius: 5px;">
            <div class="stat-icon">
                <i class="bi bi-bag-check-fill"></i>
            </div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo number_format($totalStats['total_orders']); ?></div>
        </div>
        
        <div class="stat-card revenue" style="border-radius: 5px;">
            <div class="stat-icon"> 
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">$<?php echo number_format($totalStats['total_revenue'], 2); ?></div>
        </div>
        
        <div class="stat-card items" style="border-radius: 5px;">
            <div class="stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-label">Total Items Sold</div>
            <div class="stat-value"><?php echo number_format($totalStats['total_items']); ?></div>
        </div>
        
        <div class="stat-card today" style="border-radius: 5px;">
            <div class="stat-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-label">Today's Revenue</div>
            <div class="stat-value">$<?php echo number_format($todayStats['today_revenue'], 2); ?></div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs" style="border-radius: 5px;">
        <button class="tab active" onclick="openTab('by-category')" style="border-radius: 3px; font-family: 'JetBrains Mono', monospace; font-size: 13px">By Category</button>
        <button class="tab" onclick="openTab('recent-orders')" style="border-radius: 3px; font-family: 'JetBrains Mono', monospace; font-size: 13px;">Recent Orders</button>
    </div>
    
    <!-- By Category Tab -->
    <div id="by-category" class="tab-content active" >
        <div class="card" style="border-radius: 3px;"> 
            <div class="card-header">
                <i class="bi bi-pie-chart-fill" style="color: blue; background:#ffff;"></i>
                <h4 style="font-family:'JetBrains Mono', monospace;">Orders by Category</h4>
            </div>
            <div class="card-body">
                <?php if ($categoryResult && $categoryResult->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr style=" font-family: 'JetBrains Mono', monospace; border: 1px solid #ddd; background: rgba(8, 8, 8, 0.1);">
                                <th style=" color: #ffffff;">Category Name</th>
                                <th style="text-align: center; color: #ffffff;">Order Count</th>
                                <th style="text-align: center; color: #ffffff;">Total Items</th>
                                <th style="text-align: right; color: #ffffff;">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $categoryResult->fetch_assoc()): ?>
                                <tr style="font-family: 'JetBrains Mono', monospace;">
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td style="text-align: center;"><?php echo number_format($row['order_count']); ?></td>
                                    <td style="text-align: center;"><?php echo number_format($row['total_quantity']); ?></td>
                                    <td style="text-align: right;" class="amount">$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No order data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders Tab -->
    <div id="recent-orders" class="tab-content">
        <div class="card" style="border-radius: 4px;">
            <div class="card-header">
                <i class="bi bi-clock-history" style="color: blue; background: #f8f9fa;font-family: 'JetBrains Mono', monospace; font-size: 15px;"></i>
                <h4>Recent Orders</h4>
            </div>
            <div class="card-body">
                <?php if ($recentResult && $recentResult->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr style=" font-family: 'JetBrains Mono', monospace; border: 1px solid #ddd; background: rgba(8, 8, 8, 0.1);">
                                <th style="color:#d1d5db">Order ID</th>
                                <th style="color:#d1d5db">Category Name</th>
                                <th style="text-align: center; color:#d1d5db">Price</th>
                                <th style="text-align: center; color:#d1d5db">Quantity</th>
                                <th style="text-align: right; color:#d1d5db">Total</th>
                                <th style="color:#d1d5db; text-align: right;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentResult->fetch_assoc()): ?>
                                <tr style="font-family: 'JetBrains Mono', monospace;">
                                    <td class="order-id">#<?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td style="text-align: center;">$<?php echo number_format($row['price'], 2); ?></td>
                                    <td style="text-align: center;"><?php echo $row['quantity']; ?></td>
                                    <td style="text-align: right;" class="amount">$<?php echo number_format($row['total_price'], 2); ?></td>
                                    <td style="text-align: right;"><?php echo date('M d, Y H:i', strtotime($row['order_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No recent orders</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>

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

// Get all users from registered_users table
$sql = "SELECT * FROM registered_users ORDER BY id DESC";
$result = $conn->query($sql);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

<script>
function searchUsers() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    let table = document.querySelector('table');
    let rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let nameCell = rows[i].getElementsByClassName('user-name')[0];
        let usernameCell = rows[i].getElementsByTagName('td')[1];
        let emailCell = rows[i].getElementsByClassName('user-email')[0];
        let phoneCell = rows[i].getElementsByTagName('td')[2];
        
        if (nameCell || usernameCell || emailCell || phoneCell) {
            let name = nameCell ? nameCell.textContent.toLowerCase() : '';
            let username = usernameCell ? usernameCell.textContent.toLowerCase() : '';
            let email = emailCell ? emailCell.textContent.toLowerCase() : '';
            let phone = phoneCell ? phoneCell.textContent.toLowerCase() : '';
            
            if (name.indexOf(filter) > -1 || username.indexOf(filter) > -1 || 
                email.indexOf(filter) > -1 || phone.indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}
</script>

<style>
    :root {
        --bg: #000000;
        --s1: #2c2a2a;
        --s2: #1d1c1c;
        --s3: #1a1a1a;
        --indigo: #fefeff;
        --emerald: #10b981;
        --amber: #f59e0b;
        --rose: #f43f5e;
        --border: rgba(255,255,255,0.05);
    }
    
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    .users-container {
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
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box input {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 15px 10px 40px;
        color: #fff;
        font-size: 13px;
        width: 250px;
        transition: all 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: var(--indigo);
        box-shadow: 0 0 0 3px rgba(79, 110, 247, 0.15);
        width: 300px;
    }
    
    .search-box input::placeholder {
        color: #555;
    }
    
    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        font-size: 14px;
    }
    
    .search-box:hover i {
        color: var(--indigo);
    }
    
    table { width: 100%; margin: 0; }
    
    thead { background: var(--bg); }
    
    th {
        color: #888;
        font-weight: 600;
        padding: 14px 20px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'JetBrains Mono', monospace;
    }
    
    th i {
        margin-right: 6px;
        color: var(--indigo);
    }
    
    td {
        color: #e5e7eb;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
        font-family: 'JetBrains Mono', monospace;
    }
    
    tr { transition: all 0.2s; }
    
    tr:hover { background: var(--s2); }
    
    tr:last-child td { border-bottom: none; }
    
    .user-cell {
        display: flex;
        align-items: center;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--indigo), #6366f1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        margin-right: 12px;
    }
    
    .user-details {
        display: flex;
        flex-direction: column;
    }
    
    .user-name {
        color: #fff;
        font-weight: 500;
    }
    
    .user-email {
        color: #666;
        font-size: 12px;
    }
    
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-active { 
        background: rgba(16, 185, 129, 0.15); 
        color: var(--emerald); 
    }
    
    .badge-inactive { 
        background: rgba(244, 63, 94, 0.15); 
        color: var(--rose); 
    }
    
    .action-btns {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-view { 
        background: rgba(59, 130, 246, 0.15); 
        color: #3b82f6; 
    }
    
    .btn-edit { 
        background: rgba(245, 158, 11, 0.15); 
        color: var(--amber); 
    }
    
    .btn-delete { 
        background: rgba(244, 63, 94, 0.15); 
        color: var(--rose); 
    }
    
    .btn-action:hover {
        transform: scale(1.1);
    }
    
    .empty-state { 
        text-align: center; 
        padding: 60px; 
        color: #666; 
    }
    
    .empty-state i { 
        font-size: 50px; 
        margin-bottom: 15px; 
        color: var(--indigo); 
    }
    
    .table-footer {
        padding: 15px 25px;
        background: var(--s2);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #666;
        font-size: 13px;
    }
</style>

<div class="container-fluid p-4 p-2" style="background-color: #0d0f1a; width:100%; height:100%;">

    <div class="page-header">
        <h1 class="page-title" style="margin-top: 9vh;margin-left: 20px; font-family: 'Segoe UI', sans-serif;">
            <i class="bi bi-people-fill"></i>
            Users Management
        </h1>
        <a href="../peges/users_register.php" class="btn-add" style="margin-top: 40px;background-color: #193ee2;border-radius: 5px;margin-right: 20px;">
            <i class="bi bi-plus-circle"></i> Add
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <i class="bi bi-list-ul"></i>
                <h4>All Users</h4>
            </div>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" onkeyup="searchUsers()" placeholder="Search users...">
            </div>
        </div>
        
        <div class="card-body" style="padding: 0;">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead  >
                        <tr  style="background-color: #202020;color: #fff; font-size: 22px; font-family: 'JetBrains Mono', monospace;">
                            <th style="text-align: center;color: #fff;"></i> # </th>
                            <th style="text-align: center;color: #fff;">
                                <i class="bi bi-envelope"></i>
                                 Full name && Email 
                                 <i class="bi bi-person-fill"></i>
                                 </th>
                            <th style="text-align: center;color: #fff;"><i class="bi bi-at"></i> Username</th>
                            <th style="text-align: center;color: #fff;"><i class="bi bi-telephone-fill"></i> Phone</th>
                            <th style="text-align: center;color: #fff;"><i class="bi bi-calendar-check-fill"></i> Registered</th>
                            <th style="text-align: center;color: #fff;"><i class="bi bi-toggle-on"></i> Status</th>
                            <th style="text-align: center;color: #fff;"><i class="bi bi-gear-fill"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): 
                            $initial = strtoupper(substr($row['full_name'], 0, 1));
                            $date = date('M d, Y', strtotime($row['created_at']));
                        ?>
                            <tr style="text-align: center;border:1px solid #333;">
                                <td>
                                    <span style="color: #f3f1f1;"><?= $row['id'] ?></span>
                                </td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar"><?= $initial ?></div>
                                        <div class="user-details" style="text-align: center;">
                                            <span class="user-name"><?= htmlspecialchars($row['full_name']) ?></span>
                                            <span class="user-email"><?= htmlspecialchars($row['email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="color: var(--indigo);"></span><?= htmlspecialchars($row['username']) ?>
                                </td>
                                <td></i><?= htmlspecialchars($row['phone']) ?></td>
                                <td></i><?= $date ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] == 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                        <i class="bi <?= $row['status'] == 'active' ? 'bi-check-circle' : 'bi-x-circle' ?>"></i>
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns" style="text-align: center;">
                                        <button class="btn-action btn-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="../peges/edit_user.php?id=<?= $row['id'] ?>" class="btn-action btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="../peges/manage_user.php?action=delete&id=<?= $row['id'] ?>" class="btn-action btn-delete" title="Delete" onclick="return confirm('Delete <?= htmlspecialchars($row['full_name']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="table-footer">
                    <span>Showing <?= $result->num_rows ?> users</span>
                    <span>
                        <i class="bi bi-arrow-left me-2"></i>
                        Page 1 of 1
                        <i class="bi bi-arrow-right ms-2"></i>
                    </span>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No users found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $conn->close(); ?>

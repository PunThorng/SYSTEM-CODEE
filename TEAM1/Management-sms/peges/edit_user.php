<?php
$host = 'localhost';
$user = 'root';
$pass = 'Admin@1234';
$db_name = 'management_restaurant_system';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$id = $_GET['id'] ?? 0;

// Get user data
$sql = "SELECT * FROM registered_users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; min-height: 100vh; font-family: 'Segoe UI', sans-serif; padding: 40px; }
        .container { max-width: 500px; margin: 0 auto; }
        .card { background: #0d0d0d; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden; }
        .card-header { background: #141414; padding: 20px 25px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: space-between; }
        .card-header h4 { color: #fff; display: flex; align-items: center; gap: 10px; }
        .card-header a { color: #666; text-decoration: none; font-size: 20px; }
        .card-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #888; font-size: 13px; margin-bottom: 8px; }
        input, select { width: 100%; padding: 12px 15px; background: #141414; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; font-size: 14px; }
        input:focus, select:focus { outline: none; border-color: #4f6ef7; }
        .btn-save { padding: 14px 30px; background: #4f6ef7; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn-save:hover { background: #3b5ae8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-pencil-square"></i> Edit User</h4>
                <a href="users_list.php"><i class="bi bi-x-lg"></i></a>
            </div>
            <div class="card-body">
                <?php if ($user_data): ?>
                    <form method="POST" action="manage_user.php?action=update">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user_data['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user_data['phone']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active" <?= $user_data['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $user_data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-save"><i class="bi bi-check-circle"></i> Save Changes</button>
                    </form>
                <?php else: ?>
                    <p style="color: #666;">User not found!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

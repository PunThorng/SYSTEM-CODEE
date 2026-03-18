<?php
// Update User Script
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $host = 'localhost';
    $user = 'root';
    $pass = 'Admin@1234';
    $db_name = 'management_restaurant_system';

    $conn = new mysqli($host, $user, $pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = $_POST['id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $status = $_POST['status'];
    
    $sql = "UPDATE registered_users SET full_name = ?, username = ?, email = ?, phone = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $full_name, $username, $email, $phone, $status, $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: users_list.php?updated=1");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: users_list.php?error=Update failed");
        exit();
    }
} else {
    header("Location: users_list.php");
    exit();
}
?>

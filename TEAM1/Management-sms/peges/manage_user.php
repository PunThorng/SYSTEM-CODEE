<?php
// Single file for Update and Delete operations

$host = 'localhost';
$user = 'root';
$pass = 'Admin@1234';
$db_name = 'management_restaurant_system';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// DELETE
if ($action === 'delete' && $id > 0) {
    $sql = "DELETE FROM registered_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: users_list.php?msg=deleted");
        exit();
    }
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
        header("Location: users_list.php?msg=updated");
        exit();
    }
}

$conn->close();
header("Location: users_list.php");
exit();
?>

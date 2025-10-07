<?php
session_start();
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit();
    }

    // Check user in DB
    $query = "SELECT id, name, email, password, role, is_deleted FROM admin WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $db_email, $hashed_password, $role, $is_deleted);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if ($is_deleted == 1) {
            echo json_encode(["success" => false, "message" => "Your account has been deleted."]);
        } elseif (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["name"] = $name;
            $_SESSION["email"] = $db_email;
            $_SESSION["role"] = $role;
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>

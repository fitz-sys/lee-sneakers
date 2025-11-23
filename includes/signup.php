<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        redirect('../index.php');
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        redirect('../index.php');
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long.';
        redirect('../index.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format.';
        redirect('../index.php');
    }

    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Username or email already exists.';
        redirect('../index.php');
    }

// Use plain text password (for testing only)
$sql = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $password, $full_name);


    if ($stmt->execute()) {
        $_SESSION['success'] = 'Account created successfully! Please login.';
        redirect('../index.php');
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        redirect('../index.php');
    }

    $stmt->close();
} else {
    redirect('../index.php');
}
?>